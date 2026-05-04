<?php

use Ramsey\Uuid\Uuid as ramseny;
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

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

function isUuid($s)
{
  return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
}

// INSERIR NOU LLIBRE
if (isset($_GET['llibre'])) {

  // Leer JSON
  $input_data = file_get_contents("php://input");
  $data = json_decode($input_data, true);

  if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
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

  // Validación
  $errors = [];

  $titol_original = requireField($data, 'titol_original', $errors);
  $titol_catala = optionalField($data, 'titol_catala');
  $slug         = requireField($data, 'slug', $errors);
  $any          = requireField($data, 'any', $errors);

  $tipus_id     = requireField($data, 'tipus_id', $errors);      // UUID string
  $editorial_id = requireField($data, 'editorial_id', $errors);  // UUID string
  $sub_tema_id  = requireField($data, 'sub_tema_id', $errors);   // UUID string
  $grup  = requireField($data, 'grup', $errors);   // UUID string
  $estat_id        = requireField($data, 'estat_id', $errors);   // UUID string
  $img_id          = optionalField($data, 'img_id');   // UUID string

  $lang         = requireField($data, 'lang', $errors);          // int

  if (!isUuid($tipus_id)) $errors['tipus_id'] = 'invalid_uuid';
  if (!isUuid($editorial_id)) $errors['editorial_id'] = 'invalid_uuid';
  if (!isUuid($sub_tema_id)) $errors['sub_tema_id'] = 'invalid_uuid';
  if (!isUuid($grup)) $errors['grup'] = 'invalid_uuid';
  if ($img_id && !isUuid($img_id)) {
    $errors['img_id'] = 'invalid_uuid';
  }

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
  }

  // Fechas
  $dateCreated  = date('Y-m-d');
  $dateModified = null;

  // Generar UUIDv7
  $uuid = ramseny::uuid7();
  $uuidBytes = $uuid->getBytes();   // para BINARY(16)
  $uuidString = Uuid::toBinary($uuid);
  $tipus_id_bin = Uuid::toBinary($tipus_id);
  $editorial_id_bin = Uuid::toBinary($editorial_id);
  $sub_tema_id_bin = Uuid::toBinary($sub_tema_id);
  $estat_id_bin = Uuid::toBinary($estat_id);
  $grup_bin = Uuid::toBinary($grup);
  $img_id_bin = Uuid::toBinary($img_id);

  global $conn;

  $sql = "INSERT INTO " . Tables::LLIBRES . " (
              id, titol_original, titol_catala, slug, any,
              tipus_id, editorial_id, sub_tema_id, estat_id,
              lang, img_id, dateCreated, dateModified, grup
          ) VALUES (
              :id,
              :titol_original, 
              :titol_catala,
              :slug,
              :any,
              :tipus_id,
              :editorial_id,
              :sub_tema_id, 
              :estat_id,
              :lang,
              :img_id,
              :dateCreated,
              :dateModified,
              :grup
          )";

  try {
    $stmt = $conn->prepare($sql);

    // ID UUIDv7 binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);

    $stmt->bindValue(':titol_original', $titol_original, PDO::PARAM_STR);
    $stmt->bindValue(':titol_catala', $titol_catala, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':any', (int)$any, PDO::PARAM_INT);
    $stmt->bindValue(':lang', (int)$lang, PDO::PARAM_INT);
    $stmt->bindValue(':dateCreated', $dateCreated, PDO::PARAM_STR);
    $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_NULL);

    $stmt->bindValue(':tipus_id', $tipus_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':editorial_id', $editorial_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':sub_tema_id', $sub_tema_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':estat_id', $estat_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':img_id', $img_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':grup', $grup_bin, PDO::PARAM_LOB);

    if ($stmt->execute()) {
      Response::success(
        MissatgesAPI::success('create'),
        [
          'id'   => $uuidString,
          'slug' => $slug,
        ],
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


  // INSERIR AUTOR A LLIBRE
  // 
} else if (isset($_GET['llibreAutor'])) {
  header('Content-Type: application/json; charset=utf-8');

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error(MissatgesAPI::error('method_not_allowed'), [], 405);
    exit;
  }

  $input = file_get_contents("php://input");
  $data = json_decode($input, true);

  if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
    exit;
  }

  $errors = [];

  $llibre_slug = isset($data['llibre_slug']) ? trim((string)$data['llibre_slug']) : '';
  if ($llibre_slug === '') $errors['llibre_slug'] = 'required';

  $autor_id = isset($data['autor_id']) ? (string)$data['autor_id'] : '';
  if ($autor_id === '') $errors['autor_id'] = 'required';
  if ($autor_id !== '' && !isUuid($autor_id)) $errors['autor_id'] = 'invalid_uuid';

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
  }

  try {
    global $conn;

    // 1) Obtener llibre_id (BINARY(16)) por slug
    $qBook = "SELECT id FROM " . Tables::LLIBRES . " WHERE slug = :slug LIMIT 1";
    $st = $conn->prepare($qBook);
    $st->bindValue(':slug', $llibre_slug, PDO::PARAM_STR);
    $st->execute();
    $bookRow = $st->fetch(PDO::FETCH_ASSOC);

    if (!$bookRow || empty($bookRow['id'])) {
      Response::error(MissatgesAPI::error('not_found'), ['llibre_slug' => $llibre_slug], 404);
      exit;
    }

    $llibre_id_bin = $bookRow['id']; // ya viene binario desde mysql

    // 2) Autor id binario desde UUID string
    // UNHEX(REPLACE(uuid,'-','')) para convertir a BINARY(16)
    // 3) Evitar duplicado
    $qExists = "
            SELECT la.id
            FROM " . Tables::LLIBRES_AUTORS . " la
            WHERE la.llibre_id = :llibre_id
              AND la.autor_id = UNHEX(REPLACE(:autor_uuid, '-', ''))
            LIMIT 1
        ";
    $st2 = $conn->prepare($qExists);
    $st2->bindValue(':llibre_id', $llibre_id_bin, PDO::PARAM_LOB);
    $st2->bindValue(':autor_uuid', $autor_id, PDO::PARAM_STR);
    $st2->execute();

    $exists = $st2->fetch(PDO::FETCH_ASSOC);
    if ($exists) {
      Response::success(
        MissatgesAPI::success('create'),
        ['rel_id' => (int)$exists['id']],
        200
      );
      exit;
    }

    // 4) Insert
    $sql = "
            INSERT INTO " . Tables::LLIBRES_AUTORS . " (autor_id, llibre_id)
            VALUES (UNHEX(REPLACE(:autor_uuid, '-', '')), :llibre_id)
        ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':autor_uuid', $autor_id, PDO::PARAM_STR);
    $stmt->bindValue(':llibre_id', $llibre_id_bin, PDO::PARAM_LOB);

    if ($stmt->execute()) {
      $relId = (int)$conn->lastInsertId();

      Response::success(
        MissatgesAPI::success('create'),
        [
          'rel_id' => $relId,
          'llibre_slug' => $llibre_slug,
          'autor_id' => $autor_id,
        ],
        201
      );
      exit;
    }

    Response::error(MissatgesAPI::error('db_error'), [
      'sqlState' => $stmt->errorCode(),
      'info' => $stmt->errorInfo(),
    ], 500);
    exit;
  } catch (\Throwable $e) {
    Response::error(MissatgesAPI::error('internal_error'), [
      'message' => $e->getMessage(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
    ], 500);
    exit;
  }

  // INSERIR NOU LLIBRE
} else if (isset($_GET['grupLlibre'])) {

  // Leer JSON
  $input_data = file_get_contents("php://input");
  $data = json_decode($input_data, true);

  if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
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

  // Validación
  $errors = [];

  $nom = requireField($data, 'nom', $errors);

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
  }

  // Generar UUIDv7
  $uuid = ramseny::uuid7();
  $uuidBytes = $uuid->getBytes();   // para BINARY(16)
  $uuidString = $uuid->toString();  // para devolver al frontend si quieres

  global $conn;

  $sql = "INSERT INTO " . Tables::LLIBRES_GRUP . " (
              id, nom
          ) VALUES (
              :id, :nom
          )";

  try {
    $stmt = $conn->prepare($sql);

    // ID UUIDv7 binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
    $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);

    if ($stmt->execute()) {
      Response::success(
        MissatgesAPI::success('create'),
        [
          'id'   => $uuidString,
          'nom' => $nom,
        ],
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
  $response['status'] = 'error ruta';
  header("Content-Type: application/json");
  echo json_encode($response);
  exit();
}
