<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Agendamento;
use App\Models\User;

final class ProfileController extends Controller
{
    private const MAX_PHOTO_SIZE_BYTES = 2097152;

    public function index(): void
    {
        $authUser = Auth::user();
        if (!is_array($authUser) || !isset($authUser['id'], $authUser['email'])) {
            $this->redirect('/');
            return;
        }

        $userModel = new User();
        $agendamentoModel = new Agendamento();

        $user = $userModel->findById((int) $authUser['id']);
        if ($user === null) {
            Auth::logout();
            Session::flash('error', 'Sua sessão foi encerrada. Faça login novamente.');
            $this->redirect('/');
            return;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'telefone' => $user['telefone'],
            'profile_photo' => $user['profile_photo'] ?? null,
        ];

        $agendamentos = $agendamentoModel->findByUserContext((int) $user['id'], (string) $user['email']);

        $this->view('home/profile', [
            'title' => 'Meu Perfil',
            'user' => $user,
            'agendamentos' => $agendamentos,
        ]);
    }

    public function update(): void
    {
        $authUser = Auth::user();
        if (!is_array($authUser) || !isset($authUser['id'])) {
            $this->redirect('/');
            return;
        }

        $data = [
            'name' => trim((string) $this->request->input('name', '')),
            'email' => mb_strtolower(trim((string) $this->request->input('email', ''))),
            'telefone' => $this->normalizeBrazilianPhone((string) $this->request->input('telefone', '')),
        ];

        $_SESSION['_old'] = [
            'name' => $data['name'],
            'email' => $data['email'],
            'telefone' => $this->formatBrazilianPhone($data['telefone']),
        ];

        if (mb_strlen($data['name']) < 3 || mb_strlen($data['name']) > 120) {
            Session::flash('error', 'Nome deve ter entre 3 e 120 caracteres.');
            $this->redirect('/perfil');
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'E-mail inválido.');
            $this->redirect('/perfil');
            return;
        }

        if (!$this->isValidBrazilianPhone($data['telefone'])) {
            Session::flash('error', 'Telefone inválido. Use DDD + número (ex: (11) 91234-5678).');
            $this->redirect('/perfil');
            return;
        }

        $userModel = new User();

        if ($userModel->emailExistsForAnotherUser($data['email'], (int) $authUser['id'])) {
            Session::flash('error', 'Este e-mail já está em uso por outra conta.');
            $this->redirect('/perfil');
            return;
        }

        $updated = $userModel->updateProfile((int) $authUser['id'], $data);
        if (!$updated) {
            Session::flash('error', 'Não foi possível atualizar seus dados no momento.');
            $this->redirect('/perfil');
            return;
        }

        $_SESSION['user'] = [
            'id' => $authUser['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'telefone' => $data['telefone'],
            'profile_photo' => $authUser['profile_photo'] ?? null,
        ];

        unset($_SESSION['_old']);

        Session::flash('success', 'Dados do perfil atualizados com sucesso.');
        $this->redirect('/perfil');
    }

    public function updatePhoto(): void
    {
        $authUser = Auth::user();
        if (!is_array($authUser) || !isset($authUser['id'])) {
            $this->redirect('/');
            return;
        }

        if (!isset($_FILES['profile_photo']) || !is_array($_FILES['profile_photo'])) {
            Session::flash('error', 'Selecione uma imagem para atualizar sua foto de perfil.');
            $this->redirect('/perfil');
            return;
        }

        $file = $_FILES['profile_photo'];
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($errorCode !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Não foi possível enviar a imagem. Verifique o arquivo e tente novamente.');
            $this->redirect('/perfil');
            return;
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $fileSize = (int) ($file['size'] ?? 0);

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            Session::flash('error', 'Arquivo de upload inválido.');
            $this->redirect('/perfil');
            return;
        }

        if ($fileSize <= 0 || $fileSize > self::MAX_PHOTO_SIZE_BYTES) {
            Session::flash('error', 'A foto deve ter no máximo 2MB.');
            $this->redirect('/perfil');
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($tmpName);

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowedMimeTypes[$mimeType])) {
            Session::flash('error', 'Formato não permitido. Use JPG, PNG ou WEBP.');
            $this->redirect('/perfil');
            return;
        }

        $extension = $allowedMimeTypes[$mimeType];
        $uploadDir = base_path('public/assets/imagens/perfis');

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            Session::flash('error', 'Não foi possível preparar o diretório de upload.');
            $this->redirect('/perfil');
            return;
        }

        $newFileName = sprintf('user-%d-%d.%s', (int) $authUser['id'], time(), $extension);
        $destinationPath = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

        if (!move_uploaded_file($tmpName, $destinationPath)) {
            Session::flash('error', 'Falha ao salvar a foto de perfil.');
            $this->redirect('/perfil');
            return;
        }

        $publicPath = '/assets/imagens/perfis/' . $newFileName;
        $userModel = new User();

        if (!$userModel->updateProfilePhoto((int) $authUser['id'], $publicPath)) {
            @unlink($destinationPath);
            Session::flash('error', 'Não foi possível atualizar sua foto no perfil.');
            $this->redirect('/perfil');
            return;
        }

        $oldPhoto = isset($authUser['profile_photo']) && is_string($authUser['profile_photo'])
            ? trim($authUser['profile_photo'])
            : '';

        if ($oldPhoto !== '' && str_starts_with($oldPhoto, '/assets/imagens/perfis/')) {
            $oldPhotoPath = base_path('public' . $oldPhoto);
            if (is_file($oldPhotoPath) && $oldPhotoPath !== $destinationPath) {
                @unlink($oldPhotoPath);
            }
        }

        $_SESSION['user']['profile_photo'] = $publicPath;

        Session::flash('success', 'Foto de perfil atualizada com sucesso.');
        $this->redirect('/perfil');
    }

    public function deleteAppointment(): void
    {
        $authUser = Auth::user();
        if (!is_array($authUser) || !isset($authUser['id'], $authUser['email'])) {
            $this->redirect('/');
            return;
        }

        $appointmentId = (int) $this->request->input('agendamento_id', 0);
        if ($appointmentId <= 0) {
            Session::flash('error', 'Agendamento inválido.');
            $this->redirect('/perfil');
            return;
        }

        $agendamentoModel = new Agendamento();
        $deleted = $agendamentoModel->deleteByIdAndUserContext(
            $appointmentId,
            (int) $authUser['id'],
            (string) $authUser['email']
        );

        if (!$deleted) {
            Session::flash('error', 'Não foi possível excluir o agendamento selecionado.');
            $this->redirect('/perfil');
            return;
        }

        Session::flash('success', 'Agendamento excluído com sucesso.');
        $this->redirect('/perfil');
    }

    private function normalizeBrazilianPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (!is_string($digits)) {
            return '';
        }

        if (str_starts_with($digits, '55') && (mb_strlen($digits) === 12 || mb_strlen($digits) === 13)) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    private function isValidBrazilianPhone(string $phoneDigits): bool
    {
        return preg_match('/^[1-9][1-9](?:9\d{8}|[2-8]\d{7})$/', $phoneDigits) === 1;
    }

    private function formatBrazilianPhone(string $phoneDigits): string
    {
        if (mb_strlen($phoneDigits) === 11) {
            return sprintf('(%s) %s-%s', substr($phoneDigits, 0, 2), substr($phoneDigits, 2, 5), substr($phoneDigits, 7, 4));
        }

        if (mb_strlen($phoneDigits) === 10) {
            return sprintf('(%s) %s-%s', substr($phoneDigits, 0, 2), substr($phoneDigits, 2, 4), substr($phoneDigits, 6, 4));
        }

        return $phoneDigits;
    }
}
