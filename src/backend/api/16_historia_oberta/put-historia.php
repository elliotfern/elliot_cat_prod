<?php

declare(strict_types=1);

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;
use App\Utils\ValidacioErrors;
use Ramsey\Uuid\Uuid as RamseyUuid;

$slug = $routeParams[0] ?? null;

$db  = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


// a) Modificar esdeveniment
if ($slug === 'esdeveniment') {

    // Llegir JSON
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(
            MissatgesAPI::error('bad_request'),
            ['json' => 'invalid'],
            400
        );
        exit;
    }

    // ==============================
    // VALIDACIÓ
    // ==============================
    $errors = [];

    $id = trim((string) ($data['id'] ?? ''));
    $nom = trim((string) ($data['esdeNom'] ?? ''));
    $slug = trim((string) ($data['slug'] ?? ''));

    $dataInicialDia = $data['esdeDataIDia'] ?? null;
    $dataInicialMes = $data['esdeDataIMes'] ?? null;
    $dataInicialAny = $data['esdeDataIAny'] ?? null;

    $dataFinalDia = $data['esdeDataFDia'] ?? null;
    $dataFinalMes = $data['esdeDataFMes'] ?? null;
    $dataFinalAny = $data['esdeDataFAny'] ?? null;

    // Les cadenes buides equivalen a NULL.
    $dataFinalDia = $dataFinalDia === '' ? null : $dataFinalDia;
    $dataFinalMes = $dataFinalMes === '' ? null : $dataFinalMes;
    $dataFinalAny = $dataFinalAny === '' ? null : $dataFinalAny;

    $subEtapaId = trim((string) ($data['sub_etapa_id'] ?? ''));
    $ciutatId = trim((string) ($data['ciutat_id'] ?? ''));
    $imatgeId = trim((string) ($data['imatge_id'] ?? ''));
    $descripcio = trim((string) ($data['descripcio'] ?? ''));

    $personesEnviades = array_key_exists('persones', $data);
    $organitzacionsEnviades = array_key_exists('organitzacions', $data);

    $persones = $personesEnviades ? $data['persones'] : [];
    $organitzacions = $organitzacionsEnviades ? $data['organitzacions'] : [];

    $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    // Identificador obligatori
    if ($id === '' || !preg_match($uuidRegex, $id)) {
        $errors[] = ValidacioErrors::format('id');
    }

    // Camps obligatoris
    foreach (
        [
            'esdeNom' => $nom,
            'slug' => $slug,
        ] as $camp => $valor
    ) {
        if ($valor === '') {
            $errors[] = ValidacioErrors::requerit($camp);
        }
    }

    // Camps UUID opcionals
    foreach (
        [
            'sub_etapa_id' => $subEtapaId,
            'ciutat_id' => $ciutatId,
            'imatge_id' => $imatgeId,
        ] as $camp => $valor
    ) {
        if ($valor !== '' && !preg_match($uuidRegex, $valor)) {
            $errors[] = ValidacioErrors::format($camp);
        }
    }

    // Validar dates inicials: obligatòries.
    $datesInicials = [
        'esdeDataIDia' => [$dataInicialDia, 31],
        'esdeDataIMes' => [$dataInicialMes, 12],
        'esdeDataIAny' => [$dataInicialAny, 2155],
    ];

    foreach ($datesInicials as $camp => [$valor, $maxim]) {
        if (
            $valor === null
            || $valor === ''
            || filter_var($valor, FILTER_VALIDATE_INT) === false
            || (int) $valor < 0
            || (int) $valor > $maxim
        ) {
            $errors[] = ValidacioErrors::format($camp);
        }
    }

    // Validar dates finals: opcionals, però han d'anar juntes.
    $datesFinals = [
        'esdeDataFDia' => [$dataFinalDia, 31],
        'esdeDataFMes' => [$dataFinalMes, 12],
        'esdeDataFAny' => [$dataFinalAny, 2155],
    ];

    $totsElsCampsFinalsBuits =
        $dataFinalDia === null
        && $dataFinalMes === null
        && $dataFinalAny === null;

    if (!$totsElsCampsFinalsBuits) {
        foreach ($datesFinals as $camp => [$valor, $maxim]) {
            if (
                $valor === null
                || filter_var($valor, FILTER_VALIDATE_INT) === false
                || (int) $valor < 0
                || (int) $valor > $maxim
            ) {
                $errors[] = ValidacioErrors::format($camp);
            }
        }
    }

    // En una modificació exigim els dos arrays.
    // Un array buit significa que no hi ha relacions d'aquell tipus.
    if (!is_array($persones)) {
        $errors[] = ValidacioErrors::format('persones');
    }

    if (!is_array($organitzacions)) {
        $errors[] = ValidacioErrors::format('organitzacions');
    }

    // Validar UUID de les relacions i eliminar duplicats
    $validarRelacions = static function (
        array $ids,
        string $camp
    ) use (&$errors, $uuidRegex): array {
        $resultat = [];

        foreach ($ids as $relacioId) {
            if ($relacioId === '') {
                continue;
            }

            if (
                !is_string($relacioId)
                || !preg_match($uuidRegex, $relacioId)
            ) {
                $errors[] = ValidacioErrors::format($camp);
                continue;
            }

            $resultat[strtolower($relacioId)] = $relacioId;
        }

        return array_values($resultat);
    };

    $personesIds = is_array($persones)
        ? $validarRelacions($persones, 'persones')
        : [];

    $organitzacionsIds = is_array($organitzacions)
        ? $validarRelacions($organitzacions, 'organitzacions')
        : [];

    if ($errors !== []) {
        Response::error(
            MissatgesAPI::error('validation_error'),
            $errors,
            400
        );
        exit;
    }

    // ==============================
    // CONVERSIÓ UUID I DATES
    // ==============================
    $idBytes = Uuid::toBinary($id);

    $subEtapaBytes = $subEtapaId !== ''
        ? Uuid::toBinary($subEtapaId)
        : null;

    $ciutatBytes = $ciutatId !== ''
        ? Uuid::toBinary($ciutatId)
        : null;

    $imatgeBytes = $imatgeId !== ''
        ? Uuid::toBinary($imatgeId)
        : null;

    $dateModified = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

    try {
        // ==============================
        // COMPROVAR EXISTÈNCIA
        // ==============================
        $sqlExisteix = "
        SELECT id
        FROM db_historia_esdeveniments
        WHERE id = :id
        LIMIT 1
    ";

        $stmtExisteix = $pdo->prepare($sqlExisteix);
        $stmtExisteix->bindValue(':id', $idBytes, PDO::PARAM_LOB);
        $stmtExisteix->execute();

        if ($stmtExisteix->fetchColumn() === false) {
            Response::error(
                MissatgesAPI::error('not_found'),
                ['id' => $id],
                404
            );
            exit;
        }

        // ==============================
        // INICIAR TRANSACCIÓ
        // ==============================
        $pdo->beginTransaction();

        // ==============================
        // ACTUALITZAR ESDEVENIMENT
        // ==============================
        $sql = "
        UPDATE db_historia_esdeveniments
        SET
            esdeNom = :esdeNom,
            slug = :slug,
            esdeDataIDia = :esdeDataIDia,
            esdeDataIMes = :esdeDataIMes,
            esdeDataIAny = :esdeDataIAny,
            esdeDataFDia = :esdeDataFDia,
            esdeDataFMes = :esdeDataFMes,
            esdeDataFAny = :esdeDataFAny,
            sub_etapa_id = :sub_etapa_id,
            ciutat_id = :ciutat_id,
            imatge_id = :imatge_id,
            dateModified = :dateModified,
            descripcio = :descripcio
        WHERE id = :id
    ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $idBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':esdeNom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

        $stmt->bindValue(':esdeDataIDia', (int) $dataInicialDia, PDO::PARAM_INT);
        $stmt->bindValue(':esdeDataIMes', (int) $dataInicialMes, PDO::PARAM_INT);
        $stmt->bindValue(':esdeDataIAny', (int) $dataInicialAny, PDO::PARAM_INT);

        $stmt->bindValue(
            ':esdeDataFDia',
            $dataFinalDia,
            $dataFinalDia === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':esdeDataFMes',
            $dataFinalMes,
            $dataFinalMes === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':esdeDataFAny',
            $dataFinalAny,
            $dataFinalAny === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );

        $stmt->bindValue(':descripcio', $descripcio, PDO::PARAM_STR);

        $stmt->bindValue(
            ':sub_etapa_id',
            $subEtapaBytes,
            $subEtapaBytes === null ? PDO::PARAM_NULL : PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':ciutat_id',
            $ciutatBytes,
            $ciutatBytes === null ? PDO::PARAM_NULL : PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':imatge_id',
            $imatgeBytes,
            $imatgeBytes === null ? PDO::PARAM_NULL : PDO::PARAM_LOB
        );

        $stmt->bindValue(':dateModified', $dateModified, PDO::PARAM_STR);
        $stmt->execute();

        // ==============================
        // ACTUALITZAR RELACIONS: PERSONES
        // ==============================
        if ($personesEnviades) {
            $sqlEliminarPersones = "
        DELETE FROM db_historia_esdeveniment_persones
        WHERE esdeveniment_id = :id
    ";

            $stmtEliminarPersones = $pdo->prepare($sqlEliminarPersones);
            $stmtEliminarPersones->bindValue(':id', $idBytes, PDO::PARAM_LOB);
            $stmtEliminarPersones->execute();

            if ($personesIds !== []) {
                $sqlInsertarPersona = "
            INSERT INTO db_historia_esdeveniment_persones (
                id,
                esdeveniment_id,
                persona_id
            ) VALUES (
                :id,
                :esdeveniment_id,
                :persona_id
            )
            ";


                $stmtInsertarPersona = $pdo->prepare($sqlInsertarPersona);

                foreach ($personesIds as $personaId) {
                    $relacioId = RamseyUuid::uuid7()->getBytes();

                    $stmtInsertarPersona->bindValue(':id', $relacioId, PDO::PARAM_LOB);
                    $stmtInsertarPersona->bindValue(
                        ':esdeveniment_id',
                        $idBytes,
                        PDO::PARAM_LOB
                    );
                    $stmtInsertarPersona->bindValue(
                        ':persona_id',
                        Uuid::toBinary($personaId),
                        PDO::PARAM_LOB
                    );

                    $stmtInsertarPersona->execute();
                }
            }
        }

        // ==============================
        // ACTUALITZAR RELACIONS: ORGANITZACIONS
        // ==============================
        if ($organitzacionsEnviades) {
            $sqlEliminarOrganitzacions = "
        DELETE FROM db_historia_esdeveniment_organitzacio
        WHERE esdeveniment_id = :id
    ";

            $stmtEliminarOrganitzacions = $pdo->prepare($sqlEliminarOrganitzacions);
            $stmtEliminarOrganitzacions->bindValue(':id', $idBytes, PDO::PARAM_LOB);
            $stmtEliminarOrganitzacions->execute();

            if ($organitzacionsIds !== []) {
                $sqlInsertarOrganitzacio = "
            INSERT INTO db_historia_esdeveniment_organitzacio (
                id,
                esdeveniment_id,
                organitzacio_id
            ) VALUES (
                :id,
                :esdeveniment_id,
                :organitzacio_id
            )
            ";

                $stmtInsertarOrganitzacio = $pdo->prepare($sqlInsertarOrganitzacio);

                foreach ($organitzacionsIds as $organitzacioId) {
                    $relacioId = RamseyUuid::uuid7()->getBytes();

                    $stmtInsertarOrganitzacio->bindValue(
                        ':id',
                        $relacioId,
                        PDO::PARAM_LOB
                    );
                    $stmtInsertarOrganitzacio->bindValue(
                        ':esdeveniment_id',
                        $idBytes,
                        PDO::PARAM_LOB
                    );
                    $stmtInsertarOrganitzacio->bindValue(
                        ':organitzacio_id',
                        Uuid::toBinary($organitzacioId),
                        PDO::PARAM_LOB
                    );

                    $stmtInsertarOrganitzacio->execute();
                }
            }
        }

        // ==============================
        // CONFIRMAR TRANSACCIÓ
        // ==============================
        $pdo->commit();

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => Uuid::toString($idBytes),
                'esdeNom' => $nom,
                'slug' => $slug,
                ...($personesEnviades ? ['persones' => $personesIds] : []),
                ...($organitzacionsEnviades ? ['organitzacions' => $organitzacionsIds] : []),
                'dateModified' => $dateModified,
            ]
        );
        exit;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('[PUT esdeveniment] ' . $e->getMessage());

        Response::error(
            MissatgesAPI::error('internal_error'),
            [],
            500
        );
        exit;
    }

    // b) modificar persona / carrec
} else if (isset($_GET['personaCarrec'])) {

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

    $idPersona = !empty($data['idPersona']) ? $data['idPersona'] : ($hasError = true);
    $carrecNom = !empty($data['carrecNom']) ? $data['carrecNom'] : ($hasError = true);
    $carrecNomCast = !empty($data['carrecNomCast']) ? $data['carrecNomCast'] : ($hasError = false);
    $carrecNomEng = !empty($data['carrecNomEng']) ? $data['carrecNomEng'] : ($hasError = false);
    $carrecNomIt = !empty($data['carrecNomIt']) ? $data['carrecNomIt'] : ($hasError = false);
    $carrecInici = !empty($data['carrecInici']) ? $data['carrecInici'] : ($hasError = true);
    $carrecFi = !empty($data['carrecFi']) ? $data['carrecFi'] : ($hasError = false);
    $idOrg = !empty($data['idOrg']) ? $data['idOrg'] : ($hasError = true);
    $id = !empty($data['id']) ? $data['id'] : ($hasError = true);

    if (!$hasError) {
        global $conn;
        $sql = "UPDATE aux_persones_carrecs
        SET idOrg = :idOrg,
            idPersona = :idPersona,
            carrecNom = :carrecNom,
            carrecNomCast = :carrecNomCast,
            carrecNomEng = :carrecNomEng,
            carrecNomIt = :carrecNomIt,
            carrecInici = :carrecInici,
            carrecFi = :carrecFi
             WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":idPersona", $idPersona, PDO::PARAM_INT);
        $stmt->bindParam(":carrecNom", $carrecNom, PDO::PARAM_STR);
        $stmt->bindParam(":carrecNomCast", $carrecNomCast, PDO::PARAM_STR);
        $stmt->bindParam(":carrecNomEng", $carrecNomEng, PDO::PARAM_STR);
        $stmt->bindParam(":carrecNomIt", $carrecNomIt, PDO::PARAM_STR);
        $stmt->bindParam(":carrecInici", $carrecInici, PDO::PARAM_STR);
        $stmt->bindParam(":carrecFi", $carrecFi, PDO::PARAM_STR);
        $stmt->bindParam(":idOrg", $idOrg, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // response output
            $response['status'] = 'success';
            header("Content-Type: application/json");
            echo json_encode($response);
        } else {
            // response output - data error
            $response['status'] = 'error';
            header("Content-Type: application/json");
            echo json_encode($response);
        }
    } else {
        // response output - data error
        $response['status'] = 'error';

        header("Content-Type: application/json");
        echo json_encode($response);
    }



    // ==============================
    // PUT ORGANITZACIO
    // ==============================
} else if ($slug === 'organitzacio') {

    // Llegir JSON
    $input_data = file_get_contents("php://input");
    $data = json_decode($input_data, true);

    if (!is_array($data)) {
        Response::error(
            MissatgesAPI::error('bad_request'),
            ['json' => 'invalid'],
            400
        );
        exit;
    }

    // ==============================
    // VALIDACIÓ
    // ==============================
    $errors = [];

    $id            = trim((string) ($data['id'] ?? ''));
    $organitzacio  = trim((string) ($data['organitzacio'] ?? ''));
    $slugOrg       = trim((string) ($data['slug'] ?? ''));
    $sigla         = trim((string) ($data['sigla'] ?? ''));
    $anyFundacio   = $data['any_fundacio'] ?? null;
    $anyDissolucio = $data['any_dissolucio'] ?? null;
    $paisId        = trim((string) ($data['pais_id'] ?? ''));
    $ciutatId      = trim((string) ($data['ciutat_id'] ?? ''));
    $subEtapaId    = trim((string) ($data['sub_etapa_id'] ?? ''));
    $tipus         = trim((string) ($data['tipus'] ?? ''));
    $ideologiaId   = trim((string) ($data['ideologia_id'] ?? ''));
    $imatgeId      = trim((string) ($data['imatge_id'] ?? ''));
    $dateCreated   = trim((string) ($data['dateCreated'] ?? ''));
    $dateModified  = trim((string) ($data['dateModified'] ?? ''));

    $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    // ID de l'organització
    if ($id === '') {
        $errors[] = ValidacioErrors::requerit('id');
    } elseif (!preg_match($uuidRegex, $id)) {
        $errors[] = ValidacioErrors::format('id');
    }

    // Camps de text obligatoris
    foreach (
        [
            'organitzacio' => $organitzacio,
            'slug' => $slugOrg,
            'sigla' => $sigla,
            'tipus' => $tipus,
        ] as $camp => $valor
    ) {
        if ($valor === '') {
            $errors[] = ValidacioErrors::requerit($camp);
        }
    }

    // Camps UUID opcionals: una cadena buida representa NULL
    foreach (
        [
            'pais_id' => $paisId,
            'ciutat_id' => $ciutatId,
            'sub_etapa_id' => $subEtapaId,
            'ideologia_id' => $ideologiaId,
            'imatge_id' => $imatgeId,
        ] as $camp => $valor
    ) {
        if ($valor !== '' && !preg_match($uuidRegex, $valor)) {
            $errors[] = ValidacioErrors::format($camp);
        }
    }

    // Anys
    foreach (
        [
            'any_fundacio' => $anyFundacio,
            'any_dissolucio' => $anyDissolucio,
        ] as $camp => $valor
    ) {
        if ($valor === null || $valor === '') {
            $errors[] = ValidacioErrors::requerit($camp);
        } elseif (
            filter_var($valor, FILTER_VALIDATE_INT) === false
            || (int) $valor < 0
            || (int) $valor > 2155
        ) {
            $errors[] = ValidacioErrors::format($camp);
        }
    }

    if ($errors !== []) {
        Response::error(
            MissatgesAPI::error('validation_error'),
            $errors,
            400
        );
        exit;
    }

    // ==============================
    // CONVERSIÓ UUID
    // ==============================
    try {
        $idBytes = Uuid::toBinary($id);
        $paisBytes = Uuid::toBinary($paisId);
        $ciutatBytes = Uuid::toBinary($ciutatId);
        $subEtapaBytes = Uuid::toBinary($subEtapaId);
        $ideologiaBytes = Uuid::toBinary($ideologiaId);
        $imatgeBytes = Uuid::toBinary($imatgeId);

        // Dates
        $dateModified = new \DateTimeImmutable();
        $dateModifiedFormatted = $dateModified->format('Y-m-d H:i:s');


        // ==============================
        // UPDATE
        // ==============================
        $sql = "UPDATE " . Tables::DB_HISTORIA_ORGANITZACIONS . "
                SET
                    organitzacio = :organitzacio,
                    slug = :slug,
                    sigla = :sigla,
                    any_fundacio = :any_fundacio,
                    any_dissolucio = :any_dissolucio,
                    pais_id = :pais_id,
                    ciutat_id = :ciutat_id,
                    sub_etapa_id = :sub_etapa_id,
                    tipus = :tipus,
                    ideologia_id = :ideologia_id,
                    imatge_id = :imatge_id,
                    dateModified = :dateModified
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        // UUID binaris
        $stmt->bindValue(':id', $idBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':pais_id', $paisBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':ciutat_id', $ciutatBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':sub_etapa_id', $subEtapaBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':ideologia_id', $ideologiaBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':imatge_id', $imatgeBytes, PDO::PARAM_LOB);

        // Text
        $stmt->bindValue(':organitzacio', $organitzacio, PDO::PARAM_STR);
        $stmt->bindValue(':slug', $slugOrg, PDO::PARAM_STR);
        $stmt->bindValue(':sigla', $sigla, PDO::PARAM_STR);
        $stmt->bindValue(':tipus', $tipus, PDO::PARAM_STR);

        // Anys
        $stmt->bindValue(
            ':any_fundacio',
            (int) $anyFundacio,
            PDO::PARAM_INT
        );
        $stmt->bindValue(
            ':any_dissolucio',
            (int) $anyDissolucio,
            PDO::PARAM_INT
        );

        // Dates
        $stmt->bindValue(':dateModified', $dateModifiedFormatted, PDO::PARAM_STR);

        $stmt->execute();

        // ==============================
        // COMPROVAR EXISTÈNCIA
        // ==============================
        if ($stmt->rowCount() === 0) {
            $sqlCheck = "SELECT 1
                         FROM " . Tables::DB_HISTORIA_ORGANITZACIONS . "
                         WHERE id = :id
                         LIMIT 1";

            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->bindValue(':id', $idBytes, PDO::PARAM_LOB);
            $stmtCheck->execute();

            if (!$stmtCheck->fetchColumn()) {
                Response::error(
                    MissatgesAPI::error('not_found'),
                    ['id' => $id],
                    404
                );
                exit;
            }
        }

        // ==============================
        // RESPOSTA OK
        // ==============================
        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id,
                'organitzacio' => $organitzacio,
                'slug' => $slugOrg,
            ]
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            500
        );
        exit;
    }


    /**
     * PUT : Actualitzar slot curs-article (db_historia_oberta_articles)
     * URL: /api/historia/put/updateCursArticle
     * }
     */
} else if ($slug === 'updateCursArticle') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    if (!is_array($data)) {
        Response::error(
            MissatgesAPI::error('bad_request'),
            ['json' => 'invalid'],
            400
        );
        return;
    }

    $errors = [];

    // =========================================================
    // DATOS
    // =========================================================

    $id = isset($data['id'])
        ? trim((string)$data['id'])
        : '';

    $cursId = isset($data['curs_id'])
        ? trim((string)$data['curs_id'])
        : '';

    $ordre = $data['ordre'] ?? null;

    $articleCaId = isset($data['article_ca_id'])
        ? trim((string)$data['article_ca_id'])
        : '';

    $articleEsId = isset($data['article_es_id'])
        ? trim((string)$data['article_es_id'])
        : '';

    $articleEnId = isset($data['article_en_id'])
        ? trim((string)$data['article_en_id'])
        : '';

    $articleItId = isset($data['article_it_id'])
        ? trim((string)$data['article_it_id'])
        : '';

    $articleFrId = isset($data['article_fr_id'])
        ? trim((string)$data['article_fr_id'])
        : '';


    // =========================================================
    // VALIDACIÓ UUID
    // =========================================================

    if (!Uuid::isValid($id)) {
        $errors['id'] = 'must_be_uuid';
    }

    if (!Uuid::isValid($cursId)) {
        $errors['curs_id'] = 'must_be_uuid';
    }


    // =========================================================
    // VALIDACIÓ ORDRE
    // =========================================================

    if ($ordre === null || $ordre === '' || !is_numeric($ordre)) {

        $errors['ordre'] = 'must_be_int';
    } else {

        $ordre = (int)$ordre;

        if ($ordre <= 0) {
            $errors['ordre'] = 'must_be_gt_0';
        }
    }


    // =========================================================
    // VALIDACIÓ ARTICLES
    // =========================================================

    $articleIds = [
        'article_ca_id' => $articleCaId,
        'article_es_id' => $articleEsId,
        'article_en_id' => $articleEnId,
        'article_it_id' => $articleItId,
        'article_fr_id' => $articleFrId,
    ];

    foreach ($articleIds as $key => $articleId) {

        // El català és obligatori
        if ($key === 'article_ca_id' && $articleId === '') {
            $errors[$key] = 'required';
            continue;
        }

        // La resta poden ser NULL
        if ($articleId === '') {
            $articleIds[$key] = null;
            continue;
        }

        if (!Uuid::isValid($articleId)) {
            $errors[$key] = 'must_be_uuid_or_null';
        }
    }

    // =========================================================
    // VALORS NORMALITZATS
    // =========================================================

    $articleCaId = $articleIds['article_ca_id'];
    $articleEsId = $articleIds['article_es_id'];
    $articleEnId = $articleIds['article_en_id'];
    $articleItId = $articleIds['article_it_id'];
    $articleFrId = $articleIds['article_fr_id'];


    try {

        // =====================================================
        // EXISTEIX EL SLOT?
        // =====================================================

        $sqlSlot = sprintf(
            "SELECT id
             FROM %s
             WHERE id = :id
             LIMIT 1",
            qi(Tables::DB_HISTORIA_OBERTA_ARTICLES, $pdo)
        );

        $slot = $db->getData(
            $sqlSlot,
            [
                ':id' => Uuid::toBinary($id),
            ],
            true
        );

        if (empty($slot)) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['slot' => 'not_found'],
                404
            );

            return;
        }


        // =====================================================
        // EXISTEIX EL CURS?
        // =====================================================

        $sqlCurs = sprintf(
            "SELECT id
             FROM %s
             WHERE id = :id
             LIMIT 1",
            qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo)
        );

        $existsCurs = $db->getData(
            $sqlCurs,
            [
                ':id' => Uuid::toBinary($cursId),
            ],
            true
        );

        if (empty($existsCurs)) {

            Response::error(
                MissatgesAPI::error('invalid_data'),
                ['curs_id' => 'not_found'],
                400
            );

            return;
        }

        // =====================================================
        // UPDATE
        // =====================================================

        $sql = sprintf(
            "UPDATE %s
             SET
                article_ca_id = :article_ca_id,
                article_es_id = :article_es_id,
                article_en_id = :article_en_id,
                article_it_id = :article_it_id,
                article_fr_id = :article_fr_id,
                curs_id = :curs_id,
                ordre = :ordre
             WHERE id = :id
             LIMIT 1",
            qi(Tables::DB_HISTORIA_OBERTA_ARTICLES, $pdo)
        );

        $stmt = $pdo->prepare($sql);


        // =====================================================
        // BIND UUID NULLABLES
        // =====================================================

        $bindNullableUuid = static function (
            PDOStatement $stmt,
            string $parameter,
            ?string $uuid
        ): void {

            if ($uuid === null) {

                $stmt->bindValue(
                    $parameter,
                    null,
                    PDO::PARAM_NULL
                );
            } else {

                $stmt->bindValue(
                    $parameter,
                    Uuid::toBinary($uuid),
                    PDO::PARAM_LOB
                );
            }
        };


        $bindNullableUuid(
            $stmt,
            ':article_ca_id',
            $articleCaId
        );

        $bindNullableUuid(
            $stmt,
            ':article_es_id',
            $articleEsId
        );

        $bindNullableUuid(
            $stmt,
            ':article_en_id',
            $articleEnId
        );

        $bindNullableUuid(
            $stmt,
            ':article_it_id',
            $articleItId
        );

        $bindNullableUuid(
            $stmt,
            ':article_fr_id',
            $articleFrId
        );


        // curs_id
        $stmt->bindValue(
            ':curs_id',
            Uuid::toBinary($cursId),
            PDO::PARAM_LOB
        );


        // ordre
        $stmt->bindValue(
            ':ordre',
            $ordre,
            PDO::PARAM_INT
        );


        // id
        $stmt->bindValue(
            ':id',
            Uuid::toBinary($id),
            PDO::PARAM_LOB
        );


        // =====================================================
        // EXECUTE
        // =====================================================

        if (!$stmt->execute()) {

            Response::error(
                MissatgesAPI::error('errorBD'),
                [
                    'sqlState' => $stmt->errorCode(),
                    'info' => $stmt->errorInfo(),
                ],
                500
            );

            return;
        }


        // =====================================================
        // RESPONSE
        // =====================================================

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id,
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else if ($slug === 'cursHistoria') {
    try {

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($input)) {
            Response::error(
                'Dades de la petició invàlides.',
                [],
                400
            );
            return;
        }

        $id = trim((string) ($input['id'] ?? ''));
        $curs = trim((string) ($input['curs'] ?? ''));
        $resum = trim((string) ($input['resum'] ?? ''));
        $descripcio = isset($input['descripcio'])
            ? trim((string) $input['descripcio'])
            : null;
        $slugCurs = trim((string) ($input['slug'] ?? ''));
        $imgId = trim((string) ($input['img_id'] ?? ''));
        $ordre = $input['ordre'] ?? null;

        $errors = [];

        if ($id === '') {
            $errors[] = 'L\'id del curs és obligatori.';
        }

        if ($curs === '') {
            $errors[] = 'El curs és obligatori.';
        }

        if ($resum === '') {
            $errors[] = 'El resum és obligatori.';
        }

        if ($slugCurs === '') {
            $errors[] = 'El slug és obligatori.';
        }

        if ($imgId === '') {
            $errors[] = 'La imatge és obligatòria.';
        }

        if ($ordre !== null && $ordre !== '') {
            if (filter_var($ordre, FILTER_VALIDATE_INT) === false) {
                $errors[] = 'L\'ordre ha de ser un número enter.';
            } else {
                $ordre = (int) $ordre;
            }
        } else {
            $ordre = null;
        }

        if ($errors !== []) {
            Response::error(
                'Hi ha errors en les dades del curs.',
                $errors,
                400
            );
            return;
        }

        $sql = "
            UPDATE " . qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo) . "
            SET
                curs = :curs,
                resum = :resum,
                descripcio = :descripcio,
                slug = :slug,
                img_id = :img_id,
                ordre = :ordre,
                lastModified = CURRENT_TIMESTAMP
            WHERE id = :id
        ";

        $db->execute(
            $sql,
            [
                ':id' => Uuid::toBinary($id),
                ':curs' => $curs,
                ':resum' => $resum,
                ':descripcio' => $descripcio,
                ':slug' => $slugCurs,
                ':img_id' => Uuid::toBinary($imgId),
                ':ordre' => $ordre,
            ]
        );

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id,
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    return;
} else {
    // response output - data error
    $response['status'] = 'error';
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
}
