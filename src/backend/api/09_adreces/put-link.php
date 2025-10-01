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
header("Access-Control-Allow-Methods: PUT");

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

  // 🔎 Requerim id (UUIDv7 text)
  if (empty($data['id'])) {
    $errors[] = ValidacioErrors::requerit('id');
  } else {
    $idText = (string)$data['id'];
  }

  // 📥 Campos opcionales (trim y null si vacío)
  $tema_ca = array_key_exists('tema_ca', $data) ? trim((string)$data['tema_ca']) : null;
  $tema_en = array_key_exists('tema_en', $data) ? trim((string)$data['tema_en']) : null;
  $tema_es = array_key_exists('tema_es', $data) ? trim((string)$data['tema_es']) : null;
  $tema_fr = array_key_exists('tema_fr', $data) ? trim((string)$data['tema_fr']) : null;
  $tema_it = array_key_exists('tema_it', $data) ? trim((string)$data['tema_it']) : null;

  // ✅ Debe venir al menos un campo tema_* (aunque sea para ponerlo a null)
  $anyProvided = array_key_exists('tema_ca', $data)
    || array_key_exists('tema_en', $data)
    || array_key_exists('tema_es', $data)
    || array_key_exists('tema_fr', $data)
    || array_key_exists('tema_it', $data);

  if (!$anyProvided) {
    $errors[] = ValidacioErrors::requerit('almenys_un_idioma');
  }

  // (Opcional) límites de longitud
  $maxLen = 5000;
  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };
  if (array_key_exists('tema_ca', $data)) $checkLen($tema_ca, 'tema_ca');
  if (array_key_exists('tema_en', $data)) $checkLen($tema_en, 'tema_en');
  if (array_key_exists('tema_es', $data)) $checkLen($tema_es, 'tema_es');
  if (array_key_exists('tema_fr', $data)) $checkLen($tema_fr, 'tema_fr');
  if (array_key_exists('tema_it', $data)) $checkLen($tema_it, 'tema_it');

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }


  // 🛠️ Construcción dinámica del UPDATE
  $setParts = [];
  $params = [':id' => $idText];

  $normalize = function ($val) {
    // vació -> null; si llega explicitamente null, se mantiene null
    if ($val === null) return null;
    return ($val === '') ? null : $val;
  };

  if (array_key_exists('tema_ca', $data)) {
    $setParts[] = 'tema_ca = :tema_ca';
    $params[':tema_ca'] = $normalize($tema_ca);
  }
  if (array_key_exists('tema_en', $data)) {
    $setParts[] = 'tema_en = :tema_en';
    $params[':tema_en'] = $normalize($tema_en);
  }
  if (array_key_exists('tema_es', $data)) {
    $setParts[] = 'tema_es = :tema_es';
    $params[':tema_es'] = $normalize($tema_es);
  }
  if (array_key_exists('tema_fr', $data)) {
    $setParts[] = 'tema_fr = :tema_fr';
    $params[':tema_fr'] = $normalize($tema_fr);
  }
  if (array_key_exists('tema_it', $data)) {
    $setParts[] = 'tema_it = :tema_it';
    $params[':tema_it'] = $normalize($tema_it);
  }

  if (empty($setParts)) {
    // (no debería ocurrir por la validación previa, pero por seguridad)
    Response::error(MissatgesAPI::error('validacio'), ['Cap camp a actualitzar'], 400);
  }

  try {
    $sql = "UPDATE aux_temes
            SET " . implode(', ', $setParts) . "
            WHERE id = uuid_text_to_bin(:id)
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    foreach ($params as $k => $v) {
      if ($k === ':id') {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
        continue;
      }
      // Campos tema_*: si null -> PDO::PARAM_NULL, si no -> STR
      if ($v === null) {
        $stmt->bindValue($k, null, PDO::PARAM_NULL);
      } else {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
      }
    }

    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      // No existe el id o los valores son idénticos (idempotente). Distinguimos con un SELECT.
      $chk = $conn->prepare("SELECT 1 FROM aux_temes WHERE id = uuid_text_to_bin(:id) LIMIT 1");
      $chk->bindValue(':id', $idText, PDO::PARAM_STR);
      $chk->execute();
      if (!$chk->fetchColumn()) {
        Response::error(MissatgesAPI::error('noTrobat'), ['No s’ha trobat el registre'], 404);
      }
      // Existe pero no cambió nada → devolvemos success idempotente
    }

    // 📝 Audit
    Audit::registrarCanvi(
      $conn,
      $userUuid,
      'UPDATE',
      'Actualització de tema ' . $idText,
      Tables::DB_TEMES,
      $idText // adapta si tu Audit espera binari
    );

    // 📤 Devolver eco de lo actualizado (solo campos enviados)
    $returnTema = [];
    foreach (['ca', 'en', 'es', 'fr', 'it'] as $lang) {
      $key = "tema_$lang";
      if (array_key_exists($key, $data)) {
        $returnTema[$lang] = $params[":$key"] ?? null;
      }
    }

    Response::success(
      MissatgesAPI::success('update'),
      [
        'id' => $idText,
        'tema' => $returnTema
      ],
      200
    );
  } catch (PDOException $e) {
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
} else {
  // response output - data error
  $response['status'] = 'error';

  echo json_encode($response);
}
