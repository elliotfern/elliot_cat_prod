<?php

use Ramsey\Uuid\Uuid;
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

  $tipus_id     = $requireField($data, 'tipus_id');      // UUID string
  $editorial_id = $requireField($data, 'editorial_id');  // UUID string
  $sub_tema_id  = $requireField($data, 'sub_tema_id');   // UUID string
  $grup  = $requireField($data, 'grup');   // UUID string

  $lang         = $requireField($data, 'lang');          // int
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

  // UPDATE
  // - id, tipus_id, editorial_id, sub_tema_id => BINARY(16)
  // - estat => int (NO UNHEX)
  $sql = "UPDATE " . Tables::LLIBRES . " SET
            titol_original = :titol_original,
            titol_catala = :titol_catala,
            slug = :slug,
            any = :any,
            tipus_id = UNHEX(REPLACE(:tipus_id, '-', '')),
            editorial_id = UNHEX(REPLACE(:editorial_id, '-', '')),
            sub_tema_id = UNHEX(REPLACE(:sub_tema_id, '-', '')),
            lang = :lang,
            img_id = :img_id,
            estat_id = UNHEX(REPLACE(:estat_id, '-', '')),
            dateModified = :dateModified,
            grup = UNHEX(REPLACE(:grup, '-', ''))
          WHERE id = UNHEX(REPLACE(:id, '-', ''))
          LIMIT 1";

  try {
    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':titol_original', $titol_original, PDO::PARAM_STR);
    $stmt->bindValue(':titol_catala', $titol_catala, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':any', (int)$any, PDO::PARAM_INT);

    $stmt->bindValue(':tipus_id', $tipus_id, PDO::PARAM_STR);
    $stmt->bindValue(':editorial_id', $editorial_id, PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_id', $sub_tema_id, PDO::PARAM_STR);

    $stmt->bindValue(':lang', (int)$lang, PDO::PARAM_INT);
    $stmt->bindValue(':estat_id', $estat_id, PDO::PARAM_STR);

    if ($img_id === null || $img_id === '') {
      $stmt->bindValue(':img_id', null, PDO::PARAM_NULL);
    } else {
      $stmt->bindValue(':img_id', (int)$img_id, PDO::PARAM_INT);
    }

    $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_STR);
    $stmt->bindValue(':grup', $grup, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);

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
