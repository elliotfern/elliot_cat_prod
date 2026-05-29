<?php

namespace App\Config;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $conn = null;

    public static function getConnection(): PDO
    {
        if (self::$conn !== null) {
            return self::$conn;
        }

        $dbUser = $_ENV['DB_USER'] ?? '';
        $dbPass = $_ENV['DB_PASS'] ?? '';
        $dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbName = $_ENV['DB_DBNAME'] ?? '';
        $dbPort = $_ENV['DB_PORT'] ?? 3306;;

        try {
            self::$conn = new PDO(
                "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            throw $e;
        }

        return self::$conn;
    }

    public static function reset(): void
    {
        self::$conn = null;
    }
}
