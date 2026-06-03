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

        require_once __DIR__ . "/../Views/orders/index.php";
    }
}