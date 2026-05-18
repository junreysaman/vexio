<?= $this->start('content') ?>

<main class="auth-shell">
    <section class="auth-copy">
        <a class="auth-brand" href="/">VEXIO<span>HD</span></a>
        <div class="paper-kicker"><i class="icon-person_add"></i> Standard Account</div>
        <h1>Join Vexio</h1>
        <p>Registration creates a standard user account only. Admin access is assigned separately by the site owner.</p>
    </section>

    <section class="auth-panel" aria-label="Registration form">
        <div class="auth-panel-head">
            <strong>Create account</strong>
            <span>Viewer profile</span>
        </div>

        <form class="auth-form" action="/register" method="POST">
            <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">

            <div class="auth-grid">
                <label>
                    <span>First name</span>
                    <input type="text" name="first_name" value="<?= escape($oldFormData['first_name'] ?? '') ?>" autocomplete="given-name" required>
                </label>

                <label>
                    <span>Last name</span>
                    <input type="text" name="last_name" value="<?= escape($oldFormData['last_name'] ?? '') ?>" autocomplete="family-name" required>
                </label>
            </div>

            <label>
                <span>Username</span>
                <input type="text" name="username" value="<?= escape($oldFormData['username'] ?? '') ?>" autocomplete="username" required>
            </label>

            <label>
                <span>Email</span>
                <input type="email" name="email" value="<?= escape($oldFormData['email'] ?? '') ?>" autocomplete="email" required>
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" autocomplete="new-password" minlength="8" required>
            </label>

            <label>
                <span>Confirm password</span>
                <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
            </label>

            <button class="paper-btn primary" type="submit">Create standard account</button>
        </form>

        <p class="auth-footnote">Already have an account? <a href="/login">Sign in</a>.</p>
    </section>
</main>

<?= $this->end() ?>
