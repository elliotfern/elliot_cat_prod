<?php

use Ramsey\Uuid\Uuid;
use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// JSON siempre
header('Content-Type: application/json; charset=utf-8');

// CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// SOLO POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// INPUT
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

// UUID regex (string input)
$reUUID = '~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~i';

// INPUT FIELDS
$ciutat      = $trimOrNull($data['ciutat'] ?? null);
$ciutat_ca   = $trimOrNull($data['ciutat_ca'] ?? null);
$ciutat_en   = $trimOrNull($data['ciutat_en'] ?? null);
$descripcio  = $trimOrNull($data['descripcio'] ?? null);
$pais_id     = $trimOrNull($data['pais_id'] ?? null);

// VALIDATION
$errors = [];

if (!$ciutat) {
    $errors[] = 'Camp "ciutat" requerit.';
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
    $pdo->beginTransaction();

    // 🔥 UUID v7 (ID ciudad)
    $uuid = Uuid::uuid7();
    $idBytes = $uuid->getBytes();
    $idText  = $uuid->toString();

    // 🔥 pais_id BINARIO (si existe)
    $paisBytes = null;
    if ($pais_id !== null) {
        $paisBytes = Uuid::fromString($pais_id)->getBytes();
    }

    // INSERT
    $sql = "INSERT INTO db_geo_ciutats
        (id, ciutat, ciutat_ca, ciutat_en, descripcio, pais_id, created_at, updated_at)
        VALUES
        (:id, :ciutat, :ciutat_ca, :ciutat_en, :descripcio, :pais_id, UTC_TIMESTAMP(), UTC_TIMESTAMP())";

    $stmt = $pdo->prepare($sql);

    // ID (UUID v7 binario)
    $stmt->bindValue(':id', $idBytes, PDO::PARAM_LOB);

    // TEXTOS
    $stmt->bindValue(':ciutat', $ciutat, PDO::PARAM_STR);
    $stmt->bindValue(':ciutat_ca', $ciutat_ca, PDO::PARAM_STR);

    $stmt->bindValue(':ciutat_en', $ciutat_en, $ciutat_en === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':descripcio', $descripcio, $descripcio === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    // UUID pais (binario)
    $stmt->bindValue(':pais_id', $paisBytes, $paisBytes === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);

    $stmt->execute();

    $pdo->commit();

    Response::success(
        MissatgesAPI::success('insert'),
        [
            'id' => $idText,
            'ciutat' => $ciutat,
            'ciutat_ca'    => $ciutat_ca,
            'ciutat_en' => $ciutat_en,
        ],
        httpCode: 201
    );
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    Response::error(
        MissatgesAPI::error('errorBD'),
        [$e->getMessage()],
        httpCode: 500
    );
}
