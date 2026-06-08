<?php

function loadEnv(string $path): void {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "" || str_starts_with($line, "#")) continue;

        $parts = explode("=", $line, 2);
        if (count($parts) !== 2) continue;

        $key   = trim($parts[0]);
        $value = trim($parts[1]);

        if ((str_starts_with($value, "\"") && str_ends_with($value, "\"")) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

loadEnv(__DIR__ . "/../../.env");

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
