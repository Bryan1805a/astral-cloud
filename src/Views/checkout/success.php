<div class="row justify-content-center mt-5">
    <div class="col-md-8 col-lg-6 text-center">
        <div class="glass-panel p-5">
            <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
            <h2 class="mt-3 fw-bold text-success">Order placed successfully!</h2>
            <p class="text-secondary mb-1">Order ID: <strong class="text-light">#<?= htmlspecialchars($order['id']) ?></strong></p>
            <p class="text-secondary mb-4">Total amount: <strong class="text-info"><?= number_format($order['total_price'], 0, ',', '.') ?> VND</strong></p>

            <a href="/" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-house"></i> Back to Home
            </a>
        </div>
    </div>
</div>
