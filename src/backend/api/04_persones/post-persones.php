<?php

use Ramsey\Uuid\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error(MissatgesAPI::error('method_not_allowed'), [], 405);
    exit;
}

function isUuid($s)
{
    return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
}

function requireField(array $data, string $key, array &$errors)
{
    if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
        $errors[$key] = 'required';
        return null;
    }
    return data_input($data[$key]);
}

function optionalField(array $data, string $key)
{
    return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
        ? data_input($data[$key])
        : null;
}

function optionalIntField(array $data, string $key, array &$errors)
{
    if (!array_key_exists($key, $data) || $data[$key] === '' || $data[$key] === null) return null;
    if (!is_numeric($data[$key])) {
        $errors[$key] = 'invalid_int';
        return null;
    }
    return (int)$data[$key];
}

/**
 * Inserta relaciones persona->grup
 * @param PDO $conn
 * @param string $personaIdBin (BINARY(16) bytes)
 * @param array $grupIds (uuid strings)
 */
function insertPersonGroups(PDO $conn, string $personaIdBin, array $grupIds): void
{
    if (empty($grupIds)) return;

    $sql = "
    INSERT INTO " . Tables::PERSONES_GRUPS_RELACIONS . " (id, persona_id, grup_id)
    VALUES (
      :id,
      :persona_id,
      UNHEX(REPLACE(:grup_id, '-', ''))
    )
  ";
    $stmt = $conn->prepare($sql);

    foreach ($grupIds as $gid) {
        $gid = trim((string)$gid);
        if ($gid === '') continue;

        if (!isUuid($gid)) {
            // si te interesa abortar, lanza excepción
            throw new \RuntimeException("invalid_uuid grup_id: " . $gid);
        }

        $relUuid = Uuid::uuid7();
        $stmt->bindValue(':id', $relUuid->getBytes(), PDO::PARAM_LOB);
        $stmt->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);
        $stmt->bindValue(':grup_id', $gid, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new \RuntimeException("db_error insert relacions");
        }
    }
}


// -------------------------
// POST crear persona/autor
// ruta: ?persona
// -------------------------
if (isset($_GET['persona'])) {

    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
    }

    $errors = [];

    // Requeridos (según tu caso: ajusta si alguno debe ser opcional)
    $nom     = requireField($data, 'nom', $errors);
    $slug    = requireField($data, 'slug', $errors);

    // En tu tabla cognoms puede ser NULL -> lo dejo opcional
    $cognoms = optionalField($data, 'cognoms');

    // Opcionales simples
    $web       = optionalField($data, 'web');
    $descripcio = optionalField($data, 'descripcio');

    // Opcionales int
    $sexe_id          = optionalIntField($data, 'sexe_id', $errors);
    $any_naixement    = optionalIntField($data, 'any_naixement', $errors);
    $mes_naixement    = optionalIntField($data, 'mes_naixement', $errors);
    $dia_naixement    = optionalIntField($data, 'dia_naixement', $errors);
    $any_defuncio     = optionalIntField($data, 'any_defuncio', $errors);
    $mes_defuncio     = optionalIntField($data, 'mes_defuncio', $errors);
    $dia_defuncio     = optionalIntField($data, 'dia_defuncio', $errors);
    $img_id           = optionalIntField($data, 'img_id', $errors);

    // UUIDs (string) opcionales -> se convierten con UNHEX(REPLACE()) en SQL
    $pais_autor_id        = optionalField($data, 'pais_autor_id');
    $ciutat_naixement_id  = optionalField($data, 'ciutat_naixement_id');
    $ciutat_defuncio_id   = optionalField($data, 'ciutat_defuncio_id');

    if ($pais_autor_id !== null && !isUuid($pais_autor_id)) $errors['pais_autor_id'] = 'invalid_uuid';
    if ($ciutat_naixement_id !== null && !isUuid($ciutat_naixement_id)) $errors['ciutat_naixement_id'] = 'invalid_uuid';
    if ($ciutat_defuncio_id !== null && !isUuid($ciutat_defuncio_id)) $errors['ciutat_defuncio_id'] = 'invalid_uuid';

    // Grupos: array de UUIDs
    // Acepta 'grup_ids' (nuevo) o 'grups' (legacy / form)

    // Si viene como string CSV, conviértelo
    // --- Normalizar grupos desde JSON (acepta varias claves) ---
    $grup_ids = [];

    // 1) formato correcto: grup_ids: ["uuid", ...]
    if (isset($data['grup_ids']) && is_array($data['grup_ids'])) {
        $grup_ids = $data['grup_ids'];
    }
    // 2) legacy: grups: ["uuid", ...]
    else if (isset($data['grups']) && is_array($data['grups'])) {
        $grup_ids = $data['grups'];
    }
    // 3) caso que estás enviando ahora: "grup_ids[]" : "uuid" (string)
    else if (isset($data['grup_ids[]'])) {
        $v = $data['grup_ids[]'];
        $grup_ids = is_array($v) ? $v : [$v];
    }

    // Normaliza: strings, trim, quita vacíos, quita duplicados
    $grup_ids = array_values(array_unique(array_filter(array_map(
        fn($x) => trim((string)$x),
        $grup_ids
    ), fn($x) => $x !== '')));

    foreach ($grup_ids as $i => $gid) {
        if (!isUuid($gid)) $errors["grup_ids.$i"] = 'invalid_uuid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    // UUIDv7 persona
    $uuid = Uuid::uuid7();
    $uuidBytes  = $uuid->getBytes();   // BINARY(16)
    $uuidString = $uuid->toString();   // para devolver al FE si quieres

    $created_at = date('Y-m-d H:i:s.u'); // datetime(6) (si tu DB no acepta micro, usa date('Y-m-d H:i:s'))
    $updated_at = $created_at;

    global $conn; // PDO

    try {
        $conn->beginTransaction();

        // (Opcional) asegurar slug único
        $qChk = "SELECT 1 FROM " . Tables::PERSONES . " WHERE slug = :slug LIMIT 1";
        $stChk = $conn->prepare($qChk);
        $stChk->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stChk->execute();
        if ($stChk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('invalid_data'), ['slug' => 'already_exists'], 409);
            exit;
        }

        // Insert persona
        $sql = "
      INSERT INTO " . Tables::PERSONES . " (
        id, nom, cognoms, slug,
        sexe_id,
        any_naixement, mes_naixement, dia_naixement,
        any_defuncio, mes_defuncio, dia_defuncio,
        pais_autor_id, img_id,
        ciutat_naixement_id, ciutat_defuncio_id,
        web, descripcio,
        created_at, updated_at
      ) VALUES (
        :id, :nom, :cognoms, :slug,
        :sexe_id,
        :any_naixement, :mes_naixement, :dia_naixement,
        :any_defuncio, :mes_defuncio, :dia_defuncio,
        " . ($pais_autor_id !== null ? "UNHEX(REPLACE(:pais_autor_id, '-', ''))" : "NULL") . ",
        :img_id,
        " . ($ciutat_naixement_id !== null ? "UNHEX(REPLACE(:ciutat_naixement_id, '-', ''))" : "NULL") . ",
        " . ($ciutat_defuncio_id !== null ? "UNHEX(REPLACE(:ciutat_defuncio_id, '-', ''))" : "NULL") . ",
        :web, :descripcio,
        :created_at, :updated_at
      )
    ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);

        if ($cognoms === null) $stmt->bindValue(':cognoms', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':cognoms', $cognoms, PDO::PARAM_STR);

        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

        if ($sexe_id === null) $stmt->bindValue(':sexe_id', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':sexe_id', (int)$sexe_id, PDO::PARAM_INT);

        foreach (
            [
                'any_naixement' => $any_naixement,
                'mes_naixement' => $mes_naixement,
                'dia_naixement' => $dia_naixement,
                'any_defuncio'  => $any_defuncio,
                'mes_defuncio'  => $mes_defuncio,
                'dia_defuncio'  => $dia_defuncio,
            ] as $k => $v
        ) {
            if ($v === null) $stmt->bindValue(":$k", null, PDO::PARAM_NULL);
            else $stmt->bindValue(":$k", (int)$v, PDO::PARAM_INT);
        }

        if ($pais_autor_id !== null) $stmt->bindValue(':pais_autor_id', $pais_autor_id, PDO::PARAM_STR);

        if ($img_id === null) $stmt->bindValue(':img_id', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':img_id', (int)$img_id, PDO::PARAM_INT);

        if ($ciutat_naixement_id !== null) $stmt->bindValue(':ciutat_naixement_id', $ciutat_naixement_id, PDO::PARAM_STR);
        if ($ciutat_defuncio_id !== null) $stmt->bindValue(':ciutat_defuncio_id', $ciutat_defuncio_id, PDO::PARAM_STR);

        if ($web === null) $stmt->bindValue(':web', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':web', $web, PDO::PARAM_STR);

        if ($descripcio === null) $stmt->bindValue(':descripcio', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':descripcio', $descripcio, PDO::PARAM_STR);

        $stmt->bindValue(':created_at', $created_at, PDO::PARAM_STR);
        $stmt->bindValue(':updated_at', $updated_at, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('db_error'), [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ], 500);
            exit;
        }

        // Insert relaciones grups
        if (!empty($grup_ids)) {
            insertPersonGroups($conn, $uuidBytes, $grup_ids);
        }

        $conn->commit();

        Response::success(
            MissatgesAPI::success('create'),
            [
                'id' => $uuidString,
                'slug' => $slug,
            ],
            201
        );
        exit;
    } catch (\Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }
} else if (isset($_GET['grupPersona'])) {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
    }

    $errors = [];

    // Requeridos (NOT NULL en la tabla)
    $grup_ca = requireField($data, 'grup_ca', $errors);
    $grup_es = requireField($data, 'grup_es', $errors);
    $grup_en = requireField($data, 'grup_en', $errors);
    $grup_it = requireField($data, 'grup_it', $errors);
    $grup_fr = requireField($data, 'grup_fr', $errors);

    // Normaliza (trim) y valida vacío
    $grup_ca = is_string($grup_ca) ? trim($grup_ca) : $grup_ca;
    $grup_es = is_string($grup_es) ? trim($grup_es) : $grup_es;
    $grup_en = is_string($grup_en) ? trim($grup_en) : $grup_en;
    $grup_it = is_string($grup_it) ? trim($grup_it) : $grup_it;
    $grup_fr = is_string($grup_fr) ? trim($grup_fr) : $grup_fr;

    if ($grup_ca === '') $errors['grup_ca'] = 'required';
    if ($grup_es === '') $errors['grup_es'] = 'required';
    if ($grup_en === '') $errors['grup_en'] = 'required';
    if ($grup_it === '') $errors['grup_it'] = 'required';
    if ($grup_fr === '') $errors['grup_fr'] = 'required';

    // (Opcional) límite lógico FE (tu input tiene maxlength=150)
    // Aquí solo lo aplico si quieres evitar strings enormes en TEXT:
    $maxLen = 150;
    if (is_string($grup_ca) && mb_strlen($grup_ca) > $maxLen) $errors['grup_ca'] = 'too_long';
    if (is_string($grup_es) && mb_strlen($grup_es) > $maxLen) $errors['grup_es'] = 'too_long';
    if (is_string($grup_en) && mb_strlen($grup_en) > $maxLen) $errors['grup_en'] = 'too_long';
    if (is_string($grup_it) && mb_strlen($grup_it) > $maxLen) $errors['grup_it'] = 'too_long';
    if (is_string($grup_fr) && mb_strlen($grup_fr) > $maxLen) $errors['grup_fr'] = 'too_long';

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    // UUIDv7
    $uuid = Uuid::uuid7();
    $uuidBytes  = $uuid->getBytes();   // BINARY(16)
    $uuidString = $uuid->toString();   // devolver al FE

    global $conn; // PDO

    try {
        $conn->beginTransaction();

        // (Opcional) evitar duplicados exactos por nombre CA (si te interesa)
        // Si NO quieres esta regla, borra este bloque.
        $qChk = "SELECT 1 FROM db_persones_grups WHERE grup_ca = :grup_ca LIMIT 1";
        $stChk = $conn->prepare($qChk);
        $stChk->bindValue(':grup_ca', $grup_ca, PDO::PARAM_STR);
        $stChk->execute();
        if ($stChk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('invalid_data'), ['grup_ca' => 'already_exists'], 409);
            exit;
        }

        // Insert
        $sql = "
            INSERT INTO db_persones_grups (
                id,
                grup_ca, grup_es, grup_en, grup_it, grup_fr
            ) VALUES (
                :id,
                :grup_ca, :grup_es, :grup_en, :grup_it, :grup_fr
            )
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':grup_ca', $grup_ca, PDO::PARAM_STR);
        $stmt->bindValue(':grup_es', $grup_es, PDO::PARAM_STR);
        $stmt->bindValue(':grup_en', $grup_en, PDO::PARAM_STR);
        $stmt->bindValue(':grup_it', $grup_it, PDO::PARAM_STR);
        $stmt->bindValue(':grup_fr', $grup_fr, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('db_error'), [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ], 500);
            exit;
        }

        $conn->commit();

        Response::success(
            MissatgesAPI::success('create'),
            [
                'id' => $uuidString,
            ],
            201
        );
        exit;
    } catch (\Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }
}

Response::error(MissatgesAPI::error('bad_request'), ['route' => 'invalid'], 400);
exit;
