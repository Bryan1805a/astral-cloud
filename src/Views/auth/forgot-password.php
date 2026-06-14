<section class="auth-section">
    <div class="auth-card">
        <h1>Forgot Password</h1>
        <p>Enter your email and we'll send you a reset link.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="/forgot-password" method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="auth-btn">Send Reset Link</button>
        </form>
        <p class="auth-link">Remember your password? <a href="/login">Login</a></p>
    </div>
</section>
