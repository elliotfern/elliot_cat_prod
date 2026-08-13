<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Database;
use App\Utils\ValidacioErrors;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

if ($slug === 'link') {
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];
  $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i';

  // 🔑 id: requerit + format UUID
  $idText = null;
  if (empty($data['id'])) {
    $errors[] = ValidacioErrors::requerit('id');
  } else {
    $idText = (string)$data['id'];
    if (!preg_match($uuidRegex, $idText)) {
      $errors[] = ValidacioErrors::format('id');
    }
  }

  // 📥 Campos opcionales (si no vienen, no se actualizan)
  $nom          = array_key_exists('nom', $data) ? trim((string)$data['nom']) : null;
  $web          = array_key_exists('web', $data) ? trim((string)$data['web']) : null;
  $subTemaIdTxt = array_key_exists('sub_tema_id', $data) ? trim((string)$data['sub_tema_id']) : null;
  // FIX: idioma_id tratado como UUID (texto), no como int, para ser consistente con el POST
  $idiomaIdTxt  = array_key_exists('idioma_id', $data) ? trim((string)$data['idioma_id']) : null;
  $tipusRaw     = array_key_exists('tipus', $data) ? $data['tipus'] : null;

  // 🔎 Validacions bàsiques
  if ($web !== null && $web !== '' && !filter_var($web, FILTER_VALIDATE_URL)) {
    $errors[] = ValidacioErrors::format('web');
  }
  if ($subTemaIdTxt !== null && $subTemaIdTxt !== '' && !preg_match($uuidRegex, $subTemaIdTxt)) {
    $errors[] = ValidacioErrors::format('sub_tema_id');
  }
  // FIX: validar formato UUID antes de aceptar idioma_id
  if ($idiomaIdTxt !== null && $idiomaIdTxt !== '' && !preg_match($uuidRegex, $idiomaIdTxt)) {
    $errors[] = ValidacioErrors::format('idioma_id');
  }
  // FIX: validar tipus ANTES de castear, para no enmascarar errores
  $tipus = null;
  if ($tipusRaw !== null) {
    if (!is_numeric($tipusRaw)) {
      $errors[] = ValidacioErrors::format('tipus');
    } else {
      $tipus = (int)$tipusRaw;
    }
  }

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
    'nom'         => $nom,
    'web'         => $web,
    'sub_tema_id' => $subTemaIdTxt,
    'idioma_id'   => $idiomaIdTxt,
    'tipus'       => $tipus,
  ], static fn($v) => $v !== null);

  if (empty($camposAActualizar)) {
    $errors[] = ValidacioErrors::requerit('almenys_un_camp');
  }

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    exit(); // FIX
  }

  // FIX: conversión a binario para todos los UUID, hecha una sola vez aquí
  $id_bin = Uuid::toBinary($idText);
  $subTema_id_bin = ($subTemaIdTxt !== null && $subTemaIdTxt !== '') ? Uuid::toBinary($subTemaIdTxt) : null;
  $idioma_id_bin  = ($idiomaIdTxt !== null && $idiomaIdTxt !== '') ? Uuid::toBinary($idiomaIdTxt) : null;

  try {
    // Existe el registro?
    $exists = $pdo->prepare("SELECT 1 FROM db_links WHERE id = :id LIMIT 1");
    $exists->bindValue(':id', $id_bin, PDO::PARAM_LOB); // FIX: binario, no texto
    $exists->execute();
    if (!$exists->fetchColumn()) {
      Response::error(MissatgesAPI::error('noTrobat'), [ValidacioErrors::noExisteix('id')], 404);
      exit(); // FIX
    }

    // Si se cambia el sub_tema_id, comprobar que exista
    if (array_key_exists('sub_tema_id', $camposAActualizar)) {
      $checkSub = $pdo->prepare("SELECT 1 FROM aux_sub_temes WHERE id = :stid LIMIT 1");
      $checkSub->bindValue(':stid', $subTema_id_bin, PDO::PARAM_LOB); // FIX: binario
      $checkSub->execute();
      if (!$checkSub->fetchColumn()) {
        Response::error(MissatgesAPI::error('validacio'), [ValidacioErrors::noExisteix('sub_tema_id')], 404);
        exit(); // FIX
      }
    }

    // FIX: comprobar también que idioma_id exista, igual que sub_tema_id
    if (array_key_exists('idioma_id', $camposAActualizar)) {
      $checkIdioma = $pdo->prepare("SELECT 1 FROM aux_idiomes WHERE id = :iid LIMIT 1");
      $checkIdioma->bindValue(':iid', $idioma_id_bin, PDO::PARAM_LOB);
      $checkIdioma->execute();
      if (!$checkIdioma->fetchColumn()) {
        Response::error(MissatgesAPI::error('validacio'), [ValidacioErrors::noExisteix('idioma_id')], 404);
        exit(); // FIX
      }
    }

    // Construcción dinámica del UPDATE
    $sets = ["dateModified = CURDATE()"];
    $params = [':id' => [$id_bin, PDO::PARAM_LOB]]; // FIX: binario

    if (array_key_exists('nom', $camposAActualizar)) {
      $sets[] = "nom = :nom";
      $params[':nom'] = [$nom === '' ? null : $nom, $nom === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('web', $camposAActualizar)) {
      $sets[] = "web = :web";
      $params[':web'] = [$web === '' ? null : $web, $web === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }
    if (array_key_exists('sub_tema_id', $camposAActualizar)) {
      $sets[] = "sub_tema_id = :sub_tema_id";
      $params[':sub_tema_id'] = [$subTema_id_bin, PDO::PARAM_LOB]; // FIX: binario
    }
    if (array_key_exists('idioma_id', $camposAActualizar)) {
      $sets[] = "idioma_id = :idioma_id";
      $params[':idioma_id'] = [$idioma_id_bin, PDO::PARAM_LOB]; // FIX: binario, no int
    }
    if (array_key_exists('tipus', $camposAActualizar)) {
      $sets[] = "tipus = :tipus";
      $params[':tipus'] = [$tipus, PDO::PARAM_INT];
    }

    $sql = "UPDATE db_links SET " . implode(', ', $sets) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => [$val, $type]) {
      $stmt->bindValue($k, $val, $type);
    }

    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      Response::success(MissatgesAPI::success('noCanvis'), ['id' => $idText], httpCode: 200);
      exit(); // FIX: evita la doble respuesta
    }

    Response::success(
      MissatgesAPI::success('update'),
      [
        'id' => $idText,
        'updated_fields' => array_keys($camposAActualizar),
      ],
      httpCode: 200
    );
    exit(); // FIX
  } catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
      Response::error(MissatgesAPI::error('duplicat'), ['Registre duplicat'], 409);
      exit(); // FIX
    }
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    exit(); // FIX
  }
} else if ($slug === 'tema') {

  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // 🔎 Requerim id (UUIDv7 text)
  $idText = null;
  if (empty($data['id'])) {
    $errors[] = ValidacioErrors::requerit('id');
  } else {
    $idText = (string)$data['id'];
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i', $idText)) {
      $errors[] = ValidacioErrors::format('id'); // FIX: faltaba validar formato UUID
    }
  }

  // 📥 Campos opcionales (trim y null si vacío)
  $tema = array_key_exists('tema', $data) ? trim((string)$data['tema']) : null;

  // 📥 NUEVO: ordre (int, puede llegar como string)
  $ordre = null;
  $ordreProvided = array_key_exists('ordre', $data);
  if ($ordreProvided && $data['ordre'] !== '' && $data['ordre'] !== null) {
    $ordreRaw = $data['ordre'];
    if (!is_numeric($ordreRaw) || (int)$ordreRaw != $ordreRaw) {
      $errors[] = ValidacioErrors::format('ordre');
    } else {
      $ordre = (int)$ordreRaw;
    }
  }

  // ✅ Debe venir al menos un campo a actualizar
  $anyProvided = array_key_exists('tema', $data) || $ordreProvided;

  if (!$anyProvided) {
    $errors[] = ValidacioErrors::requerit('almenys_un_camp'); // FIX: mensaje coherente
  }

  // (Opcional) límites de longitud
  $maxLen = 5000;
  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };
  if (array_key_exists('tema', $data)) $checkLen($tema, 'tema');

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    exit(); // FIX
  }

  // FIX: conversión a binario, una sola vez
  $id_bin = Uuid::toBinary($idText);

  // 🛠️ Construcción dinámica del UPDATE
  $setParts = [];
  $params = [':id' => [$id_bin, PDO::PARAM_LOB]]; // FIX: binario

  $normalize = function ($val) {
    if ($val === null) return null;
    return ($val === '') ? null : $val;
  };

  if (array_key_exists('tema', $data)) {
    $setParts[] = 'tema = :tema';
    $normalizedTema = $normalize($tema);
    $params[':tema'] = [$normalizedTema, $normalizedTema === null ? PDO::PARAM_NULL : PDO::PARAM_STR];
  }

  if ($ordreProvided) {
    $setParts[] = 'ordre = :ordre';
    $params[':ordre'] = [$ordre, $ordre === null ? PDO::PARAM_NULL : PDO::PARAM_INT]; // FIX: nuevo campo
  }

  if (empty($setParts)) {
    Response::error(MissatgesAPI::error('validacio'), ['Cap camp a actualitzar'], 400);
    exit(); // FIX
  }

  try {
    $sql = "UPDATE aux_temes
          SET " . implode(', ', $setParts) . "
          WHERE id = :id
          LIMIT 1";

    $stmt = $pdo->prepare($sql);

    // FIX: bindeo unificado, respetando tipo por campo (antes todo iba como STR)
    foreach ($params as $k => [$val, $type]) {
      $stmt->bindValue($k, $val, $type);
    }

    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      // No existe el id o los valores son idénticos (idempotente). Distinguimos con un SELECT.
      $chk = $pdo->prepare("SELECT 1 FROM aux_temes WHERE id = :id LIMIT 1");
      $chk->bindValue(':id', $id_bin, PDO::PARAM_LOB); // FIX: binario
      $chk->execute();
      if (!$chk->fetchColumn()) {
        Response::error(MissatgesAPI::error('noTrobat'), ['No s\'ha trobat el registre'], 404);
        exit(); // FIX: evita caer también en el success de abajo
      }
      // Existe pero no cambió nada → devolvemos success idempotente, seguimos abajo
    }

    // 📤 Devolver eco de lo actualizado (solo campos enviados)
    // FIX: eliminado el bucle tema_ca/en/es/fr/it que no correspondía al modelo real
    $response = ['id' => $idText];
    if (array_key_exists('tema', $data)) {
      $response['tema'] = $tema === '' ? null : $tema;
    }
    if ($ordreProvided) {
      $response['ordre'] = $ordre;
    }

    Response::success(
      MissatgesAPI::success('update'),
      $response,
      httpCode: 200
    );
    exit(); // FIX
  } catch (PDOException $e) {
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    exit(); // FIX
  }
} else if ($slug === 'subtema') {
  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // 🔑 id: requerit + format UUID
  $idText = null; // FIX: inicializar para evitar variable indefinida
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
  $sub        = array_key_exists('sub_tema', $data) ? trim((string)$data['sub_tema']) : null;

  // Si se envía tema_id, validar formato UUID
  if ($temaIdText !== null && $temaIdText !== '') {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i', $temaIdText)) {
      $errors[] = ValidacioErrors::format('tema_id');
    }
  }

  // (Opcional) límites de longitud
  $maxLen = 5000;
  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };
  $checkLen($sub, 'sub_tema');

  // Debe haber al menos 1 campo a actualizar
  $camposAActualizar = array_filter([
    'tema_id'  => $temaIdText,
    'sub_tema' => $sub,
  ], static fn($v) => $v !== null);

  if (empty($camposAActualizar)) {
    $errors[] = ValidacioErrors::requerit('almenys_un_camp');
  }

  if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    exit(); // FIX
  }

  // FIX: conversión a binario, una sola vez
  $id_bin = Uuid::toBinary($idText);
  $temaId_bin = ($temaIdText !== null && $temaIdText !== '') ? Uuid::toBinary($temaIdText) : null;

  try {
    // Verificar que el registro a actualizar existe
    $exists = $pdo->prepare("SELECT 1 FROM aux_sub_temes WHERE id = :id LIMIT 1");
    $exists->bindValue(':id', $id_bin, PDO::PARAM_LOB); // FIX: binario
    $exists->execute();
    if (!$exists->fetchColumn()) {
      Response::error(MissatgesAPI::error('noTrobat'), [ValidacioErrors::noExisteix('id')], 404);
      exit(); // FIX
    }

    // Si se va a cambiar tema_id, comprobar que el tema existe
    if (array_key_exists('tema_id', $camposAActualizar) && $temaId_bin !== null) {
      $checkTema = $pdo->prepare("SELECT 1 FROM aux_temes WHERE id = :tema_id LIMIT 1");
      $checkTema->bindValue(':tema_id', $temaId_bin, PDO::PARAM_LOB); // FIX: binario
      $checkTema->execute();
      if (!$checkTema->fetchColumn()) {
        Response::error(MissatgesAPI::error('validacio'), [ValidacioErrors::noExisteix('tema_id')], 404);
        exit(); // FIX
      }
    }

    // Construcción dinámica del UPDATE
    $sets = [];
    $params = [':id' => [$id_bin, PDO::PARAM_LOB]]; // FIX: binario

    if (array_key_exists('tema_id', $camposAActualizar) && $temaId_bin !== null) {
      $sets[] = "tema_id = :tema_id";
      $params[':tema_id'] = [$temaId_bin, PDO::PARAM_LOB]; // FIX: binario
    }
    if (array_key_exists('sub_tema', $camposAActualizar)) {
      $sets[] = "sub_tema = :sub_tema";
      $params[':sub_tema'] = [$sub === '' ? null : $sub, $sub === '' ? PDO::PARAM_NULL : PDO::PARAM_STR];
    }

    if (empty($sets)) {
      // Nada que actualizar (p.e., tema_id venía vacío y no se tocaron otros campos)
      Response::success(MissatgesAPI::success('noCanvis'), ['id' => $idText], httpCode: 200);
      exit(); // FIX: evita llegar al UPDATE con SET vacío (error de sintaxis SQL)
    }

    $sql = "UPDATE aux_sub_temes SET " . implode(", ", $sets) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => [$val, $type]) {
      $stmt->bindValue($k, $val, $type);
    }

    $stmt->execute();

    if ($stmt->rowCount() === 0) {
      // El registro existe pero los valores son idénticos → sin cambios
      Response::success(MissatgesAPI::success('noCanvis'), ['id' => $idText], httpCode: 200);
      exit(); // FIX: evita caer también en el success de más abajo
    }

    Response::success(
      MissatgesAPI::success('update'),
      [
        'id' => $idText,
        'updated_fields' => array_keys($camposAActualizar),
      ],
      httpCode: 200
    );
    exit(); // FIX
  } catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
      Response::error(MissatgesAPI::error('duplicat'), ['Registre duplicat'], 409);
      exit(); // FIX
    }
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    exit(); // FIX
  }
} else {
  // response output - data error
  $response['status'] = 'error';

  echo json_encode($response);
}
