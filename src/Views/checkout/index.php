<section class="checkout-section">
    <h1>Checkout</h1>

    <div class="checkout-layout">
        <div class="checkout-form">
            <div style="margin-bottom:24px;">
                <h3 style="font-size:20px;margin-bottom:16px;">Account Information</h3>
                <p style="color:#b8b8b8;"><strong style="color:#fff;">Full name:</strong> <?= htmlspecialchars($currentUser['name']) ?></p>
                <p style="color:#b8b8b8;"><strong style="color:#fff;">Email:</strong> <?= htmlspecialchars($currentUser['email']) ?></p>
                <p style="color:#b8b8b8;">
                    <strong style="color:#fff;">Membership tier:</strong>
                    <span style="display:inline-block;padding:4px 14px;border-radius:999px;font-weight:800;font-size:12px;
                        background:<?= $currentUser['tier'] === 'diamond' ? '#00d4ff' : ($currentUser['tier'] === 'gold' ? '#ffd700' : '#c0c0c0') ?>;
                        color:#000;margin-left:6px;"><?= strtoupper($currentUser['tier']) ?></span>
                </p>
            </div>

            <div>
                <h3 style="font-size:20px;margin-bottom:16px;">List of selected VPS Packages</h3>
                <?php foreach ($cart_items as $item): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
                        <div>
                            <div style="font-weight:600;"><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</div>
                            <div style="color:#b8b8b8;font-size:13px;"><?= htmlspecialchars($item['cpu']) ?> | <?= htmlspecialchars($item['ram']) ?></div>
                        </div>
                        <div style="font-weight:700;"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VND</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <div class="cart-summary" style="margin-bottom:16px;">
                <h3>Voucher</h3>
                <div id="voucher-error" style="display:<?= $voucher_error ? 'block' : 'none' ?>;margin-bottom:12px;padding:10px 14px;border-radius:12px;background:#2a0000;color:#ffb4b4;border:1px solid rgba(255,255,255,0.12);font-size:13px;">
                    <?= htmlspecialchars($voucher_error) ?>
                </div>
                <div id="voucher-success" style="display:<?= $voucher_success ? 'block' : 'none' ?>;margin-bottom:12px;padding:10px 14px;border-radius:12px;background:#002a16;color:#b4ffd8;border:1px solid rgba(255,255,255,0.12);font-size:13px;">
                    <?= htmlspecialchars($voucher_success) ?>
                </div>

                <form id="voucher-form" action="/checkout" method="GET" style="display:flex;gap:8px;">
                    <input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="text" id="voucher-input" name="voucher" placeholder="Enter discount code..."
                           value="<?= htmlspecialchars($voucher_code) ?>"
                           style="flex:1;padding:14px 16px;border-radius:12px;border:1px solid rgba(255,255,255,0.14);background:#0b0b0b;color:#fff;font-family:inherit;">
                    <button type="submit" style="padding:14px 24px;border-radius:12px;border:1px solid #38bdf8;background:transparent;color:#38bdf8;font-weight:700;cursor:pointer;">Apply</button>
                </form>
                <small style="color:#6b7280;margin-top:8px;display:block;">Suggest test: <strong>WELCOME10</strong> (10% discount)</small>
            </div>

            <div class="cart-summary">
                <h3>Pay</h3>

                <div class="summary-row"><span>Estimated:</span><span id="checkout-subtotal"><?= number_format($subtotal, 0, ',', '.') ?> VND</span></div>

                <div id="discount-row" class="summary-row" style="color:#4ade80;display:<?= $discount_amount > 0 ? 'flex' : 'none' ?>;">
                    <span>Discount (<span id="discount-code"><?= htmlspecialchars($voucher_code) ?></span>):</span>
                    <span id="discount-amount">- <?= number_format($discount_amount, 0, ',', '.') ?> VND</span>
                </div>

                <div class="total-row"><span>Total amount:</span><span id="checkout-total"><?= number_format($total_price, 0, ',', '.') ?> VND</span></div>

                <form action="/checkout/place-order" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="voucher" id="hidden-voucher" value="<?= htmlspecialchars($voucher_code) ?>">
                    <input type="hidden" name="place_order" value="1">
                    <div class="form-group">
                        <label>Order notes (Optional)</label>
                        <textarea name="note" rows="2" placeholder="Example: Please install Ubuntu 22.04 for me..." style="width:100%;padding:14px 16px;border-radius:12px;border:1px solid rgba(255,255,255,0.14);background:#0b0b0b;color:#fff;font-family:inherit;"></textarea>
                    </div>
                    <div style="margin:16px 0 12px;">
                        <div style="color:#b8b8b8;font-size:13px;margin-bottom:8px;">Payment method</div>
                        <div style="display:flex;align-items:center;gap:8px;padding:12px 16px;border-radius:12px;background:rgba(255,255,255,0.05);">
                            <input type="radio" name="payment_method" value="vnpay" checked style="accent-color:#38bdf8;">
                            <span>VNPay (ATM / Internet Banking)</span>
                        </div>
                    </div>
                    <button type="submit" class="checkout-btn" style="width:100%;border:none;font-size:16px;padding:18px;">
                        ORDER CONFIRMATION
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
