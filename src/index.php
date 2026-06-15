<?php
    /**
     * Astral Cloud — Application Entry Point
     *
     * Boot sequence:
     *   1. Load .env via config/db.php
     *   2. Register PSR-4-style autoloader (Controllers/ + Models/)
     *   3. Define view() helper (renders Views/ with header/footer)
     *   4. Define CSRF helpers (generateCsrfToken, csrfField, verifyCsrfToken)
     *   5. Wire routes → Router::dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'])
     */
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    session_start();

    require_once __DIR__ . "/../vendor/autoload.php";
    require_once __DIR__ . "/config/db.php";

    spl_autoload_register(function ($class) {
        $paths = [
            __DIR__ . "/Controllers/" . $class . ".php",
            __DIR__ . "/Models/" . $class . ".php",
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    });

    function view(string $view, array $data = []): void {
        extract($data);
        require __DIR__ . "/Views/layouts/header.php";
        require __DIR__ . "/Views/{$view}.php";
        require __DIR__ . "/Views/layouts/footer.php";
    }

    function generateCsrfToken(): string {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    function csrfField(): string {
        return '<input type="hidden" name="_csrf_token" value="' . generateCsrfToken() . '">';
    }

    function verifyCsrfToken(): void {
        $token = $_POST['_csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['_csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die("<h2 style='color:red;text-align:center;margin-top:50px;'>419 - CSRF token validation failed. Please go back and try again.</h2>");
        }
    }

    function jsonResponse(array $data, int $status = 200): void {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }

    function isAjaxRequest(): bool {
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";
    }

    require_once __DIR__ . "/Router.php";

    $router = new Router();

    // Routes for user
    $router->get("/",                               [ProductController::class, "index"]);

    $router->get("/login",                          [AuthController::class, "login"]);
    $router->post("/login",                         [AuthController::class, "login"]);
    $router->get("/register",                       [AuthController::class, "register"]);
    $router->post("/register",                      [AuthController::class, "register"]);
    $router->get("/logout",                         [AuthController::class, "logout"]);
    $router->get("/verify",                         [AuthController::class, "verify"]);
    $router->get("/verify-otp",                     [AuthController::class, "showOtpForm"]);
    $router->post("/verify-otp",                    [AuthController::class, "showOtpForm"]);
    $router->get("/forgot-password",                [PasswordResetController::class, "forgot"]);
    $router->post("/forgot-password",               [PasswordResetController::class, "forgot"]);
    $router->get("/reset-password",                 [PasswordResetController::class, "reset"]);
    $router->post("/reset-password",                [PasswordResetController::class, "reset"]);

    $router->get("/mfa-verify",                     [AuthController::class, "mfaVerify"]);
    $router->post("/mfa-verify",                    [AuthController::class, "mfaVerify"]);
    $router->get("/mfa-setup",                      [ProfileController::class, "setupMfa"]);
    $router->post("/mfa-setup",                     [ProfileController::class, "setupMfa"]);

    $router->get("/cart",                           [CartController::class, "index"]);
    $router->post("/cart/add",                      [CartController::class, "add"]);
    $router->post("/cart/remove",                   [CartController::class, "remove"]);
    $router->post("/cart/update",                   [CartController::class, "update"]);
    $router->get("/cart/count",                     [CartController::class, "count"]);

    $router->get("/checkout",                       [CheckoutController::class, "index"]);
    $router->post("/checkout/place-order",          [CheckoutController::class, "placeOrder"]);
    $router->get("/checkout/place-order",           [CheckoutController::class, "placeOrderGet"]);
    $router->get("/checkout/success",               [CheckoutController::class, "success"]);
    $router->post("/checkout/validate-voucher",     [CheckoutController::class, "validateVoucher"]);

    // Payment gateway routes
    $router->get("/payment/vnpay-return",           [PaymentController::class, "vnpayReturn"]);
    $router->get("/payment/vnpay-ipn",              [PaymentController::class, "vnpayIpn"]);

    $router->get("/orders",                         [OrderController::class, "index"]);
    $router->get("/orders/invoice",                 [OrderController::class, "invoice"]);
    $router->post("/orders/cancel",                 [OrderController::class, "cancel"]);

    $router->get("/inbox",                          [InboxController::class, "index"]);
    $router->post("/inbox/read",                    [InboxController::class, "markRead"]);

    $router->get("/profile",                        [ProfileController::class, "index"]);
    $router->post("/profile/update",                [ProfileController::class, "update"]);

    $router->get("/console",                        [ConsoleController::class, "index"]);

    // Routes for admin
    $router->get("/admin/orders",                   [AdminOrderController::class, "index"]);
    $router->get("/admin/orders/invoice",           [AdminOrderController::class, "invoice"]);
    $router->post("/admin/orders/update",           [AdminOrderController::class, "update"]);

    $router->get("/admin/products",                 [AdminProductController::class, "index"]);
    $router->post("/admin/products/store",          [AdminProductController::class, "store"]);
    $router->post("/admin/products/update",         [AdminProductController::class, "update"]);
    $router->post("/admin/products/toggle",         [AdminProductController::class, "toggle"]);

    $router->get("/admin",                          [AdminDashboardController::class, "index"]);
    $router->get("/admin/dashboard",                [AdminDashboardController::class, "index"]);

    $router->get("/admin/users",                    [AdminUserController::class, "index"]);
    $router->post("/admin/users/toggle-lock",       [AdminUserController::class, "toggleLock"]);

    $router->get("/admin/vouchers",                 [AdminVoucherController::class, "index"]);
    $router->post("/admin/vouchers/store",          [AdminVoucherController::class, "store"]);
    $router->post("/admin/vouchers/toggle",         [AdminVoucherController::class, "toggle"]);

    $router->get("/admin/reviews",                  [AdminReviewController::class, "index"]);
    $router->post("/admin/reviews/toggle",           [AdminReviewController::class, "toggle"]);

    $router->get("/admin/emails",                   [AdminEmailController::class, "index"]);
    $router->post("/admin/emails/send",             [AdminEmailController::class, "send"]);

    $router->get("/admin/audit-logs",              [AdminAuditLogController::class, "index"]);

    $router->dispatch($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"]);
