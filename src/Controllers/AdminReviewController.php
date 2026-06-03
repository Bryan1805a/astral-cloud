<?php
class AdminReviewController {
    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
        }

        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            die("403 - No access.");
        }
    }
    
    // Load UI and fetch all Reviews from database when enter this route
    public function index() {
        $this->checkAdmin();
        $reviews = Review::getAllForAdmin();
        require_once __DIR__ . '/../Views/admin/reviews/index.php';
    }

    // Toggle notification
    public function toggle() {
        $this->checkAdmin();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = (int)$_SERVER["id"];
            if ($id > 0) {
                Review::toggleVisibility($id);
            }
            header('Location: /admin/reviews?msg=updated');
            exit;
        }
    }
}