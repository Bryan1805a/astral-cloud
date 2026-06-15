<?php

/**
 * ServiceController — VM lifecycle actions (user-facing)
 *
 * All actions require login and verify service ownership.
 * Each action POSTs the service_id, calls the VM Bridge,
 * updates the DB, and redirects back to /orders with a message.
 */
class ServiceController {
    private function requireLogin(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }
    }

    public function stop(): void {
        $this->requireLogin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: /orders"); exit; }
        verifyCsrfToken();

        $serviceId = (int) ($_POST["service_id"] ?? 0);
        $result = Service::stopService($serviceId, $_SESSION["user_id"]);

        AuditLog::log("service.stop", "service", $serviceId,
            $result['success'] ? "Stopped service #{$serviceId}" : "Stop failed: {$result['error']}"
        );

        $msg = $result['success'] ? 'stopped' : urlencode($result['error']);
        header("Location: /orders?msg={$msg}");
        exit;
    }

    public function start(): void {
        $this->requireLogin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: /orders"); exit; }
        verifyCsrfToken();

        $serviceId = (int) ($_POST["service_id"] ?? 0);
        $result = Service::startService($serviceId, $_SESSION["user_id"]);

        AuditLog::log("service.start", "service", $serviceId,
            $result['success'] ? "Started service #{$serviceId}" : "Start failed: {$result['error']}"
        );

        $msg = $result['success'] ? 'started' : urlencode($result['error']);
        header("Location: /orders?msg={$msg}");
        exit;
    }

    public function restart(): void {
        $this->requireLogin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: /orders"); exit; }
        verifyCsrfToken();

        $serviceId = (int) ($_POST["service_id"] ?? 0);
        $result = Service::restartService($serviceId, $_SESSION["user_id"]);

        AuditLog::log("service.restart", "service", $serviceId,
            $result['success'] ? "Restarted service #{$serviceId}" : "Restart failed: {$result['error']}"
        );

        $msg = $result['success'] ? 'restarted' : urlencode($result['error']);
        header("Location: /orders?msg={$msg}");
        exit;
    }

    public function rebuild(): void {
        $this->requireLogin();
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: /orders"); exit; }
        verifyCsrfToken();

        $serviceId = (int) ($_POST["service_id"] ?? 0);
        $result = Service::rebuildService($serviceId, $_SESSION["user_id"]);

        AuditLog::log("service.rebuild", "service", $serviceId,
            $result['success'] ? "Rebuilt service #{$serviceId}" : "Rebuild failed: {$result['error']}"
        );

        $msg = $result['success'] ? 'rebuilding' : urlencode($result['error']);
        header("Location: /orders?msg={$msg}");
        exit;
    }
}
