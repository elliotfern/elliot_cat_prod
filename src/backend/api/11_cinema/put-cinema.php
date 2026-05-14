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

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");


// RUTA PARA ACTUALIZAR PELICULA
// ruta GET => "/api/cinema/put/?pelicula"
if (isset($_GET['pelicula'])) {
  // e) Actualitzar pelicula

  AdminMiddleware::handle();

  $db->beginTransaction();

  try {

    /**
     * =========================
     * INPUT ID
     * =========================
     */
    $idText = $_GET['id'] ?? null;

    if (!isUuid($idText)) {
      Response::error(
        MissatgesAPI::error('invalid_id'),
        [],
        400
      );
      return;
    }

    $idBin = Uuid::toBinary($idText);

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
     * IMATGE (OPCIONAL UPDATE)
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
        7,
        $nom,
        $alt,
        $conn
      );

      $imatge_id_bin = Uuid::toBinary($img_uuid);
    }

    /**
     * =========================
     * UPDATE PELICULA
     * =========================
     */
    $sql = <<<SQL
            UPDATE %s SET
                pelicula = :pelicula,
                pelicula_ca = :pelicula_ca,
                slug = :slug,
                any = :any,
                director_id = :director_id,
                idioma_id = :idioma_id,
                genere_id = :genere_id,
                pais_id = :pais_id,
                descripcio = :descripcio,
                dateModified = NOW()
        SQL;

    if ($imatge_id_bin !== null) {
      $sql .= ", imatge_id = :imatge_id";
    }

    $sql .= " WHERE id = :id";

    $query = sprintf($sql, qi(Tables::CINEMA_PELICULES, $pdo));

    $params = [
      ':pelicula' => $pelicula,
      ':pelicula_ca' => $pelicula_ca,
      ':slug' => $slugText,
      ':any' => $any,
      ':director_id' => $director_id,
      ':idioma_id' => $idioma_id,
      ':genere_id' => $genere_id,
      ':pais_id' => $pais_id,
      ':descripcio' => $descripcio,
      ':id' => $idBin
    ];

    if ($imatge_id_bin !== null) {
      $params[':imatge_id'] = $imatge_id_bin;
    }

    $db->execute($query, $params);

    /**
     * =========================
     * UPDATE ACTORS (REPLACE STRATEGY)
     * =========================
     */
    $actors = $_POST['actors'] ?? [];
    $roles  = $_POST['roles'] ?? [];

    // 1) borrar relacions antigues
    $sqlDelete = sprintf(
      "DELETE FROM %s WHERE pelicula_id = :id",
      qi(Tables::CINEMA_ACTORS_PELICULES, $pdo)
    );

    $db->execute($sqlDelete, [
      ':id' => $idBin
    ]);

    // 2) insertar noves relacions
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
      MissatgesAPI::success('update'),
      ['id' => $idText],
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


  // RUTA PARA ACTUALIZAR SERIE TV
  // ruta PUT => "/api/cinema/put/?serie"
} else if (isset($_GET['serie'])) {

  AdminMiddleware::handle();

  $db->beginTransaction();

  try {

    $id = $_POST['id'];

    if (!isUuid($id)) {
      Response::error("ID invalid", [], 400);
      return;
    }

    $idBin = Uuid::toBinary($id);

    /**
     * =========================
     * INPUT
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
     * IMATGE
     * =========================
     */
    $img_id_bin = '__MISSING__';

    if (!empty($_FILES['img_upload']) && $_FILES['img_upload']['error'] === UPLOAD_ERR_OK) {

      $file = $_FILES['img_upload'];

      $nomImatge = pathinfo($file['name'], PATHINFO_FILENAME);

      $alt = !empty($_POST['alt'])
        ? data_input($_POST['alt'])
        : $nomImatge;

      $img_uuid = ImageService::createFromUpload(
        $file,
        7,
        $nomImatge,
        $alt,
        $conn
      );

      $img_id_bin = Uuid::toBinary($img_uuid);
    } else if (!empty($_POST['img_id']) && isUuid($_POST['img_id'])) {

      $img_id_bin = Uuid::toBinary($_POST['img_id']);
    }

    /**
     * =========================
     * UPDATE SERIE
     * =========================
     */
    $table = qi(Tables::CINEMA_SERIES_TV, $pdo);

    $set = [
      "name = :name",
      "slug = :slug",
      "startYear = :startYear",
      "endYear = :endYear",
      "season = :season",
      "chapter = :chapter",
      "director_id = :director_id",
      "idioma_id = :idioma_id",
      "genere_id = :genere_id",
      "pais_id = :pais_id",
      "descripcio = :descripcio",
      "dateModified = NOW()"
    ];

    $params = [
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
      ':descripcio' => $descripcio
    ];

    // img opcional
    if ($img_id_bin !== '__MISSING__') {
      $set[] = "img_id = :img_id";
      $params[':img_id'] = $img_id_bin;
    }

    $sql = "
      UPDATE $table SET
        " . implode(",\n", $set) . "
      WHERE id = :id
    ";

    $db->execute($sql, $params);

    /**
     * =========================
     * RELACIONS ACTORS
     * =========================
     */

    $table = qi(Tables::CINEMA_ACTORS_SERIES, $pdo);

    $sql = "DELETE FROM $table WHERE serie_id = :id";

    $db->execute($sql, [
      ':id' => $idBin
    ]);

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
      MissatgesAPI::success('update'),
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



  // si no coincideix cap endopoint, error
} else {
  // response output - data error
  $response['status'] = 'error 2 url api';
  header("Content-Type: application/json");
  echo json_encode($response);
  exit();
}
