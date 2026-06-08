<h2 class="mb-4 fw-bold text-info"><i class="bi bi-shield-check"></i> Order Confirmation</h2>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="glass-panel p-4 mb-4">
            <h5 class="text-cyan mb-3">Account information</h5>
            <p class="mb-1"><strong>Full name:</strong> <?= htmlspecialchars($currentUser['name']) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($currentUser['email']) ?></p>
            <p class="mb-0">
                <strong>Membership tier:</strong>
                <span class="badge bg-warning text-dark"><?= strtoupper($currentUser['tier']) ?></span>
            </p>
        </div>

        <div class="glass-panel p-4">
            <h5 class="text-cyan mb-3">List of selected VPS Packages</h5>
            <ul class="list-group list-group-flush bg-transparent">
                <?php foreach ($cart_items as $item): ?>
                    <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between align-items-center px-0">
                        <div>
                            <h6 class="mb-0 text-info"><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</h6>
                            <small class="text-secondary"><?= htmlspecialchars($item['cpu']) ?> | <?= htmlspecialchars($item['ram']) ?></small>
                        </div>
                        <span><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>VND</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="glass-panel p-4 mb-4">
            <h5 class="text-cyan mb-3">Voucher</h5>

            <div id="voucher-error" class="alert alert-danger py-2 fs-6" style="display:<?= $voucher_error ? 'block' : 'none' ?>;"><?= htmlspecialchars($voucher_error) ?></div>
            <div id="voucher-success" class="alert alert-success py-2 fs-6" style="display:<?= $voucher_success ? 'block' : 'none' ?>;"><?= htmlspecialchars($voucher_success) ?></div>

            <form id="voucher-form" action="/checkout" method="GET" class="d-flex">
                <input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="text" id="voucher-input" name="voucher" class="form-control bg-dark text-light border-secondary me-2"
                       placeholder="Enter discount code..." value="<?= htmlspecialchars($voucher_code) ?>">
                <button type="submit" class="btn btn-outline-info">Apply</button>
            </form>

            <small class="text-secondary mt-2 d-block">Suggest test: <b>WELCOME10</b> (10% discount)</small>
        </div>

        <div class="glass-panel p-4">
            <h5 class="text-cyan border-bottom border-secondary pb-2 mb-3">Pay</h5>

            <div class="d-flex justify-content-between mb-2 text-secondary">
                <span>Estimated:</span>
                <span id="checkout-subtotal"><?= number_format($subtotal, 0, ',', '.') ?>VND</span>
            </div>

            <div id="discount-row" class="d-flex justify-content-between mb-2 text-success" style="display:<?= $discount_amount > 0 ? 'flex' : 'none' ?>;">
                <span>Discount (<span id="discount-code"><?= htmlspecialchars($voucher_code) ?></span>):</span>
                <span id="discount-amount">- <?= number_format($discount_amount, 0, ',', '.') ?>VND</span>
            </div>

            <div class="d-flex justify-content-between mb-4 border-top border-secondary pt-3">
                <span class="fs-5">Total amount:</span>
                <span class="fs-4 fw-bold text-info" id="checkout-total"><?= number_format($total_price, 0, ',', '.') ?>VND</span>
            </div>

            <form action="/checkout/place-order<?= $voucher_code ? '?voucher=' . urlencode($voucher_code) : '' ?>" method="POST">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label text-secondary">Payment method</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="pay-vnpay" value="vnpay" checked>
                        <label class="form-check-label" for="pay-vnpay">
                            <i class="bi bi-credit-card"></i> VNPay (ATM / Internet Banking)
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Order notes (Optional)</label>
                    <textarea name="note" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Example: Please install Ubuntu 22.04 for me..."></textarea>
                </div>
                <input type="hidden" name="place_order" value="1">
                <button type="submit" class="btn btn-primary w-100 fw-bold py-3 shadow-sm">
                    <i class="bi bi-check-circle"></i> ORDER CONFIRMATION
                </button>
            </form>
        </div>
    </div>
</div>
