<!-- PARTICLE NETWORK BACKGROUND -->
<canvas id="particleCanvas" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none;"></canvas>

<script>
(function() {
    var canvas = document.getElementById('particleCanvas');
    var ctx = canvas.getContext('2d');
    var particles = [];
    var mouse = { x: -1000, y: -1000 };
    var PARTICLE_COUNT = 60;
    var CONNECT_DIST = 140;
    var LINE_COLOR = 'rgba(255, 255, 255, 0.18)';
    var PARTICLE_COLOR = 'rgba(255, 255, 255, 0.7)';

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    document.addEventListener('mousemove', function(e) {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    function Particle() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.vx = (Math.random() - 0.5) * 0.6;
        this.vy = (Math.random() - 0.5) * 0.6;
        this.radius = Math.random() * 2.5 + 1;
    }

    Particle.prototype.update = function() {
        this.x += this.vx;
        this.y += this.vy;
        if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
        if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
        this.vx += (Math.random() - 0.5) * 0.03;
        this.vy += (Math.random() - 0.5) * 0.03;
        var speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
        var maxSpeed = 1.0;
        if (speed > maxSpeed) { this.vx *= maxSpeed / speed; this.vy *= maxSpeed / speed; }
    };

    for (var i = 0; i < PARTICLE_COUNT; i++) {
        particles.push(new Particle());
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];
            p.update();

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = PARTICLE_COLOR;
            ctx.fill();

            var dx = mouse.x - p.x;
            var dy = mouse.y - p.y;
            var dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < CONNECT_DIST) {
                var alpha = 1 - (dist / CONNECT_DIST);
                ctx.beginPath();
                ctx.moveTo(mouse.x, mouse.y);
                ctx.lineTo(p.x, p.y);
                ctx.strokeStyle = 'rgba(125,211,252,' + (alpha * 0.25).toFixed(2) + ')';
                ctx.lineWidth = 1;
                ctx.stroke();
            }
        }

        requestAnimationFrame(draw);
    }

    draw();
})();
</script>

<!-- PACKAGES PREVIEW -->
<section id="features" class="page-section package-section">
    <h2>Our VPS Packages</h2>
    <p>Popular VPS packages for a variety of usage needs.</p>
    <div class="package-grid">
        <?php foreach ($featured_plans as $plan): ?>
            <div class="package-card">
                <div class="package-icon">
                    <?php
                    $icon = str_contains(strtolower($plan['name']), 'starter') ? '🧊'
                        : (str_contains(strtolower($plan['name']), 'basic') ? '🧊'
                        : (str_contains(strtolower($plan['name']), 'pro') ? '💠'
                        : (str_contains(strtolower($plan['name']), 'gaming') ? '🎮'
                        : (str_contains(strtolower($plan['name']), 'business') ? '🔷'
                        : (str_contains(strtolower($plan['name']), 'enterprise') ? '⚡' : '🖥')))));
                    ?>
                    <?= $icon ?>
                </div>
                <h3><?= htmlspecialchars($plan['name']) ?></h3>
                <p><?= htmlspecialchars($plan['ram']) ?> / <?= htmlspecialchars($plan['cpu']) ?> / <?= htmlspecialchars($plan['storage']) ?></p>
                <strong><?= number_format($plan['price'], 0, ',', '.') ?> VND/month</strong>
                <a href="/product?slug=<?= urlencode($plan['slug']) ?>">View plan</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- VPS PLANS (from backend) -->
<section id="plans" class="products-section">
    <h1>All VPS Plans</h1>
    <p>Choose the VPS package that suits your needs.</p>

    <div class="plans-controls">
        <form method="GET" action="/plans#plans" class="plans-search-form">
            <input type="text" name="search" placeholder="Search plans..." value="<?= htmlspecialchars($search) ?>" class="plans-search-input">
            <select name="sort" class="plans-sort-select" onchange="this.form.submit()">
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name: A → Z</option>
            </select>
            <button type="submit" class="plans-search-btn">Search</button>
            <?php if ($search): ?>
                <a href="/plans#plans" class="plans-clear-btn">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($search): ?>
        <p style="text-align:center;color:#6b7280;font-size:13px;margin-bottom:20px;">
            Results for "<strong><?= htmlspecialchars($search) ?></strong>" — <?= count($vps_plans) ?> product(s) found
        </p>
    <?php endif; ?>

    <?php if (empty($vps_plans)): ?>
        <p style="text-align:center;color:#6b7280;padding:40px;">No VPS plans match your search.</p>
    <?php else: ?>
    <div class="vps-grid">
        <?php foreach ($vps_plans as $plan): ?>
            <?php
                $reviews = $product_reviews[$plan['id']] ?? [];
                $reviewCount = count($reviews);
                $avgRating = $reviewCount > 0 ? round(array_sum(array_column($reviews, 'rating')) / $reviewCount, 1) : 0;
            ?>
            <div class="vps-card" id="plan-<?= $plan['id'] ?>"
                 data-reviews='<?= htmlspecialchars(json_encode($reviews), ENT_QUOTES, 'UTF-8') ?>'
                 data-can-review='<?= htmlspecialchars(json_encode($can_review[$plan['id']] ?? null), ENT_QUOTES, 'UTF-8') ?>'>
                <h2><?= htmlspecialchars($plan['name']) ?></h2>
                <p class="description"><?= htmlspecialchars($plan['description']) ?></p>
                <div class="price"><?= number_format($plan['price'], 0, ',', '.') ?> VND<span>/month</span></div>
                <ul>
                    <li><span class="spec-label">CPU</span><span class="spec-value"><?= htmlspecialchars($plan['cpu']) ?></span></li>
                    <li><span class="spec-label">RAM</span><span class="spec-value"><?= htmlspecialchars($plan['ram']) ?></span></li>
                    <li><span class="spec-label">Storage</span><span class="spec-value"><?= htmlspecialchars($plan['storage']) ?></span></li>
                    <li><span class="spec-label">Bandwidth</span><span class="spec-value"><?= htmlspecialchars($plan['bandwidth']) ?></span></li>
                </ul>

                <div class="review-stars-line">
                    <?php if ($reviewCount > 0): ?>
                        <span class="star-rating-line">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= round($avgRating) ? '' : 'empty' ?>">★</span>
                            <?php endfor; ?>
                        </span>
                        <span class="review-avg-line"><?= $avgRating ?></span>
                        <span class="review-count-line">(<?= $reviewCount ?>)</span>
                    <?php else: ?>
                        <span class="review-count-line empty">No reviews</span>
                    <?php endif; ?>
                </div>

                <div class="card-actions" style="margin-top:auto;">
                    <button type="button" class="reviews-btn" data-product="<?= $plan['id'] ?>">
                        <?= $reviewCount > 0 ? 'Reviews (' . $reviewCount . ')' : 'Reviews' ?>
                    </button>
                    <form action="/cart/add" method="POST" class="js-add-cart">
                        <input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="product_id" value="<?= $plan['id'] ?>">
                        <button type="submit" class="plan-btn w-100">Add to Cart</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php
    $queryParams = '';
    if ($search) $queryParams .= '&search=' . urlencode($search);
    if ($sort && $sort !== 'price_asc') $queryParams .= '&sort=' . urlencode($sort);
    ?>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="/plans?page=<?= $page - 1 ?><?= $queryParams ?>#plans" class="pagination-btn">&laquo; Prev</a>
        <?php else: ?>
            <span class="pagination-btn disabled">&laquo; Prev</span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i === $page): ?>
                <span class="pagination-btn active"><?= $i ?></span>
            <?php else: ?>
                <a href="/plans?page=<?= $i ?><?= $queryParams ?>#plans" class="pagination-btn"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="/plans?page=<?= $page + 1 ?><?= $queryParams ?>#plans" class="pagination-btn">Next &raquo;</a>
        <?php else: ?>
            <span class="pagination-btn disabled">Next &raquo;</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Reviews Modal (floating window) -->
    <div class="review-modal-overlay" id="review-modal">
        <div class="review-modal">
            <div class="review-modal-header">
                <h3 id="review-modal-title">Reviews</h3>
                <button type="button" class="review-modal-close" id="review-modal-close">&times;</button>
            </div>
            <div class="review-modal-body" id="review-modal-reviews"></div>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="review-modal-form" id="review-modal-form" style="display:none;">
                <hr class="review-modal-divider">
                <h4>Write a Review</h4>
                <form id="review-form" method="POST" action="/review/submit">
                    <input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="product_id" id="review-product-id">
                    <input type="hidden" name="order_id" id="review-order-id">
                    <div class="review-form-group">
                        <label>Rating</label>
                        <div class="review-rating-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="rv-star-<?= $i ?>" <?= $i === 5 ? 'required' : '' ?>>
                                <label for="rv-star-<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-form-group">
                        <label for="review-comment">Your Review</label>
                        <textarea name="comment" id="review-comment" rows="4" placeholder="Share your experience... (min 10 characters)" required></textarea>
                    </div>
                    <div class="review-form-error" id="review-error" style="display:none;"></div>
                    <div class="review-form-buttons">
                        <button type="button" class="review-cancel-btn" id="review-cancel-btn">Cancel</button>
                        <button type="submit" class="plan-btn">Submit Review</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
