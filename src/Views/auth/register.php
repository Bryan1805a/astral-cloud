<div class="row justify-content-center mt-5">
    <div class="col-md-8 col-lg-5">
        <h2 class="text-center mb-4 text-primary">Create an Astral Cloud account</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card bg-secondary text-light p-4 shadow">
            <form action="/register" method="POST">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control bg-dark text-light border-secondary" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone number</label>
                    <input type="text" name="phone" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</div>
