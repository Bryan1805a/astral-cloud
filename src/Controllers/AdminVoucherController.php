<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\Voucher;

class AdminVoucherController extends Controller {
    private function checkAdmin() {
        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
        }

        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            die("403 - No access.");
        }
    }

    // Load UI and fetch all voucher data when admin enter this route
    public function index() {
        $this->checkAdmin();
        $vouchers = Voucher::getAll();
        require_once __DIR__ . "/../Views/admin/vouchers/index.php";
    }

    // Create new voucher
    public function store() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            // Prepare data
            $data = [
                'code' => strtoupper(trim($_POST['code'])),
                'description' => trim($_POST['description']),
                'discount_type' => $_POST['discount_type'],
                'discount_value' => (float)$_POST['discount_value'],
                'min_order_value' => (float)$_POST['min_order_value'],
                'max_discount' => empty($_POST['max_discount']) ? null : (float)$_POST['max_discount'],
                'quantity' => (int)$_POST['quantity'],
                'applicable_tier' => $_POST['applicable_tier'],
                'expiry_date' => $_POST['expiry_date'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            try {
                Voucher::create($data);
                AuditLog::log("voucher.create", "voucher", null,
                    "Created voucher code: {$data["code"]} ({$data["discount_value"]} {$data["discount_type"]})"
                );
                header('Location: /admin/vouchers?msg=created');
                exit;
            } catch (PDOException $e) {
                // Return error if voucher code already exists
                die("<h3 style='color:red;'>Error: This voucher code already exists! " . $e->getMessage() . "</h3>");
            }
        }
    }

    // Toggle voucher status
    public function toggle() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $voucherId = (int)$_POST["id"];
            Voucher::toggleActive($voucherId);
            AuditLog::log("voucher.toggle_active", "voucher", $voucherId,
                "Toggled voucher active status (ID: {$voucherId})"
            );
            header("Location: /admin/vouchers?msg=toggled");
            exit;
        }
    }
}