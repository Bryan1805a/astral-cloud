<?php
class Service {

    public static function setProvisioningStatus(int $serviceId, string $status): void {
        $valid = ['pending','creating_vm','booting','waiting_ip','preparing_console','ready'];
        if (!in_array($status, $valid)) return;
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE services SET provisioning_status = ? WHERE id = ?")
            ->execute([$status, $serviceId]);
    }

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

        $vmBridgeUrl = (getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001');

        foreach ($items as $item) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $password = '';
            for ($i = 0; $i < 12; $i++) {
                $password .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $hostname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['product_name'])) . '-' . rand(1000, 9999);

            $stmtInsert = $pdo->prepare("
                INSERT INTO services (order_item_id, user_id, hostname, ip_address, root_password, os, status, start_date, expiry_date, provisioning_status)
                VALUES (?, ?, ?, ?, ?, 'Ubuntu 22.04 LTS', 'provisioning', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'creating_vm')
            ");
            $stmtInsert->execute([$item['id'], $userId, $hostname, '0.0.0.0', $password]);
            $serviceId = (int) $pdo->lastInsertId();

            // Step 1: Clone VM via VM Bridge
            $provisionUrl = $vmBridgeUrl . '/provision?order_id=' . $orderId . '&item_id=' . $item['id']
                . '&name=' . urlencode($hostname) . '&password=' . urlencode($password);

            $ch = curl_init($provisionUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_CONNECTTIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200) {
                error_log("[AstralCloud] VM clone failed for service #{$serviceId}: HTTP {$httpCode} - {$error}");
                continue;
            }
            error_log("[AstralCloud] VM cloned for service #{$serviceId} ({$hostname})");

            // Step 2: Poll for IP (up to 60 seconds)
            self::setProvisioningStatus($serviceId, 'waiting_ip');
            $ip = null;
            $maxAttempts = 12;
            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                sleep(5);
                $statusUrl = $vmBridgeUrl . '/status?name=' . urlencode($hostname);
                $ctx = stream_context_create(['http' => ['timeout' => 10]]);
                $statusResponse = @file_get_contents($statusUrl, false, $ctx);
                if ($statusResponse !== false) {
                    $data = json_decode($statusResponse, true);
                    if ($data && !empty($data['success']) && !empty($data['ip'])) {
                        $ip = $data['ip'];
                        break;
                    }
                }
            }

            if (!$ip) {
                error_log("[AstralCloud] Timed out waiting for IP for service #{$serviceId}");
                continue;
            }

            $pdo->prepare("UPDATE services SET ip_address = ? WHERE id = ?")->execute([$ip, $serviceId]);
            error_log("[AstralCloud] Service #{$serviceId}: got IP = {$ip}");

            // Step 3: Start ttyd console
            self::setProvisioningStatus($serviceId, 'preparing_console');
            $port = TtydHelper::startConsole($serviceId, $ip, $hostname);

            if ($port !== null) {
                $pdo->prepare("UPDATE services SET console_port = ?, status = 'running', provisioning_status = 'ready' WHERE id = ?")
                    ->execute([$port, $serviceId]);
                error_log("[AstralCloud] Service #{$serviceId}: provisioned on port {$port}");
            } else {
                error_log("[AstralCloud] Service #{$serviceId}: ttyd start failed (cron will retry)");
            }
        }
    }

    public static function completePendingProvisionings(): void {
        $pdo = Database::getConnection();

        // Services still waiting for IP (no real IP yet)
        $stmt = $pdo->prepare("
            SELECT s.id, s.hostname, s.ip_address, s.root_password, s.order_item_id, s.user_id, oi.product_name,
                   s.provisioning_status, s.console_port
            FROM services s
            JOIN order_items oi ON s.order_item_id = oi.id
            WHERE s.status = 'provisioning'
              AND (s.ip_address = '0.0.0.0' OR s.console_port IS NULL)
        ");
        $stmt->execute();
        $services = $stmt->fetchAll();

        $vmBridgeUrl = (getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001');

        foreach ($services as $s) {
            $hasIp = $s['ip_address'] !== '0.0.0.0' && !empty($s['ip_address']);

            // ── Step 1: Try to obtain IP ──────────────────────────
            if (!$hasIp) {
                self::setProvisioningStatus($s['id'], 'waiting_ip');

                $found = false;
                $patterns = [
                    'VPS_Order_0_' . $s['order_item_id'],
                    $s['hostname'],
                ];

                foreach ($patterns as $name) {
                    $statusUrl = $vmBridgeUrl . '/status?name=' . urlencode($name);
                    $ctx = stream_context_create(['http' => ['timeout' => 30]]);
                    $response = @file_get_contents($statusUrl, false, $ctx);

                    if ($response !== false) {
                        $data = json_decode($response, true);
                        if ($data && !empty($data['success']) && !empty($data['ip'])) {
                            $pdo->prepare("UPDATE services SET ip_address = ? WHERE id = ?")
                                ->execute([$data['ip'], $s['id']]);
                            $hasIp = true;
                            cronLog("  Service #{$s['id']}: IP obtained = {$data['ip']}");
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    cronLog("  Service #{$s['id']}: VM still provisioning (no IP yet)");
                    continue;
                }
            }

            // ── Step 2: We have IP — start ttyd console ──────────
            if ($hasIp && empty($s['console_port'])) {
                self::setProvisioningStatus($s['id'], 'preparing_console');

                // Re-fetch the service to get the now-updated IP
                $stmtIp = $pdo->prepare("SELECT ip_address FROM services WHERE id = ?");
                $stmtIp->execute([$s['id']]);
                $row = $stmtIp->fetch();
                $ip = $row['ip_address'];

                $port = TtydHelper::startConsole($s['id'], $ip, $s['hostname']);

                if ($port !== null) {
                    $pdo->prepare("UPDATE services SET console_port = ?, status = 'running', provisioning_status = 'ready' WHERE id = ?")
                        ->execute([$port, $s['id']]);
                    cronLog("  Service #{$s['id']}: IP={$ip}, ttyd on port {$port}");
                } else {
                    cronLog("  Service #{$s['id']}: IP={$ip} (ttyd start failed, will retry)");
                }
            }
        }
    }

    public static function terminateForOrder(int $orderId): void {
        $pdo = Database::getConnection();

        $stmtFetch = $pdo->prepare("
            SELECT id, console_port FROM services
            WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)
              AND status NOT IN ('terminated')
        ");
        $stmtFetch->execute([$orderId]);
        $services = $stmtFetch->fetchAll();

        foreach ($services as $s) {
            if (!empty($s['console_port'])) {
                TtydHelper::stopConsole($s['id']);
            }
        }

        $stmt = $pdo->prepare("
            UPDATE services SET status = 'terminated', console_port = NULL, provisioning_status = NULL
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
