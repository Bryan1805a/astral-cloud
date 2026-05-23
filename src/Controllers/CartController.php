<?php

class CartController {
    public function index(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $user_id    = $_SESSION["user_id"];
        $cart_items = Cart::getUserCart($user_id);

        $total_price = 0;
        foreach ($cart_items as $item) {
            $total_price += $item["price"] * $item["quantity"];
        }

        view('cart/index', [
            'cart_items'  => $cart_items,
            'total_price' => $total_price,
            'styles'      => '
                .text-cyan { color: #38bdf8; }
                .table-glass { color: #f8fafc; }
                .table-glass th, .table-glass td {
                    background: transparent;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                    vertical-align: middle;
                }
            ',
            'title' => 'Cart | Astral Cloud',
        ]);
    }

    public function add(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login?msg=login_required");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /");
            exit;
        }

        $product_id = (int) ($_POST["product_id"] ?? 0);

        if ($product_id <= 0) {
            header("Location: /");
            exit;
        }

        $product = Product::findActive($product_id);
        if (!$product) {
            die("<h3 style='color:red;'>Error: The VPS package does not exist or has been discontinued.</h3>");
        }

        Cart::add($_SESSION["user_id"], $product_id);
        header("Location: /cart?msg=added_success");
        exit;
    }

    public function remove(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /");
            exit;
        }

        $product_id = (int) ($_POST["product_id"] ?? 0);
        if ($product_id > 0) {
            Cart::remove($_SESSION["user_id"], $product_id);
        }

        header("Location: /cart?msg=removed_success");
        exit;
    }
}
