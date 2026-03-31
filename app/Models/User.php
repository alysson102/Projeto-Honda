<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, telefone, profile_photo, password FROM users WHERE email = :email LIMIT 1');
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

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, telefone, profile_photo, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function emailExistsForAnotherUser(string $email, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute([
            'email' => mb_strtolower(trim($email)),
            'id' => $userId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = :name, email = :email, telefone = :telefone WHERE id = :id'
        );

        return $stmt->execute([
            'name' => $data['name'],
            'email' => mb_strtolower(trim((string) $data['email'])),
            'telefone' => $data['telefone'],
            'id' => $userId,
        ]);
    }

    public function updateProfilePhoto(int $userId, ?string $profilePhoto): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET profile_photo = :profile_photo WHERE id = :id');

        return $stmt->execute([
            'profile_photo' => $profilePhoto,
            'id' => $userId,
        ]);
    }
}