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
    $pelicula = data_input($_POST['pelicula']);
    $pelicula_ca = !empty($_POST['pelicula_ca'])
      ? data_input($_POST['pelicula_ca'])
      : null;

    $slugText = data_input($_POST['slug']);

    $any = (int) $_POST['any'];

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
    $imatge_id_bin = null;

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
        8,
        $nom,
        $alt,
        $conn
      );

      $imatge_id_bin = Uuid::toBinary($img_uuid);
    }

    /**
     * =========================
     * INSERT PELICULA
     * =========================
     */
    $sql = <<<SQL
            INSERT INTO %s (
                id,
                pelicula,
                pelicula_ca,
                slug,
                director_id,
                genere_id,
                pais_id,
                idioma_id,
                imatge_id,
                any,
                descripcio,
                dateCreated,
                dateModified
            ) VALUES (
                :id,
                :pelicula,
                :pelicula_ca,
                :slug,
                :director_id,
                :genere_id,
                :pais_id,
                :idioma_id,
                :imatge_id,
                :any,
                :descripcio,
                NOW(),
                NOW()
            )
        SQL;

    $query = sprintf(
      $sql,
      qi(Tables::CINEMA_PELICULES, $pdo)
    );

    $db->execute($query, [
      ':id' => $idBin,
      ':pelicula' => $pelicula,
      ':pelicula_ca' => $pelicula_ca,
      ':slug' => $slugText,
      ':director_id' => $director_id,
      ':genere_id' => $genere_id,
      ':pais_id' => $pais_id,
      ':idioma_id' => $idioma_id,
      ':imatge_id' => $imatge_id_bin,
      ':any' => $any,
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
                id,
                pelicula_id,
                actor_id,
                role
            ) VALUES (
                :id,
                :pelicula_id,
                :actor_id,
                :role
            )
        SQL;

    $queryActor = sprintf(
      $sqlActor,
      qi(Tables::CINEMA_ACTORS_PELICULES, $pdo)
    );

    foreach ($actors as $i => $actorId) {

      if (!isUuid($actorId)) {
        continue;
      }

      $role = $roles[$i] ?? '';

      $id_rel = ramsey::uuid7()->toString();
      $id_rel_bin = Uuid::toBinary($id_rel);

      $db->execute($queryActor, [
        ':id' => $id_rel_bin,
        ':pelicula_id' => $idBin,
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
        7,
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

  // si no hi ha cap endpoint valid, mostrar error:
} else {
  // response output - data error
  $response['status'] = 'error';
  header("Content-Type: application/json");
  echo json_encode($response);
  exit();
}
