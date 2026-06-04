<?php
class ConsoleController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $serverName = $_GET["name"] ?? "astral-vps";
        $hostName = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", "-", $serverName)));

        require_once __DIR__ . "/../Views/user/console/index.php";
    }
}