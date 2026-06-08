<?php

class AdminAuditLogController {
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

        $page = max(1, (int)($_GET["page"] ?? 1));
        $perPage = 50;
        $actionFilter = $_GET["action"] ?? null;
        $entityFilter = $_GET["entity"] ?? null;

        $logs = AuditLog::getAll($page, $perPage, $actionFilter, $entityFilter);
        $total = AuditLog::getTotalCount($actionFilter, $entityFilter);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $actions = AuditLog::getDistinctActions();
        $entityTypes = AuditLog::getDistinctEntityTypes();

        require_once __DIR__ . "/../Views/admin/audit-logs/index.php";
    }
}
