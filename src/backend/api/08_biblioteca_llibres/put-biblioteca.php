<?php

use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
  http_response_code(204);
  exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

function isUuid($s)
{
  return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
}


// Ruta actualizació llibre
// Ruta PUT => "/api/biblioteca/put?llibre"
if (isset($_GET['llibre'])) {
  // Leer JSON
  $input_data = file_get_contents("php://input");
  $data = json_decode($input_data, true);

  if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
    exit;
  }

  // Helpers (mismo patrón)
  $errors = [];

  $requireField = function (array $data, string $key) use (&$errors) {
    if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
      $errors[$key] = 'required';
      return null;
    }
    return data_input($data[$key]);
  };

  $optionalField = function (array $data, string $key) {
    return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
      ? data_input($data[$key])
      : null;
  };

  // Requeridos para update
  $id  = $requireField($data, 'id');            // UUID string del libro
  $titol_original = $requireField($data, 'titol_original');
  $titol_catala = $optionalField($data, 'titol_catala');
  $slug         = $requireField($data, 'slug');
  $any          = $requireField($data, 'any');
  $lang         = $requireField($data, 'lang');

  $tipus_id     = $requireField($data, 'tipus_id');      // UUID string
  $editorial_id = $requireField($data, 'editorial_id');  // UUID string
  $sub_tema_id  = $requireField($data, 'sub_tema_id');   // UUID string
  $grup  = $requireField($data, 'grup');   // UUID string
  $estat_id = $optionalField($data, 'estat_id');
  $img_id = $optionalField($data, 'img_id');

  // Validaciones UUID (usa tu isUuid() global)
  if (!isUuid($id))           $errors['id'] = 'invalid_uuid';
  if (!isUuid($tipus_id))     $errors['tipus_id'] = 'invalid_uuid';
  if (!isUuid($editorial_id)) $errors['editorial_id'] = 'invalid_uuid';
  if (!isUuid($sub_tema_id))  $errors['sub_tema_id'] = 'invalid_uuid';
  if (!isUuid($estat_id))  $errors['estat_id'] = 'invalid_uuid';
  if (!isUuid($grup))  $errors['grup'] = 'invalid_uuid';
  if (!isUuid($img_id))  $errors['img_idup'] = 'invalid_uuid';

  // Validación ints básicos
  if ($any !== null && !is_numeric($any))   $errors['any'] = 'invalid_int';
  if ($lang !== null && !is_numeric($lang)) $errors['lang'] = 'invalid_int';

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
  }

  // Fecha modificación
  $dateModified = date('Y-m-d');

  global $conn;

  $id_bin = Uuid::toBinary($id);

  $tipus_id_bin = Uuid::toBinary($tipus_id);
  $editorial_id_bin = Uuid::toBinary($editorial_id);
  $sub_tema_id_bin = Uuid::toBinary($sub_tema_id);
  $grup_bin = Uuid::toBinary($grup);
  $estat_id_bin = $estat_id ? Uuid::toBinary($estat_id) : null;
  $img_id_bin = $img_id ? Uuid::toBinary($img_id) : null;

  // UPDATE
  $sql = "UPDATE " . Tables::LLIBRES . " SET
    titol_original = :titol_original,
    titol_catala = :titol_catala,
    slug = :slug,
    any = :any,

    tipus_id = :tipus_id,
    editorial_id = :editorial_id,
    sub_tema_id = :sub_tema_id,

    lang = :lang,
    img_id = :img_id,
    estat_id = :estat_id,

    dateModified = :dateModified,
    grup = :grup

    WHERE id = :id
    LIMIT 1";

  try {
    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':titol_original', $titol_original, PDO::PARAM_STR);
    $stmt->bindValue(':titol_catala', $titol_catala, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':any', (int)$any, PDO::PARAM_INT);
    $stmt->bindValue(':lang', (int)$lang, PDO::PARAM_INT);

    $stmt->bindValue(':tipus_id', $tipus_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':editorial_id', $editorial_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':sub_tema_id', $sub_tema_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':grup', $grup_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':estat_id', $estat_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':img_id', $img_id_bin, PDO::PARAM_LOB);

    $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);

    $stmt->execute();

    // 0 filas afectadas puede ser:
    // - id no existe
    // - datos iguales (MySQL devuelve 0)
    // Lo tratamos de forma útil:
    if ($stmt->rowCount() === 0) {
      // comprobamos si existe
      // ^^^ OJO: si no quieres líos, usa SELECT 1:
      $check = $conn->prepare("SELECT 1 FROM " . Tables::LLIBRES . " WHERE id = UNHEX(REPLACE(:id, '-', '')) LIMIT 1");
      $check->bindValue(':id', $id, PDO::PARAM_STR);
      $check->execute();
      $exists = (bool)$check->fetchColumn();

      if (!$exists) {
        Response::error(MissatgesAPI::error('not_found'), ['id' => $id], 404);
        exit;
      }

      // existe pero no ha cambiado nada
      Response::success(MissatgesAPI::success('update'), ['id' => $id, 'slug' => $slug, 'changed' => false], 200);
      exit;
    }

    Response::success(MissatgesAPI::success('update'), ['id' => $id, 'slug' => $slug, 'changed' => true], 200);
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
  // response output - data error
  $response['status'] = 'error 2 url api';
  header("Content-Type: application/json");
  echo json_encode($response);
  exit();
}
