<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="footer-logo">A</div>
            <h3>Astral Cloud</h3>
            <p>High-performance VPS hosting for students, developers, and businesses. Deploy your server in minutes.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Platform</h4>
                <a href="/plans">VPS Plans</a>
                <a href="/docs">Documentation</a>
                <a href="/blog">Blog</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
                <a href="/orders">My Orders</a>
                <a href="/profile">Profile</a>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <a href="/docs#faq">FAQ</a>
                <a href="/docs#connect">Getting Started</a>
                <a href="/docs#commands">Linux Commands</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> Astral Cloud. All rights reserved.</span>
        <span>Powered by PHP + MySQL + VMware</span>
    </div>
</footer>

<script src="/js/app.js?v=<?= filemtime(dirname(__DIR__, 2) . '/js/app.js') ?>"></script>
<script src="/js/cart.js?v=<?= filemtime(dirname(__DIR__, 2) . '/js/cart.js') ?>"></script>
</body>
</html>
