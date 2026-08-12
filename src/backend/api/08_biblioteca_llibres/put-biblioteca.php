<?php

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

header('Content-Type: application/json; charset=utf-8');

// CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
  http_response_code(204);
  exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);


function isUuid($s)
{
  return is_string($s) && preg_match(
    '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
    $s
  );
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


// ==============================
// PUT LLIBRE
// ==============================
if ($slug === 'llibre') {

  $isMultipart = !empty($_FILES) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

  $data = $isMultipart ? $_POST : json_decode(file_get_contents("php://input"), true);

  if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
    exit;
  }

  $errors = [];

  // REQUIRED
  $id            = requireField($data, 'id', $errors);
  $titol_original = requireField($data, 'titol_original', $errors);
  $slugInput     = requireField($data, 'slug', $errors);
  $any           = requireField($data, 'any', $errors);
  $tipus_id      = requireField($data, 'tipus_id', $errors);
  $editorial_id  = requireField($data, 'editorial_id', $errors);
  $sub_tema_id   = requireField($data, 'sub_tema_id', $errors);
  $estat_id      = requireField($data, 'estat_id', $errors);
  $idioma_id          = requireField($data, 'idioma_id', $errors);

  // OPTIONAL
  $titol_catala = optionalField($data, 'titol_catala');

  if (!isUuid($tipus_id)) $errors['tipus_id'] = 'invalid_uuid';
  if (!isUuid($editorial_id)) $errors['editorial_id'] = 'invalid_uuid';
  if (!isUuid($sub_tema_id)) $errors['sub_tema_id'] = 'invalid_uuid';

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
  }

  $id_bin = Uuid::toBinary($id);

  // ==============================
  // IMAGE (upload o existente)
  // ==============================
  $img_id_bin = null;

  $hasImage = !empty($_FILES['img_upload']) && $_FILES['img_upload']['error'] === UPLOAD_ERR_OK;

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
  } elseif (!empty($data['img_id']) && isUuid($data['img_id'])) {
    $img_id_bin = Uuid::toBinary($data['img_id']);
  }

  // ==============================
  // UPDATE LLIBRE
  // ==============================
  $sql = "UPDATE " . Tables::LLIBRES . " SET
      titol_original = :titol_original,
      titol_catala = :titol_catala,
      slug = :slug,
      any = :any,
      tipus_id = :tipus_id,
      editorial_id = :editorial_id,
      sub_tema_id = :sub_tema_id,
      estat_id = :estat_id,
      idioma_id = :idioma_id,
      img_id = :img_id,
      dateModified = NOW()
    WHERE id = :id";

  try {

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
      ':id' => $id_bin,
      ':titol_original' => $titol_original,
      ':titol_catala' => $titol_catala,
      ':slug' => $slugInput,
      ':any' => (int)$any,
      ':tipus_id' => Uuid::toBinary($tipus_id),
      ':editorial_id' => Uuid::toBinary($editorial_id),
      ':sub_tema_id' => Uuid::toBinary($sub_tema_id),
      ':estat_id' => Uuid::toBinary($estat_id),
      ':idioma_id' => Uuid::toBinary($idioma_id),
      ':img_id' => $img_id_bin
    ]);

    // ==============================
    // AUTORS SYNC
    // ==============================

    $pdo->prepare("
      DELETE FROM " . Tables::LLIBRES_AUTORS . "
      WHERE llibre_id = :id
    ")->execute([':id' => $id_bin]);

    $autors = $data['autors'] ?? [];

    if (is_array($autors) && !empty($autors)) {

      $stmtAutor = $pdo->prepare("
        INSERT INTO " . Tables::LLIBRES_AUTORS . "
        (llibre_id, autor_id)
        VALUES (:llibre_id, :autor_id)
      ");

      foreach ($autors as $autorId) {
        if (!isUuid($autorId)) continue;

        $stmtAutor->execute([
          ':llibre_id' => $id_bin,
          ':autor_id' => Uuid::toBinary($autorId),
        ]);
      }
    }

    // ==============================
    // GRUPS SYNC (col·leccions)
    // ==============================

    $pdo->prepare("
      DELETE FROM " . Tables::LLIBRES_GRUP_LLIBRES . "
      WHERE llibre_id = :id
    ")->execute([':id' => $id_bin]);

    $grups = $data['grups'] ?? [];

    if (is_array($grups) && !empty($grups)) {

      $stmtGrup = $pdo->prepare("
        INSERT INTO " . Tables::LLIBRES_GRUP_LLIBRES . "
        (llibre_id, grup_id)
        VALUES (:llibre_id, :grup_id)
      ");

      foreach ($grups as $grupId) {
        if (!isUuid($grupId)) continue;

        $stmtGrup->execute([
          ':llibre_id' => $id_bin,
          ':grup_id' => Uuid::toBinary($grupId),
        ]);
      }
    }

    // ==============================
    // ETIQUETES SYNC
    // ==============================

    $pdo->prepare("
      DELETE FROM " . Tables::LLIBRES_ETIQUETES_LLIBRES . "
      WHERE llibre_id = :id
    ")->execute([':id' => $id_bin]);

    $etiquetes = $data['etiquetes'] ?? [];

    if (is_array($etiquetes) && !empty($etiquetes)) {

      $stmtEtiqueta = $pdo->prepare("
        INSERT INTO " . Tables::LLIBRES_ETIQUETES_LLIBRES . "
        (llibre_id, etiqueta_id)
        VALUES (:llibre_id, :etiqueta_id)
      ");

      foreach ($etiquetes as $etiquetaId) {
        if (!isUuid($etiquetaId)) continue;

        $stmtEtiqueta->execute([
          ':llibre_id' => $id_bin,
          ':etiqueta_id' => Uuid::toBinary($etiquetaId),
        ]);
      }
    }

    Response::success(
      MissatgesAPI::success('update'),
      [
        'id' => $id,
        'slug' => $slugInput
      ],
      httpCode: 200
    );
    exit;
  } catch (\Throwable $e) {

    Response::error(
      MissatgesAPI::error('internal_error'),
      [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ],
      500
    );
    exit;
  }
}
