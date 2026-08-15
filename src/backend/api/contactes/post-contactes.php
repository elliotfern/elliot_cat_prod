<?php
/*
 * BACKEND CONTACTES
 * FUNCIONS INSERIR CONTACTE
 */

use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Database;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
  http_response_code(204);
  exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

// a) Inserir contacte

// Helpers
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

$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

// Verificar si se recibieron datos
if ($data === null) {
  header('HTTP/1.1 400 Bad Request');
  echo json_encode(['error' => 'Error decoding JSON data']);
  exit();
}

// Validación
$errors = [];

$nom = requireField($data, 'nom', $errors);
$cognoms = requireField($data, 'cognoms', $errors);
$tel_1 = requireField($data, 'tel_1', $errors);
$tipus = requireField($data, 'tipus_id', $errors);
$pais = requireField($data, 'pais_id', $errors);

$tel_2 = optionalField($data, 'tel_2');
$tel_3 = optionalField($data, 'tel_3');
$adreca = optionalField($data, 'adreca');
$data_naixement = optionalField($data, 'data_naixement');
$web = optionalField($data, 'web');
$email = optionalField($data, 'email');

// Validar format UUID de tipus i pais abans de convertir-los a binari
$regexUuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

if ($tipus !== null && !preg_match($regexUuid, $tipus)) {
  $errors['tipus'] = 'format_invalid';
}

if ($pais !== null && !preg_match($regexUuid, $pais)) {
  $errors['pais'] = 'format_invalid';
}

if (!empty($errors)) {
  Response::error(MissatgesAPI::error('validacio'), $errors, httpCode: 400);
  exit;
}

// Generar nou UUID v7 pel id
$novaId = Ramsey::uuid7();
$idBinari = Uuid::toBinary($novaId->toString());

// Convertir tipus i pais (UUID strings) a binari
$tipusIdBinari = Uuid::toBinary($tipus);
$paisIdBinari = Uuid::toBinary($pais);

$query = "INSERT INTO db_contactes SET id = :id, nom = :nom, cognoms = :cognoms, email = :email, tel_1 = :tel_1, tel_2 = :tel_2, tel_3 = :tel_3, adreca = :adreca, data_naixement = :data_naixement, web = :web, tipus_id = :tipus_id, pais_id = :pais_id";
$stmt = $pdo->prepare($query);

$stmt->bindValue(':id', $idBinari, PDO::PARAM_LOB);
$stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
$stmt->bindValue(':cognoms', $cognoms, PDO::PARAM_STR);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':tel_1', $tel_1, PDO::PARAM_STR);
$stmt->bindValue(':tel_2', $tel_2, PDO::PARAM_STR);
$stmt->bindValue(':tel_3', $tel_3, PDO::PARAM_STR);
$stmt->bindValue(':adreca', $adreca, PDO::PARAM_STR);
$stmt->bindValue(':data_naixement', $data_naixement, PDO::PARAM_STR);
$stmt->bindValue(':web', $web, PDO::PARAM_STR);
$stmt->bindValue(':tipus_id', $tipusIdBinari, PDO::PARAM_LOB);
$stmt->bindValue(':pais_id', $paisIdBinari, PDO::PARAM_LOB);

try {
  $stmt->execute();

  Response::success(
    MissatgesAPI::success('create'),
    ['id' => $novaId->toString()],
    httpCode: 200
  );
} catch (PDOException $e) {
  Response::error(
    MissatgesAPI::error('errorBD'),
    [
      $e->getMessage(),
      $e->getFile(),
      $e->getLine()
    ],
    httpCode: 500
  );
}
