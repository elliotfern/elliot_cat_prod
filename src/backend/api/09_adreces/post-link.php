<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;
use Ramsey\Uuid\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

/*
 * BACKEND DB LINKS
 * FUNCIONS
 * @
 */

$conn = DatabaseConnection::getConnection();

if (!$conn) {
  die("No se pudo establecer conexión a la base de datos.");
}

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

// Requiere ADMIN por token (user_type === 1)
if (!isAuthenticatedAdmin()) {
  http_response_code(403);
  echo json_encode(['error' => 'No autoritzat (admin requerit)']);
  exit;
}

$userUuid = getAuthenticatedUserUuid(); // para auditoría, si la soportas

// a) Inserir link
if ($slug === 'link') {
  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // ✅ Generar ID (UUIDv7) en el servidor
  try {
    $idText = Uuid::uuid7()->toString();
  } catch (Throwable $t) {
    Response::error(MissatgesAPI::error('errorServidor'), ['No s’ha pogut generar l’UUID'], 500);
  }

  // 📥 Campos
  $nom          = isset($data['nom']) ? trim((string)$data['nom']) : null;
  $web          = isset($data['web']) ? trim((string)$data['web']) : null;
  $subTemaIdTxt = isset($data['sub_tema_id']) ? trim((string)$data['sub_tema_id']) : null;
  $lang         = isset($data['lang']) ? (int)$data['lang'] : null;
  $tipus        = isset($data['tipus']) ? (int)$data['tipus'] : null;

  // 🔎 Validacions bàsiques
  if (empty($subTemaIdTxt)) {
    $errors[] = ValidacioErrors::requerit('sub_tema_id');
  } elseif (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i', $subTemaIdTxt)) {
    $errors[] = ValidacioErrors::format('sub_tema_id');
  }

  if ($lang === null) {
    $errors[] = ValidacioErrors::requerit('lang');
  } elseif ($lang < 0) {
    $errors[] = ValidacioErrors::format('lang');
  }

  if ($tipus === null) {
    $errors[] = ValidacioErrors::requerit('tipus');
  }

  if (empty($web)) {
    $errors[] = ValidacioErrors::requerit('web');
  } else {
    // Validación simple de URL (permite http/https)
    if (!filter_var($web, FILTER_VALIDATE_URL)) {
      $errors[] = ValidacioErrors::format('web');
    }
  }

  // (Opcional) límites de longitud
  $maxNom = 5000;
  if ($nom !== null && $nom !== '' && mb_strlen($nom) > $maxNom) {
    $errors[] = ValidacioErrors::massaLlarg('nom', $maxNom);
  }
  $maxWeb = 1000;
  if ($web !== null && $web !== '' && mb_strlen($web) > $maxWeb) {
    $errors[] = ValidacioErrors::massaLlarg('web', $maxWeb);
  }

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }


  try {
    // ✅ Comprovar que el sub_tema existeix
    $check = $conn->prepare("SELECT 1 FROM aux_sub_temes WHERE id = uuid_text_to_bin(:sub_tema_id) LIMIT 1");
    $check->bindValue(':sub_tema_id', $subTemaIdTxt, PDO::PARAM_STR);
    $check->execute();
    if (!$check->fetchColumn()) {
      Response::error(
        MissatgesAPI::error('validacio'),
        [ValidacioErrors::noExisteix('sub_tema_id')],
        404
      );
    }

    // INSERT
    $sql = "INSERT INTO db_links (
                id, nom, web, sub_tema_id, lang, tipus, dateCreated, dateModified
            ) VALUES (
                uuid_text_to_bin(:id),
                :nom,
                :web,
                uuid_text_to_bin(:sub_tema_id),
                :lang,
                :tipus,
                CURDATE(),
                CURDATE()
            )";

    $stmt = $conn->prepare($sql);

    // Bindings (nota: nom puede ser NULL)
    $stmt->bindValue(':id', $idText, PDO::PARAM_STR);
    $stmt->bindValue(':nom', ($nom === '' ? null : $nom), $nom === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':web', $web, PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_id', $subTemaIdTxt, PDO::PARAM_STR);
    $stmt->bindValue(':lang', $lang, PDO::PARAM_INT);
    $stmt->bindValue(':tipus', $tipus, PDO::PARAM_INT);

    $stmt->execute();

    // 📝 Audit
    Audit::registrarCanvi(
      $conn,
      $userUuid,
      'INSERT',
      "Creació link ($idText) per sub_tema $subTemaIdTxt",
      Tables::DB_LINKS,
      $idText // si Audit espera binari, adapta-ho
    );

    Response::success(
      MissatgesAPI::success('create'),
      [
        'id'           => $idText,
        'nom'          => $nom,
        'web'          => $web,
        'sub_tema_id'  => $subTemaIdTxt,
        'lang'         => $lang,
        'tipus'        => $tipus,
        'dateCreated'  => date('Y-m-d'),
        'dateModified' => date('Y-m-d'),
      ],
      201
    );
  } catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
      Response::error(MissatgesAPI::error('duplicat'), ['Registre duplicat'], 409);
    }
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
} else if ($slug === 'tema') {
  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // ✅ Generar ID (UUIDv7) en el servidor
  try {
    $idText = Uuid::uuid7()->toString(); // p. ej. "018f6a9b-3b1a-7c3e-8a4b-9f2a1c0b7d2e"
  } catch (Throwable $t) {
    Response::error(MissatgesAPI::error('errorServidor'), ['No s\'ha pogut generar l\'UUID'], 500);
  }

  // 📥 Campos opcionales (trim y null si vacío)
  $tema = isset($data['tema']) ? trim((string)$data['tema']) : null;

  // 🔎 Validació: almenys un idioma amb text
  if (($tema === null || $tema === '')) {
    $errors[] = ValidacioErrors::requerit('almenys_un_idioma');
  }

  // (Opcional) Límits de longitud bàsics
  $maxLen = 5000;
  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };
  $checkLen($tema, 'tema');

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }

  try {
    $sql = "INSERT INTO aux_temes (
                id, tema
            ) VALUES (
                uuid_text_to_bin(:id), :tema
            )";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $idText, PDO::PARAM_STR);
    $stmt->bindValue(':tema', ($tema === '' ? null : $tema), $tema === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->execute();

    // 📝 Audit
    Audit::registrarCanvi(
      $conn,
      $userUuid,
      'INSERT',
      "Creació nou tema ($idText)",
      Tables::DB_TEMES,
      $idText // si el teu Audit espera binari, adapta-ho
    );

    Response::success(
      MissatgesAPI::success('create'),
      [
        'id' => $idText, // retornem el UUID text
        'tema' => [
          'ca' => $tema,
        ],
      ],
      201
    );
  } catch (PDOException $e) {
    if ((int)$e->errorInfo[1] === 1062) { // Duplicate entry
      Response::error(
        MissatgesAPI::error('duplicat'),
        ['Ja existeix un registre amb aquest id.'],
        409
      );
    }
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
} else if ($slug === 'subtema') {
  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // ✅ Generar ID (UUIDv7) en el servidor
  try {
    $idText = Uuid::uuid7()->toString();
  } catch (Throwable $t) {
    Response::error(MissatgesAPI::error('errorServidor'), ['No s’ha pogut generar l’UUID'], 500);
  }

  // 🔎 Validacions
  // tema_id: requerit + format UUID (acceptem qualsevol versió canònica)
  if (empty($data['tema_id'])) {
    $errors[] = ValidacioErrors::requerit('tema_id');
  } else {
    $temaIdText = (string)$data['tema_id'];
  }

  // Sub-temes (permès null; cal com a mínim un idioma amb text)
  $sub = isset($data['sub_tema']) ? trim((string)$data['sub_tema']) : null;

  if (($sub === null || $sub === '')) {
    $errors[] = ValidacioErrors::requerit('almenys_un_idioma');
  }

  // (Opcional) límit de longitud
  $maxLen = 5000;
  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };
  $checkLen($sub, 'sub_tema');

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }


  try {
    // ✅ Comprovem que el tema pare existeix
    $check = $conn->prepare("SELECT 1 FROM aux_temes WHERE id = uuid_text_to_bin(:tema_id) LIMIT 1");
    $check->bindValue(':tema_id', $idText, PDO::PARAM_STR);
    $check->execute();
    if (!$check->fetchColumn()) {
      Response::error(
        MissatgesAPI::error('validacio'),
        [ValidacioErrors::noExisteix('tema_id')],
        404
      );
    }

    // INSERT
    $sql = "INSERT INTO aux_sub_temes (
                id, tema_id, sub_tema
            ) VALUES (
                uuid_text_to_bin(:id),
                uuid_text_to_bin(:tema_id),
                :sub_tema
            )";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $idText, PDO::PARAM_STR);
    $stmt->bindValue(':tema_id', $idText, PDO::PARAM_STR);

    $stmt->bindValue(':sub_tema', ($sub === '' ? null : $sub), $sub === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->execute();

    // 📝 Audit
    Audit::registrarCanvi(
      $conn,
      $userUuid,
      'INSERT',
      "Creació nou sub-tema ($idText) per tema $idText",
      Tables::DB_SUBTEMES,
      $idText // si Audit espera binari, adapta-ho
    );

    Response::success(
      MissatgesAPI::success('create'),
      [
        'id' => $idText,
        'tema_id' => $idText,
        'sub_tema' => [
          'ca' => $sub,
        ],
      ],
      201
    );
  } catch (PDOException $e) {
    // Duplicats (si hi ha restriccions úniques específiques)
    if ((int)$e->errorInfo[1] === 1062) {
      Response::error(MissatgesAPI::error('duplicat'), ['Registre duplicat'], 409);
    }
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
} else {
  // response output - data error
  $response['status'] = 'error';

  echo json_encode($response);
}
