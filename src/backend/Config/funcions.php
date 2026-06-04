<?php


/**
 * Verifica que la solicitud provenga del dominio permitido.
 *
 * @param string $allowedOrigin El dominio permitido.
 * @return void
 */

function corsAllow(array $allowedOrigins): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // ✅ Sin Origin (Postman / curl / same-origin): no es un caso CORS -> no bloquees
    if ($origin === '') {
        return;
    }

    if (in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        return;
    }

    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Origin not allowed', 'origin' => $origin]);
    exit();
}

function getData($query, $params = [], $single = false)
{
    global $conn;
    /** @var PDO $conn */

    try {
        // Preparar la consulta
        $stmt = $conn->prepare($query);

        // Si hay parámetros, los vinculamos
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        // Ejecutar la consulta
        $stmt->execute();

        // Si esperamos un solo resultado, usamos fetch()
        if ($single) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Si esperamos varios resultados, usamos fetchAll()
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Verificar si hay resultados
        if ($row) {
            return $row;
        } else {
            return ['status' => 'error', 'message' => 'No hi ha cap registre disponible.'];
        }
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Error a la consulta'];
    }
}


/**
 * Quote seguro para identificadores (tabla, esquema, columna, alias).
 * Soporta "schema.table AS t". No usar para valores.
 */
/**
 * Quote seguro para identificadores (tabla, esquema, columna, alias).
 * Soporta "schema.table AS t". No usar para valores.
 */
function qi(string $identifier, PDO $pdo): string
{
    if (trim($identifier) === '') {
        throw new InvalidArgumentException('Empty SQL identifier');
    }

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    [$main, $alias] = array_pad(
        preg_split('/\s+as\s+/i', trim($identifier), 2),
        2,
        null
    );

    $quote = fn(string $id) => match ($driver) {
        'mysql' => '`' . str_replace('`', '``', $id) . '`',
        'pgsql' => '"' . str_replace('"', '""', $id) . '"',
        default => $id,
    };

    $dotQuoted = implode('.', array_map($quote, explode('.', $main)));

    return $alias
        ? $dotQuoted . ' AS ' . $quote($alias)
        : $dotQuoted;
}
