<h2 class="mb-4 fw-bold text-cyan"><i class="bi bi-cart3"></i> Your shopping cart</h2>

<?php if (isset($_GET['err']) && $_GET['err'] === 'insufficient_stock'): ?>
    <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show">
        Some items in your cart have insufficient stock. Please adjust your order.
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'added_success'): ?>
    <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show">
        VPS added to cart successfully!
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'removed_success'): ?>
    <div class="alert alert-warning bg-warning text-dark border-0 alert-dismissible fade show">
        The product has been removed from the shopping cart.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>
    <div class="glass-panel p-5 text-center">
        <i class="bi bi-cart-x text-secondary" style="font-size: 4rem;"></i>
        <h4 class="mt-3 text-secondary">The shopping cart is empty.</h4>
        <p>You haven't selected a server configuration to initialize yet.</p>
        <a href="/" class="btn btn-primary mt-2">View VPS packages</a>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="glass-panel p-4">
                <div class="table-responsive">
                    <table class="table table-glass mb-0">
                        <thead>
                            <tr>
                                <th>VPS configuration</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Estimated price</th>
                                <th class="text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-info"><?= htmlspecialchars($item['name']) ?></div>
                                        <small class="text-secondary"><?= htmlspecialchars($item['cpu']) ?> | <?= htmlspecialchars($item['ram']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary fs-6"><?= $item['quantity'] ?></span>
                                    </td>
                                    <td class="text-end text-secondary">
                                        <?= number_format($item['price'], 0, ',', '.') ?> VND
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VND
                                    </td>
                                    <td class="text-center">
                                        <form action="/cart/remove" method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove" onclick="return confirm('Do you want to remove this package from your cart?');">
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
            <div class="glass-panel p-4 position-sticky" style="top: 20px;">
                <h4 class="mb-4 text-cyan border-bottom border-secondary pb-2">Order Summary</h4>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Estimated price:</span>
                    <span class="fw-bold"><?= number_format($total_price, 0, ',', '.') ?> VND</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-secondary">VAT (0%):</span>
                    <span class="fw-bold">0 VND</span>
                </div>

                <div class="d-flex justify-content-between mb-4 border-top border-secondary pt-3">
                    <span class="fs-5">Total:</span>
                    <span class="fs-4 fw-bold text-info"><?= number_format($total_price, 0, ',', '.') ?> VND</span>
                </div>

                <a href="/checkout" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                    <i class="bi bi-credit-card"></i> Proceed to Payment
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
