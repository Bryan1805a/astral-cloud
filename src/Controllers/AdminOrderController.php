<?php
require_once __DIR__ . "/../Models/Order.php";

class AdminOrderController {
    // Check if user has admin role
    private function checkAdmin() {
        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
        }
        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            http_response_code(403);
            die("<h2 style='color:red;text-align:center;margin-top:50px;'>403 - Forbidden: You do not have permission to access this page.!</h2>");
        }
    }

    public function index() {
        $this->checkAdmin();
        $orders = Order::getAllOrders();
        require_once __DIR__ . "/../Views/admin/orders/index.php";
    }

    // Update order function
    public function update() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $orderId = (int)$_POST["order_id"];
            $status = $_POST["status"];

            // list of valid status
            $validStatuses = ['pending', 'confirmed', 'provisioning', 'active', 'success', 'cancelled'];

            if ($orderId > 0 && in_array($status, $validStatuses)) {
                // Get user id
                require_once __DIR__ . "/../Models/Order.php";
                $order = Order::findById($orderId);

                Order::updateStatus($orderId, $status);

                // If order success, auto provision VPS
                if ($status == "success" && $order) {
                    require_once __DIR__ . "/../Models/Service.php";
                    Service::provisionForOrder($orderId, $order["user_id"]);
                }
            }

            // Redirect when done
            header("Location: /admin/orders?msg=updated");
            exit;
        }
    }
}