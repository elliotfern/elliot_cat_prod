<?php

namespace App\Config;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $conn = null;

    public static function getConnection(): PDO
    {
        if (self::$conn === null) {
            $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
            $dbUser = $_ENV['DB_USER'] ?? 'root';
            $dbPass = $_ENV['DB_PASS'] ?? '';
            $dbName = $_ENV['DB_DBNAME'] ?? '';

            try {
                self::$conn = new PDO(
                    "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                error_log("Error de conexión: " . $e->getMessage());
                throw $e; // ✅ no retornes null
            }
        }

        return self::$conn;
    }
}
