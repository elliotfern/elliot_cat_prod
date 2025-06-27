<?php

use Ramsey\Uuid\Uuid;

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

$allowed_origins = ['https://elliot.cat'];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
  http_response_code(403);
  echo json_encode(['error' => 'Acceso no permitido']);
  exit;
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
} elseif (isset($_GET['llibre'])) {

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

  $autor = isset($data['autor']) ? data_input($data['autor']) : ($hasError = true);
  $titol = isset($data['titol']) ? data_input($data['titol']) : ($hasError = true);
  $titolEng = isset($data['titolEng']) ? data_input($data['titolEng']) : NULL;
  $slug = isset($data['slug']) ? data_input($data['slug']) : ($hasError = true);
  $any = isset($data['any']) ? data_input($data['any']) : ($hasError = true);
  $tipus = isset($data['tipus']) ? data_input($data['tipus']) : ($hasError = true);
  $idEd = isset($data['idEd']) ? data_input($data['idEd']) : ($hasError = true);
  $idGen = isset($data['idGen']) ? data_input($data['idGen']) : ($hasError = true);
  $subGen = isset($data['subGen']) ? data_input($data['subGen']) : ($hasError = true);
  $lang = isset($data['lang']) ? data_input($data['lang']) : ($hasError = true);
  $img = isset($data['img']) ? data_input($data['img']) : ($hasError = true);
  $lang = isset($data['lang']) ? data_input($data['lang']) : ($hasError = true);
  $estat = isset($data['estat']) ? data_input($data['estat']) : ($hasError = true);

  $id = isset($data['id']) ? data_input($data['id']) : ($hasError = true);

  $timestamp = date('Y-m-d');
  $dateModified = $timestamp;

  if ($hasError == false) {
    global $conn;
    $sql = "UPDATE 08_db_biblioteca_llibres SET autor=:autor, titol=:titol, titolEng=:titolEng, any=:any, idGen=:idGen, subGen=:subGen, idEd=:idEd, lang=:lang, slug=:slug, img=:img, tipus=:tipus, dateModified=:dateModified, estat=:estat WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":autor", $autor, PDO::PARAM_INT);
    $stmt->bindParam(":titol", $titol, PDO::PARAM_STR);
    $stmt->bindParam(":titolEng", $titolEng, PDO::PARAM_STR);
    $stmt->bindParam(":any", $any, PDO::PARAM_INT);
    $stmt->bindParam(":idEd", $idEd, PDO::PARAM_INT);
    $stmt->bindParam(":lang", $lang, PDO::PARAM_INT);
    $stmt->bindParam(":img", $img, PDO::PARAM_INT);
    $stmt->bindParam(":tipus", $tipus, PDO::PARAM_INT);
    $stmt->bindParam(":dateModified", $dateModified, PDO::PARAM_STR);
    $stmt->bindParam(":idGen", $idGen, PDO::PARAM_INT);
    $stmt->bindParam(":subGen", $subGen, PDO::PARAM_INT);
    $stmt->bindParam(":estat", $estat, PDO::PARAM_INT);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);

    if ($stmt->execute()) {
      // response output
      $response['status'] = 'success';

      header("Content-Type: application/json");
      echo json_encode($response);
    } else {
      // response output - data error
      $response['status'] = 'error bd';

      header("Content-Type: application/json");
      echo json_encode($response);
    }
  } else {
    // response output - data error
    $response['status'] = 'error has error dades';

    header("Content-Type: application/json");
    echo json_encode($response);
  }
} else {
  // response output - data error
  $response['status'] = 'error 2 url api';
  header("Content-Type: application/json");
  echo json_encode($response);
  exit();
}
