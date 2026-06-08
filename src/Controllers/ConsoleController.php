<?php
class ConsoleController {
    public function index() {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $serverName = $_GET["name"] ?? "astral-vps";
        $hostname = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", "-", $serverName)));

        require_once __DIR__ . "/../Views/console/index.php";
    }
}