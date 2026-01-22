<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error(MissatgesAPI::error('method_not_allowed'), [], 405);
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

function parseDateTimeOrError(?string $s): ?DateTime
{
    if ($s === null) return null;
    try {
        return new DateTime($s);
    } catch (Throwable $e) {
        return null;
    }
}

// Leer JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
    exit;
}

// Validación
$errors = [];

$titol = requireField($data, 'titol', $errors);
$tipus = requireField($data, 'tipus', $errors);
$data_inici_raw = requireField($data, 'data_inici', $errors);
$data_fi_raw    = requireField($data, 'data_fi', $errors);

$descripcio = optionalField($data, 'descripcio');
$lloc       = optionalField($data, 'lloc');

// Defaults si no vienen
$tot_el_dia_raw = isset($data['tot_el_dia']) ? $data['tot_el_dia'] : 0;
$estat = isset($data['estat']) && $data['estat'] !== '' && $data['estat'] !== null
    ? data_input($data['estat'])
    : 'confirmat';

// Enums permitidos
$allowedTipus = ['reunio', 'visita_medica', 'videotrucada', 'altre'];
$allowedEstat = ['pendent', 'confirmat', 'cancel·lat'];

if ($tipus !== null && !in_array((string)$tipus, $allowedTipus, true)) {
    $errors['tipus'] = 'invalid';
}
if ($estat !== null && !in_array((string)$estat, $allowedEstat, true)) {
    $errors['estat'] = 'invalid';
}

// tot_el_dia normalizado a 0/1
$tot_el_dia = 0;
if ($tot_el_dia_raw === true || $tot_el_dia_raw === 1 || $tot_el_dia_raw === '1' || $tot_el_dia_raw === 'true') {
    $tot_el_dia = 1;
}

// Fechas
$dtInici = is_string($data_inici_raw) ? parseDateTimeOrError($data_inici_raw) : null;
$dtFi    = is_string($data_fi_raw) ? parseDateTimeOrError($data_fi_raw) : null;

if (!$dtInici) $errors['data_inici'] = 'invalid_datetime';
if (!$dtFi)    $errors['data_fi'] = 'invalid_datetime';

if ($dtInici && $dtFi && $dtInici >= $dtFi) {
    $errors['data_fi'] = 'must_be_after_data_inici';
}

if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
}

// Formato para MySQL DATETIME
$data_inici = $dtInici->format('Y-m-d H:i:s');
$data_fi    = $dtFi->format('Y-m-d H:i:s');

try {
    global $conn;

    $sql = "INSERT INTO db_agenda_esdeveniments
            (titol, descripcio, tipus, lloc, data_inici, data_fi, tot_el_dia, estat)
          VALUES
            (:titol, :descripcio, :tipus, :lloc, :data_inici, :data_fi, :tot_el_dia, :estat)";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':titol', (string)$titol, PDO::PARAM_STR);

    if ($descripcio === null) $stmt->bindValue(':descripcio', null, PDO::PARAM_NULL);
    else $stmt->bindValue(':descripcio', (string)$descripcio, PDO::PARAM_STR);

    $stmt->bindValue(':tipus', (string)$tipus, PDO::PARAM_STR);

    if ($lloc === null) $stmt->bindValue(':lloc', null, PDO::PARAM_NULL);
    else $stmt->bindValue(':lloc', (string)$lloc, PDO::PARAM_STR);

    $stmt->bindValue(':data_inici', $data_inici, PDO::PARAM_STR);
    $stmt->bindValue(':data_fi', $data_fi, PDO::PARAM_STR);
    $stmt->bindValue(':tot_el_dia', $tot_el_dia, PDO::PARAM_INT);
    $stmt->bindValue(':estat', (string)$estat, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $newId = (int)$conn->lastInsertId();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $newId],
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
} catch (Throwable $e) {
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
