<?php

class Order {
    // Create order and save to database
    public static function create(int $userId, ?int $voucherId, ?string $voucherCode, float $subtotal, float $discount, float $total, string $note): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, voucher_id, voucher_code, subtotal, discount_amount, total_price, note, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$userId, $voucherId, $voucherCode, $subtotal, $discount, $total, $note]);
        return (int) $pdo->lastInsertId();
    }

    // Add items to existing order
    public static function addItem(int $orderId, int $productId, string $name, string $cpu, string $ram, string $storage, float $price, int $quantity, float $subtotal): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_cpu, product_ram, product_storage, unit_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $productId, $name, $cpu, $ram, $storage, $price, $quantity, $subtotal]);
    }

    // Save voucher usage history
    public static function recordVoucherUsage(int $voucherId, int $userId, int $orderId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO voucher_usages (voucher_id, user_id, order_id) VALUES (?, ?, ?)");
        $stmt->execute([$voucherId, $userId, $orderId]);
    }

    // Find order by ID
    public static function findById(int $orderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    // Get user's order information
    public static function getUserOrders(int $userId): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT 
                o.id AS order_id, 
                o.total_price, 
                o.status AS order_status, 
                o.created_at,
                oi.id AS order_item_id,
                oi.product_name, 
                oi.product_cpu, 
                oi.product_ram
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["user_id" => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cancel order
    public static function cancelOrder(int $orderId, int $userId): bool {
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT voucher_id, status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE");
            $stmt->execute([$orderId, $userId]);
            $order = $stmt->fetch();
            
            // Check if order exists and status is pending
            if (!$order || $order["status"] !== "pending") {
                $pdo->rollBack();
                return false;
            }

            // Update status to cancelled
            $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmtUpdate->execute([$orderId]);

            // Return vouchers if user applies vouchers
            if (!empty($order["voucher_id"])) {
                // Delete voucher usage history for this order
                $stmtDelVoucher = $pdo->prepare("DELETE FROM voucher_usages WHERE voucher_id = ? AND user_id = ? AND order_id = ?");
                $stmtDelVoucher->execute([$order["voucher_id"], $userId, $orderId]);

                $stmtRestoreVoucher = $pdo->prepare("UPDATE vouchers SET used_count = used_count - 1 WHERE id = ?");
                $stmtRestoreVoucher->execute([$order["voucher_id"]]);
            }

            // Restore product stock
            self::restoreStock($orderId);

            $pdo->commit();
            return True;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // Get all order information (Only for Admin)
    public static function getAllOrders(): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT 
                o.id AS order_id, 
                o.total_price, 
                o.status AS order_status, 
                o.created_at,
                u.name AS customer_name,
                u.email AS customer_email,
                oi.product_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            ORDER BY o.created_at DESC
        ";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update order status (Only for admin)
    public static function updateStatus(int $orderId, string $status): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
    }

    // Restore product stock when an order is cancelled
    public static function restoreStock(int $orderId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE products p
            JOIN order_items oi ON p.id = oi.product_id
            SET p.stock = p.stock + oi.quantity
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
    }

    // Get full invoice data for PDF generation
    public static function getInvoiceData(int $orderId): ?array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT
                o.id              AS order_id,
                o.subtotal,
                o.discount_amount,
                o.total_price,
                o.status,
                o.note,
                o.created_at,
                o.voucher_code,
                u.id              AS customer_id,
                u.name            AS customer_name,
                u.email           AS customer_email,
                u.phone           AS customer_phone,
                u.tier            AS customer_tier,
                u.total_spent     AS customer_total_spent
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) return null;

        $stmtItems = $pdo->prepare("
            SELECT
                product_name,
                product_cpu,
                product_ram,
                product_storage,
                unit_price,
                quantity,
                subtotal
            FROM order_items
            WHERE order_id = ?
        ");
        $stmtItems->execute([$orderId]);
        $order["items"] = $stmtItems->fetchAll();

        return $order;
    }
}
