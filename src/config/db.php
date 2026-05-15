<?php
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'astral_cloud';
$user = getenv('DB_USER') ?: 'astral_user';
$pass = getenv('DB_PASS') ?: 'astral_pass_123';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}