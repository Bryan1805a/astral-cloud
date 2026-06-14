<section class="auth-section">
    <div class="auth-card">
        <h1>Reset Password</h1>
        <p>Enter your new password below.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($token): ?>
        <form action="/reset-password?token=<?= urlencode($token) ?>" method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Min. 6 characters" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Re-enter password" required minlength="6">
            </div>
            <button type="submit" class="auth-btn">Reset Password</button>
        </form>
        <?php endif; ?>
        <p class="auth-link"><a href="/login">Back to Login</a></p>
    </div>
</section>
