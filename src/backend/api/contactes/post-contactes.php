<?php
/*
 * BACKEND CONTACTES
 * FUNCIONS INSERIR CONTACTE
 */

use Ramsey\Uuid\Uuid as Ramsey;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Database;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

// Sempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  corsAllow([
    'https://elliot.cat',
    'https://dev.elliot.cat',
    'https://elliot.local'
  ]);

  http_response_code(204);
  exit;
}

corsAllow([
  'https://elliot.cat',
  'https://dev.elliot.cat',
  'https://elliot.local'
]);

// Només POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}


// Helpers

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

$tipusPersona = optionalField($data, 'tipus_persona');

$nom = optionalField($data, 'nom');
$cognoms = optionalField($data, 'cognoms');
$empresa = optionalField($data, 'empresa');
$nif = optionalField($data, 'nif');
$email = optionalField($data, 'email');
$tel1 = optionalField($data, 'tel_1');
$tel2 = optionalField($data, 'tel_2');
$adreca = optionalField($data, 'adreca');
$cp = optionalField($data, 'cp');
$ciutatId = optionalField($data, 'ciutat_id');
$provinciaId = optionalField($data, 'provincia_id');
$paisId = optionalField($data, 'pais_id');
$web = optionalField($data, 'web');
$dataNaixement = optionalField($data, 'data_naixement');
$actiu = isset($data['actiu']) ? (int)$data['actiu'] : 1;

// tipus_persona és obligatori
$tipusPersonaValids = [
  'FAMILIA',
  'AMICS',
  'EMPRESA',
  'ALTRES'
];

if ($tipusPersona === null) {
  $errors['tipus_persona'] = 'required';
} elseif (!in_array($tipusPersona, $tipusPersonaValids, true)) {
  $errors['tipus_persona'] = 'format_invalid';
}


// Validar UUID dels camps relacionals

$regexUuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

foreach (
  [
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


// Generar nou UUID v7

$novaId = Ramsey::uuid7();

$idBinari = Uuid::toBinary(
  $novaId->toString()
);


// Convertir UUIDs a binari

$ciutatIdBinari = $ciutatId !== null
  ? Uuid::toBinary($ciutatId)
  : null;

$provinciaIdBinari = $provinciaId !== null
  ? Uuid::toBinary($provinciaId)
  : null;

$paisIdBinari = $paisId !== null
  ? Uuid::toBinary($paisId)
  : null;


// INSERT

$query = "
    INSERT INTO db_contactes (
        id,
        tipus_persona,
        nom,
        cognoms,
        empresa,
        nif,
        email,
        tel_1,
        tel_2,
        adreca,
        data_naixement,
        cp,
        ciutat_id,
        provincia_id,
        pais_id,
        web,
        actiu
    ) VALUES (
        :id,
        :tipus_persona,
        :nom,
        :cognoms,
        :empresa,
        :nif,
        :email,
        :tel_1,
        :tel_2,
        :adreca,
        :data_naixement,
        :cp,
        :ciutat_id,
        :provincia_id,
        :pais_id,
        :web,
        :actiu
    )
";

$stmt = $pdo->prepare($query);

$stmt->bindValue(':id', $idBinari, PDO::PARAM_LOB);
$stmt->bindValue(':tipus_persona', $tipusPersona, PDO::PARAM_STR);
$stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
$stmt->bindValue(':cognoms', $cognoms, PDO::PARAM_STR);
$stmt->bindValue(':empresa', $empresa, PDO::PARAM_STR);
$stmt->bindValue(':nif', $nif, PDO::PARAM_STR);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':tel_1', $tel1, PDO::PARAM_STR);
$stmt->bindValue(':tel_2', $tel2, PDO::PARAM_STR);
$stmt->bindValue(':adreca', $adreca, PDO::PARAM_STR);
$stmt->bindValue(':data_naixement', $dataNaixement, PDO::PARAM_STR);
$stmt->bindValue(':cp', $cp, PDO::PARAM_STR);
$stmt->bindValue(':ciutat_id', $ciutatIdBinari, PDO::PARAM_LOB);
$stmt->bindValue(':provincia_id', $provinciaIdBinari, PDO::PARAM_LOB);
$stmt->bindValue(':pais_id', $paisIdBinari, PDO::PARAM_LOB);
$stmt->bindValue(':web', $web, PDO::PARAM_STR);
$stmt->bindValue(':actiu', $actiu, PDO::PARAM_INT);

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
