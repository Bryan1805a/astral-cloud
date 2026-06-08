<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <h2 class="text-center mb-4 text-primary">Verify Your Account</h2>

        <p class="text-center text-secondary mb-4">
            A verification code has been sent to<br>
            <strong class="text-light"><?= htmlspecialchars($masked_email) ?></strong>
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($otp_dev): ?>
            <div class="alert alert-info">
                <strong>Dev mode — OTP:</strong>
                <span style="font-size:1.5rem;font-weight:bold;letter-spacing:4px;display:block;margin-top:4px;"><?= htmlspecialchars($otp_dev) ?></span>
            </div>
        <?php endif; ?>

        <div class="card bg-secondary text-light p-4 shadow">
            <form action="/verify-otp" method="POST">
                <?= csrfField() ?>
                <div class="mb-4">
                    <label class="form-label">Enter 6-digit verification code</label>
                    <input type="text" name="otp" class="form-control bg-dark text-light border-secondary text-center fs-3" maxlength="6" autocomplete="off" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary w-100">Verify Account</button>
            </form>
        </div>
    </div>
</div>
