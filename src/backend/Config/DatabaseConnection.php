<?php

namespace App\Config;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $conn = null;

    public static function getConnection(): ?PDO
    {
        if (self::$conn === null) {
            $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
            $dbUser = $_ENV['DB_USER'] ?? 'root';
            $dbPass = $_ENV['DB_PASS'] ?? '';
            $dbName = $_ENV['DB_DBNAME'] ?? '';

            try {
                self::$conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
                self::$conn->exec("SET NAMES utf8");
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log("Error de conexión: " . $e->getMessage());
                return null;
            }
        }

        return self::$conn;
    }
}
