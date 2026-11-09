<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $conn;

    public function __construct()
    {
        try {
            $this->conn = DatabaseConnection::getConnection();
        } catch (\Throwable $e) {
            error_log("DB CONNECTION ERROR: " . $e->getMessage());

            throw new \Exception(
                "DB connection failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Enllaça els paràmetres d'una consulta preparada.
     *
     * Accepta:
     * - Paràmetres posicionals: ? amb índexs de l'array des de 0.
     * - Paràmetres amb nom: :email o email.
     *
     * PDO utilitza índexs posicionals a partir de l'1.
     *
     * @throws PDOException
     */
    private function bindParameters(
        PDOStatement $stmt,
        array $params
    ): void {
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $paramKey = $key + 1;
            } else {
                $paramKey = str_starts_with($key, ':')
                    ? $key
                    : ':' . $key;
            }

            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };

            $stmt->bindValue($paramKey, $value, $type);
        }
    }

    /**
     * Executa una consulta SQL i retorna els resultats. LEGACY.
     *
     * - Si $single = false: retorna sempre un array (pot ser []).
     * - Si $single = true: retorna un registre (array) o null.
     *
     * @throws PDOException
     */
    public function getData(
        string $query,
        array $params = [],
        bool $single = false
    ): array|null {
        $stmt = $this->conn->prepare($query);

        $this->bindParameters($stmt, $params);

        $stmt->execute();

        if ($single) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($row === false) ? null : $row;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Executa una consulta i retorna totes les files.
     *
     * @throws PDOException
     */
    public function getAll(
        string $query,
        array $params = []
    ): array {
        $stmt = $this->conn->prepare($query);

        $this->bindParameters($stmt, $params);

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    /**
     * Executa una consulta i retorna una fila o null.
     *
     * @throws PDOException
     */
    public function getOne(
        string $query,
        array $params = []
    ): ?array {
        $stmt = $this->conn->prepare($query);

        $this->bindParameters($stmt, $params);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * Retorna la connexió PDO interna.
     */
    public function getPdo(): PDO
    {
        return $this->conn;
    }

    /**
     * Executa una consulta d'inserció, actualització o eliminació.
     *
     * @return int Nombre de files afectades.
     * @throws PDOException
     */
    public function execute(
        string $query,
        array $params = []
    ): int {
        $stmt = $this->conn->prepare($query);

        $this->bindParameters($stmt, $params);

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Retorna l'últim ID inserit.
     */
    public function lastInsertId(): string
    {
        return $this->conn->lastInsertId();
    }

    /**
     * Actualitza registres d'una taula.
     */
    public function updateData(
        string $table,
        array $data,
        string $where,
        array $whereParams = []
    ): bool {
        $setParts = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setParts[] = "$column = :$column";
            $params[":$column"] = $value;
        }

        $setString = implode(', ', $setParts);

        $sql = "UPDATE $table SET $setString WHERE $where";

        try {
            $stmt = $this->conn->prepare($sql);

            $this->bindParameters($stmt, $params);

            $this->bindParameters($stmt, $whereParams);

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error a la consulta UPDATE: " . $e->getMessage());

            return false;
        }
    }

    public function beginTransaction(): bool
    {
        return $this->conn->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->conn->commit();
    }

    public function rollBack(): bool
    {
        return $this->conn->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->conn->inTransaction();
    }

    public function qi(string $table): string
    {
        return qi($table, $this->conn);
    }
}
