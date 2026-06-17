<?php
    /**
     * Astral Cloud — Application Entry Point (OOP MVC)
     */

    use App\Core\Router;
    use App\Core\Response;

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();

    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/config/db.php';

    // ── Global helpers (compatibility shims for legacy controllers) ──
    $_response = new Response();

    function view(string $view, array $data = []): void {
        global $_response;
        $_response->render($view, $data);
    }

    function generateCsrfToken(): string {
        global $_response;
        return $_response->csrfToken();
    }

    function csrfField(): string {
        global $_response;
        return $_response->csrfField();
    }

    function verifyCsrfToken(): void {
        global $_response;
        $_response->verifyCsrf(new \App\Core\Request());
    }

    function jsonResponse(array $data, int $status = 200): void {
        global $_response;
        $_response->json($data, $status);
    }

    function isAjaxRequest(): bool {
        return (new \App\Core\Request())->isAjaxRequest();
    }

    $router = new Router();

    // Routes for user
    $router->get('/',           \App\Controllers\ProductController::class, 'index');
    $router->get('/plans',      \App\Controllers\ProductController::class, 'plans');
    $router->get('/product',    \App\Controllers\ProductController::class, 'detail');
    $router->get('/blog',       \App\Controllers\ProductController::class, 'blog');
    $router->get('/docs',       \App\Controllers\ProductController::class, 'docs');

    $router->get('/api/health', \App\Controllers\HealthController::class, 'index');

    $router->get('/login',      \App\Controllers\AuthController::class, 'login');
    $router->post('/login',     \App\Controllers\AuthController::class, 'login');
    $router->get('/register',   \App\Controllers\AuthController::class, 'register');
    $router->post('/register',  \App\Controllers\AuthController::class, 'register');
    $router->get('/logout',     \App\Controllers\AuthController::class, 'logout');
    $router->get('/verify',     \App\Controllers\AuthController::class, 'verify');
    $router->get('/verify-otp', \App\Controllers\AuthController::class, 'showOtpForm');
    $router->post('/verify-otp',\App\Controllers\AuthController::class, 'showOtpForm');
    $router->get('/forgot-password',    \App\Controllers\PasswordResetController::class, 'forgot');
    $router->post('/forgot-password',   \App\Controllers\PasswordResetController::class, 'forgot');
    $router->get('/reset-password',     \App\Controllers\PasswordResetController::class, 'reset');
    $router->post('/reset-password',    \App\Controllers\PasswordResetController::class, 'reset');

    $router->get('/mfa-verify',     \App\Controllers\AuthController::class, 'mfaVerify');
    $router->post('/mfa-verify',    \App\Controllers\AuthController::class, 'mfaVerify');
    $router->get('/mfa-setup',      \App\Controllers\ProfileController::class, 'setupMfa');
    $router->post('/mfa-setup',     \App\Controllers\ProfileController::class, 'setupMfa');

    $router->get('/cart',           \App\Controllers\CartController::class, 'index');
    $router->post('/cart/add',      \App\Controllers\CartController::class, 'add');
    $router->post('/cart/remove',   \App\Controllers\CartController::class, 'remove');
    $router->post('/cart/update',   \App\Controllers\CartController::class, 'update');
    $router->get('/cart/count',     \App\Controllers\CartController::class, 'count');

    $router->get('/checkout',       \App\Controllers\CheckoutController::class, 'index');
    $router->post('/checkout/place-order',      \App\Controllers\CheckoutController::class, 'placeOrder');
    $router->get('/checkout/place-order',       \App\Controllers\CheckoutController::class, 'placeOrderGet');
    $router->get('/checkout/success',           \App\Controllers\CheckoutController::class, 'success');
    $router->post('/checkout/validate-voucher', \App\Controllers\CheckoutController::class, 'validateVoucher');

    $router->get('/payment/vnpay-return',   \App\Controllers\PaymentController::class, 'vnpayReturn');
    $router->get('/payment/vnpay-ipn',      \App\Controllers\PaymentController::class, 'vnpayIpn');

    $router->get('/orders',             \App\Controllers\OrderController::class, 'index');
    $router->get('/orders/invoice',     \App\Controllers\OrderController::class, 'invoice');
    $router->post('/orders/cancel',     \App\Controllers\OrderController::class, 'cancel');

    $router->post('/service/stop',      \App\Controllers\ServiceController::class, 'stop');
    $router->post('/service/start',     \App\Controllers\ServiceController::class, 'start');
    $router->post('/service/restart',   \App\Controllers\ServiceController::class, 'restart');
    $router->post('/service/rebuild',   \App\Controllers\ServiceController::class, 'rebuild');

    $router->get('/inbox',              \App\Controllers\InboxController::class, 'index');
    $router->post('/inbox/read',        \App\Controllers\InboxController::class, 'markRead');

    $router->get('/profile',            \App\Controllers\ProfileController::class, 'index');
    $router->post('/profile/update',    \App\Controllers\ProfileController::class, 'update');

    $router->get('/console',            \App\Controllers\ConsoleController::class, 'index');

    // Routes for admin
    $router->get('/admin/orders',           \App\Controllers\AdminOrderController::class, 'index');
    $router->get('/admin/orders/invoice',   \App\Controllers\AdminOrderController::class, 'invoice');
    $router->post('/admin/orders/update',   \App\Controllers\AdminOrderController::class, 'update');

    $router->get('/admin/products',         \App\Controllers\AdminProductController::class, 'index');
    $router->post('/admin/products/store',  \App\Controllers\AdminProductController::class, 'store');
    $router->post('/admin/products/update', \App\Controllers\AdminProductController::class, 'update');
    $router->post('/admin/products/toggle', \App\Controllers\AdminProductController::class, 'toggle');

    $router->get('/admin',                  \App\Controllers\AdminDashboardController::class, 'index');
    $router->get('/admin/dashboard',        \App\Controllers\AdminDashboardController::class, 'index');

    $router->get('/admin/users',            \App\Controllers\AdminUserController::class, 'index');
    $router->post('/admin/users/toggle-lock', \App\Controllers\AdminUserController::class, 'toggleLock');

    $router->get('/admin/vouchers',         \App\Controllers\AdminVoucherController::class, 'index');
    $router->post('/admin/vouchers/store',  \App\Controllers\AdminVoucherController::class, 'store');
    $router->post('/admin/vouchers/toggle', \App\Controllers\AdminVoucherController::class, 'toggle');

    $router->get('/admin/reviews',          \App\Controllers\AdminReviewController::class, 'index');
    $router->post('/admin/reviews/toggle',   \App\Controllers\AdminReviewController::class, 'toggle');

    $router->get('/admin/emails',           \App\Controllers\AdminEmailController::class, 'index');
    $router->post('/admin/emails/send',     \App\Controllers\AdminEmailController::class, 'send');

    $router->get('/admin/audit-logs',      \App\Controllers\AdminAuditLogController::class, 'index');

    $router->post('/review/submit',        \App\Controllers\ReviewController::class, 'submit');

    $router->dispatch();
