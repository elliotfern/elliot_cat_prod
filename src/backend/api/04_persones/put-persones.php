<?php

use App\Config\Database;
use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\ImageService;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

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
        ? $data[$key]
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

function deletePersonGroups(PDO $pdo, string $personaIdBin): void
{
    $sql = "DELETE FROM " . Tables::PERSONES_GRUPS_RELACIONS . " WHERE persona_id = :persona_id";
    $st = $pdo->prepare($sql);
    $st->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);

    if (!$st->execute()) {
        throw new \RuntimeException("db_error delete relacions");
    }
}

function insertPersonGroups(PDO $pdo, string $personaIdBin, array $grupIds): void
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

    $stmt = $pdo->prepare($sql);

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

function syncPersonCharges(PDO $pdo, string $personaIdBin, array $carrecs): void
{
    // Obtener los cargos actuales de la persona.
    $sql = "
        SELECT id
        FROM " . Tables::PERSONES_CARRECS . "
        WHERE persona_id = :persona_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);
    $stmt->execute();

    $existingIds = [];

    while ($idBin = $stmt->fetchColumn()) {
        $existingIds[bin2hex($idBin)] = $idBin;
    }

    $retainedIds = [];

    $sqlUpdate = "
        UPDATE " . Tables::PERSONES_CARRECS . "
        SET
            carrecNom = :nom,
            carrecInici = :inici,
            carrecFi = :fi,
            organitzacio_id = :organitzacio_id
        WHERE id = :id
          AND persona_id = :persona_id
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);

    $sqlInsert = "
        INSERT INTO " . Tables::PERSONES_CARRECS . " (
            id,
            persona_id,
            carrecNom,
            carrecInici,
            carrecFi,
            organitzacio_id
        ) VALUES (
            :id,
            :persona_id,
            :nom,
            :inici,
            :fi,
            :organitzacio_id
        )
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);

    foreach ($carrecs as $carrec) {

        $organitzacioBin = $carrec['organitzacio_id'] !== null
            ? Uuid::toBinary($carrec['organitzacio_id'])
            : null;

        if ($carrec['id'] !== null) {

            $idBin = Uuid::toBinary($carrec['id']);
            $idKey = bin2hex($idBin);

            // El cargo debe pertenecer a esta persona.
            if (!isset($existingIds[$idKey])) {
                throw new \InvalidArgumentException('invalid_carrec_id');
            }

            $retainedIds[$idKey] = true;

            $stmtUpdate->bindValue(':nom', $carrec['nom'], PDO::PARAM_STR);
            $stmtUpdate->bindValue(
                ':inici',
                $carrec['inici'],
                $carrec['inici'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );
            $stmtUpdate->bindValue(
                ':fi',
                $carrec['fi'],
                $carrec['fi'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );
            $stmtUpdate->bindValue(
                ':organitzacio_id',
                $organitzacioBin,
                $organitzacioBin === null ? PDO::PARAM_NULL : PDO::PARAM_LOB
            );
            $stmtUpdate->bindValue(':id', $idBin, PDO::PARAM_LOB);
            $stmtUpdate->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);

            if (!$stmtUpdate->execute()) {
                throw new \RuntimeException('db_error update persona carrec');
            }
        } else {

            $newIdBin = ramsey::uuid7()->getBytes();

            $stmtInsert->bindValue(':id', $newIdBin, PDO::PARAM_LOB);
            $stmtInsert->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);
            $stmtInsert->bindValue(':nom', $carrec['nom'], PDO::PARAM_STR);
            $stmtInsert->bindValue(
                ':inici',
                $carrec['inici'],
                $carrec['inici'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );
            $stmtInsert->bindValue(
                ':fi',
                $carrec['fi'],
                $carrec['fi'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );
            $stmtInsert->bindValue(
                ':organitzacio_id',
                $organitzacioBin,
                $organitzacioBin === null ? PDO::PARAM_NULL : PDO::PARAM_LOB
            );

            if (!$stmtInsert->execute()) {
                throw new \RuntimeException('db_error insert persona carrec');
            }
        }
    }

    // Eliminar los cargos que ya no aparecen en el formulario.
    $sqlDelete = "
        DELETE FROM " . Tables::PERSONES_CARRECS . "
        WHERE id = :id
          AND persona_id = :persona_id
    ";

    $stmtDelete = $pdo->prepare($sqlDelete);

    foreach ($existingIds as $idKey => $idBin) {

        if (isset($retainedIds[$idKey])) {
            continue;
        }

        $stmtDelete->bindValue(':id', $idBin, PDO::PARAM_LOB);
        $stmtDelete->bindValue(':persona_id', $personaIdBin, PDO::PARAM_LOB);

        if (!$stmtDelete->execute()) {
            throw new \RuntimeException('db_error delete persona carrec');
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

        $nomImatge = pathinfo($file['name'], PATHINFO_FILENAME);

        $alt = !empty($_POST['alt'])
            ? $_POST['alt']
            : $nomImatge;

        $img_uuid = ImageService::createFromUpload(
            $file,
            1,
            $nomImatge,
            $alt,
            $pdo
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

    // -------------------------
    // CARRECS
    // -------------------------

    $hasCarrecs = array_key_exists('carrecs', $data);
    $carrecs = [];

    if ($hasCarrecs) {

        if (!is_array($data['carrecs'])) {

            $errors['carrecs'] = 'invalid_array';
        } else {

            foreach ($data['carrecs'] as $i => $item) {

                if (!is_array($item)) {
                    $errors["carrecs.$i"] = 'invalid_format';
                    continue;
                }

                $carrecId = $item['id'] ?? null;
                $nomCarrec = $item['carrecNom'] ?? '';
                $iniciRaw = $item['carrecInici'] ?? null;
                $fiRaw = $item['carrecFi'] ?? null;
                $organitzacioId = $item['organitzacio_id'] ?? null;

                // Normalizar valores vacíos.
                $carrecId = ($carrecId === '') ? null : $carrecId;
                $nomCarrec = is_string($nomCarrec) ? trim($nomCarrec) : null;
                $iniciRaw = ($iniciRaw === '') ? null : $iniciRaw;
                $fiRaw = ($fiRaw === '') ? null : $fiRaw;
                $organitzacioId = ($organitzacioId === '') ? null : $organitzacioId;

                // Validar ID del cargo existente.
                if ($carrecId !== null && !isUuid($carrecId)) {
                    $errors["carrecs.$i.id"] = 'invalid_uuid';
                }

                // Ignorar bloques nuevos completamente vacíos.
                if (
                    $carrecId === null &&
                    ($nomCarrec === null || $nomCarrec === '') &&
                    $iniciRaw === null &&
                    $fiRaw === null &&
                    $organitzacioId === null
                ) {
                    continue;
                }

                // Nombre del cargo.
                if ($nomCarrec === null || $nomCarrec === '') {
                    $errors["carrecs.$i.carrecNom"] = 'required';
                } elseif (mb_strlen($nomCarrec) > 155) {
                    $errors["carrecs.$i.carrecNom"] = 'max_length';
                }

                // Año de inicio.
                $inici = null;

                if ($iniciRaw !== null) {
                    $validated = filter_var($iniciRaw, FILTER_VALIDATE_INT);

                    if ($validated === false) {
                        $errors["carrecs.$i.carrecInici"] = 'invalid_int';
                    } else {
                        $inici = $validated;
                    }
                }

                // Año de finalización.
                $fi = null;

                if ($fiRaw !== null) {
                    $validated = filter_var($fiRaw, FILTER_VALIDATE_INT);

                    if ($validated === false) {
                        $errors["carrecs.$i.carrecFi"] = 'invalid_int';
                    } else {
                        $fi = $validated;
                    }
                }

                // Organización.
                if ($organitzacioId !== null && !isUuid($organitzacioId)) {
                    $errors["carrecs.$i.organitzacio_id"] = 'invalid_uuid';
                }

                $carrecs[] = [
                    'id' => $carrecId,
                    'nom' => $nomCarrec ?? '',
                    'inici' => $inici,
                    'fi' => $fi,
                    'organitzacio_id' => $organitzacioId,
                ];
            }
        }
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }


    try {

        $personaIdBin = Uuid::toBinary($id);

        $pdo->beginTransaction();

        // check exists
        $st = $pdo->prepare("
            SELECT 1 FROM " . Tables::PERSONES . "
            WHERE id = :id LIMIT 1
        ");
        $st->bindValue(':id', $personaIdBin, PDO::PARAM_LOB);
        $st->execute();

        if (!$st->fetchColumn()) {
            $pdo->rollBack();
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

            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':id', $personaIdBin, PDO::PARAM_LOB);

            foreach ($bind as [$p, $v, $t]) {
                $stmt->bindValue($p, $v, $t);
            }

            if (!$stmt->execute()) {
                $pdo->rollBack();
                Response::error(MissatgesAPI::error('db_error'), $stmt->errorInfo(), 500);
                exit;
            }
        }

        // groups replace
        if ($hasGrups) {
            deletePersonGroups($pdo, $personaIdBin);

            if (is_array($grup_ids)) {

                $grup_ids = array_values(array_filter($grup_ids));

                if (!empty($grup_ids)) {
                    insertPersonGroups($pdo, $personaIdBin, $grup_ids);
                }
            }
        }

        // cargos replace / synchronize
        if ($hasCarrecs) {
            syncPersonCharges($pdo, $personaIdBin, $carrecs);
        }

        $pdo->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            httpCode: 200
        );
        exit;
    } catch (\InvalidArgumentException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        Response::error(
            MissatgesAPI::error('invalid_data'),
            ['carrecs' => 'invalid_carrec_id'],
            400
        );
        exit;
    } catch (\Throwable $e) {

        if ($pdo->inTransaction()) $pdo->rollBack();

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
