<?php

class AdminUserController {
    private function checkAdmin() {
        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
        }

        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            die("403 - No access.");
        }
    }

    public function index() {
        $this->checkAdmin();
        $customers = User::getAllCustomers();
        require_once __DIR__ . "/../Views/admin/users/index.php";
    }

    public function toggleLock() {
        $this->checkAdmin();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $id = (int)$_POST["id"];
            if ($id > 0) {
                User::toggleLock($id);
                AuditLog::log("user.toggle_lock", "user", $id,
                    "Toggled lock/unlock for user #{$id}"
                );
            }

            header("Location: /admin/users?msg=status_changed");
            exit;
        }
    }
}