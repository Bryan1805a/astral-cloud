<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\TtydHelper;

class ConsoleController extends Controller {
    public function index() {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }

        $serviceId = (int) ($_GET["id"] ?? 0);
        $consoleUrl = null;
        $service = null;
        $provisioningStatus = null;

        if ($serviceId > 0) {
            $pdo = \App\Core\Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT s.*, oi.product_name
                FROM services s
                JOIN order_items oi ON s.order_item_id = oi.id
                WHERE s.id = ? AND s.user_id = ?
            ");
            $stmt->execute([$serviceId, $_SESSION["user_id"]]);
            $service = $stmt->fetch();

            if ($service) {
                $provisioningStatus = $service['provisioning_status'] ?? 'pending';
                if ($provisioningStatus === 'ready') {
                    $consoleUrl = TtydHelper::generateConsoleUrl((int) $service['id']);
                }
            }
        }

        $hostname = $service['hostname'] ?? ($_GET["name"] ?? "astral-vps");
        $hostname = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", "-", $hostname)));

        require_once __DIR__ . "/../Views/console/index.php";
    }

    public function status() {
        if (!$this->isLoggedIn()) {
            $this->json(['success' => false], 401);
        }

        $serviceId = (int) ($_GET['id'] ?? 0);
        if ($serviceId <= 0) {
            $this->json(['success' => false]);
        }

        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT provisioning_status, ip_address, root_password, hostname, os,
                   console_port, status
            FROM services
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$serviceId, $_SESSION["user_id"]]);
        $service = $stmt->fetch();

        if (!$service) {
            $this->json(['success' => false]);
        }

        $ready = ($service['provisioning_status'] === 'ready');
        $consoleUrl = $ready ? TtydHelper::generateConsoleUrl($serviceId) : null;

        $this->json([
            'success'       => true,
            'status'        => $service['provisioning_status'],
            'ip'            => $service['ip_address'],
            'password'      => $service['root_password'],
            'hostname'      => $service['hostname'],
            'ready'         => $ready,
            'console_url'   => $consoleUrl,
        ]);
    }
}
