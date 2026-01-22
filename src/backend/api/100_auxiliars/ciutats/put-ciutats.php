<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;

/*
 * BACKEND DB CURRICULUM
 * FUNCIONS
 * @
 */

$conn = DatabaseConnection::getConnection();

if (!$conn) {
    die("No se pudo establecer conexión a la base de datos.");
}

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Requiere ADMIN por token (user_type === 1)
if (!isAuthenticatedAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autoritzat (admin requerit)']);
    exit;
}

$userUuid = getAuthenticatedUserUuid(); // para auditoría, si la soportas

$input = file_get_contents('php://input');
$data  = json_decode($input, true);
if (!is_array($data)) {
    Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
}

// Helpers
$trimOrNull = static function ($v): ?string {
    if ($v === null) return null;
    if (is_string($v)) {
        $t = trim($v);
        return ($t === '') ? null : $t;
    }
    $s = (string)$v;
    $s = trim($s);
    return $s === '' ? null : $s;
};
$reUUID = '~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~i';

// Datos entrantes
$id          = $trimOrNull($data['id'] ?? null);               // UUID texto (obligatorio)
$ciutat_ca   = $trimOrNull($data['ciutat_ca'] ?? null);        // obligatori
$ciutat_en   = $trimOrNull($data['ciutat_en'] ?? null);        // opcional
$descripcio  = $trimOrNull($data['descripcio'] ?? null);       // opcional (TEXT)
$pais_id     = $trimOrNull($data['pais_id'] ?? null);          // UUID texto opcional

// Validación
$errors = [];
if (!$id) {
    $errors[] = 'Camp "id" requerit.';
} elseif (!preg_match($reUUID, $id)) {
    $errors[] = 'Camp "id" no és un UUID vàlid.';
}
if (!$ciutat_ca) {
    $errors[] = 'Camp "ciutat_ca" requerit.';
}
if ($pais_id !== null && !preg_match($reUUID, $pais_id)) {
    $errors[] = 'Camp "pais_id" no és un UUID vàlid.';
}
if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
}

try {
    $conn->beginTransaction();

    // Comprobar existencia (id es BINARY(16) -> comparamos usando la funció)
    $chk = $conn->prepare("SELECT 1 FROM db_geo_ciutats WHERE id = uuid_text_to_bin(:id) LIMIT 1");
    $chk->bindValue(':id', $id, PDO::PARAM_STR);
    $chk->execute();
    if (!$chk->fetchColumn()) {
        $conn->rollBack();
        Response::error(MissatgesAPI::error('not_found'), ["Ciutat amb id {$id} no existeix"], 404);
    }

    // UPDATE — convertir UUIDs texto -> binario en SQL; strings a NULL si vienen vacíos
    $sql = "UPDATE db_geo_ciutats
               SET ciutat_ca  = :ciutat_ca,
                   ciutat_en  = :ciutat_en,
                   descripcio = :descripcio,
                   pais_id    = uuid_text_to_bin(NULLIF(:pais_id, '')),
                   updated_at = UTC_TIMESTAMP()
             WHERE id = uuid_text_to_bin(:id)";
    $stmt = $conn->prepare($sql);

    // id (WHERE)
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);

    // ciutat_ca (obligatorio)
    $stmt->bindValue(':ciutat_ca', $ciutat_ca, PDO::PARAM_STR);

    // ciutat_en (nullable)
    if ($ciutat_en === null) $stmt->bindValue(':ciutat_en', null, PDO::PARAM_NULL);
    else                     $stmt->bindValue(':ciutat_en', $ciutat_en, PDO::PARAM_STR);

    // descripcio (nullable)
    if ($descripcio === null) $stmt->bindValue(':descripcio', null, PDO::PARAM_NULL);
    else                      $stmt->bindValue(':descripcio', $descripcio, PDO::PARAM_STR);

    // pais_id (nullable) — lo convertimos en SQL con uuid_text_to_bin(NULLIF(:pais_id,''))
    if ($pais_id === null) $stmt->bindValue(':pais_id', null, PDO::PARAM_NULL);
    else                   $stmt->bindValue(':pais_id', $pais_id, PDO::PARAM_STR);

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
