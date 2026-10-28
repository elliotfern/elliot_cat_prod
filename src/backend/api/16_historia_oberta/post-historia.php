<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use Ramsey\Uuid\Uuid as RamseyUuid;
use App\Utils\Uuid;
use App\Utils\ValidacioErrors;

$slug = $routeParams[0] ?? '';

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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// a) Inserir esdeveniment
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

    $persones = $data['persones'] ?? [];
    $organitzacions = $data['organitzacions'] ?? [];

    $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

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

    // Validar arrays de relacions
    if (!is_array($persones)) {
        $errors[] = ValidacioErrors::format('persones');
    }

    if (!is_array($organitzacions)) {
        $errors[] = ValidacioErrors::format('organitzacions');
    }

    // Validar UUID de les relacions i eliminar valors repetits
    $validarRelacions = static function (
        array $ids,
        string $camp
    ) use (&$errors, $uuidRegex): array {
        $resultat = [];

        foreach ($ids as $id) {
            // Els selectors sense selecció no representen relacions.
            if ($id === '') {
                continue;
            }

            if (
                !is_string($id)
                || !preg_match($uuidRegex, $id)
            ) {
                $errors[] = ValidacioErrors::format($camp);
                continue;
            }

            $resultat[strtolower($id)] = $id;
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
    // UUID v7 I DATES
    // ==============================
    $uuid = RamseyUuid::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = Uuid::toString($uuidBytes);

    $dateCreated = new \DateTimeImmutable();
    $dateCreatedFormatted = $dateCreated->format('Y-m-d H:i:s');
    $dateModifiedFormatted = $dateCreated->format('Y-m-d H:i:s');

    // Els camps UUID opcionals buits s'emmagatzemen com a NULL.
    $subEtapaBytes = $subEtapaId !== ''
        ? Uuid::toBinary($subEtapaId)
        : null;

    $ciutatBytes = $ciutatId !== ''
        ? Uuid::toBinary($ciutatId)
        : null;

    $imatgeBytes = $imatgeId !== ''
        ? Uuid::toBinary($imatgeId)
        : null;

    // ==============================
    // INSERT + RELACIONS
    // ==============================
    try {
        $pdo->beginTransaction();

        // 1. Crear l'esdeveniment
        $sql = "
        INSERT INTO db_historia_esdeveniments (
            id,
            esdeNom,
            slug,
            esdeDataIDia,
            esdeDataIMes,
            esdeDataIAny,
            esdeDataFDia,
            esdeDataFMes,
            esdeDataFAny,
            sub_etapa_id,
            ciutat_id,
            imatge_id,
            dateCreated,
            dateModified,
            descripcio
        ) VALUES (
            :id,
            :esdeNom,
            :slug,
            :esdeDataIDia,
            :esdeDataIMes,
            :esdeDataIAny,
            :esdeDataFDia,
            :esdeDataFMes,
            :esdeDataFAny,
            :sub_etapa_id,
            :ciutat_id,
            :imatge_id,
            :dateCreated,
            :dateModified,
            :descripcio
        )
    ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
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

        $stmt->bindValue(
            ':dateCreated',
            $dateCreatedFormatted,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':dateModified',
            $dateModifiedFormatted,
            PDO::PARAM_STR
        );

        $stmt->execute();

        // 2. Relacionar les persones
        if ($personesIds !== []) {
            $sqlPersona = "
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

            $stmtPersona = $pdo->prepare($sqlPersona);

            foreach ($personesIds as $personaId) {
                $relacioId = RamseyUuid::uuid7()->getBytes();

                $stmtPersona->bindValue(
                    ':id',
                    $relacioId,
                    PDO::PARAM_LOB
                );

                $stmtPersona->bindValue(
                    ':esdeveniment_id',
                    $uuidBytes,
                    PDO::PARAM_LOB
                );

                $stmtPersona->bindValue(
                    ':persona_id',
                    Uuid::toBinary($personaId),
                    PDO::PARAM_LOB
                );

                $stmtPersona->execute();
            }
        }

        // 3. Relacionar les organitzacions
        if ($organitzacionsIds !== []) {
            $sqlOrganitzacio = "
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

            $stmtOrganitzacio = $pdo->prepare($sqlOrganitzacio);

            foreach ($organitzacionsIds as $organitzacioId) {
                $relacioId = RamseyUuid::uuid7()->getBytes();

                $stmtOrganitzacio->bindValue(
                    ':id',
                    $relacioId,
                    PDO::PARAM_LOB
                );

                $stmtOrganitzacio->bindValue(
                    ':esdeveniment_id',
                    $uuidBytes,
                    PDO::PARAM_LOB
                );

                $stmtOrganitzacio->bindValue(
                    ':organitzacio_id',
                    Uuid::toBinary($organitzacioId),
                    PDO::PARAM_LOB
                );

                $stmtOrganitzacio->execute();
            }
        }

        // 4. Confirmar totes les operacions
        $pdo->commit();

        Response::success(
            MissatgesAPI::success('create'),
            [
                'id' => $uuidString,
                'esdeNom' => $nom,
                'slug' => $slug,
                'persones' => $personesIds,
                'organitzacions' => $organitzacionsIds,
            ],
            httpCode: 201
        );
        exit;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Registra el detall intern al servidor, no en la resposta pública.
        error_log(
            '[POST esdeveniment] ' . $e->getMessage()
        );

        var_dump(
            '[POST esdeveniment] ' . $e->getMessage()
        );
        exit;

        Response::error(
            MissatgesAPI::error('internal_error'),
            [],
            500
        );
        exit;
    }

    // b) Inserir persona / carrec
} else if (isset($_GET['personaCarrec'])) {


    // ==============================
    // POST ORGANITZACIO
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

    $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

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
    // UUID v7
    // ==============================
    $uuid = RamseyUuid::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = Uuid::toString($uuidBytes);

    // Convertir UUID de text a BINARY(16)
    $paisBytes = Uuid::toBinary((string) $paisId);
    $ciutatBytes = Uuid::toBinary((string) $ciutatId);
    $subEtapaBytes = Uuid::toBinary((string) $subEtapaId);
    $ideologiaBytes = Uuid::toBinary((string) $ideologiaId);
    $imatgeBytes = Uuid::toBinary((string) $imatgeId);

    // Dates
    $dateCreated = new \DateTimeImmutable();
    $dateCreatedFormatted = $dateCreated->format('Y-m-d H:i:s');

    // ==============================
    // INSERT
    // ==============================
    $sql = "INSERT INTO " . Tables::DB_HISTORIA_ORGANITZACIONS . " (
                id,
                organitzacio,
                slug,
                sigla,
                any_fundacio,
                any_dissolucio,
                pais_id,
                ciutat_id,
                sub_etapa_id,
                tipus,
                ideologia_id,
                imatge_id,
                dateCreated
            ) VALUES (
                :id,
                :organitzacio,
                :slug,
                :sigla,
                :any_fundacio,
                :any_dissolucio,
                :pais_id,
                :ciutat_id,
                :sub_etapa_id,
                :tipus,
                :ideologia_id,
                :imatge_id,
                :dateCreated
            )";

    try {
        $stmt = $pdo->prepare($sql);

        // UUID binari
        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
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
        $stmt->bindValue(':any_fundacio', (int) $anyFundacio, PDO::PARAM_INT);
        $stmt->bindValue(':any_dissolucio', (int) $anyDissolucio, PDO::PARAM_INT);

        // Dates
        $stmt->bindValue(':dateCreated', $dateCreatedFormatted, PDO::PARAM_STR);

        if ($stmt->execute()) {
            Response::success(
                MissatgesAPI::success('create'),
                [
                    'id' => $uuidString,
                    'organitzacio' => $organitzacio,
                    'slug' => $slugOrg,
                    'sigla' => $sigla,
                ],
                httpCode: 201
            );
            exit;
        }

        Response::error(
            MissatgesAPI::error('db_error'),
            [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ],
            httpCode: 500
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            httpCode: 500
        );
        exit;
    }

    /**
     * POST : Crear slot curs-article (db_historia_oberta_articles)
     * URL: /api/historia/post/createCursArticle
     */
} else if ($slug === 'createCursArticle') {
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
    // VALIDACIÓ CURS
    // =========================================================

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
    // ERROR VALIDACIÓ
    // =========================================================

    if (!empty($errors)) {
        Response::error(
            MissatgesAPI::error('invalid_data'),
            $errors,
            400
        );
        return;
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
        // GENERAR ID
        // =====================================================

        $id = Uuid::generate();


        // =====================================================
        // INSERT
        // =====================================================

        $sql = sprintf(
            "INSERT INTO %s (
                id,
                article_ca_id,
                article_es_id,
                article_en_id,
                article_it_id,
                article_fr_id,
                curs_id,
                ordre
            ) VALUES (
                :id,
                :article_ca_id,
                :article_es_id,
                :article_en_id,
                :article_it_id,
                :article_fr_id,
                :curs_id,
                :ordre
            )",
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
            MissatgesAPI::success('create'),
            [
                'id' => $id,
            ],
            httpCode: 201
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    return;
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

        $curs = trim((string) ($input['curs'] ?? ''));
        $resum = trim((string) ($input['resum'] ?? ''));
        $descripcio = isset($input['descripcio'])
            ? trim((string) $input['descripcio'])
            : null;
        $slugCurs = trim((string) ($input['slug'] ?? ''));
        $imgId = trim((string) ($input['img_id'] ?? ''));
        $ordre = $input['ordre'] ?? null;

        $errors = [];

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
            if (
                filter_var($ordre, FILTER_VALIDATE_INT) === false
            ) {
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

        $id = Uuid::generate();

        $sql = "
            INSERT INTO " . qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo) . "
            (
                id,
                curs,
                resum,
                descripcio,
                slug,
                img_id,
                ordre
            )
            VALUES
            (
                :id,
                :curs,
                :resum,
                :descripcio,
                :slug,
                :img_id,
                :ordre
            )
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
            MissatgesAPI::success('create'),
            [
                'id' => $id,
            ],
            httpCode: 201
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    return;
    // si no hi ha cap endpoint valid, mostrar error:
} else {
    // response output - data error
    $response['status'] = 'error';
    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
}
