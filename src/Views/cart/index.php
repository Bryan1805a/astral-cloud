<section class="cart-section">
    <h1>Shopping Cart</h1>

    <div id="cart-empty-state"<?= empty($cart_items) ? '' : ' style="display:none;"' ?>>
        <div class="cart-item">
            <div>
                <h2>Cart is empty</h2>
                <p>Select a VPS package to get started.</p>
            </div>
        </div>
        <a href="/" class="checkout-btn" style="display:inline-block;margin-top:16px;">Browse VPS Plans</a>
    </div>

    <div id="cart-content"<?= empty($cart_items) ? ' style="display:none;"' : '' ?>>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item js-cart-row" style="margin-bottom:16px;">
                        <div>
                            <h2><?= htmlspecialchars($item['name']) ?></h2>
                            <p><?= htmlspecialchars($item['cpu']) ?> | <?= htmlspecialchars($item['ram']) ?></p>
                            <div class="qty-controls" style="margin-top:12px;">
                                <button type="button" class="js-qty-minus" data-product-id="<?= $item['product_id'] ?>">−</button>
                                <span class="js-qty-input"><?= $item['quantity'] ?></span>
                                <button type="button" class="js-qty-plus" data-product-id="<?= $item['product_id'] ?>">+</button>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div class="item-price js-item-total"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VND</div>
                            <div style="color:#b8b8b8;font-size:13px;margin-top:4px;"><?= number_format($item['price'], 0, ',', '.') ?> VND / unit</div>
                            <form action="/cart/remove" method="POST" class="js-remove-cart" style="margin-top:12px;">
                                <input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" style="background:none;border:1px solid rgba(255,255,255,0.14);color:#ef4444;padding:8px 16px;border-radius:8px;cursor:pointer;font-size:13px;">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Estimated price:</span><span id="cart-subtotal-amount"><?= number_format($total_price, 0, ',', '.') ?> VND</span></div>
                <div class="summary-row"><span>VAT (0%):</span><span>0 VND</span></div>
                <div class="total-row"><span>Total:</span><span id="cart-total-amount"><?= number_format($total_price, 0, ',', '.') ?> VND</span></div>
                <a href="/checkout" class="checkout-btn">Proceed to Checkout</a>
            </div>
        </div>
    </div>
</section>
