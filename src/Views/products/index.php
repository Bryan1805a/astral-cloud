<div class="text-center mb-5">
    <h1 class="display-4 fw-bold">Deploy the system quickly.</h1>
    <p class="lead text-secondary">Create a powerful Cloud VPS in just 60 seconds.</p>
</div>

<div class="row g-4">
    <?php foreach ($vps_plans as $plan): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card glass-card h-100 text-light p-3">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold text-info">
                        <i class="bi bi-server"></i> <?= htmlspecialchars($plan['name']) ?>
                    </h5>
                    <p class="card-text text-secondary small"><?= htmlspecialchars($plan['description']) ?></p>

                    <ul class="list-unstyled mt-3 mb-4">
                        <li class="mb-2"><i class="bi bi-cpu text-primary me-2"></i> <strong>CPU:</strong> <?= htmlspecialchars($plan['cpu']) ?></li>
                        <li class="mb-2"><i class="bi bi-memory text-primary me-2"></i> <strong>RAM:</strong> <?= htmlspecialchars($plan['ram']) ?></li>
                        <li class="mb-2"><i class="bi bi-hdd-network text-primary me-2"></i> <strong>Storage:</strong> <?= htmlspecialchars($plan['storage']) ?></li>
                        <li class="mb-2"><i class="bi bi-speedometer2 text-primary me-2"></i> <strong>Network:</strong> <?= htmlspecialchars($plan['bandwidth']) ?></li>
                    </ul>

                    <div class="mt-auto border-top border-secondary pt-3 text-center">
                        <div class="price-text mb-3">
                            <?= number_format($plan['price'], 0, ',', '.') ?> VND <span class="fs-6 text-secondary fw-normal">/month</span>
                        </div>

                        <form action="/cart/add" method="POST">
                            <input type="hidden" name="product_id" value="<?= $plan['id'] ?>">
                            <button type="submit" class="btn btn-info w-100 fw-bold">
                                <i class="bi bi-cart-plus"></i> Register configuration
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
