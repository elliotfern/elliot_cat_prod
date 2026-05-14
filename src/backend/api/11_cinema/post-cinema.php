<?php

use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Database;
use App\Utils\ImageService;
use App\Utils\AdminMiddleware;

$db = new Database();
$pdo = $db->getPdo();
global $conn;

function isUuid($s)
{
  return is_string($s) && preg_match(
    '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
    $s
  );
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

// a) Inserir pelicula
if (isset($_GET['pelicula'])) {

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

  // Ahora puedes acceder a los datos como un array asociativo
  $hasError = false; // Inicializamos la variable $hasError como false

  $pelicula = !empty($data['pelicula']) ? data_input($data['pelicula']) : ($hasError = true);
  $slug = !empty($data['slug']) ? data_input($data['slug']) : ($hasError = true);
  $pelicula_es = !empty($data['pelicula_es']) ? data_input($data['pelicula_es']) : ($hasError = true);
  $director = !empty($data['director']) ? data_input($data['director']) : ($hasError = true);
  $any = !empty($data['any']) ? data_input($data['any']) : ($hasError = true);
  $genere = !empty($data['genere']) ? data_input($data['genere']) : ($hasError = true);
  $pais = !empty($data['pais']) ? data_input($data['pais']) : ($hasError = true);
  $lang = !empty($data['lang']) ? data_input($data['lang']) : ($hasError = true);
  $img = !empty($data['img']) ? data_input($data['img']) : ($hasError = true);
  $descripcio = !empty($data['descripcio']) ? data_input($data['descripcio']) : ($hasError = true);
  $dataVista = !empty($data['dataVista']) ? data_input($data['dataVista']) : ($hasError = true);

  $timestamp = date('Y-m-d');
  $dateCreated = $timestamp;
  $dateModified = $timestamp;

  if (!$hasError) {
    global $conn;
    $sql = "INSERT INTO 11_db_pelicules SET pelicula=:pelicula, pelicula_es=:pelicula_es, director=:director, any=:any, genere=:genere, img=:img, pais=:pais, lang=:lang, dataVista=:dataVista, dateModified=:dateModified, dateCreated=:dateCreated, descripcio=:descripcio, slug=:slug";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":pelicula", $pelicula, PDO::PARAM_STR);
    $stmt->bindParam(":pelicula_es", $pelicula_es, PDO::PARAM_STR);
    $stmt->bindParam(":director", $director, PDO::PARAM_STR);
    $stmt->bindParam(":any", $any, PDO::PARAM_INT);
    $stmt->bindParam(":genere", $genere, PDO::PARAM_INT);
    $stmt->bindParam(":pais", $pais, PDO::PARAM_INT);
    $stmt->bindParam(":img", $img, PDO::PARAM_INT);
    $stmt->bindParam(":lang", $lang, PDO::PARAM_STR);
    $stmt->bindParam(":dataVista", $dataVista, PDO::PARAM_STR);
    $stmt->bindParam(":dateCreated", $dateCreated, PDO::PARAM_STR);
    $stmt->bindParam(":dateModified", $dateModified, PDO::PARAM_STR);
    $stmt->bindParam(":descripcio", $descripcio, PDO::PARAM_STR);
    $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);

    if ($stmt->execute()) {
      // response output
      $response['status'] = 'success';
      header("Content-Type: application/json");
      echo json_encode($response);
    } else {
      // response output - data error
      $response['status'] = 'error';
      header("Content-Type: application/json");
      echo json_encode($response);
    }
  } else {
    // response output - data error
    $response['status'] = 'error';

    header("Content-Type: application/json");
    echo json_encode($response);
  }

  // c) Crear nova serie
} else if (isset($_GET['serie'])) {

  AdminMiddleware::handle();

  $db->beginTransaction();

  try {

    /**
     * =========================
     * CREATE ID (UUID v7)
     * =========================
     */
    $id = ramsey::uuid7()->toString();
    $idBin = Uuid::toBinary($id);

    /**
     * =========================
     * INPUT PRINCIPAL
     * =========================
     */
    $name = data_input($_POST['name']);
    $slugText = data_input($_POST['slug']);

    $startYear = (int) $_POST['startYear'];
    $endYear = !empty($_POST['endYear']) ? (int) $_POST['endYear'] : null;

    $season = (int) $_POST['season'];
    $chapter = (int) $_POST['chapter'];

    $director_id = Uuid::toBinary($_POST['director_id']);
    $idioma_id = Uuid::toBinary($_POST['idioma_id']);
    $genere_id = Uuid::toBinary($_POST['genere_id']);
    $pais_id = Uuid::toBinary($_POST['pais_id']);

    $descripcio = data_input($_POST['descripcio']);

    /**
     * =========================
     * IMATGE (OPCIONAL)
     * =========================
     */
    $img_id_bin = null;

    $hasImage = !empty($_FILES['img_upload'])
      && $_FILES['img_upload']['error'] === UPLOAD_ERR_OK;

    if ($hasImage) {

      $file = $_FILES['img_upload'];

      $nom = pathinfo($file['name'], PATHINFO_FILENAME);

      $alt = !empty($_POST['alt'])
        ? data_input($_POST['alt'])
        : $nom;

      $img_uuid = ImageService::createFromUpload(
        $file,
        1,
        $nom,
        $alt,
        $conn
      );

      $img_id_bin = Uuid::toBinary($img_uuid);
    }

    /**
     * =========================
     * INSERT SERIE
     * =========================
     */
    $sql = <<<SQL
            INSERT INTO %s (
                id, name, slug,
                startYear, endYear,
                season, chapter,
                director_id, idioma_id, genere_id, pais_id,
                img_id, descripcio,
                dateCreated, dateModified
            ) VALUES (
                :id, :name, :slug,
                :startYear, :endYear,
                :season, :chapter,
                :director_id, :idioma_id, :genere_id, :pais_id,
                :img_id, :descripcio,
                NOW(), NOW()
            )
        SQL;

    $query = sprintf($sql, qi(Tables::CINEMA_SERIES_TV, $pdo));

    $db->execute($query, [
      ':id' => $idBin,
      ':name' => $name,
      ':slug' => $slugText,
      ':startYear' => $startYear,
      ':endYear' => $endYear,
      ':season' => $season,
      ':chapter' => $chapter,
      ':director_id' => $director_id,
      ':idioma_id' => $idioma_id,
      ':genere_id' => $genere_id,
      ':pais_id' => $pais_id,
      ':img_id' => $img_id_bin,
      ':descripcio' => $descripcio
    ]);

    /**
     * =========================
     * INSERT ACTORS RELATION
     * =========================
     */
    $actors = $_POST['actors'] ?? [];
    $roles  = $_POST['roles'] ?? [];


    $sqlActor = <<<SQL
            INSERT INTO %s (
                id, serie_id, actor_id, role
            ) VALUES (
                :id, :serie_id, :actor_id, :role
            )
        SQL;

    $queryActor = sprintf(
      $sqlActor,
      qi(Tables::CINEMA_ACTORS_SERIES, $pdo)
    );

    foreach ($actors as $i => $actorId) {

      if (!isUuid($actorId)) continue;

      $role = $roles[$i] ?? '';
      $id_rel = ramsey::uuid7()->toString();
      $id_rel_bin = Uuid::toBinary($id_rel);

      $db->execute($queryActor, [
        ':id' => $id_rel_bin,
        ':serie_id' => $idBin,
        ':actor_id' => Uuid::toBinary($actorId),
        ':role' => $role
      ]);
    }

    $db->commit();

    Response::success(
      MissatgesAPI::success('create'),
      ['id' => $id],
      200
    );
  } catch (PDOException $e) {

    $db->rollBack();

    Response::error(
      MissatgesAPI::error('errorBD'),
      [$e->getMessage()],
      500
    );
  }

  // a) Inserir Actor en pelicula
} else if (isset($_GET['actorPelicula'])) {

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

  // Ahora puedes acceder a los datos como un array asociativo
  $hasError = false; // Inicializamos la variable $hasError como false

  $idMovie = !empty($data['idMovie']) ? data_input($data['idMovie']) : ($hasError = true);
  $idActor = !empty($data['idActor']) ? data_input($data['idActor']) : ($hasError = true);
  $role = !empty($data['role']) ? data_input($data['role']) : ($hasError = true);

  if (!$hasError) {
    global $conn;
    $sql = "INSERT INTO 11_aux_cinema_actors_pelicules SET idActor=:idActor, idMovie=:idMovie, role=:role";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":idActor", $idActor, PDO::PARAM_INT);
    $stmt->bindParam(":idMovie", $idMovie, PDO::PARAM_INT);
    $stmt->bindParam(":role", $role, PDO::PARAM_STR);

    if ($stmt->execute()) {
      // response output
      $response['status'] = 'success';

      header("Content-Type: application/json");
      echo json_encode($response);
    } else {
      // response output - data error
      $response['status'] = 'error';

      header("Content-Type: application/json");
      echo json_encode($response);
    }
  }
  // si no hi ha cap endpoint valid, mostrar error:
} else {
  // response output - data error
  $response['status'] = 'error';
  header("Content-Type: application/json");
  echo json_encode($response);
  exit();
}
