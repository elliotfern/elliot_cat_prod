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


// a) Inserir autor
if (isset($_GET['autor'])) {

  // Obtener el cuerpo de la solicitud PUT
  $input_data = file_get_contents("php://input");

  // Decodificar los datos JSON
  $data = json_decode($input_data, true);

  // Verificar si se recibieron datos
  if ($data === null) {
    // Error al decodificar JSON
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Error decoding JSON data']);
    exit();
  }

  // Generar UUID v7 (requiere PHP >= 8.1)
  $id = Uuid::uuid7()->getBytes(); // BINARY(16) para MySQL

  // Ahora puedes acceder a los datos como un array asociativo
  $hasError = false;

  $grup = !empty($data['grup']) ? data_input($data['grup']) : ($hasError = true);
  $nom = !empty($data['nom']) ? data_input($data['nom']) : ($hasError = true);
  $cognoms = isset($data['cognoms']) ? data_input($data['cognoms']) : ($hasError = false);
  $slug = !empty($data['slug']) ? data_input($data['slug']) : ($hasError = true);
  $ocupacio = !empty($data['ocupacio']) ? data_input($data['ocupacio']) : ($hasError = true);
  $anyNaixement = !empty($data['anyNaixement']) ? data_input($data['anyNaixement']) : ($hasError = true);
  $anyDefuncio = isset($data['anyDefuncio']) ? data_input($data['anyDefuncio']) : ($hasError = false);
  $paisAutor = !empty($data['paisAutor']) ? data_input($data['paisAutor']) : ($hasError = true);
  $img = !empty($data['img']) ? data_input($data['img']) : ($hasError = true);
  $web = !empty($data['web']) ? data_input($data['web']) : ($hasError = false);

  $sexe = !empty($data['sexe']) ? data_input($data['sexe']) : ($hasError = true);
  $mesNaixement = !empty($data['mesNaixement']) ? data_input($data['mesNaixement']) : ($hasError = false);
  $diaNaixement = !empty($data['diaNaixement']) ? data_input($data['diaNaixement']) : ($hasError = false);
  $mesDefuncio = !empty($data['mesDefuncio']) ? data_input($data['mesDefuncio']) : ($hasError = false);
  $diaDefuncio = !empty($data['diaDefuncio']) ? data_input($data['diaDefuncio']) : ($hasError = false);
  $ciutatNaixement = !empty($data['ciutatNaixement']) ? data_input($data['ciutatNaixement']) : ($hasError = false);
  $ciutatDefuncio = !empty($data['ciutatDefuncio']) ? data_input($data['ciutatDefuncio']) : ($hasError = false);
  $descripcio = !empty($data['descripcio']) ? data_input($data['descripcio']) : ($hasError = true);
  $descripcioCast = !empty($data['descripcioCast']) ? data_input($data['descripcioCast']) : ($hasError = false);
  $descripcioEng = !empty($data['descripcioEng']) ? data_input($data['descripcioEng']) : ($hasError = false);
  $descripcioIt = !empty($data['descripcioIt']) ? data_input($data['descripcioIt']) : ($hasError = false);

  $timestamp = date('Y-m-d');
  $dateCreated = $timestamp;
  $dateModified = $timestamp;

  if (!$hasError) {
    try {
      global $conn;
      $sql = "INSERT INTO db_persones 
      (id, nom, cognoms, anyNaixement, anyDefuncio, paisAutor, img, web, descripcio, ocupacio, dateModified, dateCreated, slug, grup, sexe, mesNaixement, diaNaixement, mesDefuncio, diaDefuncio, ciutatNaixement, ciutatDefuncio, descripcioCast, descripcioEng, descripcioIt) 
      VALUES 
      (:id, :nom, :cognoms, :anyNaixement, :anyDefuncio, :paisAutor, :img, :web, :descripcio, :ocupacio, :dateModified, :dateCreated, :slug, :grup, :sexe, :mesNaixement, :diaNaixement, :mesDefuncio, :diaDefuncio, :ciutatNaixement, :ciutatDefuncio, :descripcioCast, :descripcioEng, :descripcioIt)";
      $stmt = $conn->prepare($sql);

      $stmt->bindParam(":nom", $nom, PDO::PARAM_STR);
      $stmt->bindParam(":cognoms", $cognoms, PDO::PARAM_STR);
      $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
      $stmt->bindParam(":anyNaixement", $anyNaixement, PDO::PARAM_INT);
      $stmt->bindParam(":anyDefuncio", $anyDefuncio, PDO::PARAM_INT);
      $stmt->bindParam(":paisAutor", $paisAutor, PDO::PARAM_INT);
      $stmt->bindParam(":img", $img, PDO::PARAM_INT);
      $stmt->bindParam(":web", $web, PDO::PARAM_STR);
      $stmt->bindParam(":ocupacio", $ocupacio, PDO::PARAM_INT);
      $stmt->bindParam(":dateCreated", $dateCreated, PDO::PARAM_STR);
      $stmt->bindParam(":dateModified", $dateModified, PDO::PARAM_STR);
      $stmt->bindParam(":grup", $grup, PDO::PARAM_INT);
      $stmt->bindParam(":sexe", $sexe, PDO::PARAM_INT);
      $stmt->bindParam(":mesNaixement", $mesNaixement, PDO::PARAM_INT);
      $stmt->bindParam(":diaNaixement", $diaNaixement, PDO::PARAM_INT);
      $stmt->bindParam(":mesDefuncio", $mesDefuncio, PDO::PARAM_INT);
      $stmt->bindParam(":diaDefuncio", $diaDefuncio, PDO::PARAM_INT);
      $stmt->bindParam(":ciutatNaixement", $ciutatNaixement, PDO::PARAM_STR);
      $stmt->bindParam(":ciutatDefuncio", $ciutatDefuncio, PDO::PARAM_STR);
      $stmt->bindParam(":descripcio", $descripcio, PDO::PARAM_STR);
      $stmt->bindParam(":descripcioCast", $descripcioCast, PDO::PARAM_STR);
      $stmt->bindParam(":descripcioEng", $descripcioEng, PDO::PARAM_STR);
      $stmt->bindParam(":descripcioIt", $descripcioIt, PDO::PARAM_STR);
      $stmt->bindParam(":id", $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
        $response['status'] = 'success';
      } else {
        $response['status'] = 'error';
        $response['message'] = 'Hubo un problema con la base de datos.';
      }
    } catch (PDOException $e) {
      $response['status'] = 'error';
      $response['message'] = $e->getMessage();
    }
  } else {
    $response['status'] = 'error';
    $response['message'] = 'Errores de validación.';
  }

  header("Content-Type: application/json");
  echo json_encode($response);


  // INSERIR NOU LLIBRE
  // autor	titol	titolEng	slug	any	tipus	idEd	idGen	subGen	lang	img	dateCreated
} else if (isset($_GET['llibre'])) {

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

  $titol        = requireField($data, 'titol', $errors);
  $slug         = requireField($data, 'slug', $errors);
  $any          = requireField($data, 'any', $errors);

  $tipus_id     = requireField($data, 'tipus_id', $errors);      // UUID string
  $editorial_id = requireField($data, 'editorial_id', $errors);  // UUID string
  $sub_tema_id  = requireField($data, 'sub_tema_id', $errors);   // UUID string

  $lang         = requireField($data, 'lang', $errors);          // int
  $estat        = requireField($data, 'estat', $errors);         // int
  $img          = optionalField($data, 'img');                   // int|null

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
  $uuid = Uuid::uuid7();
  $uuidBytes = $uuid->getBytes();   // para BINARY(16)
  $uuidString = $uuid->toString();  // para devolver al frontend si quieres

  global $conn;

  $sql = "INSERT INTO " . Tables::LLIBRES . " (
              id, titol, slug, any,
              tipus_id, editorial_id, sub_tema_id, estat,
              lang, img, 
              dateCreated, dateModified
          ) VALUES (
              :id, :titol, :slug, :any,
              UNHEX(REPLACE(:tipus_id, '-', '')),
              UNHEX(REPLACE(:editorial_id, '-', '')),
              UNHEX(REPLACE(:sub_tema_id, '-', '')),
              UNHEX(REPLACE(:estat, '-', '')),
              :lang, :img,
              :dateCreated, :dateModified
          )";

  try {
    $stmt = $conn->prepare($sql);

    // ID UUIDv7 binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);

    $stmt->bindValue(':titol', $titol, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':any', (int)$any, PDO::PARAM_INT);

    $stmt->bindValue(':tipus_id', $tipus_id, PDO::PARAM_STR);
    $stmt->bindValue(':editorial_id', $editorial_id, PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_id', $sub_tema_id, PDO::PARAM_STR);

    $stmt->bindValue(':lang', (int)$lang, PDO::PARAM_INT);
    $stmt->bindValue(':estat', (int)$estat, PDO::PARAM_INT);

    if ($img === null || $img === '') {
      $stmt->bindValue(':img', null, PDO::PARAM_NULL);
    } else {
      $stmt->bindValue(':img', (int)$img, PDO::PARAM_INT);
    }

    $stmt->bindValue(':dateCreated', $dateCreated, PDO::PARAM_STR);
    $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_NULL);

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
} else {
  // response output - data error
  $response['status'] = 'error ruta';
  header("Content-Type: application/json");
  echo json_encode($response);
  exit();
}
