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

    $router->get('/',               ['ProductController', 'index']);
    $router->get('/login',          ['AuthController', 'login']);
    $router->post('/login',         ['AuthController', 'login']);
    $router->get('/register',       ['AuthController', 'register']);
    $router->post('/register',      ['AuthController', 'register']);
    $router->get('/logout',         ['AuthController', 'logout']);
    $router->get('/cart',           ['CartController', 'index']);
    $router->post('/cart/add',      ['CartController', 'add']);
    $router->post('/cart/remove',   ['CartController', 'remove']);
    $router->get('/checkout',       ['CheckoutController', 'index']);
    $router->post('/checkout/place-order', ['CheckoutController', 'placeOrder']);
    $router->get('/checkout/success', ['CheckoutController', 'success']);

    $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
