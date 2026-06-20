<?php
namespace App\Models;

use App\Core\Database;

/**
 * Service — VPS lifecycle management
 *
 * Flow:
 *   provisionForOrder() is called when payment succeeds (or admin confirms).
 *   It pings the VM Bridge first — if reachable, inserts service rows and
 *   triggers VM cloning, then returns immediately.  If unreachable, inserts
 *   rows with status "pending" so cron can retry the clone step later.
 *
 *   completePendingProvisionings() (runs every 2 min via cron) handles the
 *   async follow-up: for services missing a VM it triggers the clone; then
 *   polls for the guest IP and registers the web terminal console.
 *
 *   terminateForOrder() stops consoles and marks services as terminated.
 */

class Service {

    private static function getBridgeUrl(): string {
        return getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001';
    }

    private static function isBridgeReachable(): bool {
        $url = self::getBridgeUrl() . '/ttyd/status?service_id=0';
        $ctx = stream_context_create([
            'http' => ['timeout' => 5],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $result = @file_get_contents($url, false, $ctx);
        return $result !== false;
    }

    public static function setProvisioningStatus(int $serviceId, string $status): void {
        $valid = ['pending','creating_vm','booting','waiting_ip','preparing_console','ready'];
        if (!in_array($status, $valid)) return;
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE services SET provisioning_status = ? WHERE id = ?")
            ->execute([$status, $serviceId]);
    }

    /**
     * @return bool  true if provisioning started, false if bridge was down (cron will retry)
     */
    public static function provisionForOrder(int $orderId, int $userId): bool {
        $pdo = Database::getConnection();

        $stmtCheck = $pdo->prepare("
            SELECT id, provisioning_status FROM services
            WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)
        ");
        $stmtCheck->execute([$orderId]);
        $existing = $stmtCheck->fetchAll();

        if ($existing) {
            $allDone = true;
            foreach ($existing as $s) {
                if ($s['provisioning_status'] !== 'ready') {
                    $allDone = false;
                    break;
                }
            }
            if ($allDone) return true;
        }

        $stmtItems = $pdo->prepare("SELECT id, product_name, product_cpu, product_ram FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        if (empty($items)) return false;

        $vmBridgeUrl = self::getBridgeUrl();
        $bridgeOk    = self::isBridgeReachable();

        $initialStatus = $bridgeOk ? 'creating_vm' : 'pending';

        foreach ($items as $item) {
            $hostname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['product_name'])) . '-' . rand(1000, 9999);
            $password = getenv('VM_BASE_PASSWORD') ?: 'password';

            $stmtInsert = $pdo->prepare("
                INSERT INTO services (order_item_id, user_id, hostname, ip_address, root_password, os, status, start_date, expiry_date, provisioning_status)
                VALUES (?, ?, ?, ?, ?, 'Ubuntu 22.04 LTS', 'provisioning', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?)
            ");
            $stmtInsert->execute([$item['id'], $userId, $hostname, '0.0.0.0', $password, $initialStatus]);
            $serviceId = (int) $pdo->lastInsertId();

            if (!$bridgeOk) {
                error_log("[AstralCloud] Bridge unreachable — service #{$serviceId} queued as 'pending' (cron will retry)");
                continue;
            }

            $provisionUrl = $vmBridgeUrl . '/provision?order_id=' . $orderId . '&item_id=' . $item['id']
                . '&name=' . urlencode($hostname) . '&password=' . urlencode($password);

            $ch = curl_init($provisionUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_CONNECTTIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err      = curl_error($ch);

            if ($httpCode !== 200) {
                error_log("[AstralCloud] Clone failed for service #{$serviceId}: HTTP {$httpCode} - {$err}");
                self::setProvisioningStatus($serviceId, 'pending');
                continue;
            }
            error_log("[AstralCloud] VM cloned for service #{$serviceId} ({$hostname}) — cron will handle the rest");
            self::setProvisioningStatus($serviceId, 'booting');
        }

        if (!$bridgeOk) {
            AuditLog::logSystem('service.bridge_unreachable', 'service', null,
                "VM Bridge unreachable during provisioning for order #{$orderId} — services queued as 'pending'"
            );
        }

        return $bridgeOk;
    }

    public static function completePendingProvisionings(): void {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT s.id, s.hostname, s.ip_address, s.root_password, s.order_item_id, s.user_id,
                   oi.product_name, oi.id AS item_id,
                   s.provisioning_status, s.console_port
            FROM services s
            JOIN order_items oi ON s.order_item_id = oi.id
            WHERE s.status = 'provisioning'
              AND (s.ip_address = '0.0.0.0' OR s.console_port IS NULL)
        ");
        $stmt->execute();
        $services = $stmt->fetchAll();

        if (empty($services)) return;

        $vmBridgeUrl = self::getBridgeUrl();

        foreach ($services as $s) {
            // ── Step 0: Trigger clone if never attempted ───────────
            $status = $s['provisioning_status'];

            if ($status === 'pending') {
                if (!self::isBridgeReachable()) {
                    cronLog("  Service #{$s['id']}: bridge unreachable, will retry");
                    continue;
                }

                $provisionUrl = $vmBridgeUrl . '/provision'
                    . '?name=' . urlencode($s['hostname'])
                    . '&password=' . urlencode($s['root_password'])
                    . '&order_id=0&item_id=' . $s['item_id'];

                $ctx = stream_context_create(['http' => ['timeout' => 120], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
                $response = @file_get_contents($provisionUrl, false, $ctx);

                if ($response === false) {
                    cronLog("  Service #{$s['id']}: clone trigger failed, will retry");
                    continue;
                }

                $data = json_decode($response, true);
                if (!$data || empty($data['success'])) {
                    cronLog("  Service #{$s['id']}: clone returned error: " . ($data['message'] ?? 'unknown'));
                    continue;
                }

                self::setProvisioningStatus($s['id'], 'booting');
                cronLog("  Service #{$s['id']}: clone triggered ({$s['hostname']})");
                continue; // VM just started — IP won't be ready yet, get it next cycle
            }

            $hasIp = $s['ip_address'] !== '0.0.0.0' && !empty($s['ip_address']);

            // ── Step 1: Try to obtain IP ──────────────────────────
            if (!$hasIp) {
                self::setProvisioningStatus($s['id'], 'waiting_ip');

                $found = false;
                $patterns = [
                    'VPS_Order_0_' . $s['item_id'],
                    $s['hostname'],
                ];

                foreach ($patterns as $name) {
                    $statusUrl = $vmBridgeUrl . '/status?name=' . urlencode($name);
                    $ctx = stream_context_create(['http' => ['timeout' => 30], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
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

            // ── Step 2: We have IP — register console ──────────
            if ($hasIp && empty($s['console_port'])) {
                $stmtIp = $pdo->prepare("SELECT ip_address FROM services WHERE id = ?");
                $stmtIp->execute([$s['id']]);
                $row = $stmtIp->fetch();
                $ip = $row['ip_address'];

                self::setProvisioningStatus($s['id'], 'preparing_console');

                $port = TtydHelper::startConsole($s['id'], $ip, $s['hostname'], $s['root_password']);

                if ($port !== null) {
                    $pdo->prepare("UPDATE services SET console_port = 1, status = 'running', provisioning_status = 'ready' WHERE id = ?")
                        ->execute([$s['id']]);
                    cronLog("  Service #{$s['id']}: IP={$ip}, console registered");
                } else {
                    cronLog("  Service #{$s['id']}: IP={$ip} (console registration failed, will retry)");
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
            UPDATE services SET status = 'terminated', console_port = NULL
            WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)
              AND status NOT IN ('terminated')
        ");
        $stmt->execute([$orderId]);
    }

    // ── Lifecycle: call the bridge and return result ─────────────

    private static function getOwnedService(int $serviceId, int $userId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND user_id = ?");
        $stmt->execute([$serviceId, $userId]);
        return $stmt->fetch() ?: null;
    }

    private static function callBridge(string $endpoint, string $hostname, ?string $password = null): array {
        $url = self::getBridgeUrl() . "/{$endpoint}?name=" . urlencode($hostname);
        if ($password) {
            $url .= '&password=' . urlencode($password);
        }
        $ctx = stream_context_create(['http' => ['timeout' => 30], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $response = @file_get_contents($url, false, $ctx);

        if ($response === false) {
            return ['success' => false, 'error' => 'VM Bridge unreachable'];
        }
        $data = json_decode($response, true);
        return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response'];
    }

    public static function stopService(int $serviceId, int $userId): array {
        $srv = self::getOwnedService($serviceId, $userId);
        if (!$srv) return ['success' => false, 'error' => 'Service not found'];

        if ($srv['status'] === 'stopped') {
            return ['success' => false, 'error' => 'Already stopped'];
        }

        $result = self::callBridge('stop', $srv['hostname']);
        if ($result['success']) {
            $pdo = Database::getConnection();
            $pdo->prepare("UPDATE services SET status = 'stopped' WHERE id = ?")
                ->execute([$serviceId]);
        }
        return $result;
    }

    public static function startService(int $serviceId, int $userId): array {
        $srv = self::getOwnedService($serviceId, $userId);
        if (!$srv) return ['success' => false, 'error' => 'Service not found'];

        if ($srv['status'] === 'running') {
            return ['success' => false, 'error' => 'Already running'];
        }

        $result = self::callBridge('start', $srv['hostname']);
        if ($result['success']) {
            $pdo = Database::getConnection();
            $pdo->prepare("UPDATE services SET status = 'running', provisioning_status = 'booting' WHERE id = ?")
                ->execute([$serviceId]);
        }
        return $result;
    }

    public static function restartService(int $serviceId, int $userId): array {
        $srv = self::getOwnedService($serviceId, $userId);
        if (!$srv) return ['success' => false, 'error' => 'Service not found'];

        $result = self::callBridge('restart', $srv['hostname']);
        if ($result['success']) {
            $pdo = Database::getConnection();
            $pdo->prepare("UPDATE services SET status = 'running', provisioning_status = 'booting' WHERE id = ?")
                ->execute([$serviceId]);
        }
        return $result;
    }

    public static function rebuildService(int $serviceId, int $userId): array {
        $srv = self::getOwnedService($serviceId, $userId);
        if (!$srv) return ['success' => false, 'error' => 'Service not found'];

        $password = getenv('VM_BASE_PASSWORD') ?: 'password';
        $result = self::callBridge('rebuild', $srv['hostname'], $password);

        if ($result['success']) {
            $pdo = Database::getConnection();
            $pdo->prepare("UPDATE services SET status = 'running', provisioning_status = 'booting', ip_address = '0.0.0.0', console_port = NULL WHERE id = ?")
                ->execute([$serviceId]);
            TtydHelper::stopConsole($serviceId);
        }
        return $result;
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
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Resource Metrics ──────────────────────────────────────

    public static function collectMetrics(int $serviceId, string $ip, string $password): bool {
        $url = self::getBridgeUrl() . '/resources?ip=' . urlencode($ip)
             . '&password=' . urlencode($password);

        $ctx = stream_context_create(['http' => ['timeout' => 20], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) return false;

        $data = json_decode($response, true);
        if (!$data || empty($data['success'])) return false;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO resource_metrics
                (service_id, cpu_load, ram_used_mb, ram_total_mb, disk_used_gb, disk_total_gb, net_rx_bytes, net_tx_bytes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $serviceId,
            $data['cpu']       ?? null,
            $data['ram_used']  ?? null,
            $data['ram_total'] ?? null,
            $data['disk_used'] ?? null,
            $data['disk_total']?? null,
            $data['net_rx']    ?? 0,
            $data['net_tx']    ?? 0,
        ]);
        return true;
    }

    public static function getLatestMetrics(int $serviceId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM resource_metrics
            WHERE service_id = ?
            ORDER BY collected_at DESC
            LIMIT 1
        ");
        $stmt->execute([$serviceId]);
        return $stmt->fetch() ?: null;
    }

    public static function collectMetricsForAllRunning(): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, ip_address, root_password
            FROM services
            WHERE status = 'running'
              AND ip_address IS NOT NULL
              AND ip_address != '0.0.0.0'
        ");
        $stmt->execute();
        $services = $stmt->fetchAll();

        foreach ($services as $srv) {
            self::collectMetrics($srv['id'], $srv['ip_address'], $srv['root_password']);
        }
    }
}
