<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Service;

class OrderController extends Controller {
    public function index() {
        // Check login
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION["user_id"];

        $orders = Order::getUserOrders($userId);
        $services = Service::getUserServices($userId);

        require_once __DIR__ . "/../Views/orders/index.php";
    }

    public function cancel() {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $orderId = (int)$_POST["order_id"];
            $userId = $_SESSION["user_id"];

            if ($orderId > 0) {
                $success = Order::cancelOrder($orderId, $userId);

                if ($success) {
                    AuditLog::log("order.cancel", "order", $orderId,
                        "Cancelled order #{$orderId}"
                    );
                    header('Location: /orders?msg=cancelled');
                }
                else {
                    header('Location: /orders?err=cannot_cancel');
                }
                exit;
            }
        }
    }

    // Download invoice PDF for own order
    public function invoice() {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $orderId = (int)($_GET["id"] ?? 0);
        if ($orderId <= 0) {
            header("Location: /orders");
            exit;
        }

        $order = Order::getInvoiceData($orderId);
        if (!$order || $order["customer_id"] != $_SESSION["user_id"]) {
            header("Location: /orders");
            exit;
        }

        ob_start();
        require __DIR__ . "/../Views/admin/orders/invoice.php";
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set("defaultFont", "DejaVu Sans");
        $options->set("isRemoteEnabled", false);
        $options->set("isHtml5ParserEnabled", true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper("A4", "portrait");
        $dompdf->render();

        $filename = "INV-" . str_pad($orderId, 6, "0", STR_PAD_LEFT) . ".pdf";
        $dompdf->stream($filename, ["Attachment" => 1]);
        exit;
    }
}