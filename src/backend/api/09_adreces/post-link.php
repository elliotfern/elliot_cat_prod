<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;
use Ramsey\Uuid\Uuid;

$slug = $routeParams[0];

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

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

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
  $tema_ca = isset($data['tema_ca']) ? trim((string)$data['tema_ca']) : null;
  $tema_en = isset($data['tema_en']) ? trim((string)$data['tema_en']) : null;
  $tema_es = isset($data['tema_es']) ? trim((string)$data['tema_es']) : null;
  $tema_fr = isset($data['tema_fr']) ? trim((string)$data['tema_fr']) : null;
  $tema_it = isset($data['tema_it']) ? trim((string)$data['tema_it']) : null;

  // 🔎 Validació: almenys un idioma amb text
  if (
    ($tema_ca === null || $tema_ca === '') &&
    ($tema_en === null || $tema_en === '') &&
    ($tema_es === null || $tema_es === '') &&
    ($tema_fr === null || $tema_fr === '') &&
    ($tema_it === null || $tema_it === '')
  ) {
    $errors[] = ValidacioErrors::requerit('almenys_un_idioma');
  }

  // (Opcional) Límits de longitud bàsics
  $maxLen = 5000;
  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };
  $checkLen($tema_ca, 'tema_ca');
  $checkLen($tema_en, 'tema_en');
  $checkLen($tema_es, 'tema_es');
  $checkLen($tema_fr, 'tema_fr');
  $checkLen($tema_it, 'tema_it');

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }

  try {
    $sql = "INSERT INTO aux_temes (
                id, tema_ca, tema_en, tema_es, tema_fr, tema_it
            ) VALUES (
                uuid_text_to_bin(:id), :tema_ca, :tema_en, :tema_es, :tema_fr, :tema_it
            )";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $idText, PDO::PARAM_STR);
    $stmt->bindValue(':tema_ca', ($tema_ca === '' ? null : $tema_ca), $tema_ca === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':tema_en', ($tema_en === '' ? null : $tema_en), $tema_en === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':tema_es', ($tema_es === '' ? null : $tema_es), $tema_es === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':tema_fr', ($tema_fr === '' ? null : $tema_fr), $tema_fr === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':tema_it', ($tema_it === '' ? null : $tema_it), $tema_it === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);

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
          'ca' => $tema_ca,
          'en' => $tema_en,
          'es' => $tema_es,
          'fr' => $tema_fr,
          'it' => $tema_it,
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
  $sub_ca = isset($data['sub_tema_ca']) ? trim((string)$data['sub_tema_ca']) : null;
  $sub_en = isset($data['sub_tema_en']) ? trim((string)$data['sub_tema_en']) : null;
  $sub_es = isset($data['sub_tema_es']) ? trim((string)$data['sub_tema_es']) : null;
  $sub_it = isset($data['sub_tema_it']) ? trim((string)$data['sub_tema_it']) : null;
  $sub_fr = isset($data['sub_tema_fr']) ? trim((string)$data['sub_tema_fr']) : null;

  if (
    ($sub_ca === null || $sub_ca === '') &&
    ($sub_en === null || $sub_en === '') &&
    ($sub_es === null || $sub_es === '') &&
    ($sub_it === null || $sub_it === '') &&
    ($sub_fr === null || $sub_fr === '')
  ) {
    $errors[] = ValidacioErrors::requerit('almenys_un_idioma');
  }

  // (Opcional) límit de longitud
  $maxLen = 5000;
  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };
  $checkLen($sub_ca, 'sub_tema_ca');
  $checkLen($sub_en, 'sub_tema_en');
  $checkLen($sub_es, 'sub_tema_es');
  $checkLen($sub_it, 'sub_tema_it');
  $checkLen($sub_fr, 'sub_tema_fr');

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }


  try {
    // ✅ Comprovem que el tema pare existeix
    $check = $conn->prepare("SELECT 1 FROM aux_temes WHERE id = uuid_text_to_bin(:tema_id) LIMIT 1");
    $check->bindValue(':tema_id', $temaIdText, PDO::PARAM_STR);
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
                id, tema_id, sub_tema_ca, sub_tema_en, sub_tema_es, sub_tema_it, sub_tema_fr
            ) VALUES (
                uuid_text_to_bin(:id),
                uuid_text_to_bin(:tema_id),
                :sub_tema_ca, :sub_tema_en, :sub_tema_es, :sub_tema_it, :sub_tema_fr
            )";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $idText, PDO::PARAM_STR);
    $stmt->bindValue(':tema_id', $temaIdText, PDO::PARAM_STR);

    $stmt->bindValue(':sub_tema_ca', ($sub_ca === '' ? null : $sub_ca), $sub_ca === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_en', ($sub_en === '' ? null : $sub_en), $sub_en === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_es', ($sub_es === '' ? null : $sub_es), $sub_es === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_it', ($sub_it === '' ? null : $sub_it), $sub_it === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_fr', ($sub_fr === '' ? null : $sub_fr), $sub_fr === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->execute();

    // 📝 Audit
    Audit::registrarCanvi(
      $conn,
      $userUuid,
      'INSERT',
      "Creació nou sub-tema ($idText) per tema $temaIdText",
      Tables::DB_SUBTEMES,
      $idText // si Audit espera binari, adapta-ho
    );

    Response::success(
      MissatgesAPI::success('create'),
      [
        'id' => $idText,
        'tema_id' => $temaIdText,
        'sub_tema' => [
          'ca' => $sub_ca,
          'en' => $sub_en,
          'es' => $sub_es,
          'it' => $sub_it,
          'fr' => $sub_fr,
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
