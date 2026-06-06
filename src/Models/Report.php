<?php
class Report {
    // Calculate monthly total
    public static function getMonthlyRevenue(): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%m/%Y') AS month,
                SUM(total_price) AS revenue
            FROM orders
            WHERE status = 'success'
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC
            LIMIT 12
        ";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get the Top 5 Bestselling Products
    public static function getTopSellingProducts(): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                oi.product_name, 
                SUM(oi.quantity) AS total_sold,
                SUM(oi.subtotal) AS total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'success'
            GROUP BY oi.product_id, oi.product_name
            ORDER BY total_sold DESC
            LIMIT 5
        ";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get the Top 5 Worst-Selling Products
    public static function getWorstSellingProducts(): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                p.name AS product_name, 
                COALESCE(SUM(oi.quantity), 0) AS total_sold
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'success'
            WHERE p.is_active = 1
            GROUP BY p.id, p.name
            ORDER BY total_sold ASC
            LIMIT 5
        ";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get Summary stat
    public static function getSummaryStats(): array {
        $pdo = Database::getConnection();

        $stats = [];

        $stats['total_users'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
        $stats['total_orders'] = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stats['total_revenue'] = (float) $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'success'")->fetchColumn() ?: 0;

        return $stats;
    }
}