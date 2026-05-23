<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <h2 class="text-center mb-4 text-primary">Astral Cloud Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
            <div class="alert alert-success">Registration successful! Please log in.</div>
        <?php endif; ?>

        <div class="card bg-secondary text-light p-4 shadow">
            <form action="/login" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>

                <div class="text-center mt-3">
                    <a href="/register" class="text-info text-decoration-none">Don't have an account yet? Register now!</a>
                </div>
            </form>
        </div>
    </div>
</div>
