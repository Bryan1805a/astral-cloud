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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment page | Astral Cloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4 fw-bold text-info"><i class="bi bi-shield-check"></i> Order Confirmation</h2>
        
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="glass-panel p-4 mb-4">
                    <h5 class="text-cyan mb-3">Account information</h5>
                    <p class="mb-1"><strong>Full name:</strong> <?= htmlspecialchars($currentUser['name']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($currentUser['email']) ?></p>
                    <p class="mb-0">
                        <strong>Membership tier:</strong> 
                        <span class="badge bg-warning text-dark"><?= strtoupper($currentUser['tier']) ?></span>
                    </p>
                </div>

                <div class="glass-panel p-4">
                    <h5 class="text-cyan mb-3">List of selected VPS Packages</h5>
                    <ul class="list-group list-group-flush bg-transparent">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0 text-info"><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</h6>
                                    <small class="text-secondary"><?= htmlspecialchars($item['cpu']) ?> | <?= htmlspecialchars($item['ram']) ?></small>
                                </div>
                                <span><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>VND</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="glass-panel p-4 mb-4">
                    <h5 class="text-cyan mb-3">Voucher</h5>
                    
                    <?php if ($voucher_error): ?>
                        <div class="alert alert-danger py-2 fs-6"><?= $voucher_error ?></div>
                    <?php endif; ?>
                    <?php if ($voucher_success): ?>
                        <div class="alert alert-success py-2 fs-6"><?= $voucher_success ?></div>
                    <?php endif; ?>

                    <form action="checkout.php" method="GET" class="d-flex">
                        <input type="text" name="voucher" class="form-control bg-dark text-light border-secondary me-2" 
                               placeholder="Nhập mã ưu đãi..." value="<?= htmlspecialchars($voucher_code) ?>">
                        <button type="submit" class="btn btn-outline-info">Apply</button>
                    </form>
                    
                    <small class="text-secondary mt-2 d-block">Suggest test: <b>WELCOME10</b> (10% discount)</small>
                </div>

                <div class="glass-panel p-4">
                    <h5 class="text-cyan border-bottom border-secondary pb-2 mb-3">Pay</h5>
                    
                    <div class="d-flex justify-content-between mb-2 text-secondary">
                        <span>Estimated:</span>
                        <span><?= number_format($subtotal, 0, ',', '.') ?>VND</span>
                    </div>
                    
                    <?php if ($discount_amount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount (<?= htmlspecialchars($voucher_code) ?>):</span>
                            <span>- <?= number_format($discount_amount, 0, ',', '.') ?>VND</span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-4 border-top border-secondary pt-3">
                        <span class="fs-5">Total amount:</span>
                        <span class="fs-4 fw-bold text-info"><?= number_format($total_price, 0, ',', '.') ?>VND</span>
                    </div>

                    <form action="checkout.php<?= $voucher_code ? '?voucher=' . urlencode($voucher_code) : '' ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Order notes (Optional)</label>
                            <textarea name="note" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Example: Please install Ubuntu 22.04 for me..."></textarea>
                        </div>
                        <input type="hidden" name="place_order" value="1">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-3 shadow-sm">
                            <i class="bi bi-check-circle"></i> ORDER CONFIRMATION
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>