<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Database;
use App\Utils\ValidacioErrors;
use App\Utils\Uuid;
use Ramsey\Uuid\Uuid as Ramsey;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

if ($slug === 'link') {
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  $uuid = Ramsey::uuid7();
  $uuidBytes = $uuid->getBytes();
  $idText = $uuid->toString(); // <-- FIX: faltaba definir esto

  $nom          = isset($data['nom']) ? trim((string)$data['nom']) : null;
  $web          = isset($data['web']) ? trim((string)$data['web']) : null;
  $subTemaIdTxt = isset($data['sub_tema_id']) ? trim((string)$data['sub_tema_id']) : null;
  $idiomaIdTxt  = isset($data['idioma_id']) ? trim((string)$data['idioma_id']) : null;
  $tipus        = isset($data['tipus']) ? (int)$data['tipus'] : null;

  $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i';

  if (empty($subTemaIdTxt)) {
    $errors[] = ValidacioErrors::requerit('sub_tema_id');
  } elseif (!preg_match($uuidRegex, $subTemaIdTxt)) {
    $errors[] = ValidacioErrors::format('sub_tema_id');
  }

  // FIX: validar también el formato de idioma_id, no solo que no sea null
  if (empty($idiomaIdTxt)) {
    $errors[] = ValidacioErrors::requerit('idioma');
  } elseif (!preg_match($uuidRegex, $idiomaIdTxt)) {
    $errors[] = ValidacioErrors::format('idioma');
  }

  if ($tipus === null) {
    $errors[] = ValidacioErrors::requerit('tipus');
  }

  if (empty($web)) {
    $errors[] = ValidacioErrors::requerit('web');
  } elseif (!filter_var($web, FILTER_VALIDATE_URL)) {
    $errors[] = ValidacioErrors::format('web');
  }

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
    exit(); // FIX: asegurar que no continúa ejecutando tras el error
  }

  // Solo convertir a binario cuando ya sabemos que el formato es válido
  $subTema_id_bin = Uuid::toBinary($subTemaIdTxt);
  $idioma_id_bin  = Uuid::toBinary($idiomaIdTxt);

  try {
    $check = $pdo->prepare("SELECT 1 FROM aux_sub_temes WHERE id = :sub_tema_id LIMIT 1");
    $check->bindValue(':sub_tema_id', $subTema_id_bin, PDO::PARAM_LOB); // FIX: comparar binario con binario, no el texto
    $check->execute();
    if (!$check->fetchColumn()) {
      Response::error(
        MissatgesAPI::error('validacio'),
        [ValidacioErrors::noExisteix('sub_tema_id')],
        404
      );
      exit(); // FIX
    }

    $sql = "INSERT INTO db_links (
                id, nom, web, sub_tema_id, idioma_id, tipus, dateCreated, dateModified
            ) VALUES (
                :id, :nom, :web, :sub_tema_id, :idioma_id, :tipus, CURDATE(), CURDATE()
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
    $stmt->bindValue(':nom', ($nom === '' ? null : $nom), $nom === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':web', $web, PDO::PARAM_STR);
    $stmt->bindValue(':sub_tema_id', $subTema_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':idioma_id', $idioma_id_bin, PDO::PARAM_LOB);
    $stmt->bindValue(':tipus', $tipus, PDO::PARAM_INT);

    $stmt->execute();

    Response::success(
      MissatgesAPI::success('create'),
      [
        'id'           => $idText,
        'nom'          => $nom,
        'web'          => $web,
        'sub_tema_id'  => $subTemaIdTxt,
        'idioma_id'    => $idiomaIdTxt,
        'tipus'        => $tipus,
        'dateCreated'  => date('Y-m-d'),
        'dateModified' => date('Y-m-d'),
      ],
      httpCode: 201
    );
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

  // ✅ Generar ID (UUIDv7) en el servidor
  try {
    $idText = Ramsey::uuid7()->toString(); // FIX: alias explícito para evitar colisión de nombres
  } catch (Throwable $t) {
    Response::error(MissatgesAPI::error('errorServidor'), ['No s\'ha pogut generar l\'UUID'], 500);
    exit(); // FIX: sin esto, $idText queda indefinida y el código sigue
  }

  // 📥 Campos
  $tema = isset($data['tema']) ? trim((string)$data['tema']) : null;

  // 📥 NUEVO: ordre (int, puede llegar como string desde el formulario)
  $ordre = 0; // valor por defecto
  if (isset($data['ordre']) && $data['ordre'] !== '' && $data['ordre'] !== null) {
    $ordreRaw = $data['ordre'];
    if (!is_numeric($ordreRaw) || (int)$ordreRaw != $ordreRaw) {
      // rechaza no numéricos y decimales (p. ej. "3.5"); si quieres permitir decimales, quita la 2ª condición
      $errors[] = ValidacioErrors::format('ordre');
    } else {
      $ordre = (int)$ordreRaw;
    }
  }

  // 🔎 Validació: tema requerit
  if ($tema === null || $tema === '') {
    $errors[] = ValidacioErrors::requerit('tema');
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
    exit(); // FIX
  }

  try {
    $sql = "INSERT INTO aux_temes (
                id, tema, ordre
            ) VALUES (
                :id, :tema, :ordre
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':id', $idText, PDO::PARAM_LOB);
    $stmt->bindValue(':tema', ($tema === '' ? null : $tema), $tema === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':ordre', $ordre, PDO::PARAM_INT); // FIX: nuevo campo

    $stmt->execute();

    Response::success(
      MissatgesAPI::success('create'),
      [
        'id' => $idText,
        'tema' => $tema,
        'ordre' => $ordre, // FIX: devolver también el valor guardado
      ],
      httpCode: 201
    );
    exit(); // FIX
  } catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) { // Duplicate entry
      Response::error(
        MissatgesAPI::error('duplicat'),
        ['Ja existeix un registre amb aquest id.'],
        409
      );
      exit(); // FIX
    }
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    exit(); // FIX
  }
} else if ($slug === 'subtema') {
  // 📨 Entrada JSON
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true) ?: [];

  $errors = [];

  // ✅ Generar ID (UUIDv7) en el servidor
  try {
    $idText = Ramsey::uuid7()->toString(); // FIX: alias, evita colisión con App\Utils\Uuid
  } catch (Throwable $t) {
    Response::error(
      MissatgesAPI::error('errorServidor'),
      ['No s\'ha pogut generar l\'UUID'],
      500
    );
    exit(); // FIX: sin esto, $idText queda indefinida y el código sigue
  }

  // 🔎 Validació tema_id
  $temaIdText = isset($data['tema_id']) ? trim((string)$data['tema_id']) : null; // FIX: isset antes de castear
  $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f][0-9a-f]{3}-[0-9a-f][0-9a-f]{3}-[0-9a-f]{12}$/i';

  if (empty($temaIdText)) {
    $errors[] = ValidacioErrors::requerit('tema_id');
  } elseif (!preg_match($uuidRegex, $temaIdText)) {
    $errors[] = ValidacioErrors::format('tema_id'); // FIX: faltaba validar formato
  }

  // 🔎 Validació sub_tema
  $sub = isset($data['sub_tema'])
    ? trim((string)$data['sub_tema'])
    : null;

  if ($sub === null || $sub === '') {
    $errors[] = ValidacioErrors::requerit('sub_tema');
  }

  // Límit de longitud
  $maxLen = 5000;

  $checkLen = function (?string $val, string $field) use (&$errors, $maxLen) {
    if ($val !== null && $val !== '' && mb_strlen($val) > $maxLen) {
      $errors[] = ValidacioErrors::massaLlarg($field, $maxLen);
    }
  };

  $checkLen($sub, 'nom');

  if (!empty($errors)) {
    Response::error(
      MissatgesAPI::error('validacio'),
      $errors,
      400
    );
    exit(); // FIX
  }

  try {

    $temaIdBin = Uuid::toBinary($temaIdText); // FIX: clase correcta (Uuid, no UtilsUuid)
    $idBin = Uuid::toBinary($idText);

    // ✅ Comprovem que el tema pare existeix
    $check = $pdo->prepare(
      "SELECT tema FROM aux_temes WHERE id = :tema_id LIMIT 1"
    );

    $check->bindValue(
      ':tema_id',
      $temaIdBin,
      PDO::PARAM_LOB // FIX: binario, no STR
    );

    $check->execute();
    $tema = $check->fetchColumn();

    if ($tema === false) {
      Response::error(
        MissatgesAPI::error('validacio'),
        [ValidacioErrors::noExisteix('tema_id')],
        404
      );
      exit(); // FIX
    }

    // INSERT
    $sql = "INSERT INTO aux_sub_temes (
                    id,
                    tema_id,
                    sub_tema
                ) VALUES (
                    :id,
                    :tema_id,
                    :sub_tema
                )";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
      ':id',
      $idBin,
      PDO::PARAM_LOB // FIX: binario, no STR
    );

    $stmt->bindValue(
      ':tema_id',
      $temaIdBin,
      PDO::PARAM_LOB // FIX: binario, no STR
    );

    $stmt->bindValue(
      ':sub_tema',
      $sub,
      PDO::PARAM_STR
    );

    $stmt->execute();

    Response::success(
      MissatgesAPI::success('create'),
      [
        'id' => $idText,
        'tema_id' => $temaIdText,
        'sub_tema' => $sub,
        'tema' => $tema,
      ],
      httpCode: 201
    );
    exit(); // FIX
  } catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
      Response::error(
        MissatgesAPI::error('duplicat'),
        ['Registre duplicat'],
        409
      );
      exit(); // FIX
    }

    Response::error(
      MissatgesAPI::error('errorBD'),
      [$e->getMessage()],
      500
    );
    exit(); // FIX
  }
} else {
  // response output - data error
  $response['status'] = 'error';

  echo json_encode($response);
}
