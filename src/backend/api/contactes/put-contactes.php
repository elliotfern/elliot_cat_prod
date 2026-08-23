<?php

use App\Config\Database;
use App\Utils\MissatgesAPI;
use App\Utils\Response;
use App\Utils\Uuid;

$db = new Database();
$pdo = $db->getPdo();

header("Content-Type: application/json");

corsAllow([
  'https://elliot.cat',
  'https://dev.elliot.cat',
  'https://elliot.local'
]);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Metode no permès']);
  exit();
}


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


// Llegir JSON

$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

if ($data === null) {
  header('HTTP/1.1 400 Bad Request');
  echo json_encode(['error' => 'Error decoding JSON data']);
  exit();
}


// Validació

$errors = [];

$id = requireField($data, 'id', $errors);
$tipusPersona = requireField($data, 'tipus_persona', $errors);

$nom = optionalField($data, 'nom');
$cognoms = optionalField($data, 'cognoms');
$empresa = optionalField($data, 'empresa');
$nif = optionalField($data, 'nif');
$email = optionalField($data, 'email');
$tel_1 = optionalField($data, 'tel_1');
$tel_2 = optionalField($data, 'tel_2');
$data_naixement = optionalField($data, 'data_naixement');
$adreca = optionalField($data, 'adreca');
$cp = optionalField($data, 'cp');
$ciutatId = optionalField($data, 'ciutat_id');
$provinciaId = optionalField($data, 'provincia_id');
$paisId = optionalField($data, 'pais_id');
$web = optionalField($data, 'web');


// Valors vàlids de tipus_persona

$tipusPersonaValids = [
  'FAMILIA',
  'AMICS',
  'EMPRESA',
  'ALTRES'
];

if (
  $tipusPersona !== null &&
  !in_array($tipusPersona, $tipusPersonaValids, true)
) {
  $errors['tipus_persona'] = 'format_invalid';
}


// Validar UUID

$regexUuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

foreach (
  [
    'id' => $id,
    'ciutat_id' => $ciutatId,
    'provincia_id' => $provinciaId,
    'pais_id' => $paisId
  ] as $field => $value
) {

  if ($value !== null && !preg_match($regexUuid, $value)) {
    $errors[$field] = 'format_invalid';
  }
}


if (!empty($errors)) {
  Response::error(
    MissatgesAPI::error('validacio'),
    $errors,
    httpCode: 400
  );

  exit;
}


// Convertir UUIDs a binari

$idBinari = Uuid::toBinary($id);

$ciutatIdBinari = $ciutatId !== null
  ? Uuid::toBinary($ciutatId)
  : null;

$provinciaIdBinari = $provinciaId !== null
  ? Uuid::toBinary($provinciaId)
  : null;

$paisIdBinari = $paisId !== null
  ? Uuid::toBinary($paisId)
  : null;


// UPDATE

$query = "
  UPDATE db_contactes SET
    tipus_persona = :tipus_persona,
    nom = :nom,
    cognoms = :cognoms,
    empresa = :empresa,
    nif = :nif,
    email = :email,
    tel_1 = :tel_1,
    tel_2 = :tel_2,
    data_naixement = :data_naixement,
    adreca = :adreca,
    cp = :cp,
    ciutat_id = :ciutat_id,
    provincia_id = :provincia_id,
    pais_id = :pais_id,
    web = :web
  WHERE id = :id
";

$binds = [
  ':tipus_persona' => [$tipusPersona, PDO::PARAM_STR],
  ':nom' => [$nom, PDO::PARAM_STR],
  ':cognoms' => [$cognoms, PDO::PARAM_STR],
  ':empresa' => [$empresa, PDO::PARAM_STR],
  ':nif' => [$nif, PDO::PARAM_STR],
  ':email' => [$email, PDO::PARAM_STR],
  ':tel_1' => [$tel_1, PDO::PARAM_STR],
  ':tel_2' => [$tel_2, PDO::PARAM_STR],
  ':data_naixement' => [$data_naixement, PDO::PARAM_STR],
  ':adreca' => [$adreca, PDO::PARAM_STR],
  ':cp' => [$cp, PDO::PARAM_STR],
  ':ciutat_id' => [$ciutatIdBinari, PDO::PARAM_LOB],
  ':provincia_id' => [$provinciaIdBinari, PDO::PARAM_LOB],
  ':pais_id' => [$paisIdBinari, PDO::PARAM_LOB],
  ':web' => [$web, PDO::PARAM_STR],
  ':id' => [$idBinari, PDO::PARAM_LOB],
];


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
