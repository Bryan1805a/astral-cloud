<?php
class ConsoleController {
    public function index() {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $serviceId = (int) ($_GET["id"] ?? 0);
        $consoleUrl = null;
        $service = null;
        $provisioningStatus = null;

        if ($serviceId > 0) {
            $pdo = Database::getConnection();
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
                if (!empty($service['console_port']) && $provisioningStatus === 'ready') {
                    $consoleUrl = TtydHelper::generateConsoleUrl((int) $service['id']);
                }
            }
        }

        $hostname = $service['hostname'] ?? ($_GET["name"] ?? "astral-vps");
        $hostname = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", "-", $hostname)));

        require_once __DIR__ . "/../Views/console/index.php";
    }
}
