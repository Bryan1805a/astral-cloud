<?php
require_once __DIR__ . "/../Models/Report.php";

class AdminDashboardController {
    private function checkAdmin() {
        // Redirect to login page if user not login
        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
        }

        // Check user role
        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            die("403 - No access.");
        }
    }

    public function index() {
        $this->checkAdmin();

        // Get summary
        $stats = Report::getSummaryStats();
        $topProducts = Report::getTopSellingProducts();
        $worstProducts = Report::getWorstSellingProducts();

        // Get data for chart
        $monthlyRevenue = Report::getMonthlyRevenue();
        $chartLabels = [];
        $chartData = [];
        foreach ($monthlyRevenue as $row) {
            $chartLabels[] = $row["month"];
            $chartData[] = (float)$row["revenue"];
        }

        // Convert PHP arrays to JSON for the view
        $chartLabelsJson = json_encode($chartLabels);
        $chartDataJson = json_encode($chartData);

        require_once __DIR__ . "/../Views/admin/dashboard/index.php";
    }
}