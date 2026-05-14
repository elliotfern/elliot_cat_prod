<?php

use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\ImageService;

global $conn;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

function isUuid($s)
{
    return is_string($s) && preg_match(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
        $s
    );
}

function optionalField(array $data, string $key)
{
    return (array_key_exists($key, $data) && $data[$key] !== '' && $data[$key] !== null)
        ? data_input($data[$key])
        : (array_key_exists($key, $data) ? null : '__MISSING__');
}

function optionalIntField(array $data, string $key, array &$errors)
{
    if (!array_key_exists($key, $data)) return '__MISSING__';
    if ($data[$key] === '' || $data[$key] === null) return null;
    if (!is_numeric($data[$key])) {
        $errors[$key] = 'invalid_int';
        return null;
    }
    return (int)$data[$key];
}

function deletePersonGroups(PDO $conn, string $personaIdBin): void
{
    $sql = "DELETE FROM " . Tables::PERSONES_GRUPS_RELACIONS . " WHERE persona_id = :persona_id";
    $st = $conn->prepare($sql);
    $st->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);

    if (!$st->execute()) {
        throw new \RuntimeException("db_error delete relacions");
    }
}

function insertPersonGroups(PDO $conn, string $personaIdBin, array $grupIds): void
{
    if (empty($grupIds)) return;

    $sql = "
        INSERT INTO " . Tables::PERSONES_GRUPS_RELACIONS . " (id, persona_id, grup_id)
        VALUES (
            :id,
            :persona_id,
            :grup_id
        )
    ";

    $stmt = $conn->prepare($sql);

    foreach ($grupIds as $gid) {

        $gid = trim((string)$gid);
        if ($gid === '') continue;

        if (!isUuid($gid)) {
            throw new \RuntimeException("invalid_uuid grup_id: " . $gid);
        }

        $relUuid = ramsey::uuid7();

        $stmt->bindValue(':id', $relUuid->getBytes(), PDO::PARAM_LOB);
        $stmt->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);
        $stmt->bindValue(':grup_id', Uuid::toBinary($gid), PDO::PARAM_LOB);

        if (!$stmt->execute()) {
            throw new \RuntimeException("db_error insert relacions");
        }
    }
}


// -------------------------
// PUT PERSONA
// -------------------------
if (isset($_GET['persona'])) {

    $id = $_GET['persona'] ?? '';

    if (!isUuid($id)) {
        Response::error(MissatgesAPI::error('invalid_data'), ['id' => 'invalid_uuid'], 400);
        exit;
    }

    $isMultipart = !empty($_FILES) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

    if ($isMultipart) {
        $data = $_POST;
    } else {
        $data = json_decode(file_get_contents("php://input"), true);
    }

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
    }

    $errors = [];

    // fields
    $nom = optionalField($data, 'nom');
    $cognoms = optionalField($data, 'cognoms');
    $slug = optionalField($data, 'slug');
    $web = optionalField($data, 'web');
    $descripcio = optionalField($data, 'descripcio');

    $sexe_id = optionalIntField($data, 'sexe_id', $errors);

    $any_naixement = optionalIntField($data, 'any_naixement', $errors);
    $mes_naixement = optionalIntField($data, 'mes_naixement', $errors);
    $dia_naixement = optionalIntField($data, 'dia_naixement', $errors);

    $any_defuncio = optionalIntField($data, 'any_defuncio', $errors);
    $mes_defuncio = optionalIntField($data, 'mes_defuncio', $errors);
    $dia_defuncio = optionalIntField($data, 'dia_defuncio', $errors);

    // UUID fields
    $pais_autor_id = optionalField($data, 'pais_autor_id');
    $ciutat_naixement_id = optionalField($data, 'ciutat_naixement_id');
    $ciutat_defuncio_id = optionalField($data, 'ciutat_defuncio_id');

    if ($pais_autor_id !== '__MISSING__' && $pais_autor_id !== null && !isUuid($pais_autor_id)) {
        $errors['pais_autor_id'] = 'invalid_uuid';
    }
    if ($ciutat_naixement_id !== '__MISSING__' && $ciutat_naixement_id !== null && !isUuid($ciutat_naixement_id)) {
        $errors['ciutat_naixement_id'] = 'invalid_uuid';
    }
    if ($ciutat_defuncio_id !== '__MISSING__' && $ciutat_defuncio_id !== null && !isUuid($ciutat_defuncio_id)) {
        $errors['ciutat_defuncio_id'] = 'invalid_uuid';
    }

    $img_id_bin = '__MISSING__';

    $hasImage = !empty($_FILES['img_upload'])
        && $_FILES['img_upload']['error'] === UPLOAD_ERR_OK;

    /**
     * 1) Usuario sube una nueva imagen
     */
    if ($hasImage) {

        $file = $_FILES['img_upload'];

        $nom = pathinfo($file['name'], PATHINFO_FILENAME);

        $alt = !empty($_POST['alt'])
            ? data_input($_POST['alt'])
            : $nom;

        $img_uuid = ImageService::createFromUpload(
            $file,
            1,
            $nom,
            $alt,
            $conn
        );

        $img_id_bin = Uuid::toBinary($img_uuid);

        /**
         * 2) Usuario selecciona una imagen existente
         * 3) Usuario elimina la imagen
         */
    } else if (array_key_exists('img_id', $data)) {

        // eliminar imagen
        if ($data['img_id'] === null || $data['img_id'] === '') {

            $img_id_bin = null;

            // asignar imagen existente
        } else if (isUuid($data['img_id'])) {

            $img_id_bin = Uuid::toBinary($data['img_id']);
        }
    }

    // groups
    $hasGrups = array_key_exists('grup_ids', $data) || array_key_exists('grups', $data);
    $grup_ids = $hasGrups ? ($data['grup_ids'] ?? ($data['grups'] ?? [])) : null;

    if ($hasGrups && !is_array($grup_ids)) {
        $errors['grup_ids'] = 'invalid_array';
    }

    if ($hasGrups && is_array($grup_ids)) {

        foreach ($grup_ids as $i => $gid) {

            if (empty($gid)) {
                continue; // 👈 clave
            }

            if (!isUuid($gid)) {
                $errors["grup_ids.$i"] = 'invalid_uuid';
            }
        }
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    global $conn;

    try {

        $personaIdBin = Uuid::toBinary($id);

        $conn->beginTransaction();

        // check exists
        $st = $conn->prepare("
            SELECT 1 FROM " . Tables::PERSONES . "
            WHERE id = :id LIMIT 1
        ");
        $st->bindValue(':id', $personaIdBin, PDO::PARAM_LOB);
        $st->execute();

        if (!$st->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ['id' => $id], 404);
            exit;
        }

        $set = [];
        $bind = [];

        $add = function ($field, $param, $value, $type) use (&$set, &$bind) {
            $set[] = "$field = $param";
            $bind[] = [$param, $value, $type];
        };

        // normal fields
        if ($nom !== '__MISSING__')
            $add('nom', ':nom', $nom, $nom === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($cognoms !== '__MISSING__')
            $add('cognoms', ':cognoms', $cognoms, $cognoms === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($slug !== '__MISSING__')
            $add('slug', ':slug', $slug, $slug === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($sexe_id !== '__MISSING__')
            $add('sexe_id', ':sexe_id', $sexe_id, $sexe_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        foreach (
            [
                'any_naixement' => $any_naixement,
                'mes_naixement' => $mes_naixement,
                'dia_naixement' => $dia_naixement,
                'any_defuncio' => $any_defuncio,
                'mes_defuncio' => $mes_defuncio,
                'dia_defuncio' => $dia_defuncio,
            ] as $k => $v
        ) {
            if ($v === '__MISSING__') continue;
            $add($k, ":$k", $v, $v === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        }

        if ($web !== '__MISSING__')
            $add('web', ':web', $web, $web === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($descripcio !== '__MISSING__')
            $add('descripcio', ':descripcio', $descripcio, $descripcio === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        // UUIDs (NOW CONSISTENT)
        if ($pais_autor_id !== '__MISSING__') {
            if ($pais_autor_id === null) {
                $set[] = "pais_autor_id = NULL";
            } else {
                $set[] = "pais_autor_id = :pais_autor_id";
                $bind[] = [':pais_autor_id', Uuid::toBinary($pais_autor_id), PDO::PARAM_LOB];
            }
        }

        if ($ciutat_naixement_id !== '__MISSING__') {
            if ($ciutat_naixement_id === null) {
                $set[] = "ciutat_naixement_id = NULL";
            } else {
                $set[] = "ciutat_naixement_id = :ciutat_naixement_id";
                $bind[] = [':ciutat_naixement_id', Uuid::toBinary($ciutat_naixement_id), PDO::PARAM_LOB];
            }
        }

        if ($ciutat_defuncio_id !== '__MISSING__') {
            if ($ciutat_defuncio_id === null) {
                $set[] = "ciutat_defuncio_id = NULL";
            } else {
                $set[] = "ciutat_defuncio_id = :ciutat_defuncio_id";
                $bind[] = [':ciutat_defuncio_id', Uuid::toBinary($ciutat_defuncio_id), PDO::PARAM_LOB];
            }
        }

        if ($img_id_bin !== '__MISSING__') {

            if ($img_id_bin === null) {

                $set[] = "img_id = NULL";
            } else {

                $set[] = "img_id = :img_id";
                $bind[] = [':img_id', $img_id_bin, PDO::PARAM_LOB];
            }
        }

        $add('updated_at', ':updated_at', date('Y-m-d H:i:s.u'), PDO::PARAM_STR);

        // UPDATE
        if (!empty($set)) {

            $sql = "
                UPDATE " . Tables::PERSONES . "
                SET " . implode(", ", $set) . "
                WHERE id = :id
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bindValue(':id', $personaIdBin, PDO::PARAM_LOB);

            foreach ($bind as [$p, $v, $t]) {
                $stmt->bindValue($p, $v, $t);
            }

            if (!$stmt->execute()) {
                $conn->rollBack();
                Response::error(MissatgesAPI::error('db_error'), $stmt->errorInfo(), 500);
                exit;
            }
        }

        // groups replace
        if ($hasGrups) {
            deletePersonGroups($conn, $personaIdBin);

            if (is_array($grup_ids)) {

                $grup_ids = array_values(array_filter($grup_ids));

                if (!empty($grup_ids)) {
                    insertPersonGroups($conn, $personaIdBin, $grup_ids);
                }
            }
        }

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            200
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

    // -------------------------
    // PUT update persona/autor
    // ruta: ?grupPersona
    // -------------------------
} else if (isset($_GET['grupPersona'])) {

    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
    }

    // ✅ id viene dentro del body
    $id = isset($data['id']) ? trim((string)$data['id']) : '';

    if ($id === '' || !isUuid($id)) {
        Response::error(MissatgesAPI::error('invalid_data'), ['id' => 'invalid_uuid'], 400);
        exit;
    }

    $errors = [];

    // Requeridos (NOT NULL)
    $grup_ca = $data['grup_ca'];

    // Normaliza
    $grup_ca = is_string($grup_ca) ? trim($grup_ca) : $grup_ca;

    if ($grup_ca === '') $errors['grup_ca'] = 'required';

    // (Opcional) límite FE (maxlength=150)
    $maxLen = 150;
    if (is_string($grup_ca) && mb_strlen($grup_ca) > $maxLen) $errors['grup_ca'] = 'too_long';

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    global $conn; // PDO (como en tu POST persona)
    // Si usas $db->getData / $db->execute, te lo adapto, pero este es el estilo PDO.

    try {
        $conn->beginTransaction();

        // 1) comprobar que existe
        $qChk = "SELECT 1 FROM " . Tables::PERSONES_GRUPS . " WHERE id = :id LIMIT 1";
        $stChk = $conn->prepare($qChk);
        $stChk->bindValue(':id', uuid::toBinary($id), PDO::PARAM_STR);
        $stChk->execute();

        if (!$stChk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ['grupPersona' => 'not_found'], 404);
            exit;
        }

        // 2) update
        $sql = "
            UPDATE " . Tables::PERSONES_GRUPS . "
            SET
                grup_ca = :grup_ca
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', uuid::toBinary($id), PDO::PARAM_STR);
        $stmt->bindValue(':grup_ca', $grup_ca, PDO::PARAM_STR);

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
            MissatgesAPI::success('update'),
            [
                'id' => $id,
            ],
            200
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
