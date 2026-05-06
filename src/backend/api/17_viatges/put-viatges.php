<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Uuid;
use App\Utils\AdminMiddleware;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Metode no permès']);
    exit();
}



// a) Actualitzar espai
if ($slug === 'espai') {

    AdminMiddleware::handle();

    // Leer JSON
    $input_data = file_get_contents("php://input");
    $data = json_decode($input_data, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
    }

    function parseCoordinate($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Normalizar coma decimal europea
        $value = str_replace(',', '.', trim($value));

        // Si ya es decimal válido → devolver directamente
        if (is_numeric($value)) {
            return (float)$value;
        }

        // Normalizar símbolos DMS
        $value = str_replace(["′", "’"], "'", $value);
        $value = str_replace(["″", '"'], '"', $value);

        // Regex DMS
        if (preg_match('/(\d+)[^\d]+(\d+)[^\d]+([\d\.]+)[^\d]*([NSEW])/i', $value, $m)) {

            $deg = (float)$m[1];
            $min = (float)$m[2];
            $sec = (float)$m[3];
            $dir = strtoupper($m[4]);

            $decimal = $deg + ($min / 60) + ($sec / 3600);

            if ($dir === 'S' || $dir === 'W') {
                $decimal *= -1;
            }

            return $decimal;
        }

        throw new InvalidArgumentException("Formato de coordenada inválido: " . $value);
    }

    function isUuid($s)
    {
        return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
    }

    // Helpers
    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return data_input($data[$key]);
    }

    function optionalField(array $data, string $key)
    {
        return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
            ? data_input($data[$key])
            : null;
    }

    // Validación
    $errors = [];

    $id         = requireField($data, 'id', $errors); // UUID string
    $nom        = requireField($data, 'nom', $errors);
    $slug_input = requireField($data, 'slug', $errors);
    $descripcio = requireField($data, 'descripcio', $errors);

    $any_fundacio = optionalField($data, 'any_fundacio');
    $web          = optionalField($data, 'web');

    $tipus_id  = requireField($data, 'tipus_id', $errors); // INT
    $ciutat_id = requireField($data, 'ciutat_id', $errors); // UUID
    $img_id    = optionalField($data, 'img_id'); // UUID

    if (!is_numeric($tipus_id)) {
        $errors['tipus_id'] = 'invalid_int';
    }

    if (!isUuid($ciutat_id)) {
        $errors['ciutat_id'] = 'invalid_uuid';
    }

    if ($img_id && !isUuid($img_id)) {
        $errors['img_id'] = 'invalid_uuid';
    }

    try {
        $lat = isset($data['coordinades_latitud'])
            ? parseCoordinate($data['coordinades_latitud'])
            : null;

        $lon = isset($data['coordinades_longitud'])
            ? parseCoordinate($data['coordinades_longitud'])
            : null;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('invalid_data'),
            ['coordenades' => 'invalid_format'],
            400
        );
        exit;
    }

    if ($lat !== null && ($lat < -90 || $lat > 90)) {
        $errors['coordinades_latitud'] = 'out_of_range';
    }

    if ($lon !== null && ($lon < -180 || $lon > 180)) {
        $errors['coordinades_longitud'] = 'out_of_range';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    // Fecha modificación
    $dateModified = date('Y-m-d');

    // Convertir a binary
    $ciutat_id_bin = Uuid::toBinary($ciutat_id);
    $img_id_bin = $img_id ? Uuid::toBinary($img_id) : null;
    $id_bin = uuid::toBinary($id);

    $sql = "UPDATE " . Tables::DB_VIATGES_ESPAIS . " SET
                nom = :nom,
                slug = :slug,
                any_fundacio = :any_fundacio,
                descripcio = :descripcio,
                tipus_id = :tipus_id,
                web = :web,
                ciutat_id = :ciutat_id,
                img_id = :img_id,
                coordinades_latitud = :lat,
                coordinades_longitud = :lon,
                dateModified = :dateModified
            WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':slug', $slug_input, PDO::PARAM_STR);
        $stmt->bindValue(':any_fundacio', $any_fundacio, PDO::PARAM_STR);
        $stmt->bindValue(':descripcio', $descripcio, PDO::PARAM_STR);
        $stmt->bindValue(':tipus_id', (int)$tipus_id, PDO::PARAM_INT);
        $stmt->bindValue(':web', $web, PDO::PARAM_STR);
        $stmt->bindValue(':ciutat_id', $ciutat_id_bin, PDO::PARAM_LOB);

        if ($img_id_bin) {
            $stmt->bindValue(':img_id', $img_id_bin, PDO::PARAM_LOB);
        } else {
            $stmt->bindValue(':img_id', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':lat', $lat, PDO::PARAM_STR);
        $stmt->bindValue(':lon', $lon, PDO::PARAM_STR);
        $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_STR);

        $stmt->execute();

        $checkSql = "SELECT 1 FROM " . Tables::DB_VIATGES_ESPAIS . " WHERE id = :id LIMIT 1";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $checkStmt->execute();

        if (!$checkStmt->fetchColumn()) {
            Response::error(
                MissatgesAPI::error('not_found'),
                ['id' => $id],
                404
            );
            exit;
        }

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id,
                'slug' => $slug_input
            ],
            200
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            500
        );
        exit;
    }
}
