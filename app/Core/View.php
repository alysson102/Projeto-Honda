<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = base_path('app/Views/' . $view . '.php');

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'View inválida.';
            return;
        }

        extract($data, EXTR_SKIP);

        // Renderiza a view primeiro e injeta somente o HTML final no layout.
        ob_start();
        extract($data, EXTR_SKIP);
        require $viewPath;
        $contentHtml = (string) ob_get_clean();

        $layoutPath = base_path('app/Views/layouts/main.php');
        require $layoutPath;

        unset($_SESSION['_old']);
    }
}
