<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Uuid;
use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\AdminMiddleware;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Metode no permès']);
    exit();
}

// a) Inserir espai
if ($slug === 'espai') {

    AdminMiddleware::handle();

    // Leer JSON
    $input_data = file_get_contents("php://input");
    $data = json_decode($input_data, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
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

    $nom        = requireField($data, 'nom', $errors);
    $slug_input = requireField($data, 'slug', $errors);
    $descripcio = requireField($data, 'descripcio', $errors);

    $any_fundacio = optionalField($data, 'any_fundacio');
    $web          = optionalField($data, 'web');

    $tipus_id  = requireField($data, 'tipus_id', $errors); // INT
    $ciutat_id = requireField($data, 'ciutat_id', $errors); // UUID
    $img_id    = optionalField($data, 'img_id'); // UUID

    $lat = optionalField($data, 'coordinades_latitud');
    $lon = optionalField($data, 'coordinades_longitud');

    if (!is_numeric($tipus_id)) {
        $errors['tipus_id'] = 'invalid_int';
    }

    if (!isUuid($ciutat_id)) {
        $errors['ciutat_id'] = 'invalid_uuid';
    }

    if ($img_id && !isUuid($img_id)) {
        $errors['img_id'] = 'invalid_uuid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    // Fechas
    $dateCreated  = date('Y-m-d');
    $dateModified = null;

    // UUID v7
    $uuid = ramsey::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = Uuid::toString($uuidBytes);

    // Convertir a binary
    $ciutat_id_bin = Uuid::toBinary($ciutat_id);
    $img_id_bin = $img_id ? Uuid::toBinary($img_id) : null;

    $sql = "INSERT INTO " . Tables::DB_VIATGES_ESPAIS . " (
                id,
                nom,
                slug,
                any_fundacio,
                descripcio,
                tipus_id,
                web,
                ciutat_id,
                img_id,
                coordinades_latitud,
                coordinades_longitud,
                dateCreated,
                dateModified
            ) VALUES (
                :id,
                :nom,
                :slug,
                :any_fundacio,
                :descripcio,
                :tipus_id,
                :web,
                :ciutat_id,
                :img_id,
                :lat,
                :lon,
                :dateCreated,
                :dateModified
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
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
        $stmt->bindValue(':dateCreated', $dateCreated, PDO::PARAM_STR);
        $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_NULL);

        if ($stmt->execute()) {
            Response::success(
                MissatgesAPI::success('create'),
                [
                    'id' => $uuidString,
                    'slug' => $slug_input
                ],
                201
            );
            exit;
        }

        Response::error(
            MissatgesAPI::error('db_error'),
            [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ],
            500
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
