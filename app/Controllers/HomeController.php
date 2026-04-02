<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

final class HomeController extends Controller
{
    public function index(): void
    {
        if (!Auth::check()) {
            $this->view('home/landing', [
                'title' => 'Início',
            ]);
            return;
        }

        $userModel = new User();

        $this->view('home/index', [
            'title' => 'Início',
            'user'  => Auth::user(),
            'users' => $userModel->findAll(),
        ]);
    }

    public function agendamento(): void
    {
        $this->view('home/agendamento', [
            'title' => 'Agendamento',
            'user' => Auth::user(),
        ]);
    }

    public function about(): void
    {
        $this->view('home/about', [
            'title' => 'Sobre',
            'user' => Auth::user(),
        ]);
    }

    public function pecas(): void
    {
        $this->view('home/pecas', [
            'title' => 'Peças Originais',
            'user' => Auth::user(),
        ]);
    }

    public function contact(): void
    {
        $this->view('home/contact', [
            'title' => 'Contato',
            'user' => Auth::user(),
        ]);
    }

    public function register(): void
    {
        $this->view('home/register', [
            'title' => 'Registrar',
            'user' => Auth::user(),
        ]);
    }

}
