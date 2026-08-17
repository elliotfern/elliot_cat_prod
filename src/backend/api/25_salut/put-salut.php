<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

/*
 * BACKEND SALUT
 * PUT SALUT
 */

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


/**
 * PUT : Modifica facultatiu
 * URL: https://elliot.cat/api/salut/put/facultatiu
 */
if ($slug === "facultatiu") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
        exit;
    }

    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return $data[$key];
    }

    function optionalField(array $data, string $key)
    {
        return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
            ? $data[$key]
            : null;
    }

    function isUuid($s)
    {
        return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
    }

    $errors = [];

    $id       = requireField($data, 'id', $errors);
    $nom      = requireField($data, 'nom', $errors);
    $genereFacultatiu = $data['genere'] ?? 'm';
    $especialitat = requireField($data, 'especialitat', $errors);
    $direccio = optionalField($data, 'direccio');
    $email    = optionalField($data, 'email');
    $telefon  = optionalField($data, 'telefon');
    $ciutatId = optionalField($data, 'ciutat_id');

    if ($id !== null && !isUuid($id)) {
        $errors['id'] = 'invalid_uuid';
    }

    if ($ciutatId !== null && !isUuid($ciutatId)) {
        $errors['ciutat_id'] = 'invalid_uuid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    $id_bin = Uuid::toBinary($id);

    $sql = "UPDATE " . Tables::DB_SALUT_FACULTATIUS . " SET
                nom = :nom,
                genere = :genere,
                direccio = :direccio,
                ciutat_id = :ciutat_id,
                email = :email,
                telefon = :telefon,
                especialitat = :especialitat,
                updated_at = NOW()
            WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':genere', $genereFacultatiu, PDO::PARAM_STR);
        $stmt->bindValue(':especialitat', $especialitat, PDO::PARAM_STR);
        $stmt->bindValue(':direccio', $direccio, $direccio === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':ciutat_id', $ciutatId === null ? null : App\Utils\Uuid::toBinary($ciutatId), $ciutatId === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
        $stmt->bindValue(':email', $email, $email === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':telefon', $telefon, $telefon === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($stmt->execute()) {
            Response::success(
                MissatgesAPI::success('update'),
                [
                    'id'  => $id,
                    'nom' => $nom,
                ],
                httpCode: 200
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
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }


    /**
     * PUT : Alta medicament
     * URL: https://elliot.cat/api/salut/put/medicament
     */
} else if ($slug === "medicament") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
        exit;
    }

    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return $data[$key];
    }

    function optionalField(array $data, string $key)
    {
        return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
            ? $data[$key]
            : null;
    }

    function isUuid($s)
    {
        return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
    }

    $errors = [];

    $id                = requireField($data, 'id', $errors);
    $medicament        = requireField($data, 'medicament', $errors);
    $dosis             = optionalField($data, 'dosis');
    $quantitatDefecte  = optionalField($data, 'quantitat_defecte');
    $facultatiuId      = optionalField($data, 'facultatiu_id');
    $necessitaRecepta  = array_key_exists('necessita_recepta', $data) ? (bool) $data['necessita_recepta'] : false;

    if ($id !== null && !isUuid($id)) {
        $errors['id'] = 'invalid_uuid';
    }

    if ($facultatiuId !== null && !isUuid($facultatiuId)) {
        $errors['facultatiu_id'] = 'invalid_uuid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    $id_bin = Uuid::toBinary($id);

    $sql = "UPDATE " . Tables::DB_SALUT_MEDICAMENTS . " SET
                medicament = :medicament,
                dosis = :dosis,
                necessita_recepta = :necessita_recepta,
                quantitat_defecte = :quantitat_defecte,
                facultatiu_id = :facultatiu_id,
                updated_at = NOW()
            WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':medicament', $medicament, PDO::PARAM_STR);
        $stmt->bindValue(':dosis', $dosis, $dosis === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':necessita_recepta', $necessitaRecepta, PDO::PARAM_BOOL);
        $stmt->bindValue(':quantitat_defecte', $quantitatDefecte, $quantitatDefecte === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':facultatiu_id', $facultatiuId === null ? null : App\Utils\Uuid::toBinary($facultatiuId), $facultatiuId === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);

        if ($stmt->execute()) {
            Response::success(
                MissatgesAPI::success('update'),
                [
                    'id'         => $id,
                    'medicament' => $medicament,
                ],
                httpCode: 200
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
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }

    /**
     * PUT : Alta patologia i associacio a medicaments
     * URL: https://elliot.cat/api/salut/put/patologia
     */
} else if ($slug === "patologia") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
        exit;
    }

    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return $data[$key];
    }

    function isUuid($s)
    {
        return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
    }

    $errors = [];

    $id        = requireField($data, 'id', $errors);
    $patologia = requireField($data, 'patologia', $errors);
    $genere    = requireField($data, 'genere', $errors);

    if ($id !== null && !isUuid($id)) {
        $errors['id'] = 'invalid_uuid';
    }

    if ($genere !== null && !in_array($genere, ['f', 'm'], true)) {
        $errors['genere'] = 'invalid_value';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    $id_bin = App\Utils\Uuid::toBinary($id);

    $sql = "UPDATE " . Tables::DB_SALUT_PATOLOGIES . " SET
                patologia = :patologia,
                genere = :genere,
                updated_at = NOW()
            WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':patologia', $patologia, PDO::PARAM_STR);
        $stmt->bindValue(':genere', $genere, PDO::PARAM_STR);

        $stmt->execute();

        // ==============================
        // MEDICAMENTS SYNC (delete + reinsert)
        // ==============================

        $pdo->prepare("
            DELETE FROM " . Tables::DB_SALUT_PATOLOGIES_MEDICAMENTS . "
            WHERE patologia_id = :id
        ")->execute([':id' => $id_bin]);

        $medicaments = $data['medicaments'] ?? [];

        if (is_array($medicaments) && !empty($medicaments)) {

            $stmtMedicament = $pdo->prepare("
                INSERT INTO " . Tables::DB_SALUT_PATOLOGIES_MEDICAMENTS . "
                (patologia_id, medicament_id)
                VALUES (:patologia_id, :medicament_id)
            ");

            foreach ($medicaments as $medicamentId) {
                if (!isUuid($medicamentId)) continue;

                $stmtMedicament->execute([
                    ':patologia_id'  => $id_bin,
                    ':medicament_id' => App\Utils\Uuid::toBinary($medicamentId),
                ]);
            }
        }

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id'        => $id,
                'patologia' => $patologia,
            ],
            httpCode: 200
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }
} else {
    // Slug no reconocido
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
