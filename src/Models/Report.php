<?php
class Report {
    // Calculate monthly total
    public static function getMonthlyRevenue(): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                CONCAT(LPAD(month, 2, '0'), '/', year) AS month,
                revenue
            FROM (
                SELECT 
                    YEAR(created_at) AS year,
                    MONTH(created_at) AS month,
                    SUM(total_price) AS revenue
                FROM orders
                WHERE status = 'success'
                GROUP BY YEAR(created_at), MONTH(created_at)
            ) AS monthly
            ORDER BY year, month ASC
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
        $stats['active_services'] = (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'running'")->fetchColumn();
        $stats['pending_orders'] = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $stats['today_revenue'] = (float) $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'success' AND DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;

        return $stats;
    }

    public static function getOrderStatusBreakdown(): array {
        $pdo = Database::getConnection();
        return $pdo->query("
            SELECT status, COUNT(*) AS count
            FROM orders
            GROUP BY status
            ORDER BY FIELD(status, 'pending','confirmed','provisioning','active','success','cancelled')
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getServiceStatusCounts(): array {
        $pdo = Database::getConnection();
        return [
            'running'   => (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'running'")->fetchColumn(),
            'stopped'   => (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'stopped'")->fetchColumn(),
            'suspended' => (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'suspended'")->fetchColumn(),
            'total'     => (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status != 'terminated'")->fetchColumn(),
        ];
    }

    public static function getRecentActivity(int $limit = 10): array {
        $pdo = Database::getConnection();
        return $pdo->query("
            SELECT a.action, a.description, a.created_at, u.name AS user_name
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT {$limit}
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMonthlyComparison(): array {
        $pdo = Database::getConnection();

        $thisMonth = $pdo->query("
            SELECT COALESCE(SUM(total_price), 0)
            FROM orders
            WHERE status = 'success'
              AND YEAR(created_at) = YEAR(CURDATE())
              AND MONTH(created_at) = MONTH(CURDATE())
        ")->fetchColumn();

        $lastMonth = $pdo->query("
            SELECT COALESCE(SUM(total_price), 0)
            FROM orders
            WHERE status = 'success'
              AND created_at >= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL DAYOFMONTH(CURDATE())-1 DAY), INTERVAL 1 MONTH)
              AND created_at <  DATE_SUB(CURDATE(), INTERVAL DAYOFMONTH(CURDATE())-1 DAY)
        ")->fetchColumn();

        $change = $thisMonth - $lastMonth;
        $pct = $lastMonth > 0 ? round(($change / $lastMonth) * 100, 1) : ($thisMonth > 0 ? 100 : 0);

        return [
            'this_month' => (float) $thisMonth,
            'last_month' => (float) $lastMonth,
            'change'     => (float) $change,
            'pct'        => (float) $pct,
        ];
    }

    public static function getAllProductSales(): array {
        $pdo = Database::getConnection();
        return $pdo->query("
            SELECT
                p.name AS product_name,
                p.price,
                COALESCE(SUM(oi.quantity), 0) AS total_sold,
                COALESCE(SUM(oi.subtotal), 0) AS total_revenue
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'success'
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.price
            ORDER BY total_sold DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTopCustomers(int $limit = 8): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT
                name, email, tier, total_spent,
                (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count,
                (SELECT COUNT(*) FROM services WHERE user_id = u.id AND status = 'running') AS active_services
            FROM users u
            WHERE role = 'user'
            ORDER BY total_spent DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}