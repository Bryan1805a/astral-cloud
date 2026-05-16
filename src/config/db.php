<?php
    // debug mode
    // only for dev
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    // Get data from env
    $host = getenv("DB_HOST") ?: "db";
    $db = getenv("DB_NAME") ?: "astral_cloud";
    $user = getenv("DB_USER") ?: "astral_user";
    $pass = getenv("DB_PASS") ?: "astral_pass_123";
    $charset = "utf8mb4";

    // Data Source Name configuration
    $dsn = "mysql:host=$host;
            dbname=$db;
            charset=$charset";

    $option = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Try catch
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // return key - value
        PDO::ATTR_EMULATE_PREPARES => false, // Prevent SQL injection
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $option);
    } catch (\PDOException $e) {
        die("<h2 style='color:red;'>Database connected failed:" . $e->getMessage() . "</h2>");
    }
?>