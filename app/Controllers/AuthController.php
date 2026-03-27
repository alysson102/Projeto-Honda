<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class AuthController extends Controller
{
    private const DEFAULT_MAX_LOGIN_ATTEMPTS = 5;
    private const DEFAULT_LOGIN_WINDOW_SECONDS = 900;
    private const DEFAULT_LOGIN_LOCKOUT_SECONDS = 900;

    public function showLogin(): void
    {
        $this->redirect('/');
    }

    public function login(): void
    {
        $data = [
            'email' => mb_strtolower(trim((string) $this->request->input('email', ''))),
            'password' => (string) $this->request->input('password', ''),
        ];
        $rateKey = $this->loginRateKey($data['email']);

        if ($this->isLoginBlocked($rateKey)) {
            Session::flash('error', 'Muitas tentativas de login. Tente novamente em alguns minutos.');
            $this->redirect('/');
        }

        $validator = new Validator();
        $valid = $validator->validate($data, [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        if (!$valid) {
            $this->registerFailedLogin($rateKey);
            $_SESSION['_old'] = ['email' => $data['email']];
            Session::flash('error', 'Dados inválidos.');
            $this->redirect('/');
        }

        $loginAttempt = Auth::attemptWithResult($data['email'], $data['password']);

        if (!$loginAttempt['success']) {
            $this->registerFailedLogin($rateKey);
            $_SESSION['_old'] = ['email' => $data['email']];

            if ($loginAttempt['reason'] === 'not_registered') {
                Session::flash('error', 'Este usuário não possui cadastro.');
                $this->redirect('/');
            }

            Session::flash('error', 'Email ou senha inválidos.');
            $this->redirect('/');
        }

        $this->clearLoginRateLimit($rateKey);

        Session::flash('success', 'Login realizado com sucesso.');
        $this->redirect('/');
    }

    public function register(): void
    {
        $normalizedPhone = $this->normalizeBrazilianPhone((string) $this->request->input('telefone', ''));

        $data = [
            'name' => trim((string) $this->request->input('name', '')),
            'email' => mb_strtolower(trim((string) $this->request->input('email', ''))),
            'telefone' => $normalizedPhone,
            'password' => (string) $this->request->input('password', ''),
        ];

        $validator = new Validator();
        $valid = $validator->validate($data, [
            'name' => ['required', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'telefone' => ['required'],
            'password' => ['required', 'min:12', 'max:255'],
        ]);

        if (!$valid) {
            $this->flashRegisterError($validator->errors(), $data);
            return;
        }

        if (!$this->isValidBrazilianPhone($data['telefone'])) {
            $this->storeOldRegisterInput($data);
            Session::flash('error', 'Telefone inválido. Use DDD + número (ex: (11) 91234-5678).');
            $this->redirect('/register');
        }

        if (!$this->isStrongPassword($data['password'], $data['name'], $data['email'], $data['telefone'])) {
            $this->storeOldRegisterInput($data);
            Session::flash('error', 'Use uma senha com letras maiúsculas, minúsculas, números e símbolo, sem repetir dados pessoais.');
            $this->redirect('/register');
        }

        $userModel = new User();

        if ($userModel->findByEmail($data['email']) !== null) {
            $this->storeOldRegisterInput($data);
            Session::flash('error', 'Este e-mail já está cadastrado.');
            $this->redirect('/register');
        }

        $hashAlgorithm = config('security.password_algo', PASSWORD_BCRYPT);
        $passwordHash = password_hash($data['password'], $hashAlgorithm);

        if ($passwordHash === false) {
            $this->storeOldRegisterInput($data);
            Session::flash('error', 'Não foi possível processar a senha informada.');
            $this->redirect('/register');
        }

        try {
            $createdUserId = $userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'telefone' => $data['telefone'],
                'password' => $passwordHash,
            ]);
        } catch (\Throwable) {
            $this->storeOldRegisterInput($data);
            Session::flash('error', 'Não foi possível concluir o cadastro no momento. Tente novamente.');
            $this->redirect('/register');
        }

        if ($createdUserId <= 0) {
            $this->storeOldRegisterInput($data);
            Session::flash('error', 'Não foi possível concluir o cadastro no momento.');
            $this->redirect('/register');
        }

        $_SESSION['_old'] = [
            'email' => $data['email'],
        ];

        Session::flash('success', 'Cadastro realizado com sucesso. Faça seu login para continuar.');
        $this->redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Sessão encerrada.');
        $this->redirect('/');
    }

    private function loginRateKey(string $email): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $normalizedEmail = strtolower(trim($email));

        return hash('sha256', $ip . '|' . $normalizedEmail);
    }

    private function isLoginBlocked(string $key): bool
    {
        $store = $_SESSION['_login_rate_limit'][$key] ?? null;

        if (!is_array($store)) {
            return false;
        }

        $blockedUntil = (int) ($store['blocked_until'] ?? 0);
        $now = time();

        return $blockedUntil > $now;
    }

    private function registerFailedLogin(string $key): void
    {
        $maxAttempts = (int) config('security.login_max_attempts', self::DEFAULT_MAX_LOGIN_ATTEMPTS);
        $windowSeconds = (int) config('security.login_attempt_window_seconds', self::DEFAULT_LOGIN_WINDOW_SECONDS);
        $lockoutSeconds = (int) config('security.login_lockout_seconds', self::DEFAULT_LOGIN_LOCKOUT_SECONDS);
        $now = time();

        $store = $_SESSION['_login_rate_limit'][$key] ?? [
            'attempts' => 0,
            'window_started_at' => $now,
            'blocked_until' => 0,
        ];

        $windowStartedAt = (int) ($store['window_started_at'] ?? $now);
        if (($now - $windowStartedAt) > $windowSeconds) {
            $store['attempts'] = 0;
            $store['window_started_at'] = $now;
            $store['blocked_until'] = 0;
        }

        $store['attempts'] = (int) ($store['attempts'] ?? 0) + 1;

        if ($store['attempts'] >= $maxAttempts) {
            $store['blocked_until'] = $now + $lockoutSeconds;
            $store['attempts'] = 0;
            $store['window_started_at'] = $now;
        }

        $_SESSION['_login_rate_limit'][$key] = $store;
    }

    private function clearLoginRateLimit(string $key): void
    {
        unset($_SESSION['_login_rate_limit'][$key]);
    }

    private function flashRegisterError(array $errors, array $data): void
    {
        $this->storeOldRegisterInput($data);

        foreach ($errors as $fieldErrors) {
            if (is_array($fieldErrors) && isset($fieldErrors[0])) {
                Session::flash('error', (string) $fieldErrors[0]);
                $this->redirect('/register');
            }
        }

        Session::flash('error', 'Dados de cadastro inválidos.');
        $this->redirect('/register');
    }

    private function storeOldRegisterInput(array $data): void
    {
        $_SESSION['_old'] = [
            'name' => $data['name'],
            'email' => $data['email'],
            'telefone' => $this->formatBrazilianPhone($data['telefone']),
        ];
    }

    private function normalizeBrazilianPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (!is_string($digits)) {
            return '';
        }

        // Accept optional country code 55 and normalize to national number only.
        if (str_starts_with($digits, '55') && (mb_strlen($digits) === 12 || mb_strlen($digits) === 13)) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    private function isValidBrazilianPhone(string $phoneDigits): bool
    {
        // BR phone: DDD (11-99) + 8-digit landline (2-8...) OR 9-digit mobile (9...).
        return preg_match('/^[1-9][1-9](?:9\d{8}|[2-8]\d{7})$/', $phoneDigits) === 1;
    }

    private function formatBrazilianPhone(string $phoneDigits): string
    {
        if (!is_string($phoneDigits)) {
            return '';
        }

        if (mb_strlen($phoneDigits) === 11) {
            return sprintf('(%s) %s-%s', substr($phoneDigits, 0, 2), substr($phoneDigits, 2, 5), substr($phoneDigits, 7, 4));
        }

        if (mb_strlen($phoneDigits) === 10) {
            return sprintf('(%s) %s-%s', substr($phoneDigits, 0, 2), substr($phoneDigits, 2, 4), substr($phoneDigits, 6, 4));
        }

        return $phoneDigits;
    }

    private function isStrongPassword(string $password, string $name, string $email, string $phone): bool
    {
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        if (!preg_match('/\d/', $password)) {
            return false;
        }

        if (!preg_match('/[^a-zA-Z\d]/', $password)) {
            return false;
        }

        $passwordLower = mb_strtolower($password);
        $personalFragments = [
            mb_strtolower($name),
            mb_strtolower($email),
            preg_replace('/\D+/', '', $phone),
        ];

        foreach ($personalFragments as $fragment) {
            if (!is_string($fragment)) {
                continue;
            }

            $fragment = trim($fragment);
            if ($fragment !== '' && mb_strlen($fragment) >= 3 && str_contains($passwordLower, $fragment)) {
                return false;
            }
        }

        return true;
    }
}
