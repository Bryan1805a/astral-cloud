<?php
require_once __DIR__ . "/../Models/Report.php";

class AdminDashboardController {
    private function checkAdmin() {
        if (session_start() !== PHP_SESSION_NONE) {
            session_start();
        }

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
        foreach($monthlyRevenue as $row) {
            $chartLabels = $row["month"];
            $chartData = (float)$row["revenue"];
        }

        // Convert PHP to JSON array
        $chartLabelsJSON = json_encode($chartLabels);
        $chartDataJSON = json_encode($chartData);

        require_once __DIR__ . "/../Views/admin/dashboard/index.php";
    }
}