<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private const DUMMY_PASSWORD_HASH = '$2y$10$YiZANMbzJJrbv1TpFCgX5.ITWCDDYaz53l4q7BV80ZNO8oUS/WC0.';

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $attemptResult = self::attemptWithResult($email, $password);

        return $attemptResult['success'];
    }

    /**
     * @return array{success: bool, reason: 'ok'|'not_registered'|'invalid_password'}
     */
    public static function attemptWithResult(string $email, string $password): array
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Always verify against a hash to reduce timing differences when the user does not exist.
        $hashToVerify = is_array($user) ? (string) $user['password'] : self::DUMMY_PASSWORD_HASH;
        $passwordIsValid = password_verify($password, $hashToVerify);

        if (!$user) {
            return [
                'success' => false,
                'reason' => 'not_registered',
            ];
        }

        if (!$passwordIsValid) {
            return [
                'success' => false,
                'reason' => 'invalid_password',
            ];
        }

        $passwordAlgorithm = config('security.password_algo', PASSWORD_BCRYPT);
        if (password_needs_rehash($user['password'], $passwordAlgorithm)) {
            $stmt = Database::connection()->prepare('UPDATE users SET password = :password WHERE id = :id');
            $stmt->execute([
                'password' => password_hash($password, $passwordAlgorithm),
                'id' => $user['id'],
            ]);
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'telefone' => $user['telefone'],
            'profile_photo' => $user['profile_photo'] ?? null,
        ];

        return [
            'success' => true,
            'reason' => 'ok',
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
