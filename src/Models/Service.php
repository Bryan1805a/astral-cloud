<?php
class Service {
    public static function provisionForOrder(int $orderId, int $userId): void {
        $pdo = Database::getConnection();

        // Check if this VPS has already provise
        $stmtCheck = $pdo->prepare("SELECT id FROM services WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)");
        $stmtCheck->execute([$orderId]);

        if ($stmtCheck->fetch()) {
            return;
        }

        // Get the list of VPS in this order
        $stmtItems = $pdo->prepare("SELECT id, product_name, product_cpu, product_ram FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        // Insert to table service in database
        $stmtInsert = $pdo->prepare("
            INSERT INTO services (order_item_id, user_id, hostname, ip_address, root_password, os, status, start_date, expiry_date)
            VALUES (?, ?, ?, ?, ?, 'Ubuntu 22.04 LTS', 'running', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))
        ");

        // VM Bridge API base URL (Docker → Windows host)
        $vmBridgeUrl = (getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001');

        // Loop every items for creating VPS
        foreach ($items as $item) {
            // 1. Generate root password locally (so we know it regardless of VM Bridge response)
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            $password = substr(str_shuffle($chars), 0, 12);

            // 2. Call VM Bridge to clone + start a real VMware VM
            $hostname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['product_name'])) . '-' . rand(1000, 9999);
            $ip = '0.0.0.0';

            $apiUrl = $vmBridgeUrl . '/provision?order_id=' . $orderId . '&item_id=' . $item['id']
                . '&name=' . urlencode($hostname) . '&password=' . urlencode($password);

            $ctx = stream_context_create(['http' => ['timeout' => 120]]);
            $response = @file_get_contents($apiUrl, false, $ctx);

            if ($response !== false) {
                $data = json_decode($response, true);
                if ($data && !empty($data['success'])) {
                    $hostname = $data['hostname'] ?? $hostname;
                    $ip       = $data['ip'] ?? $ip;
                }
            }

            // 3. Save to database
            $stmtInsert->execute([$item['id'], $userId, $hostname, $ip, $password]);
        }
    }

    // Terminate all services for an order (used when admin cancels a provisioned order)
    public static function terminateForOrder(int $orderId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE services SET status = 'terminated'
            WHERE order_item_id IN (SELECT id FROM order_items WHERE order_id = ?)
              AND status NOT IN ('terminated')
        ");
        $stmt->execute([$orderId]);
    }

    // Display VPS information for customer
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