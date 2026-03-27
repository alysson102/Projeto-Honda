<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, telefone, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => mb_strtolower(trim($email))]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, telefone, password) VALUES (:name, :email, :telefone, :password)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'telefone' => $data['telefone'],
            'password' => $data['password'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findAll(): array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, created_at FROM users ORDER BY id ASC');
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}