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
     * Executa una consulta SQL i retorna els resultats. > LEGACY
     *
     * - Si $single = false: retorna SEMPRE un array (pot ser []).
     * - Si $single = true : retorna un registre (array) o null si no hi ha fila.
     *
     * @param string $query  Consulta SQL
     * @param array  $params Paràmetres per a la consulta preparada (claus amb ':' o sense)
     * @param bool   $single Indica si es vol un únic registre o tots
     * @return array|null
     * @throws PDOException En cas d'error en la consulta
     */
    public function getData(string $query, array $params = [], bool $single = false): array|null
    {
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            // Accepta ':slug' o 'slug'
            $paramKey = (is_string($key) && $key !== '' && $key[0] !== ':') ? ':' . $key : $key;

            // Tipus PDO
            if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif ($value === null) {
                $type = PDO::PARAM_NULL;
            } else {
                $type = PDO::PARAM_STR;
            }

            $stmt->bindValue($paramKey, $value, $type);
        }

        $stmt->execute();

        if ($single) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($row === false) ? null : $row;
        }

        // ✅ IMPORTANT: sempre array (si no hi ha files, retorna [])
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Ejecuta una consulta y devuelve TODAS las filas (array, puede ser vacío)
     */
    public function getAll(string $query, array $params = []): array
    {
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $paramKey = (is_string($key) && $key !== '' && $key[0] !== ':')
                ? ':' . $key
                : $key;

            $type = match (true) {
                is_int($value)   => PDO::PARAM_INT,
                is_bool($value)  => PDO::PARAM_BOOL,
                $value === null  => PDO::PARAM_NULL,
                default          => PDO::PARAM_STR,
            };

            $stmt->bindValue($paramKey, $value, $type);
        }

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Ejecuta una consulta y devuelve UNA fila o null
     */
    public function getOne(string $query, array $params = []): ?array
    {
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $paramKey = (is_string($key) && $key !== '' && $key[0] !== ':')
                ? ':' . $key
                : $key;

            $type = match (true) {
                is_int($value)   => PDO::PARAM_INT,
                is_bool($value)  => PDO::PARAM_BOOL,
                $value === null  => PDO::PARAM_NULL,
                default          => PDO::PARAM_STR,
            };

            $stmt->bindValue($paramKey, $value, $type);
        }

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
