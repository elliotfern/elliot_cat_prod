<?php

use App\Config\Database;
use App\Utils\MissatgesAPI;
use App\Utils\Response;
use App\Utils\Uuid;

$db = new Database();
$pdo = $db->getPdo();

header("Content-Type: application/json");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Metode no permès']);
  exit();
}

// a) Modificar contacte

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

if ($data === null) {
  header('HTTP/1.1 400 Bad Request');
  echo json_encode(['error' => 'Error decoding JSON data']);
  exit();
}

// Validación
$errors = [];

$id = requireField($data, 'id', $errors);
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

// Validar format UUID de id, tipus i pais abans de convertir-los a binari
$regexUuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

if ($id !== null && !preg_match($regexUuid, $id)) {
  $errors['id'] = 'format_invalid';
}

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

// Convertir id, tipus i pais (UUID strings) a binari
$idBinari = Uuid::toBinary($id);
$tipusIdBinari = Uuid::toBinary($tipus);
$paisIdBinari = Uuid::toBinary($pais);

// Construcció dinàmica del query
$query = "UPDATE db_contactes SET nom = :nom, cognoms = :cognoms, email = :email, tel_1 = :tel_1, tel_2 = :tel_2, tel_3 = :tel_3, adreca = :adreca, data_naixement = :data_naixement, web = :web, tipus_id = :tipus_id, pais_id = :pais_id";

$binds = [
  ':nom' => [$nom, PDO::PARAM_STR],
  ':cognoms' => [$cognoms, PDO::PARAM_STR],
  ':email' => [$email, PDO::PARAM_STR],
  ':tel_1' => [$tel_1, PDO::PARAM_STR],
  ':tel_2' => [$tel_2, PDO::PARAM_STR],
  ':tel_3' => [$tel_3, PDO::PARAM_STR],
  ':adreca' => [$adreca, PDO::PARAM_STR],
  ':data_naixement' => [$data_naixement, PDO::PARAM_STR],
  ':web' => [$web, PDO::PARAM_STR],
  ':tipus_id' => [$tipusIdBinari, PDO::PARAM_LOB],
  ':pais_id' => [$paisIdBinari, PDO::PARAM_LOB],
];

$query .= " WHERE id = :id";
$binds[':id'] = [$idBinari, PDO::PARAM_LOB];

try {
  $stmt = $pdo->prepare($query);

  foreach ($binds as $param => [$value, $type]) {
    $stmt->bindValue($param, $value, $type);
  }

  $stmt->execute();

  Response::success(
    MissatgesAPI::success('update'),
    ['id' => $id],
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
