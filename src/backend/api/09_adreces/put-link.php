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
  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // 🔑 id: requerit + format UUID
  if (empty($data['id'])) {
    $errors[] = ValidacioErrors::requerit('id');
  } else {
    $idText = (string)$data['id'];
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i', $idText)) {
      $errors[] = ValidacioErrors::format('id', 'uuid');
    }
  }

  // 📥 Campos opcionales (si no vienen, no se actualizan)
  $nom          = array_key_exists('nom', $data) ? trim((string)$data['nom']) : null;
  $web          = array_key_exists('web', $data) ? trim((string)$data['web']) : null;
  $subTemaIdTxt = array_key_exists('sub_tema_id', $data) ? trim((string)$data['sub_tema_id']) : null;
  $lang         = array_key_exists('lang', $data) ? (int)$data['lang'] : null;
  $tipus        = array_key_exists('tipus', $data) ? (int)$data['tipus'] : null;

  // 🔎 Validacions bàsiques
  if ($web !== null && $web !== '' && !filter_var($web, FILTER_VALIDATE_URL)) {
    $errors[] = ValidacioErrors::format('web', 'url');
  }
  if ($subTemaIdTxt !== null && $subTemaIdTxt !== '') {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i', $subTemaIdTxt)) {
      $errors[] = ValidacioErrors::format('sub_tema_id', 'uuid');
    }
  }
  if ($lang !== null && !is_int($lang)) {
    $errors[] = ValidacioErrors::format('lang', 'int');
  }
  if ($tipus !== null && !is_int($tipus)) {
    $errors[] = ValidacioErrors::format('tipus', 'int');
  }

  // (Opcional) límites de longitud
  $maxNom = 5000;
  $maxWeb = 1000;
  if ($nom !== null && $nom !== '' && mb_strlen($nom) > $maxNom) {
    $errors[] = ValidacioErrors::massaLlarg('nom', $maxNom);
  }
  if ($web !== null && $web !== '' && mb_strlen($web) > $maxWeb) {
    $errors[] = ValidacioErrors::massaLlarg('web', $maxWeb);
  }

  // Debe haber al menos 1 campo a actualizar
  $camposAActualizar = array_filter([
    'nom'          => $nom,
    'web'          => $web,
    'sub_tema_id'  => $subTemaIdTxt,
    'lang'         => $lang,
    'tipus'        => $tipus,
  ], static fn($v) => $v !== null);

  if (empty($camposAActualizar)) {
    $errors[] = ValidacioErrors::requerit('almenys_un_camp');
  }

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }

  try {
    // Existe el registro?
    $exists = $conn->prepare("SELECT 1 FROM db_links WHERE id = uuid_text_to_bin(:id) LIMIT 1");
    $exists->bindValue(':id', $idText, PDO::PARAM_STR);
    $exists->execute();
    if (!$exists->fetchColumn()) {
      Response::error(MissatgesAPI::error('noTrobat'), [ValidacioErrors::noExisteix('id')], 404);
    }

    // Si se cambia el sub_tema_id, comprobar que exista
    if (array_key_exists('sub_tema_id', $camposAActualizar) && $subTemaIdTxt !== null && $subTemaIdTxt !== '') {
      $checkSub = $conn->prepare("SELECT 1 FROM aux_sub_temes WHERE id = uuid_text_to_bin(:stid) LIMIT 1");
      $checkSub->bindValue(':stid', $subTemaIdTxt, PDO::PARAM_STR);
      $checkSub->execute();
      if (!$checkSub->fetchColumn()) {
        Response::error(MissatgesAPI::error('validacio'), [ValidacioErrors::noExisteix('sub_tema_id')], 404);
      }
    }

    // Construcción dinámica del UPDATE
    $sets = ["dateModified = CURDATE()"]; // siempre actualizamos fecha mod
    $params = [':id' => [$idText, PDO::PARAM_STR]];

    if (array_key_exists('nom', $camposAActualizar)) {
      $sets[] = "nom = :nom";
      $params[':nom'] = [$nom === '' ? null : $nom, $nom === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('web', $camposAActualizar)) {
      $sets[] = "web = :web";
      $params[':web'] = [$web === '' ? null : $web, $web === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('sub_tema_id', $camposAActualizar)) {
      if ($subTemaIdTxt !== '' && $subTemaIdTxt !== null) {
        $sets[] = "sub_tema_id = uuid_text_to_bin(:sub_tema_id)";
        $params[':sub_tema_id'] = [$subTemaIdTxt, PDO::PARAM_STR];
      }
    }
    if (array_key_exists('lang', $camposAActualizar)) {
      $sets[] = "lang = :lang";
      $params[':lang'] = [$lang, PDO::PARAM_INT];
    }
    if (array_key_exists('tipus', $camposAActualizar)) {
      $sets[] = "tipus = :tipus";
      $params[':tipus'] = [$tipus, PDO::PARAM_INT];
    }

    $sql = "UPDATE db_links SET " . implode(', ', $sets) . " WHERE id = uuid_text_to_bin(:id)";
    $stmt = $conn->prepare($sql);

    foreach ($params as $k => [$val, $type]) {
      $stmt->bindValue($k, $val, $type);
    }

    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      // Sin cambios (mismos valores)
      Response::success(MissatgesAPI::success('noCanvis'), ['id' => $idText], 200);
    }

    // 📝 Audit
    Audit::registrarCanvi(
      $conn,
      $userUuid,
      'UPDATE',
      "Actualització link ($idText)",
      Tables::DB_LINKS,
      $idText // adapta si Audit espera binari
    );

    Response::success(
      MissatgesAPI::success('update'),
      [
        'id' => $idText,
        'updated_fields' => array_keys($camposAActualizar),
      ],
      200
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
} else if ($slug === 'subtema') {

  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // 🔑 id: requerit + format UUID
  if (empty($data['id'])) {
    $errors[] = ValidacioErrors::requerit('id');
  } else {
    $idText = (string)$data['id'];
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i', $idText)) {
      $errors[] = ValidacioErrors::format('id', 'uuid');
    }
  }

  // Campos opcionales a actualizar
  $temaIdText = isset($data['tema_id']) ? trim((string)$data['tema_id']) : null;
  $sub_ca     = array_key_exists('sub_tema_ca', $data) ? trim((string)$data['sub_tema_ca']) : null;
  $sub_en     = array_key_exists('sub_tema_en', $data) ? trim((string)$data['sub_tema_en']) : null;
  $sub_es     = array_key_exists('sub_tema_es', $data) ? trim((string)$data['sub_tema_es']) : null;
  $sub_it     = array_key_exists('sub_tema_it', $data) ? trim((string)$data['sub_tema_it']) : null;
  $sub_fr     = array_key_exists('sub_tema_fr', $data) ? trim((string)$data['sub_tema_fr']) : null;

  // Si se envía tema_id, validar formato UUID
  if ($temaIdText !== null && $temaIdText !== '') {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i', $temaIdText)) {
      $errors[] = ValidacioErrors::format('tema_id', 'uuid');
    }
  }

  // (Opcional) límites de longitud
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

  // Debe haber al menos 1 campo a actualizar
  $camposAActualizar = array_filter([
    'tema_id'      => $temaIdText,
    'sub_tema_ca'  => $sub_ca,
    'sub_tema_en'  => $sub_en,
    'sub_tema_es'  => $sub_es,
    'sub_tema_it'  => $sub_it,
    'sub_tema_fr'  => $sub_fr,
  ], static fn($v) => $v !== null); // si no viene la clave, no se actualiza

  if (empty($camposAActualizar)) {
    $errors[] = ValidacioErrors::requerit('almenys_un_camp');
  }

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
  }

  try {
    // Verificar que el registro a actualizar existe
    $exists = $conn->prepare("SELECT 1 FROM aux_sub_temes WHERE id = uuid_text_to_bin(:id) LIMIT 1");
    $exists->bindValue(':id', $idText, PDO::PARAM_STR);
    $exists->execute();
    if (!$exists->fetchColumn()) {
      Response::error(MissatgesAPI::error('noTrobat'), [ValidacioErrors::noExisteix('id')], 404);
    }

    // Si se va a cambiar tema_id, comprobar que el tema existe
    if (array_key_exists('tema_id', $camposAActualizar) && $temaIdText !== null && $temaIdText !== '') {
      $checkTema = $conn->prepare("SELECT 1 FROM aux_temes WHERE id = uuid_text_to_bin(:tema_id) LIMIT 1");
      $checkTema->bindValue(':tema_id', $temaIdText, PDO::PARAM_STR);
      $checkTema->execute();
      if (!$checkTema->fetchColumn()) {
        Response::error(MissatgesAPI::error('validacio'), [ValidacioErrors::noExisteix('tema_id')], 404);
      }
    }

    // Construcción dinámica del UPDATE
    $sets = [];
    $params = [':id' => [$idText, PDO::PARAM_STR]];

    if (array_key_exists('tema_id', $camposAActualizar)) {
      // tema_id puede venir como cadena vacía para "no cambiar"? aquí solo lo actualizamos si no es null
      if ($temaIdText !== null && $temaIdText !== '') {
        $sets[] = "tema_id = uuid_text_to_bin(:tema_id)";
        $params[':tema_id'] = [$temaIdText, PDO::PARAM_STR];
      }
    }
    if (array_key_exists('sub_tema_ca', $camposAActualizar)) {
      $sets[] = "sub_tema_ca = :sub_tema_ca";
      $params[':sub_tema_ca'] = [$sub_ca === '' ? null : $sub_ca, $sub_ca === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('sub_tema_en', $camposAActualizar)) {
      $sets[] = "sub_tema_en = :sub_tema_en";
      $params[':sub_tema_en'] = [$sub_en === '' ? null : $sub_en, $sub_en === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('sub_tema_es', $camposAActualizar)) {
      $sets[] = "sub_tema_es = :sub_tema_es";
      $params[':sub_tema_es'] = [$sub_es === '' ? null : $sub_es, $sub_es === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('sub_tema_it', $camposAActualizar)) {
      $sets[] = "sub_tema_it = :sub_tema_it";
      $params[':sub_tema_it'] = [$sub_it === '' ? null : $sub_it, $sub_it === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('sub_tema_fr', $camposAActualizar)) {
      $sets[] = "sub_tema_fr = :sub_tema_fr";
      $params[':sub_tema_fr'] = [$sub_fr === '' ? null : $sub_fr, $sub_fr === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }

    if (empty($sets)) {
      // Nada que actualizar (p.e., tema_id venía vacío y no se tocaron otros campos)
      Response::success(MissatgesAPI::success('noCanvis'), ['id' => $idText], 200);
    }

    $sql = "UPDATE aux_sub_temes SET " . implode(", ", $sets) . " WHERE id = uuid_text_to_bin(:id)";
    $stmt = $conn->prepare($sql);

    foreach ($params as $k => [$val, $type]) {
      $stmt->bindValue($k, $val, $type);
    }

    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      // El registro existe pero los valores son idénticos → sin cambios
      Response::success(MissatgesAPI::success('noCanvis'), ['id' => $idText], 200);
    }

    // 📝 Audit
    Audit::registrarCanvi(
      $conn,
      $userUuid,
      'UPDATE',
      "Actualització sub-tema ($idText)",
      Tables::DB_SUBTEMES,
      $idText // si Audit espera binari, adapta-ho
    );

    Response::success(
      MissatgesAPI::success('update'),
      [
        'id' => $idText,
        'updated_fields' => array_keys($camposAActualizar),
      ],
      200
    );
  } catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
      Response::error(MissatgesAPI::error('duplicat'), ['Registre duplicat'], 409);
    }
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
} else {
  // response output - data error
  $response['status'] = 'error';

  echo json_encode($response);
}
