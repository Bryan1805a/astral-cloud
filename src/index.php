<?php
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    session_start();

    require_once __DIR__ . '/config/db.php';

    spl_autoload_register(function ($class) {
        $paths = [
            __DIR__ . '/Controllers/' . $class . '.php',
            __DIR__ . '/Models/' . $class . '.php',
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
        require __DIR__ . '/Views/layouts/header.php';
        require __DIR__ . "/Views/{$view}.php";
        require __DIR__ . '/Views/layouts/footer.php';
    }

    require_once __DIR__ . '/Router.php';

    $router = new Router();

    // Routes for user
    $router->get('/',                               [ProductController::class, 'index']);

    $router->get('/login',                          [AuthController::class, 'login']);
    $router->post('/login',                         [AuthController::class, 'login']);
    $router->get('/register',                       [AuthController::class, 'register']);
    $router->post('/register',                      [AuthController::class, 'register']);
    $router->get('/logout',                         [AuthController::class, 'logout']);

    $router->get('/cart',                           [CartController::class, 'index']);
    $router->post('/cart/add',                      [CartController::class, 'add']);
    $router->post('/cart/remove',                   [CartController::class, 'remove']);

    $router->get('/checkout',                       [CheckoutController::class, 'index']);
    $router->post('/checkout/place-order',          [CheckoutController::class, 'placeOrder']);
    $router->get('/checkout/success',               [CheckoutController::class, 'success']);

    $router->get('/orders',                         [OrderController::class, "index"]);

    // Routes for admin
    $router->get("/admin/orders",                   [AdminOrderController::class, "index"]);
    $router->post("/admin/orders/update",           [AdminOrderController::class, "update"]);

    $router->get("/admin/products",                 [AdminProductController::class, "index"]);
    $router->post("/admin/products/store",          [AdminProductController::class, "store"]);
    $router->post("/admin/products/update",         [AdminProductController::class, "update"]);
    $router->post("/admin/products/toggle",         [AdminProductController::class, "toggle"]);

    $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
