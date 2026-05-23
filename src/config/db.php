<?php

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = getenv("DB_HOST") ?: "db";
            $db   = getenv("DB_NAME") ?: "astral_cloud";
            $user = getenv("DB_USER") ?: "astral_user";
            $pass = getenv("DB_PASS");

            if (!$pass) {
                die("DB_PASS not set in environment. Copy .env.example to .env and configure your credentials.");
            }

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
