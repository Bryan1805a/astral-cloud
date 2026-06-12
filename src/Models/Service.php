<?php
class Service {
    public static function provisionForOrder(int $orderId, int $userId): void {
        $pdo = Database::getConnection();

        $stmtCheck = $pdo->prepare("SELECT id FROM services WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)");
        $stmtCheck->execute([$orderId]);
        if ($stmtCheck->fetch()) {
            return;
        }

        $stmtItems = $pdo->prepare("SELECT id, product_name, product_cpu, product_ram FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        $stmtInsert = $pdo->prepare("
            INSERT INTO services (order_item_id, user_id, hostname, ip_address, root_password, os, status, start_date, expiry_date)
            VALUES (?, ?, ?, ?, ?, 'Ubuntu 22.04 LTS', 'provisioning', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))
        ");

        $vmBridgeUrl = (getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001');

        foreach ($items as $item) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            $password = substr(str_shuffle($chars), 0, 12);
            $hostname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['product_name'])) . '-' . rand(1000, 9999);

            $stmtInsert->execute([$item['id'], $userId, $hostname, '0.0.0.0', $password]);
            $serviceId = (int) $pdo->lastInsertId();

            // Fire-and-forget: call VM Bridge in background so PHP doesn't block
            $apiUrl = $vmBridgeUrl . '/provision?order_id=' . $orderId . '&item_id=' . $item['id']
                . '&name=' . urlencode($hostname) . '&password=' . urlencode($password);
            $cmd = 'curl -s --max-time 300 "' . $apiUrl . '" > /dev/null 2>&1 &';
            @exec($cmd);

            error_log("[AstralCloud] Provisioning service #{$serviceId}: cloned VM ({$hostname}) started in background");
        }
    }

    public static function completePendingProvisionings(): void {
        $pdo = Database::getConnection();

        // Find services that have been provisioned (status='provisioning') but no real IP yet
        $stmt = $pdo->prepare("
            SELECT s.id, s.hostname, s.root_password, s.order_item_id, s.user_id, oi.product_name
            FROM services s
            JOIN order_items oi ON s.order_item_id = oi.id
            WHERE s.status = 'provisioning'
              AND (s.ip_address = '0.0.0.0' OR s.guacamole_connection_id IS NULL)
        ");
        $stmt->execute();
        $services = $stmt->fetchAll();

        $vmBridgeUrl = (getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001');

        foreach ($services as $s) {
            // Call VM Bridge to get the actual IP
            $vmName = 'VPS_Order_0_' . $s['order_item_id'];
            $statusUrl = $vmBridgeUrl . '/status?name=' . urlencode($vmName);

            $ctx = stream_context_create(['http' => ['timeout' => 30]]);
            $response = @file_get_contents($statusUrl, false, $ctx);

            if ($response !== false) {
                $data = json_decode($response, true);
                if ($data && !empty($data['success']) && !empty($data['ip'])) {
                    $ip = $data['ip'];

                    // Update service with real IP
                    $pdo->prepare("UPDATE services SET ip_address = ?, status = 'running' WHERE id = ?")
                        ->execute([$ip, $s['id']]);

                    // Create Guacamole SSH connection
                    $connName = 'VPS_' . $s['product_name'] . '_' . $s['id'];
                    $connId = GuacamoleHelper::createConnection($connName, $ip, 22, 'root');
                    if ($connId !== null) {
                        $pdo->prepare("UPDATE services SET guacamole_connection_id = ? WHERE id = ?")
                            ->execute([$connId, $s['id']]);
                        cronLog("  Service #{$s['id']}: IP={$ip}, Guacamole connection #{$connId} created");
                    } else {
                        cronLog("  Service #{$s['id']}: IP={$ip} (Guacamole creation skipped)");
                    }
                    continue;
                }
            }

            // Try with a fallback VM name pattern (using hostname)
            $statusUrl2 = $vmBridgeUrl . '/status?name=' . urlencode($s['hostname']);
            $response2 = @file_get_contents($statusUrl2, false, $ctx);

            if ($response2 !== false) {
                $data2 = json_decode($response2, true);
                if ($data2 && !empty($data2['success']) && !empty($data2['ip'])) {
                    $ip = $data2['ip'];
                    $pdo->prepare("UPDATE services SET ip_address = ?, status = 'running' WHERE id = ?")
                        ->execute([$ip, $s['id']]);

                    $connName = 'VPS_' . $s['product_name'] . '_' . $s['id'];
                    $connId = GuacamoleHelper::createConnection($connName, $ip, 22, 'root');
                    if ($connId !== null) {
                        $pdo->prepare("UPDATE services SET guacamole_connection_id = ? WHERE id = ?")
                            ->execute([$connId, $s['id']]);
                        cronLog("  Service #{$s['id']}: IP={$ip}, Guacamole connection #{$connId} created (fallback)");
                    }
                    continue;
                }
            }

            cronLog("  Service #{$s['id']}: VM still provisioning (no IP yet)");
        }
    }

    public static function terminateForOrder(int $orderId): void {
        $pdo = Database::getConnection();

        $stmtFetch = $pdo->prepare("
            SELECT id, guacamole_connection_id FROM services
            WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)
              AND status NOT IN ('terminated')
        ");
        $stmtFetch->execute([$orderId]);
        $services = $stmtFetch->fetchAll();

        foreach ($services as $s) {
            if (!empty($s['guacamole_connection_id'])) {
                GuacamoleHelper::deleteConnection((int) $s['guacamole_connection_id']);
            }
        }

        $stmt = $pdo->prepare("
            UPDATE services SET status = 'terminated', guacamole_connection_id = NULL
            WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)
              AND status NOT IN ('terminated')
        ");
        $stmt->execute([$orderId]);
    }

    public static function getUserServices(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, oi.product_name, oi.product_cpu, oi.product_ram
            FROM services s
            JOIN order_items oi ON s.order_item_id = oi.id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
