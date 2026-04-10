
<?php



use App\Core\Auth;
use App\Core\Session;

$title = $title ?? 'Projeto Honda';
$currentRequestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$basePath = base_url_path();

if ($basePath !== '' && str_starts_with($currentRequestPath, $basePath)) {
    $currentRequestPath = (string) substr($currentRequestPath, strlen($basePath));
}

$currentRequestPath = '/' . trim($currentRequestPath, '/');
$currentRequestPath = $currentRequestPath === '/' ? '/' : rtrim($currentRequestPath, '/');
$isGuestLandingPage = $currentRequestPath === '/' && !Auth::check();

$errorMessage = Session::flash('error');
$successMessage = Session::flash('success');

$isActive = static function (string $path) use ($currentRequestPath): bool {
    $normalizedPath = $path === '/' ? '/' : rtrim($path, '/');
    return $currentRequestPath === $normalizedPath;
};

$homeActive = $isActive('/');
$aboutActive = $isActive('/about');
$contactActive = $isActive('/contact');
$registerActive = $isActive('/register');
$profileActive = $isActive('/perfil');
$isRegisterPage = $registerActive;
$shouldApplyMobileFooterSpacing = in_array($currentRequestPath, ['/register', '/about', '/contact', '/agendamento', '/perfil', '/info-revisoes'], true);
$isPecasPage = $currentRequestPath === '/pecas';
$bodyClasses = [$isGuestLandingPage ? 'page-home' : 'page-inner'];

if ($shouldApplyMobileFooterSpacing) {
    $bodyClasses[] = 'page-mobile-footer-spacing';
}

if ($currentRequestPath === '/' && Auth::check()) {
    $bodyClasses[] = 'page-index';
}

if ($isPecasPage) {
    $bodyClasses[] = 'page-pecas';
}

if ($currentRequestPath === '/agendamento') {
    $bodyClasses[] = 'page-agendamento';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?> - <?= e((string) config('app.name')) ?></title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= e(url('/assets/imagens/favicon-192.png')) ?>">
    <link rel="icon" type="image/png" sizes="64x64" href="<?= e(url('/assets/imagens/favicon-64.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(url('/assets/imagens/favicon-32.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(url('/assets/imagens/apple-touch-icon.png')) ?>">
    <link rel="preload" as="image" href="<?= e(url('/assets/imagens/optimized/logo_site10_opt.png')) ?>" fetchpriority="high">
    <?php if ($isGuestLandingPage): ?>
        <link rel="preload" as="image" href="<?= e(url('/assets/imagens/optimized/baner_04_q88.jpg')) ?>" fetchpriority="high">
    <?php endif; ?>
    <meta name="theme-color" content="#b30000">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
    <meta name="author" content="Alysson Souza" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
	<meta name="keywords" content="palavras-chave,do,site">
	<meta name="description" content="Descrição do website">

    
</head>
<body class="<?= e(implode(' ', $bodyClasses)) ?>">
    <header class="topbar">
    

            <div class="logo">
                <img src="<?= e(url('/assets/imagens/optimized/logo_site10_opt.png')) ?>" alt="Logo Atlântica Motos" width="560" height="373" loading="eager" decoding="async" fetchpriority="high"></img>
            </div>

            <button class="menu-toggle" type="button" aria-label="Abrir menu" aria-controls="site-menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!--<div class="logo">
                <video autoplay muted loop>
                    <source src="<//?= e(url('/assets/imagens/logo_site11.mp4')) ?>" type="video/mp4">
                </video>
            </div>-->

                <nav class="nav" id="site-menu" aria-label="Menu principal">
                    <a class="<?= $homeActive ? 'is-active' : '' ?>" href="<?= e(url('/')) ?>" <?= $homeActive ? 'aria-current="page"' : '' ?>>Home</a>
                    <a class="<?= $aboutActive ? 'is-active' : '' ?>" href="<?= e(url('/about')) ?>" <?= $aboutActive ? 'aria-current="page"' : '' ?>>Sobre</a>
                    <a class="<?= $contactActive ? 'is-active' : '' ?>" href="<?= e(url('/contact')) ?>" <?= $contactActive ? 'aria-current="page"' : '' ?>>Contato</a>
    
                    <?php if (Auth::check()): ?>
                        <a class="nav-profile-link <?= $profileActive ? 'is-active' : '' ?>" href="<?= e(url('/perfil')) ?>" <?= $profileActive ? 'aria-current="page"' : '' ?>>Perfil</a>
                        <form action="<?= e(url('/logout')) ?>" method="post" class="nav-logout-form">
                            <?= App\Core\Csrf::field() ?>
                            <button type="submit" class="nav-logout-btn">Sair</button>
                        </form>
                    <?php else: ?>
                        
                        <a class="<?= $registerActive ? 'is-active' : '' ?>" href="<?= e(url('/register')) ?>" <?= $registerActive ? 'aria-current="page"' : '' ?>>Registrar</a>
                    <?php endif; ?>
            </nav>
            <div class="clear"></div>
      
    </header>

    <?php if ($isGuestLandingPage): ?>
        <section class="baner">
            <div class="ban-lef">
                <img src="<?= e(url('/assets/imagens/optimized/baner_04_q88.jpg')) ?>" alt="Banner Atlântica Motos" width="1400" height="933" loading="eager" decoding="async" fetchpriority="high" />
            </div>

            <div class="form-login">
            
                <div class="form-login-conteudo">

                    <form action="<?= e(url('/login')) ?>" method="post">
                        <?= App\Core\Csrf::field() ?>
                        <?php if ($errorMessage !== null || $successMessage !== null): ?>
                            <div class="form-login-feedback">
                                <?php if ($errorMessage !== null): ?>
                                    <div class="alert alert-error"><?= e($errorMessage) ?></div>
                                <?php endif; ?>

                                <?php if ($successMessage !== null): ?>
                                    <div class="alert alert-success"><?= e($successMessage) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h2>Faça seu login</h2>

                        <div class="email-row">
                            <input type="email" placeholder=" " name="email" required value="<?= old('email') ?>"><span class="user-ani">E-mail</span>
                        </div>
                        <div class="password-field">
                            <input id="login-password" type="password" placeholder=" " name="password" required>
                            <span class="user-ani">Senha</span>
                            <button
                                type="button"
                                class="password-toggle"
                                data-target="#login-password"
                                aria-controls="login-password"
                                aria-label="Mostrar senha"
                                aria-pressed="false"
                                title="Mostrar senha"
                            >
                                <span class="password-icon password-icon-show" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="20" height="20" focusable="false">
                                        <path d="M12 5C6 5 2.2 10 2 12c.2 2 4 7 10 7s9.8-5 10-7c-.2-2-4-7-10-7zm0 2c4.8 0 8 3.8 8.9 5-.9 1.2-4.1 5-8.9 5s-8-3.8-8.9-5c.9-1.2 4.1-5 8.9-5z" fill="currentColor"></path>
                                        <circle class="eye-pupil" cx="12" cy="12" r="2.1" fill="currentColor"></circle>
                                    </svg>
                                </span>
                                <span class="password-icon password-icon-hide" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="20" height="20" focusable="false">
                                        <path d="M3.3 4.7 2 6l3 3C3.4 10.4 2.2 11.8 2 13c.2 2 4 7 10 7 2.3 0 4.2-.7 5.8-1.8l2.9 2.8L22 19.7 3.3 4.7zM8.6 12.6a3.5 3.5 0 0 0 2.8 2.8L8.6 12.6zM12 7c6 0 9.8 5 10 7-.1 1.1-1.1 2.7-2.8 4.1l-1.4-1.4c1-.9 1.7-1.9 1.9-2.7-.2-2-4-7-10-7-.9 0-1.8.1-2.6.4L5.5 6C7.4 5.4 9.5 5 12 5z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <button type="submit" class="login-submit">Entrar</button>
                        <div class="login-access-links" aria-label="Links de acesso">
                            <a href="<?= e(url('/register')) ?>" class="login-access-link">Registrar</a>
                            <a href="#" class="login-access-link" onclick="return false;" aria-disabled="true">Esqueceu a senha?</a>
                        </div>
                    </form>
                </div>

            </div>

            <div class="clear"></div>
        </section>
    <?php endif; ?>

    <main>
        <?php if (!$isGuestLandingPage && ($errorMessage !== null || $successMessage !== null)): ?>
            <section class="flash-messages<?= $isRegisterPage ? ' register-flash-messages' : '' ?>" aria-live="polite">
                <?php if ($errorMessage !== null): ?>
                    <div class="alert alert-error"><?= e($errorMessage) ?></div>
                <?php endif; ?>

                <?php if ($successMessage !== null): ?>
                    <div class="alert alert-success"><?= e($successMessage) ?></div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (!$isGuestLandingPage): ?>
            <?= $contentHtml ?>
        <?php endif; ?>
    </main>


    <div class="footer">
        <p>&copy; <?= date('Y') ?> Atlântica Motos. Todos os direitos reservados.</p>
        <p>Sua segurança é nossa prioridade!</p>
    </div>

    <script src="<?= e(url('/assets/js/app.js')) ?>" defer></script>



</body>
</html>