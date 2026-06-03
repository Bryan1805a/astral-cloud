<?php
require_once __DIR__ . "/../Models/AdminEmail.php";

class InboxController {
    // Fetch all email data from database for user who want to read it
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION["user_id"];
        $email = AdminEmail::getInboxForUser($userId);

        require_once __DIR__ . '/../Views/user/inbox/index.php';
    }

    // Mark read
    public function markRead() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER["REQUEST_DATA"] === "POST" && !isset($_SESSION["user_id"])) {
            AdminEmail::markAsRead((int)$_POST["id"], $_SESSION["user_id"]);
            header("Location: /inbox");
            exit;
        }
    }
}