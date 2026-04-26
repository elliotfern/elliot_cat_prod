<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\DatabaseConnection;
use Ramsey\Uuid\Uuid;

$conn = DatabaseConnection::getConnection();

if (!$conn) {
    die("No se pudo establecer conexión a la base de datos.");
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

if (!isAuthenticatedAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autoritzat (admin requerit)']);
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!is_array($data)) {
    Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
}

// HELPERS
$trimOrNull = static function ($v): ?string {
    if ($v === null) return null;
    $t = trim((string)$v);
    return $t === '' ? null : $t;
};

$reUUID = '~^[0-9a-f-]{36}$~i';

// INPUT
$id          = $trimOrNull($data['id'] ?? null);
$ciutat      = $trimOrNull($data['ciutat'] ?? null);
$ciutat_ca   = $trimOrNull($data['ciutat_ca'] ?? null);
$ciutat_en   = $trimOrNull($data['ciutat_en'] ?? null);
$descripcio  = $trimOrNull($data['descripcio'] ?? null);
$pais_id     = $trimOrNull($data['pais_id'] ?? null);

// VALIDATION
$errors = [];

if (!$id || !preg_match($reUUID, $id)) {
    $errors[] = 'Camp "id" no és vàlid.';
}
if (!$ciutat) {
    $errors[] = 'Camp "ciutat" requerit.';
}
if ($pais_id !== null && !preg_match($reUUID, $pais_id)) {
    $errors[] = 'Camp "pais_id" no és un UUID vàlid.';
}

if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
}

try {
    $conn->beginTransaction();

    // 🔥 convertir a BINARIO en PHP (coherente con POST)
    $idBytes = Uuid::fromString($id)->getBytes();

    // check existence
    $chk = $conn->prepare("SELECT 1 FROM db_geo_ciutats WHERE id = :id LIMIT 1");
    $chk->bindValue(':id', $idBytes, PDO::PARAM_LOB);
    $chk->execute();

    if (!$chk->fetchColumn()) {
        $conn->rollBack();
        Response::error(MissatgesAPI::error('not_found'), ["Ciutat no existeix"], 404);
    }

    // pais_id binario
    $paisBytes = null;
    if ($pais_id !== null) {
        $paisBytes = Uuid::fromString($pais_id)->getBytes();
    }

    // UPDATE
    $sql = "UPDATE db_geo_ciutats
            SET ciutat = :ciutat,
                ciutat_ca = :ciutat_ca,
                ciutat_en = :ciutat_en,
                descripcio = :descripcio,
                pais_id = :pais_id,
                updated_at = UTC_TIMESTAMP()
            WHERE id = :id";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $idBytes, PDO::PARAM_LOB);

    $stmt->bindValue(':ciutat', $ciutat, PDO::PARAM_STR);
    $stmt->bindValue(':ciutat_ca', $ciutat_ca, PDO::PARAM_STR);

    $stmt->bindValue(':ciutat_en', $ciutat_en, $ciutat_en === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':descripcio', $descripcio, $descripcio === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->bindValue(':pais_id', $paisBytes, $paisBytes === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);

    $stmt->execute();

    $conn->commit();

    Response::success(
        MissatgesAPI::success('update'),
        ['id' => $id],
        200
    );
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();

    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
}
