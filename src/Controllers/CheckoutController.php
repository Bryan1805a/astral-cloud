<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\MailHelper;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Voucher;

require_once __DIR__ . '/../config/vnpay.php';

/**
 * Checkout — cart → order → VNPay payment flow
 *
 * placeOrder() inside a DB transaction:
 *   1. Locks product rows (FOR UPDATE), checks stock
 *   2. Creates order + order_items, validates/applies voucher
 *   3. Decrements stock, clears cart, creates payment record
 *   4. Redirects to VNPay payment page
 *
 * On payment success, PaymentController::vnpayReturn() triggers
 * Service::provisionForOrder() to clone the VM.
 */
class CheckoutController extends Controller {
    public function index(): void {
        // Redirect user to login page if they are not login
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $user_id     = $_SESSION["user_id"];
        $currentUser = User::findById($user_id); // Get user information from database
        $cart_items  = Cart::getUserCart($user_id); // Get cart information from database

        if (empty($cart_items)) {
            header("Location: /cart");
            exit;
        }

        // Calculate sub total of all items
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item["price"] * $item["quantity"];
        }

        // Recalculate if user uses voucher
        $voucher_code    = trim($_GET["voucher"] ?? "");
        $discount_amount = 0;
        $voucher_id      = null;
        $voucher_error   = $_GET["error"] ?? "";
        $voucher_success = "";

        // Check if the voucher code exists
        if ($voucher_code) {
            $voucher = Voucher::findByCode($voucher_code);

            if ($voucher) {
                // Check expire date
                if (strtotime($voucher["expiry_date"]) < strtotime("today")) {
                    $voucher_error = "The voucher has expired.";
                }
                // Check quantity
                elseif ($voucher["quantity"] <= $voucher["used_count"]) {
                    $voucher_error = "The voucher has run out.";
                }
                // Check minimum order quantity to apply voucher
                elseif ($subtotal < $voucher["min_order_value"]) {
                    $voucher_error = "The order has not met the minimum order requirement " . number_format($voucher["min_order_value"], 0, ",", ".") . "VND.";
                }
                // Check user tier
                elseif ($voucher["applicable_tier"] !== "all" && $voucher["applicable_tier"] !== $currentUser["tier"]) {
                    $voucher_error = "This code is only for customer tier " . strtoupper($voucher["applicable_tier"]) . ".";
                }
                // Check per-user usage limit
                elseif (Voucher::getUsageCount($voucher["id"], $user_id) >= $voucher["usage_limit_per_user"]) {
                    $voucher_error = "You have reached the usage limit for this voucher.";
                }
                else {
                    $voucher_error = "";
                    $voucher_id = $voucher["id"];
                    if ($voucher["discount_type"] === "fixed") {
                        $discount_amount = $voucher["discount_value"];
                    } else {
                        $discount_amount = $subtotal * ($voucher["discount_value"] / 100);
                        if (!empty($voucher["max_discount"]) && $discount_amount > $voucher["max_discount"]) {
                            $discount_amount = $voucher["max_discount"];
                        }
                    }
                    $voucher_success = "The code [ $voucher_code ] has been successfully applied.";
                }
            } else {
                $voucher_error = "The discount code does not exist.";
            }
        }

        $total_price = max(0, $subtotal - $discount_amount);

        view('checkout/index', [
            'currentUser'     => $currentUser,
            'cart_items'      => $cart_items,
            'subtotal'        => $subtotal,
            'discount_amount' => $discount_amount,
            'total_price'     => $total_price,
            'voucher_code'    => $voucher_code,
            'voucher_error'   => $voucher_error,
            'voucher_success' => $voucher_success,
            'voucher_id'      => $voucher_id,
            'title'           => 'Payment page | Astral Cloud',
        ]);
    }

    public function validateVoucher(): void {
        if (!isset($_SESSION["user_id"])) {
            jsonResponse(["success" => false, "message" => "Not logged in."], 401);
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            jsonResponse(["success" => false, "message" => "Invalid request."], 405);
        }

        verifyCsrfToken();

        $user_id       = $_SESSION["user_id"];
        $currentUser   = User::findById($user_id);
        $cart_items    = Cart::getUserCart($user_id);
        $voucher_code  = trim($_POST["voucher"] ?? "");

        if (empty($cart_items)) {
            jsonResponse(["success" => false, "message" => "Cart is empty."]);
        }

        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item["price"] * $item["quantity"];
        }

        if (!$voucher_code) {
            jsonResponse(["success" => true, "discount" => 0, "total" => $subtotal, "subtotal" => $subtotal, "code" => ""]);
        }

        $voucher = Voucher::findByCode($voucher_code);

        if (!$voucher) {
            jsonResponse(["success" => false, "message" => "The discount code does not exist.", "subtotal" => $subtotal]);
        }

        if (strtotime($voucher["expiry_date"]) < strtotime("today")) {
            jsonResponse(["success" => false, "message" => "The voucher has expired.", "subtotal" => $subtotal]);
        }

        if ($voucher["quantity"] <= $voucher["used_count"]) {
            jsonResponse(["success" => false, "message" => "The voucher has run out.", "subtotal" => $subtotal]);
        }

        if ($subtotal < $voucher["min_order_value"]) {
            jsonResponse(["success" => false, "message" => "Minimum order not met (requires " . number_format($voucher["min_order_value"], 0, ",", ".") . " VND).", "subtotal" => $subtotal]);
        }

        if ($voucher["applicable_tier"] !== "all" && $voucher["applicable_tier"] !== $currentUser["tier"]) {
            jsonResponse(["success" => false, "message" => "This code is only for tier " . strtoupper($voucher["applicable_tier"]) . ".", "subtotal" => $subtotal]);
        }

        if (Voucher::getUsageCount($voucher["id"], $user_id) >= $voucher["usage_limit_per_user"]) {
            jsonResponse(["success" => false, "message" => "You have reached the usage limit for this voucher.", "subtotal" => $subtotal]);
        }

        $discount = 0;
        if ($voucher["discount_type"] === "fixed") {
            $discount = $voucher["discount_value"];
        } else {
            $discount = $subtotal * ($voucher["discount_value"] / 100);
            if (!empty($voucher["max_discount"]) && $discount > $voucher["max_discount"]) {
                $discount = $voucher["max_discount"];
            }
        }

        $total = max(0, $subtotal - $discount);

        jsonResponse([
            "success"  => true,
            "discount" => $discount,
            "total"    => $total,
            "subtotal" => $subtotal,
            "code"     => $voucher_code,
            "message"  => "The code has been successfully applied!",
        ]);
    }

    public function placeOrder(): void {
        // Redirect user to login page if user not login
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["place_order"])) {
            header("Location: /checkout");
            exit;
        }

        verifyCsrfToken();

        $user_id     = $_SESSION["user_id"];
        $currentUser = User::findById($user_id);
        $cart_items  = Cart::getUserCart($user_id);

        // Redirect if empty
        if (empty($cart_items)) {
            header("Location: /cart");
            exit;
        }

        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item["price"] * $item["quantity"];
        }

        $voucher_code    = trim($_POST["voucher"] ?? $_GET["voucher"] ?? "");
        $voucher_id      = null;
        $discount_amount = 0;

        $voucher_error = "";
        if ($voucher_code) {
            $voucher = Voucher::findByCode($voucher_code);
            if ($voucher) {
                if (strtotime($voucher["expiry_date"]) < strtotime("today")) {
                    $voucher_error = "The voucher has expired.";
                } elseif ($voucher["quantity"] <= $voucher["used_count"]) {
                    $voucher_error = "The voucher has run out.";
                } elseif ($subtotal < $voucher["min_order_value"]) {
                    $voucher_error = "Minimum order not met.";
                } elseif ($voucher["applicable_tier"] !== "all" && $voucher["applicable_tier"] !== $currentUser["tier"]) {
                    $voucher_error = "This code is not for your tier.";
                } elseif (Voucher::getUsageCount($voucher["id"], $user_id) >= $voucher["usage_limit_per_user"]) {
                    $voucher_error = "You have reached the usage limit for this voucher.";
                } else {
                    $voucher_id = $voucher["id"];
                    if ($voucher["discount_type"] === "fixed") {
                        $discount_amount = $voucher["discount_value"];
                    } else {
                        $discount_amount = $subtotal * ($voucher["discount_value"] / 100);
                        if (!empty($voucher["max_discount"]) && $discount_amount > $voucher["max_discount"]) {
                            $discount_amount = $voucher["max_discount"];
                        }
                    }
                }
            } else {
                $voucher_error = "The discount code does not exist.";
            }
        }

        if ($voucher_error) {
            $error_message = urlencode($voucher_error);
            header("Location: /checkout?voucher=" . urlencode($voucher_code) . "&error=$error_message");
            exit;
        }

        $total_price = max(0, $subtotal - $discount_amount);
        $note        = trim($_POST["note"] ?? "");

        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            // Check stock for each item inside the transaction (FOR UPDATE needs an active transaction)
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare(
                    "SELECT stock FROM products WHERE id = ? AND is_active = 1 FOR UPDATE"
                );
                $stmt->execute([$item["product_id"]]);
                $product = $stmt->fetch();
                if (!$product || $product["stock"] < $item["quantity"]) {
                    $pdo->rollBack();
                    header("Location: /cart?err=insufficient_stock");
                    exit;
                }
            }

            $new_order_id = Order::create($user_id, $voucher_id, $voucher_code ?: null, $subtotal, $discount_amount, $total_price, $note);

            foreach ($cart_items as $item) {
                $item_sub = $item["price"] * $item["quantity"];
                Order::addItem(
                    $new_order_id,
                    $item["product_id"],
                    $item["name"],
                    $item["cpu"],
                    $item["ram"],
                    $item["storage"],
                    $item["price"],
                    $item["quantity"],
                    $item_sub
                );
            }

            // Save voucher usage history
            if ($voucher_id) {
                Order::recordVoucherUsage($voucher_id, $user_id, $new_order_id);
            }

            // Decrement stock for each item
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item["quantity"], $item["product_id"]]);
            }

            Cart::clear($user_id);

            $txnRef = 'order_' . $new_order_id . '_' . time();
            Payment::create($new_order_id, $total_price, 'vnpay', $txnRef, 'pending');

            $pdo->commit();

            AuditLog::log("order.place", "order", $new_order_id,
                "Placed order #{$new_order_id} (total: " . number_format($total_price, 0, ",", ".") . " VND)"
            );

            // Send order confirmation email
            if (MailHelper::isConfigured()) {
                $emailItems = [];
                foreach ($cart_items as $item) {
                    $emailItems[] = [
                        'product_name' => $item['name'],
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $item['price'],
                    ];
                }
                MailHelper::sendOrderConfirmation($currentUser['email'], $currentUser['name'], $new_order_id, $total_price, $emailItems);
            }

            $ipAddr    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $appUrl    = getenv('APP_URL') ?: '';
            $returnUrl = getenv('VNP_RETURN_URL') ?: rtrim($appUrl, '/') . '/payment/vnpay-return';
            $orderInfo = 'Thanh toan don hang ' . $new_order_id;

            $paymentUrl = vnpayCreatePaymentUrl($txnRef, $total_price, $orderInfo, $ipAddr, $returnUrl);

            header("Location: " . $paymentUrl);
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            die("<h3 style='color:red;'>Order error: " . $e->getMessage() . "</h3>");
        }
    }
    
    // Handle GET requests to /checkout/place-order (VNPay sandbox may redirect here on error)
    public function placeOrderGet(): void {
        $query = $_SERVER['QUERY_STRING'] ?? '';
        header("Location: /payment/vnpay-return" . ($query ? "?{$query}" : ""));
        exit;
    }

    // Success notification
    public function success(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $order_id = (int) ($_GET["order_id"] ?? 0);
        $order    = Order::findById($order_id);

        if (!$order || $order["user_id"] != $_SESSION["user_id"]) {
            header("Location: /");
            exit;
        }

        view('checkout/success', [
            'order'  => $order,
            'title'  => 'Order Success | Astral Cloud',
        ]);
    }
}
