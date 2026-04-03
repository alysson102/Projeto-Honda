<section class="register-section"> 

    <div class="form-register">
        
        <form action="<?= e(url('/register')) ?>" method="post">
            <h2>Cadastre-se</h2>
            <?= App\Core\Csrf::field() ?>
            <div class="register-field">
                <input type="text" placeholder=" " name="name" autocomplete="name" required maxlength="120" value="<?= old('name') ?>">
                <span class="user-ani">Nome Completo</span>
            </div>
            <div class="register-field">
                <input type="email" placeholder=" " name="email" autocomplete="email" required maxlength="180" value="<?= old('email') ?>">
                <span class="user-ani">E-mail</span>
            </div>
            <div class="register-field">
                <input type="tel" placeholder=" " name="telefone" autocomplete="tel" inputmode="numeric" pattern="^\(?[1-9]{2}\)?\s?(?:9\d{4}|[2-8]\d{3})-?\d{4}$" title="Use um telefone brasileiro com DDD, ex: (11) 91234-5678" required minlength="14" maxlength="15" value="<?= old('telefone') ?>">
                <span class="user-ani">Telefone (WhatsApp)</span>
            </div>
            <div class="password-field">
                <input id="register-password" type="password" placeholder=" " name="password" autocomplete="new-password" required minlength="12" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{12,}$" title="Use ao menos 12 caracteres com maiúscula, minúscula, número e símbolo">
                <span class="user-ani">Senha</span>
                <button
                    type="button"
                    class="password-toggle"
                    data-target="#register-password"
                    aria-controls="register-password"
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
            <button type="submit" class="register-submit">Registrar</button>
        </form>

    </div>

</section>