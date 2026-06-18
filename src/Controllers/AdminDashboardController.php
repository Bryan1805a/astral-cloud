<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Report;

class AdminDashboardController extends Controller {
    private function checkAdmin() {
        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
        }

        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            die("403 - No access.");
        }
    }

    public function index() {
        $this->checkAdmin();

        $stats          = Report::getSummaryStats();
        $topProducts    = Report::getTopSellingProducts();
        $worstProducts  = Report::getWorstSellingProducts();
        $orderBreakdown = Report::getOrderStatusBreakdown();
        $serviceCounts  = Report::getServiceStatusCounts();
        $recentActivity = Report::getRecentActivity(10);
        $revenueComp    = Report::getMonthlyComparison();
        $allProductSales= Report::getAllProductSales();
        $topCustomers   = Report::getTopCustomers(6);

        $monthlyRevenue = Report::getMonthlyRevenue();
        $chartLabels = [];
        $chartData   = [];
        foreach ($monthlyRevenue as $row) {
            $chartLabels[] = $row["month"];
            $chartData[]   = (float) $row["revenue"];
        }

        $chartLabelsJson = json_encode($chartLabels);
        $chartDataJson   = json_encode($chartData);
        $orderLabelsJson = json_encode(array_column($orderBreakdown, 'status'));
        $orderDataJson   = json_encode(array_column($orderBreakdown, 'count'));

        // System health
        $dbOk = false;
        try {
            Database::getConnection()->query("SELECT 1");
            $dbOk = true;
        } catch (\Throwable $e) {}

        $bridgeUrl = getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001';
        $bridgeOk = false;
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $result = @file_get_contents($bridgeUrl . '/ttyd/status?service_id=0', false, $ctx);
        $bridgeOk = $result !== false;

        $systemHealth = [
            'database'  => $dbOk,
            'vm_bridge' => $bridgeOk,
            'bridge_url' => $bridgeUrl,
        ];

        require_once __DIR__ . "/../Views/admin/dashboard/index.php";
    }
}