<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Agendamento;

final class AgendamentoController extends Controller
{
    private Agendamento $agendamentoModel;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->agendamentoModel = new Agendamento();
    }

    /**
     * Processar POST de novo agendamento
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/agendamento');
            return;
        }

        // Validar dados
        $dados = $this->validarDados();
        
        if (!is_array($dados)) {
            // Se houver erro de validação, redirecionar com mensagem
            Session::flash('error', $dados);
            $this->redirect('/agendamento');
            return;
        }

        // Verificar conflitos de agendamento
        if (!$this->horarioCabeNaJanelaDeAtendimento($dados['data'], $dados['horario'], $dados['duracao'])) {
            Session::flash('error', '❌ Este horário não comporta a duração da revisão dentro do expediente.');
            $this->redirect('/agendamento');
            return;
        }

        if ($this->agendamentoModel->temConflito($dados['data'], $dados['horario'], $dados['duracao'])) {
            Session::flash('error', '❌ Este horário já está reservado. Por favor, escolha outro horário ou data.');
            $this->redirect('/agendamento');
            return;
        }

        try {
            // Incluir ID do usuário se autenticado
            if (Auth::check()) {
                $dados['user_id'] = Auth::user()['id'];
            }

            // Criar agendamento
            $agendamentoId = $this->agendamentoModel->create($dados);

            // Enviar email de confirmação (implementar depois)
            $this->enviarEmailConfirmacao($dados, $agendamentoId);

            Session::flash('success', '✅ Agendamento realizado com sucesso! Você receberá uma confirmação por e-mail.');
            $this->redirect('/agendamento');
        } catch (\Exception $e) {
            Session::flash('error', '❌ Erro ao processar agendamento. Tente novamente.');
            $this->redirect('/agendamento');
        }
    }

    /**
     * Verificar disponibilidade de horário (API)
     */
    public function verificarDisponibilidade(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
            exit;
        }

        // Ler dados JSON
        $input = json_decode(file_get_contents('php://input'), true);

        $data = $input['data'] ?? '';
        $horario = $input['horario'] ?? '';
        $duracao = (int) ($input['duracao'] ?? 30); // minutos

        // Validar entrada
        if (!$data || !$horario || $duracao <= 0 || !$this->validarData($data) || !$this->validarHorario($horario, $data) || !$this->horarioCabeNaJanelaDeAtendimento($data, $horario, $duracao)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados inválidos']);
            exit;
        }

        // Verificar conflitos
        $temConflito = $this->agendamentoModel->temConflito($data, $horario, $duracao);

        header('Content-Type: application/json');
        echo json_encode([
            'disponivel' => !$temConflito,
            'data' => $data,
            'horario' => $horario,
            'duracao' => $duracao
        ]);
        exit;
    }

    /**
     * Validar dados do formulário
     */
    private function validarDados(): array|string
    {
        $validator = new Validator();

        // Campo: Nome
        $nome = trim($_POST['nome'] ?? '');
        if (strlen($nome) < 3 || strlen($nome) > 100) {
            return 'Nome deve ter entre 3 e 100 caracteres.';
        }

        // Campo: Email
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido.';
        }

        // Campo: Telefone
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            return 'Telefone inválido. Deve incluir DDD.';
        }
        $telefone = '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);

        // Campo: Marca/Modelo
        $marca = trim($_POST['marca'] ?? '');
        if (strlen($marca) < 3 || strlen($marca) > 100) {
            return 'Marca/Modelo deve ter entre 3 e 100 caracteres.';
        }

        // Campo: Ano
        $ano = (int) ($_POST['ano'] ?? 0);
        if ($ano < 1990 || $ano > 2100) {
            return 'Ano inválido.';
        }

        // Campo: Chassis
        $chassi = trim($_POST['chassi'] ?? '');
        if (strlen($chassi) < 5 || strlen($chassi) > 30) {
            return 'Chassis/VIN deve ter entre 5 e 30 caracteres.';
        }

        // Campo: Placa
        $placa = preg_replace('/[^A-Z0-9]/', '', mb_strtoupper($_POST['placa'] ?? ''));
        if (strlen($placa) < 6 || strlen($placa) > 8) {
            return 'Placa deve ter 6 a 8 caracteres.';
        }
        $placa = $this->formatarPlaca($placa);

        // Campo: Quilometragem
        $quilometragem = (int) ($_POST['quilometragem'] ?? 0);
        if ($quilometragem < 0 || $quilometragem > 999999) {
            return 'Quilometragem inválida.';
        }

        // Campo: Revisão
        $revisoes_validas = [1000, 6000, 12000, 18000, 24000, 30000, 36000, 42000, 48000, 54000];
        $revisao = (int) ($_POST['revisao'] ?? 0);
        if (!in_array($revisao, $revisoes_validas)) {
            return 'Tipo de revisão inválido.';
        }

        // Calcular duração
        $duracao = in_array($revisao, [12000, 18000, 24000, 30000, 36000, 42000, 48000, 54000]) ? 120 : 20;

        // Campo: Data
        $data = $_POST['data'] ?? '';
        if (!$this->validarData($data)) {
            return 'Data inválida ou fora do horário de atendimento.';
        }

        // Campo: Horário
        $horario = $_POST['horario'] ?? '';
        if (!$this->validarHorario($horario, $data)) {
            return 'Horário inválido.';
        }

        // Campo: Observações (opcional)
        $observacoes = trim($_POST['observacoes'] ?? '');
        if (strlen($observacoes) > 500) {
            return 'Observações não podem exceder 500 caracteres.';
        }

        return [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'marca' => $marca,
            'ano' => $ano,
            'chassi' => $chassi,
            'placa' => $placa,
            'quilometragem' => $quilometragem,
            'revisao' => $revisao,
            'data' => $data,
            'horario' => $horario,
            'duracao' => $duracao,
            'observacoes' => $observacoes ?: null,
        ];
    }

    /**
     * Validar data (deve ser segunda a sábado e no futuro)
     */
    private function validarData(string $data): bool
    {
        try {
            $dataObj = \DateTime::createFromFormat('Y-m-d', $data);
            if (!$dataObj || $dataObj->format('Y-m-d') !== $data) {
                return false;
            }

            // Verificar se é data futura
            $hoje = new \DateTime();
            $hoje->setTime(0, 0, 0);

            if ($dataObj <= $hoje) {
                return false;
            }

            // Aceitar segunda (1) a sábado (6), bloquear apenas domingo (0)
            $diaSemana = (int) $dataObj->format('w');
            return $diaSemana !== 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validar horário conforme o dia da semana
     * Seg–Sex: 07h–13h e 15h–17h | Sáb: 07h–11h
     */
    private function validarHorario(string $horario, string $data = ''): bool
    {
        $horariosSemanais = [
            '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
            '15:00', '15:30', '16:00', '16:30'
        ];

        $horariosSabado = [
            '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30'
        ];

        if ($data !== '') {
            $dataObj = \DateTime::createFromFormat('Y-m-d', $data);
            if ($dataObj && (int) $dataObj->format('w') === 6) {
                return in_array($horario, $horariosSabado);
            }
        }

        return in_array($horario, $horariosSemanais);
    }

    private function horarioCabeNaJanelaDeAtendimento(string $data, string $horario, int $duracaoMinutos): bool
    {
        $inicio = \DateTime::createFromFormat('Y-m-d H:i', $data . ' ' . $horario);
        if (!$inicio) {
            return false;
        }

        $fim = clone $inicio;
        $fim->modify("+{$duracaoMinutos} minutes");
        $diaSemana = (int) $inicio->format('w');

        if ($diaSemana === 6) {
            $inicioSabado = \DateTime::createFromFormat('Y-m-d H:i', $data . ' 07:00');
            $fimSabado = \DateTime::createFromFormat('Y-m-d H:i', $data . ' 11:00');
            return $inicio >= $inicioSabado && $fim <= $fimSabado;
        }

        $inicioManha = \DateTime::createFromFormat('Y-m-d H:i', $data . ' 07:00');
        $fimManha = \DateTime::createFromFormat('Y-m-d H:i', $data . ' 13:00');
        $inicioTarde = \DateTime::createFromFormat('Y-m-d H:i', $data . ' 15:00');
        $fimTarde = \DateTime::createFromFormat('Y-m-d H:i', $data . ' 17:00');

        $cabeNaManha = $inicio >= $inicioManha && $fim <= $fimManha;
        $cabeNaTarde = $inicio >= $inicioTarde && $fim <= $fimTarde;

        return $cabeNaManha || $cabeNaTarde;
    }

    /**
     * Formatar placa (ABC-1234 ou ABCD1234)
     */
    private function formatarPlaca(string $placa): string
    {
        if (strlen($placa) === 7) {
            return substr($placa, 0, 3) . '-' . substr($placa, 3);
        }
        return $placa;
    }

    /**
     * Enviar email de confirmação
     */
    private function enviarEmailConfirmacao(array $dados, int $agendamentoId): void
    {
        // TODO: Implementar envio de email
        // Usar uma biblioteca como PHPMailer ou SwiftMailer
        
        $assunto = 'Confirmação de Agendamento - Atlântica Motos';
        $mensagem = "
            Olá {$dados['nome']},
            
            Seu agendamento foi confirmado!
            
            Dados do Agendamento:
            - ID: {$agendamentoId}
            - Data: " . $this->formatarDataBr($dados['data']) . "
            - Horário: {$dados['horario']}
            - Revisão: {$dados['revisao']} km
            - Motocicleta: {$dados['marca']} ({$dados['ano']})
            - Placa: {$dados['placa']}
            
            Horário de Atendimento:
            Segunda a Sexta: 7h às 13h e 15h às 17h
            
            Obrigado por confiar em nós!
            Atlântica Motos
        ";

        // mail($dados['email'], $assunto, $mensagem);
    }

    /**
     * Formatar data para formato brasileiro
     */
    private function formatarDataBr(string $data): string
    {
        return (new \DateTime($data))->format('d/m/Y');
    }
}
