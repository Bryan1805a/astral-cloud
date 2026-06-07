<h2 class="mb-4 fw-bold text-cyan"><i class="bi bi-cart3"></i> Your shopping cart</h2>

<div id="cart-empty-state" class="glass-panel p-5 text-center"<?= empty($cart_items) ? '' : ' style="display:none;"' ?>>
    <i class="bi bi-cart-x text-secondary icon-xl"></i>
    <h4 class="mt-3 text-secondary">The shopping cart is empty.</h4>
    <p>You haven't selected a server configuration to initialize yet.</p>
    <a href="/" class="btn btn-primary mt-2">View VPS packages</a>
</div>

<div id="cart-content"<?= empty($cart_items) ? ' style="display:none;"' : '' ?>>
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="glass-panel p-4">
                <div class="table-responsive">
                    <table class="table table-glass mb-0">
                        <thead>
                            <tr>
                                <th>VPS configuration</th>
                                <th class="text-center" style="width:140px;">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Estimated price</th>
                                <th class="text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody class="js-cart-body">
                            <?php foreach ($cart_items as $item): ?>
                                <tr class="js-cart-row">
                                    <td>
                                        <div class="fw-bold text-info"><?= htmlspecialchars($item['name']) ?></div>
                                        <small class="text-secondary"><?= htmlspecialchars($item['cpu']) ?> | <?= htmlspecialchars($item['ram']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm justify-content-center">
                                            <button class="btn btn-outline-secondary js-qty-minus" data-product-id="<?= $item['product_id'] ?>" type="button">-</button>
                                            <input type="text" class="form-control bg-dark text-light border-secondary text-center js-qty-input" style="width:40px;max-width:40px;" value="<?= $item['quantity'] ?>" readonly>
                                            <button class="btn btn-outline-secondary js-qty-plus" data-product-id="<?= $item['product_id'] ?>" type="button">+</button>
                                        </div>
                                    </td>
                                    <td class="text-end text-secondary">
                                        <?= number_format($item['price'], 0, ',', '.') ?> VND
                                    </td>
                                    <td class="text-end fw-bold js-item-total">
                                        <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VND
                                    </td>
                                    <td class="text-center">
                                        <form action="/cart/remove" method="POST" class="js-remove-cart">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-panel p-4 sticky-summary">
                <h4 class="mb-4 text-cyan border-bottom border-secondary pb-2">Order Summary</h4>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Estimated price:</span>
                    <span class="fw-bold" id="cart-subtotal-amount"><?= number_format($total_price, 0, ',', '.') ?> VND</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-secondary">VAT (0%):</span>
                    <span class="fw-bold">0 VND</span>
                </div>

                <div class="d-flex justify-content-between mb-4 border-top border-secondary pt-3">
                    <span class="fs-5">Total:</span>
                    <span class="fs-4 fw-bold text-info" id="cart-total-amount"><?= number_format($total_price, 0, ',', '.') ?> VND</span>
                </div>

                <a href="/checkout" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                    <i class="bi bi-credit-card"></i> Proceed to Payment
                </a>
            </div>
        </div>
    </div>
</div>
