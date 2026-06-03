<?php
class AdminVoucherController {
    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();

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
            Voucher::toggleActive((int)$_POST["id"]);
            header("Location: /admin/vouchers?msg=toggled");
            exit;
        }
    }
}