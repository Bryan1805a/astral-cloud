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

    // Place Order processing (when user press Confirm)
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["place_order"])) {
        $note = trim($_POST["note"] ?? "");

        try {
            $pdo->beginTransaction();

            // Save to Orders table
            $stmtOrder = $pdo->prepare("
                INSERT INTO orders (user_id, voucher_id, voucher_code, subtotal, discount_amount, total_price, note, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmtOrder->execute([$user_id, $voucher_id, $voucher_code ?: null, $subtotal, $discount_amount, $total_price, $note]);
            $new_order_id = $pdo->lastInsertId(); // Get the ID of the order just created

            // Save to order_items table (Snapshot data of CPU/RAM/Price at purchase)
            $stmtItem = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, product_cpu, product_ram, product_storage, unit_price, quantity, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($cart_items as $item) {
                $item_sub = $item["price"] * $item["quantity"];
                $stmtItem->execute([
                    $new_order_id, $item["product_id"], $item["name"], $item["cpu"],
                    $item["ram"], $item["storage"], $item["quantity"], $item_sub
                ]);
            }

            // Save voucher usage history
            if ($voucher_id) {
                $stmtVUsage = $pdo->prepare("INSERT INTO voucher_usages (voucher_id, user_id, order_id) VALUES (?, ?, ?)");
                $stmtVUsage->execute([$voucher_id, $user_id, $new_order_id]);
            }

            // Remove all items in the cart
            $stmtClearCart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmtClearCart->execute([$user_id]);

            $pdo->commit();

            header("Location: checkout_success.php?order_id=" . $new_order_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("<h3 style='color:red;'>Order error: " . $e->getMessage() . "</h3>");
        }
    }
?>