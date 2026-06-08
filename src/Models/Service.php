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
        $stmtItems = $pdo->prepare("SELECT id, product_name FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();

        // Insert to table service in database
        $stmtInsert = $pdo->prepare("
            INSERT INTO services (order_item_id, user_id, hostname, ip_address, root_password, os, status, start_date, expiry_date)
            VALUES (?, ?, ?, ?, ?, 'Ubuntu 22.04 LTS', 'running', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))
        ");

        // Loop every items for creating VPS
        foreach ($items as $item) {
            // 1. Random Hostname: vps-pro-8472
            $hostname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['product_name'])) . '-' . rand(1000, 9999);
            
            // 2. Random IP Address (Dummy public IP)
            $ip = '103.14.' . rand(1, 255) . '.' . rand(1, 255);
            
            // 3. Random Root Password (12 characters)
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            $password = substr(str_shuffle($chars), 0, 12);

            // Save to database
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