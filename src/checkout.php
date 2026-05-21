<?php
    session_start();
    require_once "config/db.php";

    if (!isset($_SESSION["user_id"])) {
        header("Location: auth/login.php");
        exit;
    }

    $user_id = $_SESSION["user_id"];

    // Get info of current user
    $stmtUser = $pdo->prepare("SELECT name, email, phone, tier FROM users WHERE id = :id");
    $stmtUser->execute(["id" => $user_id]);
    $currentUser = $stmtUser->fetch();

    // Get cart info and calc total
    $stmtCart = $pdo->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price, p.cpu, p.ram, p.storage 
        FROM cart c JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = :user_id
    ");
    $stmt->execute(["user_id" => $user_id]);
    $cart_items = $stmtCart->fetchAll();

    if (empty($cart_items)) {
        header("Location: cart.php");
        exit;
    }

    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += ($item["price"] * $item["quantity"]);
    }

    // Voucher processing (if user enter code)
    $voucher_code = trim($_GET["voucher"] ?? "");
    $discount_amount = 0;
    $voucher_id = null;
    $voucher_error = "";
    $voucher_success = "";

    if ($voucher_code) {
        $stmtVoucher = $pdo->prepare("SELECT * FROM vouchers WHERE code = :code AND is_active = 1");
        $stmtVoucher->execute(["code" => $voucher_code]);
        $voucher = $stmtVoucher->fetch();

        if ($voucher) {
            if (strtotime($voucher["expiry_date"]) < strtotime("today")) {
                $voucher_error = "The voucher has expired.";
            }
            elseif ($voucher["quantity"] <= $voucher["used_count"]) {
                $voucher_error = "The voucher has run out.";
            }
            elseif ($subtotal < $voucher["min_order_value"]) {
                $voucher_error = "The order has not met the minimum order requirement " . number_format($voucher["min_order_value"], 0, ",", ".") . "VND.";
            }
            elseif ($voucher["applicable_tier"] !== "all" && $voucher["applicable_tier"] !== $currentUser["tier"]) {
                $voucher_error = "This code is only for customer tier " . strtoupper($voucher["applicable_tier"]) . ".";
            }
            else {
                // Valid
                $voucher_id = $voucher["id"];
                if ($voucher["discount_type"] === "fixed") {
                    $discount_amount = $voucher["discount_value"];
                }
                else {
                    $discount_amount = $subtotal * ($voucher["discount_value"] / 100);
                    if (!empty($voucher["max_discount"]) && $discount_amount > $voucher["max_discount"]) {
                        $discount_amount = $voucher["max_discount"];
                    }
                }

                $voucher_success = "The code [ $voucher_code ] has been successfully applied.";
            }
        }
        else {
            $voucher_error = "The discount code does not exist.";
        }
    }

    $total_price = $subtotal - $discount_amount;
    if ($total_price < 0) $total_price = 0;

    // Place Order processing
?>