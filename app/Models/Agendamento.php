<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Agendamento extends Model
{
    /**
     * Criar agendamento com proteção contra corrida por data.
     * Retorna false se o horário ficou indisponível durante a tentativa.
     */
    public function createSemConflito(array $data): int|false
    {
        $lockName = 'agendamento:' . preg_replace('/[^0-9]/', '', (string) ($data['data'] ?? ''));
        $lockStmt = $this->db->prepare('SELECT GET_LOCK(:lock_name, :timeout) AS acquired');
        $lockStmt->execute([
            'lock_name' => $lockName,
            'timeout' => 5,
        ]);

        $lockResult = $lockStmt->fetch();
        $acquired = (int) ($lockResult['acquired'] ?? 0) === 1;

        if (!$acquired) {
            return false;
        }

        try {
            $duracao = (int) ($data['duracao'] ?? $this->calcularDuracao((int) ($data['revisao'] ?? 0)));
            if ($this->temConflito((string) $data['data'], (string) $data['horario'], $duracao)) {
                return false;
            }

            $data['duracao'] = $duracao;
            return $this->create($data);
        } finally {
            try {
                $releaseStmt = $this->db->prepare('SELECT RELEASE_LOCK(:lock_name)');
                $releaseStmt->execute(['lock_name' => $lockName]);
            } catch (\Throwable) {
                // Em caso de falha, o lock ainda sera liberado ao encerrar a conexao.
            }
        }
    }

    /**
     * Criar novo agendamento
     */
    public function create(array $data): int
    {
        $duracao = (int) ($data['duracao'] ?? $this->calcularDuracao((int) ($data['revisao'] ?? 0)));

        $stmt = $this->db->prepare(
            'INSERT INTO agendamentos (
                nome, email, telefone, marca_modelo, ano_moto, chassi, placa, 
                quilometragem, tipo_revisao, data_agendamento, horario_inicio, 
                duracao_minutos, observacoes, user_id
            ) VALUES (
                :nome, :email, :telefone, :marca_modelo, :ano_moto, :chassi, :placa,
                :quilometragem, :tipo_revisao, :data_agendamento, :horario_inicio,
                :duracao_minutos, :observacoes, :user_id
            )'
        );

        $stmt->execute([
            'nome' => $data['nome'],
            'email' => mb_strtolower(trim($data['email'])),
            'telefone' => $data['telefone'],
            'marca_modelo' => $data['marca'],
            'ano_moto' => (int) $data['ano'],
            'chassi' => mb_strtoupper(trim($data['chassi'])),
            'placa' => mb_strtoupper(str_replace('-', '', trim($data['placa']))),
            'quilometragem' => (int) $data['quilometragem'],
            'tipo_revisao' => (int) $data['revisao'],
            'data_agendamento' => $data['data'],
            'horario_inicio' => $data['horario'],
            'duracao_minutos' => $duracao,
            'observacoes' => $data['observacoes'] ?? null,
            'user_id' => $data['user_id'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Verificar conflito de agendamento
     */
    public function temConflito(string $data, string $horaInicio, int $duracao): bool
    {
        // $duracao agora em MINUTOS
        $horaFinObj = \DateTime::createFromFormat('H:i', $horaInicio);
        $horaFinObj->modify("+{$duracao} minutes");
        $horaFim = $horaFinObj->format('H:i:s');
        $horaInicio = $horaInicio . ':00';

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as conflitos FROM agendamentos 
            WHERE data_agendamento = :data 
            AND status IN ("pendente", "confirmado")
            AND (
                horario_inicio < :hora_fim
                AND ADDTIME(horario_inicio, SEC_TO_TIME(duracao_minutos * 60)) > :hora_inicio
            )'
        );

        $stmt->execute([
            'data' => $data,
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
        ]);

        $resultado = $stmt->fetch();
        return (int) $resultado['conflitos'] > 0;
    }

    /**
     * Calcular duração da revisão em horas
     */
    private function calcularDuracao(int $km): int
    {
        return match($km) {
            1000, 6000 => 20,
            12000, 18000, 24000, 30000, 36000, 42000, 48000, 54000 => 120, // 2 horas
            default => 20
        };
    }

    /**
     * Buscar agendamentos por email
     */
    public function findByEmail(string $email): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos 
            WHERE email = :email 
            ORDER BY data_agendamento DESC, horario_inicio DESC'
        );

        $stmt->execute(['email' => mb_strtolower(trim($email))]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Buscar agendamentos por placa
     */
    public function findByPlaca(string $placa): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos 
            WHERE placa = :placa 
            ORDER BY data_agendamento DESC'
        );

        $stmt->execute(['placa' => mb_strtoupper(str_replace('-', '', $placa))]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Buscar agendamentos por data
     */
    public function findByData(string $data): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM agendamentos 
            WHERE data_agendamento = :data 
            AND status IN ("pendente", "confirmado")
            ORDER BY horario_inicio ASC'
        );

        $stmt->execute(['data' => $data]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Obter agendamento por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM agendamentos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $agendamento = $stmt->fetch();

        return $agendamento ?: null;
    }

    /**
     * Cancelar agendamento
     */
    public function cancelar(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE agendamentos SET status = :status WHERE id = :id'
        );

        return $stmt->execute([
            'status' => 'cancelado',
            'id' => $id
        ]);
    }

    /**
     * Confirmar agendamento
     */
    public function confirmar(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE agendamentos SET status = :status WHERE id = :id'
        );

        return $stmt->execute([
            'status' => 'confirmado',
            'id' => $id
        ]);
    }

    /**
     * Buscar todos os agendamentos com filtro de status
     */
    public function findAll(?string $status = null): array
    {
        if ($status) {
            $stmt = $this->db->prepare(
                'SELECT * FROM agendamentos WHERE status = :status ORDER BY data_agendamento DESC'
            );
            $stmt->execute(['status' => $status]);
        } else {
            $stmt = $this->db->prepare('SELECT * FROM agendamentos ORDER BY data_agendamento DESC');
            $stmt->execute();
        }

        return $stmt->fetchAll() ?: [];
    }
}
