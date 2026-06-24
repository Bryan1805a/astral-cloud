<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\Service;

/**
 * ServiceController — VM lifecycle actions (user-facing)
 *
 * All actions require login and verify service ownership.
 * Each action POSTs the service_id, calls the VM Bridge,
 * updates the DB, and redirects back to /orders with a message.
 */
class ServiceController extends Controller {
    protected function requireLogin(): void {
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

    public function detail(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $serviceId = (int) ($_GET["id"] ?? 0);
        if ($serviceId <= 0) { header("Location: /orders"); exit; }

        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, oi.product_name, oi.product_cpu, oi.product_ram
            FROM services s
            JOIN order_items oi ON s.order_item_id = oi.id
            WHERE s.id = ? AND s.user_id = ?
        ");
        $stmt->execute([$serviceId, $_SESSION["user_id"]]);
        $service = $stmt->fetch();

        if (!$service) { header("Location: /orders"); exit; }

        $metrics = null;
        if ($service['status'] === 'running') {
            $metrics = Service::getLatestMetrics($serviceId);
        }

        // Get recent activity
        $stmt = $pdo->prepare("
            SELECT action, description, created_at
            FROM audit_logs
            WHERE entity_type = 'service' AND entity_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$serviceId]);
        $activity = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        view('vps/index', [
            'service'  => $service,
            'metrics'  => $metrics,
            'activity' => $activity,
            'css'      => ['products'],
            'title'    => htmlspecialchars($service['hostname']) . ' | Astral Cloud',
        ]);
    }
}
