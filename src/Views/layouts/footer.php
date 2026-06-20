<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="footer-logo">A</div>
            <h3>Astral Cloud</h3>
            <p><?= __('footer_tagline') ?></p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4><?= __('footer_platform') ?></h4>
                <a href="/plans"><?= __('nav_plans') ?></a>
                <a href="/docs"><?= __('nav_docs') ?></a>
                <a href="/blog"><?= __('nav_blog') ?></a>
            </div>
            <div class="footer-col">
                <h4><?= __('footer_account') ?></h4>
                <a href="/login"><?= __('nav_login') ?></a>
                <a href="/register"><?= __('nav_register') ?></a>
                <a href="/orders"><?= __('nav_orders') ?></a>
                <a href="/profile"><?= __('nav_profile') ?></a>
            </div>
            <div class="footer-col">
                <h4><?= __('footer_support') ?></h4>
                <a href="/docs#faq"><?= __('docs_faq') ?></a>
                <a href="/docs#connect"><?= __('docs_started') ?></a>
                <a href="/docs#commands"><?= __('docs_commands') ?></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> <?= __('footer_copyright') ?></span>
        <span><?= __('footer_powered') ?></span>
    </div>
</footer>

<script src="/js/app.js?v=<?= filemtime(dirname(__DIR__, 2) . '/js/app.js') ?>"></script>
<script src="/js/cart.js?v=<?= filemtime(dirname(__DIR__, 2) . '/js/cart.js') ?>"></script>
</body>
</html>
