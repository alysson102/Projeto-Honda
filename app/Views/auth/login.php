<section class="card card-sm">
    <h1>Login</h1>
    <form action="<?= e(url('/login')) ?>" method="post">
        <?= App\Core\Csrf::field() ?>

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" required value="<?= old('email') ?>">

        <label for="password">Senha</label>
        <input id="password" name="password" type="password" minlength="8" required>

        <button type="submit">Entrar</button>
    </form>
</section>
