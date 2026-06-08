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
            'css'         => ['cart'],
            'title' => 'Cart | Astral Cloud',
        ]);
    }

    public function add(): void {
        if (!isset($_SESSION["user_id"])) {
            if (isAjaxRequest()) {
                jsonResponse(["success" => false, "message" => "Please log in first."], 401);
            }
            header("Location: /login?msg=login_required");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /");
            exit;
        }

        verifyCsrfToken();

        $product_id = (int) ($_POST["product_id"] ?? 0);

        if ($product_id <= 0) {
            header("Location: /");
            exit;
        }

        $product = Product::findActive($product_id);
        if (!$product) {
            if (isAjaxRequest()) {
                jsonResponse(["success" => false, "message" => "VPS package does not exist or has been discontinued."]);
            }
            die("<h3 style='color:red;'>Error: The VPS package does not exist or has been discontinued.</h3>");
        }

        Cart::add($_SESSION["user_id"], $product_id);
        $count = Cart::getCartCount($_SESSION["user_id"]);

        AuditLog::log("cart.add", "product", $product_id,
            "Added product #{$product_id} to cart"
        );

        if (isAjaxRequest()) {
            jsonResponse(["success" => true, "message" => "Added to cart!", "count" => $count]);
        }

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

        verifyCsrfToken();

        $product_id = (int) ($_POST["product_id"] ?? 0);
        if ($product_id > 0) {
            Cart::remove($_SESSION["user_id"], $product_id);
            AuditLog::log("cart.remove", "product", $product_id,
                "Removed product #{$product_id} from cart"
            );
        }

        $count = Cart::getCartCount($_SESSION["user_id"]);

        if (isAjaxRequest()) {
            jsonResponse(["success" => true, "message" => "Item removed.", "count" => $count]);
        }

        header("Location: /cart?msg=removed_success");
        exit;
    }

    public function update(): void {
        if (!isset($_SESSION["user_id"])) {
            jsonResponse(["success" => false, "message" => "Not logged in."], 401);
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            jsonResponse(["success" => false, "message" => "Invalid request."], 405);
        }

        verifyCsrfToken();

        $product_id = (int) ($_POST["product_id"] ?? 0);
        $quantity   = (int) ($_POST["quantity"] ?? 1);

        if ($product_id <= 0 || $quantity < 0) {
            jsonResponse(["success" => false, "message" => "Invalid parameters."], 400);
        }

        Cart::updateQuantity($_SESSION["user_id"], $product_id, $quantity);

        AuditLog::log("cart.update", "product", $product_id,
            "Updated cart quantity for product #{$product_id} to {$quantity}"
        );

        $cart_items = Cart::getUserCart($_SESSION["user_id"]);
        $total      = 0;
        $item_total = 0;
        foreach ($cart_items as $item) {
            $line_total = $item["price"] * $item["quantity"];
            $total += $line_total;
            if ($item["product_id"] == $product_id) {
                $item_total = $line_total;
            }
        }
        $count = Cart::getCartCount($_SESSION["user_id"]);

        jsonResponse([
            "success"    => true,
            "count"      => $count,
            "total"      => $total,
            "item_total" => $item_total,
            "quantity"   => $quantity,
        ]);
    }

    public function count(): void {
        if (!isset($_SESSION["user_id"])) {
            jsonResponse(["count" => 0]);
        }

        $count = Cart::getCartCount($_SESSION["user_id"]);
        jsonResponse(["count" => $count]);
    }
}
