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


// RUTA PARA ACTUALIZAR AUTOR
// ruta PUT => "/api/biblioteca/put?autor"
if (isset($_GET['autor'])) {

  $input_data = file_get_contents("php://input");
  $data = json_decode($input_data, true);

  if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Error decoding JSON data']);
    exit();
  }

  $hasError = false;

  $id = $data['id'];

  if ($id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Error ID']);
    exit();
  }

  $nom = !empty($data['nom']) ? data_input($data['nom']) : ($hasError = true);
  $cognoms = isset($data['cognoms']) ? data_input($data['cognoms']) : '';
  $slug = !empty($data['slug']) ? data_input($data['slug']) : ($hasError = true);
  $anyNaixement = !empty($data['anyNaixement']) ? data_input($data['anyNaixement']) : ($hasError = true);
  $anyDefuncio = isset($data['anyDefuncio']) ? data_input($data['anyDefuncio']) : null;
  $paisAutor = !empty($data['paisAutor']) ? data_input($data['paisAutor']) : ($hasError = true);
  $img = !empty($data['img']) ? data_input($data['img']) : ($hasError = true);
  $web = !empty($data['web']) ? data_input($data['web']) : null;
  $sexe = !empty($data['sexe']) ? data_input($data['sexe']) : ($hasError = true);
  $mesNaixement = !empty($data['mesNaixement']) ? data_input($data['mesNaixement']) : 0;
  $diaNaixement = !empty($data['diaNaixement']) ? data_input($data['diaNaixement']) : 0;
  $mesDefuncio = !empty($data['mesDefuncio']) ? data_input($data['mesDefuncio']) : 0;
  $diaDefuncio = !empty($data['diaDefuncio']) ? data_input($data['diaDefuncio']) : 0;
  $ciutatNaixement = !empty($data['ciutatNaixement']) ? data_input($data['ciutatNaixement']) : null;
  $ciutatDefuncio = !empty($data['ciutatDefuncio']) ? data_input($data['ciutatDefuncio']) : null;
  $descripcio = !empty($data['descripcio']) ? data_input($data['descripcio']) : ($hasError = true);
  $descripcioCast = !empty($data['descripcioCast']) ? data_input($data['descripcioCast']) : null;
  $descripcioEng = !empty($data['descripcioEng']) ? data_input($data['descripcioEng']) : null;
  $descripcioIt = !empty($data['descripcioIt']) ? data_input($data['descripcioIt']) : null;
  $grups = !empty($data['grups']) && is_array($data['grups']) ? $data['grups'] : [];

  $dateModified = date('Y-m-d');

  if (!$hasError) {
    try {
      global $conn;

      // 1. Actualizar datos del autor
      $sql = "UPDATE db_persones SET 
                nom = :nom,
                cognoms = :cognoms,
                slug = :slug,
                anyNaixement = :anyNaixement,
                anyDefuncio = :anyDefuncio,
                paisAutor = :paisAutor,
                img = :img,
                web = :web,
                sexe = :sexe,
                mesNaixement = :mesNaixement,
                diaNaixement = :diaNaixement,
                mesDefuncio = :mesDefuncio,
                diaDefuncio = :diaDefuncio,
                ciutatNaixement = :ciutatNaixement,
                ciutatDefuncio = :ciutatDefuncio,
                descripcio = :descripcio,
                descripcioCast = :descripcioCast,
                descripcioEng = :descripcioEng,
                descripcioIt = :descripcioIt,
                dateModified = :dateModified
              WHERE id2 = UNHEX(REPLACE(:id, '-', ''))";
      $stmt = $conn->prepare($sql);

      $stmt->bindParam(":nom", $nom);
      $stmt->bindParam(":cognoms", $cognoms);
      $stmt->bindParam(":slug", $slug);
      $stmt->bindParam(":anyNaixement", $anyNaixement);
      $stmt->bindParam(":anyDefuncio", $anyDefuncio);
      $stmt->bindParam(":paisAutor", $paisAutor);
      $stmt->bindParam(":img", $img);
      $stmt->bindParam(":web", $web);
      $stmt->bindParam(":sexe", $sexe);
      $stmt->bindParam(":mesNaixement", $mesNaixement);
      $stmt->bindParam(":diaNaixement", $diaNaixement);
      $stmt->bindParam(":mesDefuncio", $mesDefuncio);
      $stmt->bindParam(":diaDefuncio", $diaDefuncio);
      $stmt->bindParam(":ciutatNaixement", $ciutatNaixement);
      $stmt->bindParam(":ciutatDefuncio", $ciutatDefuncio);
      $stmt->bindParam(":descripcio", $descripcio);
      $stmt->bindParam(":descripcioCast", $descripcioCast);
      $stmt->bindParam(":descripcioEng", $descripcioEng);
      $stmt->bindParam(":descripcioIt", $descripcioIt);
      $stmt->bindParam(":dateModified", $dateModified);
      $stmt->bindParam(":id", $id, PDO::PARAM_LOB);

      $stmt->execute();

      // 2. Actualizar grupos relacionados (tabla pivot)
      // Borrar relaciones existentes
      $deleteStmt = $conn->prepare("DELETE FROM db_persones_grups_relacions WHERE persona_id = UNHEX(REPLACE(:id, '-', ''))");
      $deleteStmt->bindParam(":id", $id);
      $deleteStmt->execute();

      // Insertar nuevas relaciones
      if (!empty($grups)) {
        $insertStmt = $conn->prepare("INSERT INTO db_persones_grups_relacions (id, persona_id, grup_id) VALUES (:id, UNHEX(REPLACE(:persona_id, '-', '')), UNHEX(REPLACE(:grup_id, '-', '')))");
        foreach ($grups as $grup_id) {
          $idUUID = Uuid::uuid7()->getBytes();
          $insertStmt->bindParam(":id", $idUUID, PDO::PARAM_LOB);
          $insertStmt->bindParam(":persona_id", $id, PDO::PARAM_LOB);
          $insertStmt->bindParam(":grup_id", $grup_id, PDO::PARAM_LOB);
          $insertStmt->execute();
        }
      }

      $response['status'] = 'success';
    } catch (PDOException $e) {
      http_response_code(500);
      $response['status'] = 'error';
      $response['message'] = $e->getMessage();
    }
  } else {
    http_response_code(400);
    $response['status'] = 'error';
    $response['message'] = 'Errores de validación.';
  }

  header("Content-Type: application/json");
  echo json_encode($response);


  // Ruta actualizació llibre
  // Ruta PUT => "/api/biblioteca/put?llibre"
} else if (isset($_GET['llibre'])) {
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
  $id           = $requireField($data, 'id');            // UUID string del libro
  $titol        = $requireField($data, 'titol');
  $slug         = $requireField($data, 'slug');
  $any          = $requireField($data, 'any');

  $tipus_id     = $requireField($data, 'tipus_id');      // UUID string
  $editorial_id = $requireField($data, 'editorial_id');  // UUID string
  $sub_tema_id  = $requireField($data, 'sub_tema_id');   // UUID string

  $lang         = $requireField($data, 'lang');          // int
  $estat        = $requireField($data, 'estat');         // int
  $img          = $optionalField($data, 'img');          // int|null

  // Validaciones UUID (usa tu isUuid() global)
  if (!isUuid($id))           $errors['id'] = 'invalid_uuid';
  if (!isUuid($tipus_id))     $errors['tipus_id'] = 'invalid_uuid';
  if (!isUuid($editorial_id)) $errors['editorial_id'] = 'invalid_uuid';
  if (!isUuid($sub_tema_id))  $errors['sub_tema_id'] = 'invalid_uuid';
  if (!isUuid($estat))  $errors['estat'] = 'invalid_uuid';

  // Validación ints básicos
  if ($any !== null && !is_numeric($any))   $errors['any'] = 'invalid_int';
  if ($lang !== null && !is_numeric($lang)) $errors['lang'] = 'invalid_int';
  if ($img !== null && $img !== '' && !is_numeric($img)) $errors['img'] = 'invalid_int';

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
            titol = :titol,
            slug = :slug,
            any = :any,
            tipus_id = UNHEX(REPLACE(:tipus_id, '-', '')),
            editorial_id = UNHEX(REPLACE(:editorial_id, '-', '')),
            sub_tema_id = UNHEX(REPLACE(:sub_tema_id, '-', '')),
            lang = :lang,
            img = :img,
            estat = UNHEX(REPLACE(:estat, '-', '')),
            dateModified = :dateModified
          WHERE id = UNHEX(REPLACE(:id, '-', ''))
          LIMIT 1";

  try {
    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':titol', $titol, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':any', (int)$any, PDO::PARAM_INT);

    $stmt->bindValue(':tipus_id', $tipus_id, PDO::PARAM_STR);
    $stmt->bindValue(':editorial_id', $editorial_id, PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_id', $sub_tema_id, PDO::PARAM_STR);

    $stmt->bindValue(':lang', (int)$lang, PDO::PARAM_INT);
    $stmt->bindValue(':estat', $estat, PDO::PARAM_STR);

    if ($img === null || $img === '') {
      $stmt->bindValue(':img', null, PDO::PARAM_NULL);
    } else {
      $stmt->bindValue(':img', (int)$img, PDO::PARAM_INT);
    }

    $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);

    $stmt->execute();

    // 0 filas afectadas puede ser:
    // - id no existe
    // - datos iguales (MySQL devuelve 0)
    // Lo tratamos de forma útil:
    if ($stmt->rowCount() === 0) {
      // comprobamos si existe
      $check = $conn->prepare("SELECT لاحظ FROM " . Tables::LLIBRES . " WHERE id = UNHEX(REPLACE(:id, '-', '')) LIMIT 1");
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
