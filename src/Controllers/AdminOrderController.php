<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Service;

class AdminOrderController extends Controller {
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

    // Order lifecycle — only forward progress or cancellation is allowed
    private const ALLOWED_TRANSITIONS = [
        'pending'       => ['confirmed', 'cancelled'],
        'confirmed'     => ['provisioning', 'cancelled'],
        'provisioning'  => ['active', 'cancelled'],
        'active'        => ['success', 'cancelled'],
        'success'       => ['cancelled'],
        'cancelled'     => [],
    ];

    // Update order function
    public function update() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $orderId = (int)$_POST["order_id"];
            $status = $_POST["status"];

            // list of valid status
            $validStatuses = ['pending', 'confirmed', 'provisioning', 'active', 'success', 'cancelled'];

            if ($orderId > 0 && in_array($status, $validStatuses)) {
                $order = Order::findById($orderId);
                $oldStatus = $order["status"] ?? "unknown";

                $allowed = self::ALLOWED_TRANSITIONS[$oldStatus] ?? [];
                if ($status !== $oldStatus && !in_array($status, $allowed)) {
                    header("Location: /admin/orders?err=invalid_transition");
                    exit;
                }

                if ($status === "cancelled" && $oldStatus !== "cancelled") {
                    $pdo = Database::getConnection();
                    try {
                        $pdo->beginTransaction();

                        Order::updateStatus($orderId, $status);

                        // Restore voucher usage if the order had one
                        if (!empty($order["voucher_id"])) {
                            $stmtDel = $pdo->prepare("DELETE FROM voucher_usages WHERE voucher_id = ? AND order_id = ?");
                            $stmtDel->execute([$order["voucher_id"], $orderId]);
                            $stmtRestore = $pdo->prepare("UPDATE vouchers SET used_count = used_count - 1 WHERE id = ?");
                            $stmtRestore->execute([$order["voucher_id"]]);
                        }

                        // Restore product stock
                        Order::restoreStock($orderId);

                        // Terminate any provisioned services (kills ttyd, stops VM)
                        Service::terminateForOrder($orderId);

                        $pdo->commit();
                    } catch (\Exception $e) {
                        $pdo->rollBack();
                        header("Location: /admin/orders?err=cancel_failed");
                        exit;
                    }
                } else {
                    Order::updateStatus($orderId, $status);
                }

                AuditLog::log("order.update_status", "order", $orderId,
                    "Order #{$orderId} status changed from {$oldStatus} to {$status}",
                    ["status" => $oldStatus],
                    ["status" => $status]
                );

                // Auto provision VPS when order is confirmed
                if ($status === 'confirmed' && $order) {
                    Service::provisionForOrder($orderId, $order["user_id"]);
                }
            }

            // Redirect when done
            header("Location: /admin/orders?msg=updated");
            exit;
        }
    }

    // Generate and download invoice PDF
    public function invoice() {
        $this->checkAdmin();

        $orderId = (int)($_GET["id"] ?? 0);
        if ($orderId <= 0) {
            header("Location: /admin/orders");
            exit;
        }

        $order = Order::getInvoiceData($orderId);
        if (!$order) {
            header("Location: /admin/orders?err=not_found");
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