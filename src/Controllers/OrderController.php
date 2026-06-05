<?php
require_once __DIR__ . "/../Models/Order.php";

class OrderController {
    public function index() {
        // Check login
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION["user_id"];

        $orders = Order::getUserOrders($userId);

        require_once __DIR__ . "/../Models/Service.php";

        $services = Service::getUserServices($userId);

        require_once __DIR__ . "/../Views/orders/index.php";
    }

    public function cancel() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $orderId = (int)$_POST["order_id"];
            $userId = $_SESSION["user_id"];

            if ($orderId > 0) {
                $success = Order::cancelOrder($orderId, $userId);

                if ($success) {
                    header('Location: /orders?msg=cancelled');
                }
                else {
                    header('Location: /orders?err=cannot_cancel');
                }
                exit;
            }
        }
    }
}