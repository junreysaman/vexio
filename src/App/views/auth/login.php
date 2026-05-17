<?= $this->start('content') ?>

<main class="auth-shell">
    <section class="auth-copy">
        <a class="auth-brand" href="/">VEXIO<span>HD</span></a>
        <div class="paper-kicker"><i class="icon-lock"></i> Admin Access</div>
        <h1>Vexio Control Room</h1>
        <p>Sign in to manage licensed movies, TV episodes, publishing status, and view trends.</p>
    </section>

    <section class="auth-panel" aria-label="Login form">
        <div class="auth-panel-head">
            <strong>Login</strong>
            <span>Admin and user accounts</span>
        </div>

        <form class="auth-form" action="/authentication/v3/login" method="POST">
            <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">

            <label>
                <span>Username or email</span>
                <input type="text" name="identity" value="<?= escape($oldFormData['identity'] ?? '') ?>" autocomplete="username" required>
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <button class="paper-btn primary" type="submit">Sign in</button>
        </form>

        <p class="auth-footnote">Need a standard viewer account? <a href="/register">Create one</a>.</p>
    </section>
</main>

<?= $this->end() ?>
