<?php

class InboxController {
    // Fetch all email data from database for user who want to read it
    public function index() {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $userId = $_SESSION["user_id"];
        $email = AdminEmail::getInboxForUser($userId);

        view('inbox/index', [
            'emails' => $email,
            'styles' => '',
            'title'  => 'Inbox | Astral Cloud',
        ]);
    }

    // Mark read
    public function markRead() {
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["user_id"])) {
            AdminEmail::markAsRead((int)$_POST["id"], $_SESSION["user_id"]);
            header("Location: /inbox");
            exit;
        }
    }
}