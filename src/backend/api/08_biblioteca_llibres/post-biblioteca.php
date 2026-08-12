<?php

use Ramsey\Uuid\Uuid as ramseny;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Database;
use App\Utils\ImageService;

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

function isUuid($s)
{
  return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
}

// INSERIR NOU LLIBRE
if ($slug === 'llibre') {

  $isMultipart = !empty($_FILES) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

  if ($isMultipart) {
    $data = $_POST;
  } else {
    $input_data = file_get_contents("php://input");
    $data = json_decode($input_data, true);
  }

  if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
    exit;
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

  // Validación
  $errors = [];

  $titol_original = requireField($data, 'titol_original', $errors);
  $titol_catala = optionalField($data, 'titol_catala');
  $slug         = requireField($data, 'slug', $errors);
  $any          = requireField($data, 'any', $errors);

  $tipus_id     = requireField($data, 'tipus_id', $errors);      // UUID string
  $editorial_id = requireField($data, 'editorial_id', $errors);  // UUID string
  $sub_tema_id  = requireField($data, 'sub_tema_id', $errors);   // UUID string
  $estat_id        = requireField($data, 'estat_id', $errors);   // UUID string
  $newImgId = null;
  // detectar si viene imagen
  $hasImage = !empty($_FILES['img_upload']) && $_FILES['img_upload']['error'] === UPLOAD_ERR_OK;

  $img_id_bin = null;

  $img_id_bin = null;

  // 1) caso upload
  if ($hasImage) {



    $file = $_FILES['img_upload'];

    $nom = pathinfo($file['name'], PATHINFO_FILENAME);

    $alt = !empty($data['img'])
      ? $data['img']
      : $nom;

    $img_uuid = ImageService::createFromUpload(
      $file,
      2,
      $nom,
      $alt,
      $pdo
    );

    $img_id_bin = Uuid::toBinary($img_uuid);
  }

  // 2) caso selección existente (IMPORTANTE)
  elseif (!empty($data['img_id'])) {

    if (isUuid($data['img_id'])) {
      $img_id_bin = Uuid::toBinary($data['img_id']);
    }
  }

  $idioma_id         = requireField($data, 'idioma_id', $errors);          // int

  if (!isUuid($tipus_id)) $errors['tipus_id'] = 'invalid_uuid';
  if (!isUuid($editorial_id)) $errors['editorial_id'] = 'invalid_uuid';
  if (!isUuid($sub_tema_id)) $errors['sub_tema_id'] = 'invalid_uuid';

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
  $idioma_id_bin = Uuid::toBinary($idioma_id);

  $sql = "INSERT INTO " . Tables::LLIBRES . " (
              id, titol_original, titol_catala, slug, any,
              tipus_id, editorial_id, sub_tema_id, estat_id,
              idioma_id, img_id, dateCreated, dateModified
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
              :idioma_id,
              :img_id,
              :dateCreated,
              :dateModified
          )";

  try {
    $stmt = $pdo->prepare($sql);

    // ID UUIDv7 binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);

    $stmt->bindValue(':titol_original', $titol_original, PDO::PARAM_STR);
    $stmt->bindValue(':titol_catala', $titol_catala, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':any', (int)$any, PDO::PARAM_INT);
    $stmt->bindValue(':dateCreated', $dateCreated, PDO::PARAM_STR);
    $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_NULL);

    $stmt->bindValue(':idioma_id', $idioma_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':tipus_id', $tipus_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':editorial_id', $editorial_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':sub_tema_id', $sub_tema_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':estat_id', $estat_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':img_id', $img_id_bin, PDO::PARAM_LOB);

    if ($stmt->execute()) {

      // ===============================
      // AUTORS (RELACIÓN LLIBRE)
      // ===============================

      $autors = $data['autors'] ?? [];

      if (!is_array($autors)) {
        $autors = [];
      }

      if (!empty($autors)) {

        $sqlAutor = "
          INSERT IGNORE INTO " . Tables::LLIBRES_AUTORS . "
          (llibre_id, autor_id)
          VALUES
          (:llibre_id, :autor_uuid)
      ";

        $stmtAutor = $pdo->prepare($sqlAutor);

        foreach ($autors as $autorId) {

          if (!isUuid($autorId)) continue;

          $stmtAutor->execute([
            ':llibre_id' => $uuidBytes,
            ':autor_uuid' => Uuid::toBinary($autorId),
          ]);
        }
      }

      // ===============================
      // GRUPS (COL·LECCIONS - RELACIÓN N:M LLIBRE)
      // ===============================

      $grups = $data['grups'] ?? [];

      if (!is_array($grups)) {
        $grups = [];
      }

      if (!empty($grups)) {

        $sqlGrup = "
          INSERT IGNORE INTO " . Tables::LLIBRES_GRUP_LLIBRES . "
          (llibre_id, grup_id)
          VALUES
          (:llibre_id, :grup_uuid)
      ";

        $stmtGrup = $pdo->prepare($sqlGrup);

        foreach ($grups as $grupId) {

          if (!isUuid($grupId)) continue;

          $stmtGrup->execute([
            ':llibre_id' => $uuidBytes,
            ':grup_uuid' => Uuid::toBinary($grupId),
          ]);
        }
      }

      // ===============================
      // ETIQUETES (RELACIÓN N:M LLIBRE)
      // ===============================

      $etiquetes = $data['etiquetes'] ?? [];

      if (!is_array($etiquetes)) {
        $etiquetes = [];
      }

      if (!empty($etiquetes)) {

        $sqlEtiqueta = "
          INSERT IGNORE INTO " . Tables::LLIBRES_ETIQUETES_LLIBRES . "
          (llibre_id, etiqueta_id)
          VALUES
          (:llibre_id, :etiqueta_uuid)
      ";

        $stmtEtiqueta = $pdo->prepare($sqlEtiqueta);

        foreach ($etiquetes as $etiquetaId) {

          if (!isUuid($etiquetaId)) continue;

          $stmtEtiqueta->execute([
            ':llibre_id' => $uuidBytes,
            ':etiqueta_uuid' => Uuid::toBinary($etiquetaId),
          ]);
        }
      }

      Response::success(
        MissatgesAPI::success('create'),
        [
          'id'   => $uuidString,
          'slug' => $slug,
        ],
        httpCode: 201
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

  // INSERIR NOU GRUP LLIBRE
} else if ($slug === 'grupLlibre') {
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
    return $data[$key];
  }

  function optionalField(array $data, string $key)
  {
    return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
      ? $data[$key]
      : null;
  }

  // Validación
  $errors = [];

  $nom = requireField($data, 'nom', $errors);
  $slug = requireField($data, 'slug', $errors);

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
  }

  // Generar UUIDv7
  $uuid = ramseny::uuid7();
  $uuidBytes = $uuid->getBytes();   // para BINARY(16)
  $uuidString = $uuid->toString();  // para devolver al frontend si quieres

  $sql = "INSERT INTO " . Tables::LLIBRES_GRUP . " (
              id, nom, slug
          ) VALUES (
              :id, :nom, :slug
          )";

  try {
    $stmt = $pdo->prepare($sql);

    // ID UUIDv7 binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
    $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

    if ($stmt->execute()) {
      Response::success(
        MissatgesAPI::success('create'),
        [
          'id'   => $uuidString,
          'nom' => $nom,
        ],
        httpCode: 201
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
      httpCode: 500
    );
    exit;
  }

  // INSERIR NOVA ETIQUETA LLIBRE
} else if ($slug === 'etiqueta') {
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
    return $data[$key];
  }

  function optionalField(array $data, string $key)
  {
    return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
      ? $data[$key]
      : null;
  }

  // Validación
  $errors = [];

  $nom = requireField($data, 'nom', $errors);
  $slug = requireField($data, 'slug', $errors);

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
  }

  // Generar UUIDv7
  $uuid = ramseny::uuid7();
  $uuidBytes = $uuid->getBytes();   // para BINARY(16)
  $uuidString = $uuid->toString();  // para devolver al frontend si quieres

  $sql = "INSERT INTO " . Tables::LLIBRES_ETIQUETES . " (
              id, nom, slug
          ) VALUES (
              :id, :nom, :slug
          )";

  try {
    $stmt = $pdo->prepare($sql);

    // ID UUIDv7 binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
    $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

    if ($stmt->execute()) {
      Response::success(
        MissatgesAPI::success('create'),
        [
          'id'   => $uuidString,
          'nom' => $nom,
        ],
        httpCode: 201
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
      httpCode: 500
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
