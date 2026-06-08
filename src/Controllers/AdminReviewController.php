<?php

class AdminReviewController {
    private function checkAdmin() {
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
            verifyCsrfToken();
            $id = (int)($_POST["id"] ?? 0);
            if ($id > 0) {
                Review::toggleVisibility($id);
                AuditLog::log("review.toggle_visibility", "review", $id,
                    "Toggled review visibility (ID: {$id})"
                );
            }
            header('Location: /admin/reviews?msg=updated');
            exit;
        }
    }
}