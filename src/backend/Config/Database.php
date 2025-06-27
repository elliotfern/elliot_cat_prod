<?php

namespace App\Config;

use App\Config\DatabaseConnection;
use PDO;
use PDOException;

class Database
{
    private PDO $conn;

    public function __construct()
    {
        $conn = DatabaseConnection::getConnection();
        if ($conn === null) {
            throw new \Exception("No s'ha pogut establir la connexió amb la base de dades.");
        }
        $this->conn = $conn;
    }

    /**
     * Executa una consulta SQL i retorna els resultats.
     *
     * @param string $query Consulta SQL
     * @param array $params Paràmetres per a la consulta preparada, amb claus amb ':' (ex: ':id')
     * @param bool $single Indica si es vol un únic registre o tots
     * @return array|null Retorna array de resultats, un únic registre o null si no hi ha dades.
     * @throws PDOException En cas d'error en la consulta
     */
    public function getData(string $query, array $params = [], bool $single = false): array|null
    {
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        if ($single) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result === false ? null : $result;
        } else {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return empty($result) ? null : $result;
        }
    }

    /**
     * Executa una consulta d'inserció, actualització o eliminació.
     *
     * @param string $query Consulta SQL
     * @param array $params Paràmetres per a la consulta preparada
     * @return int Nombre de files afectades
     * @throws PDOException En cas d'error en la consulta
     */
    public function execute(string $query, array $params = []): int
    {
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Retorna l'últim ID inserit.
     *
     * @return string Últim ID inserit
     */
    public function lastInsertId(): string
    {
        return $this->conn->lastInsertId();
    }

    public function updateData(string $table, array $data, string $where, array $whereParams = []): bool
    {
        // Construir SET de la consulta con los campos a actualizar
        $setParts = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setParts[] = "$column = :$column";
            $params[":$column"] = $value;
        }

        $setString = implode(', ', $setParts);

        // Consulta SQL completa
        $sql = "UPDATE $table SET $setString WHERE $where";

        try {
            $stmt = $this->conn->prepare($sql);

            // Bind de los parámetros de SET
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }

            // Bind de los parámetros del WHERE
            foreach ($whereParams as $key => $val) {
                // Asegurar que el parámetro empiece por ':'
                $paramKey = (str_starts_with($key, ':')) ? $key : ':' . $key;
                $stmt->bindValue($paramKey, $val);
            }

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error a la consulta UPDATE: " . $e->getMessage());
            return false;
        }
    }
}
