<?php
require_once __DIR__ . "/../Models/AdminEmail.php";
require_once __DIR__ . "/../Models/User.php";
class AdminEmailController {
    private function checkAdmin() {
        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
        }

        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            die("403 - No access.");
        }
    }

    // Load UI and fetch list of customers to prepare sending email
    public function index() {
        $this->checkAdmin();
        $customers = User::getAllCustomers();
        require_once __DIR__ . '/../Views/admin/emails/index.php';
    }

    // Send email to user
    public function send() {
        $this->checkAdmin();

        // Prepare email data 
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $senderId = $_SESSION["user_id"];
            $recipientId = empty($_POST["recipient_id"]) ? null : (int)$_POST["recipient_id"];
            $subject = trim($_POST["subject"]);
            $body = trim($_POST["body"]);

            AdminEmail::send($senderId, $recipientId, $subject, $body);
            header('Location: /admin/emails?msg=sent');
            exit;
        }
    }
}