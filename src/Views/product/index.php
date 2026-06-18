<section class="page-section" style="padding-top:120px;">
    <div class="product-detail-container">
        <a href="/plans" class="back-link-detail">← Back to Plans</a>

        <div class="product-detail-header">
            <div class="product-detail-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <p class="product-detail-desc"><?= htmlspecialchars($product['description']) ?></p>
                <div class="product-detail-price"><?= number_format($product['price'], 0, ',', '.') ?> VND<span>/month</span></div>

                <div class="product-detail-specs">
                    <div class="spec-item">
                        <span class="spec-icon">⚡</span>
                        <div><strong><?= htmlspecialchars($product['cpu']) ?></strong><small>CPU</small></div>
                    </div>
                    <div class="spec-item">
                        <span class="spec-icon">🧠</span>
                        <div><strong><?= htmlspecialchars($product['ram']) ?></strong><small>RAM</small></div>
                    </div>
                    <div class="spec-item">
                        <span class="spec-icon">💾</span>
                        <div><strong><?= htmlspecialchars($product['storage']) ?></strong><small>Storage</small></div>
                    </div>
                    <div class="spec-item">
                        <span class="spec-icon">🌐</span>
                        <div><strong><?= htmlspecialchars($product['bandwidth']) ?></strong><small>Bandwidth</small></div>
                    </div>
                </div>

                <div class="product-detail-stock">
                    <?php if ((int)$product['stock'] > 0): ?>
                        <span style="color:#4ade80;">● In stock</span> (<?= (int)$product['stock'] ?> available)
                    <?php else: ?>
                        <span style="color:#ef4444;">● Out of stock</span>
                    <?php endif; ?>
                </div>

                <form action="/cart/add" method="POST" class="js-add-cart" style="margin-top:20px;">
                    <input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" class="plan-btn" style="padding:14px 40px;font-size:15px;" <?= (int)$product['stock'] <= 0 ? 'disabled style="opacity:0.4;cursor:not-allowed;padding:14px 40px;font-size:15px;"' : '' ?>>
                        <?= (int)$product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart — ' . number_format($product['price'], 0, ',', '.') . ' VND' ?>
                    </button>
                </form>
            </div>
            <div class="product-detail-visual">
                <div class="product-orb"></div>
                <div class="product-shape"></div>
            </div>
        </div>

        <!-- Reviews -->
        <div class="product-detail-reviews">
            <h2>Customer Reviews</h2>
            <?php if (empty($reviews)): ?>
                <p class="review-empty-text">No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="review-author"><?= htmlspecialchars($review['user_name']) ?></span>
                                <span class="review-date"><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                            </div>
                            <div class="review-item-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i > $review['rating'] ? 'empty' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <p class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.product-detail-container { max-width: 960px; margin: 0 auto; padding: 0 24px 64px; }
.back-link-detail { display: inline-block; margin-bottom: 24px; color: #6b7280; font-size: 13px; text-decoration: none; font-weight: 600; }
.back-link-detail:hover { color: #38bdf8; }
.product-detail-header { display: flex; gap: 48px; align-items: center; flex-wrap: wrap; }
.product-detail-info { flex: 1; min-width: 300px; }
.product-detail-info h1 { font-size: 36px; font-weight: 900; margin: 0 0 12px; }
.product-detail-desc { font-size: 15px; color: #94a3b8; line-height: 1.7; margin: 0 0 20px; }
.product-detail-price { font-size: 36px; font-weight: 900; color: #38bdf8; margin-bottom: 24px; }
.product-detail-price span { font-size: 16px; font-weight: 400; color: #6b7280; margin-left: 6px; }
.product-detail-specs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
.spec-item { display: flex; align-items: center; gap: 10px; padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; }
.spec-icon { font-size: 20px; }
.spec-item strong { display: block; font-size: 14px; color: #e2e8f0; }
.spec-item small { font-size: 11px; color: #64748b; }
.product-detail-stock { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
.product-detail-visual { flex: 0 0 260px; display: flex; align-items: center; justify-content: center; }
.product-orb { width: 200px; height: 200px; border-radius: 50%; background: radial-gradient(circle at 40% 40%, rgba(56,189,248,0.25), rgba(15,23,42,0) 70%); animation: pulseOrb 3s ease-in-out infinite; }
@keyframes pulseOrb { 0%,100% { transform:scale(1); opacity:0.6; } 50% { transform:scale(1.1); opacity:1; } }
.product-detail-reviews { margin-top: 48px; padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.06); }
.product-detail-reviews h2 { font-size: 22px; font-weight: 800; margin-bottom: 20px; }
@media (max-width: 768px) {
    .product-detail-header { flex-direction: column-reverse; }
    .product-orb { width: 140px; height: 140px; }
}
</style>
