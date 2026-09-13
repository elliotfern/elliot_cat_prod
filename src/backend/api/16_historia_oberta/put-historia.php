<?php

declare(strict_types=1);

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;

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

    $esdeNom       = !empty($data['esdeNom']) ? $data['esdeNom'] : ($hasError = true);
    $slug          = !empty($data['slug']) ? $data['slug'] : ($hasError = true);
    $esdeDataIDia  = isset($data['esdeDataIDia']) ? (int) $data['esdeDataIDia'] : null;
    $esdeDataIMes  = isset($data['esdeDataIMes']) ? (int) $data['esdeDataIMes'] : null;
    $esdeDataIAny  = isset($data['esdeDataIAny']) ? (int) $data['esdeDataIAny'] : ($hasError = true);
    $esdeDataFDia  = isset($data['esdeDataFDia']) ? (int) $data['esdeDataFDia'] : null;
    $esdeDataFMes  = isset($data['esdeDataFMes']) ? (int) $data['esdeDataFMes'] : null;
    $esdeDataFAny  = isset($data['esdeDataFAny']) ? (int) $data['esdeDataFAny'] : null;
    $esSubEtapa    = isset($data['esSubEtapa']) ? (int) $data['esSubEtapa'] : null;
    $esdeCiutat = !empty($data['esdeCiutat'])
        ? uuid::toBinary($data['esdeCiutat'])
        : null;

    $img           = !empty($data['img']) ? $data['img'] : '';

    $id = !empty($data['id']) ? uuid::toBinary($data['id']) : ($hasError = true);
    $timestamp = date('Y-m-d');
    $dateModified = $timestamp;

    if (!$hasError) {
        global $conn;

        $sql = "UPDATE db_historia_esdeveniments 
        SET esdeNom = :esdeNom, 
            slug = :slug, 
            esdeDataIDia = :esdeDataIDia, 
            esdeDataIMes = :esdeDataIMes, 
            esdeDataIAny = :esdeDataIAny, 
            esdeDataFDia = :esdeDataFDia, 
            esdeDataFMes = :esdeDataFMes, 
            esdeDataFAny = :esdeDataFAny, 
            esSubEtapa = :esSubEtapa,
            img = :img,
            esdeCiutat = :esdeCiutat,
            dateModified = :dateModified
        WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":esdeNom", $esdeNom, PDO::PARAM_STR);
        $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
        $stmt->bindParam(":esdeDataIDia", $esdeDataIDia, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataIMes", $esdeDataIMes, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataIAny", $esdeDataIAny, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataFDia", $esdeDataFDia, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataFMes", $esdeDataFMes, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataFAny", $esdeDataFAny, PDO::PARAM_INT);
        $stmt->bindParam(":esSubEtapa", $esSubEtapa, PDO::PARAM_INT);
        $stmt->bindParam(":esdeCiutat", $esdeCiutat, PDO::PARAM_LOB);
        $stmt->bindParam(":img", $img, PDO::PARAM_INT);
        $stmt->bindParam(":dateModified", $dateModified, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_LOB);

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


    // b) Inserir esdeveniment/persona
} else if (isset($_GET['esdevenimentPersona'])) {

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

    $idEsdev     = !empty($data['idEsdev']) ? $data['idEsdev'] : ($hasError = true);
    $idPersona   = !empty($data['idPersona']) ? $data['idPersona'] : ($hasError = true);
    $id          = !empty($data['id']) ? $data['id'] : ($hasError = true);

    if (!$hasError) {

        global $conn;
        $sql = "UPDATE db_historia_esdeveniment_persones 
        SET  idEsdev = :idEsdev, 
            idPersona = :idPersona
        WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":idEsdev", $idEsdev, PDO::PARAM_INT);
        $stmt->bindParam(":idPersona", $idPersona, PDO::PARAM_INT);
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

    // b) modificar esdeveniment/organitzacio
} else if (isset($_GET['esdevenimentOrganitzacio'])) {

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

    $idEsde  = !empty($data['idEsde']) ? $data['idEsde'] : ($hasError = true);
    $idOrg   = !empty($data['idOrg']) ? $data['idOrg'] : ($hasError = true);
    $id      = !empty($data['id']) ? $data['id'] : ($hasError = true);

    if (!$hasError) {
        global $conn;
        $sql = "UPDATE db_historia_esdeveniment_organitzacio 
        SET idEsde = :idEsde, 
            idOrg = :idOrg
        WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":idEsde", $idEsde, PDO::PARAM_INT);
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

    // b) Inserir organitzacio
} else if (isset($_GET['organitzacio'])) {

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

    $nomOrg = !empty($data['nomOrg']) ? $data['nomOrg'] : ($hasError = true);
    $nomOrgCast = !empty($data['nomOrgCast']) ? $data['nomOrgCast'] : ($hasError = false);
    $nomOrgEng = !empty($data['nomOrgEng']) ? $data['nomOrgEng'] : ($hasError = false);
    $nomOrgIt = !empty($data['nomOrgIt']) ? $data['nomOrgIt'] : ($hasError = false);
    $slug = !empty($data['slug']) ? $data['slug'] : ($hasError = true);
    $orgSig = !empty($data['orgSig']) ? $data['orgSig'] : ($hasError = false);
    $dataFunda = !empty($data['dataFunda']) ? $data['dataFunda'] : ($hasError = true);
    $dataDiss = !empty($data['dataDiss']) ? $data['dataDiss'] : ($hasError = false);
    $orgPais = !empty($data['orgPais']) ? $data['orgPais'] : ($hasError = true);
    $orgCiutat = !empty($data['orgCiutat']) ? $data['orgCiutat'] : ($hasError = true);
    $orgSubEtapa = !empty($data['orgSubEtapa']) ? $data['orgSubEtapa'] : ($hasError = true);
    $orgTipus = !empty($data['orgTipus']) ? $data['orgTipus'] : ($hasError = true);
    $orgIdeologia = !empty($data['orgIdeologia']) ? $data['orgIdeologia'] : ($hasError = false);
    $img = !empty($data['img']) ? $data['img'] : ($hasError = true);
    $id = !empty($data['id']) ? $data['id'] : ($hasError = true);

    $timestamp = date('Y-m-d');
    $dateModified = $timestamp;

    if (!$hasError) {
        global $conn;
        $sql = "UPDATE db_historia_organitzacions
        SET nomOrg = :nomOrg,
            nomOrgCast = :nomOrgCast,
            nomOrgEng = :nomOrgEng,
            nomOrgIt = :nomOrgIt,
            slug = :slug,
            orgSig = :orgSig,
            dataFunda = :dataFunda,
            dataDiss = :dataDiss,
            orgPais = :orgPais,
            orgCiutat = :orgCiutat,
            orgSubEtapa = :orgSubEtapa,
            orgTipus = :orgTipus,
            orgIdeologia = :orgIdeologia,
            img = :img,
            dateModified = :dateModified
            WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":nomOrg", $nomOrg, PDO::PARAM_STR);
        $stmt->bindParam(":nomOrgCast", $nomOrgCast, PDO::PARAM_STR);
        $stmt->bindParam(":nomOrgEng", $nomOrgEng, PDO::PARAM_STR);
        $stmt->bindParam(":nomOrgIt", $nomOrgIt, PDO::PARAM_STR);
        $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
        $stmt->bindParam(":orgSig", $orgSig, PDO::PARAM_STR);
        $stmt->bindParam(":dataFunda", $dataFunda, PDO::PARAM_STR);
        $stmt->bindParam(":dataDiss", $dataDiss, PDO::PARAM_STR);
        $stmt->bindParam(":orgPais", $orgPais, PDO::PARAM_INT);
        $stmt->bindParam(":orgCiutat", $orgCiutat, PDO::PARAM_INT);
        $stmt->bindParam(":orgSubEtapa", $orgSubEtapa, PDO::PARAM_INT);
        $stmt->bindParam(":orgTipus", $orgTipus, PDO::PARAM_INT);
        $stmt->bindParam(":orgIdeologia", $orgIdeologia, PDO::PARAM_INT);
        $stmt->bindParam(":img", $img, PDO::PARAM_INT);
        $stmt->bindParam(":dateModified", $dateModified, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);

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

    /**
     * PUT : Actualitzar slot curs-article (db_historia_oberta_articles)
     * URL: /api/historia/put/updateCursArticle
     * BODY:
     * {
     *   "id": 12,
     *   "curs": 3,
     *   "ordre": 2,
     *   "ca": 123,
     *   "es": 456,
     *   "en": null,
     *   "fr": null,
     *   "it": null
     * }
     */
} else if ($slug === 'updateCursArticle') {

    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        return;
    }

    $errors = [];

    $optIntOrNull = static function ($v): ?int {
        if ($v === null) return null;
        if ($v === '') return null;
        if (!is_numeric($v)) return null;
        return (int)$v;
    };

    $requireInt = static function (array $data, string $key, array &$errors): ?int {
        if (!array_key_exists($key, $data)) {
            $errors[$key] = 'required';
            return null;
        }
        if (!is_numeric($data[$key])) {
            $errors[$key] = 'must_be_int';
            return null;
        }
        $n = (int)$data[$key];
        if ($n <= 0) {
            $errors[$key] = 'must_be_gt_0';
            return null;
        }
        return $n;
    };

    $id    = $requireInt($data, 'id', $errors);
    $curs  = $requireInt($data, 'curs', $errors);
    $ordre = $requireInt($data, 'ordre', $errors);

    $ca = $optIntOrNull($data['ca'] ?? null);
    $es = $optIntOrNull($data['es'] ?? null);
    $en = $optIntOrNull($data['en'] ?? null);
    $fr = $optIntOrNull($data['fr'] ?? null);
    $it = $optIntOrNull($data['it'] ?? null);

    foreach (['ca' => $ca, 'es' => $es, 'en' => $en, 'fr' => $fr, 'it' => $it] as $k => $v) {
        if ($v !== null && $v <= 0) $errors[$k] = 'must_be_gt_0_or_null';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        return;
    }

    try {
        // Existe slot?
        $sqlSlot = sprintf(
            "SELECT id FROM %s WHERE id = :id LIMIT 1",
            qi(Tables::DB_HISTORIA_OBERTA_ARTICLES, $pdo)
        );
        $slot = $db->getData($sqlSlot, [':id' => $id], true);
        if (empty($slot)) {
            Response::error(MissatgesAPI::error('not_found'), ['slot not found'], 404);
            return;
        }

        // Existe curs?
        $sqlCurs = sprintf(
            "SELECT id FROM %s WHERE id = :id LIMIT 1",
            qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo)
        );
        $exists = $db->getData($sqlCurs, [':id' => $curs], true);
        if (empty($exists)) {
            Response::error(MissatgesAPI::error('invalid_data'), ['curs' => 'not_found'], 400);
            return;
        }

        // Validar blog ids si vienen
        $validateBlog = static function (\PDO $pdo, Database $db, int $blogId, int $expectedLang): bool {
            $q = sprintf(
                "SELECT id FROM %s WHERE id = :id AND lang = :lang AND post_type = 'historia_oberta' LIMIT 1",
                qi(Tables::BLOG, $pdo)
            );
            $r = $db->getData($q, [':id' => $blogId, ':lang' => $expectedLang], true);
            return !empty($r);
        };

        $langMap = ['ca' => 1, 'en' => 2, 'es' => 3, 'it' => 4, 'fr' => 7];

        foreach (['ca' => $ca, 'es' => $es, 'en' => $en, 'fr' => $fr, 'it' => $it] as $k => $v) {
            if ($v !== null) {
                $expected = $langMap[$k];
                if (!$validateBlog($pdo, $db, $v, $expected)) {
                    Response::error(MissatgesAPI::error('invalid_data'), [$k => 'blog_not_found_or_lang_mismatch'], 400);
                    return;
                }
            }
        }

        // Update
        $sql = <<<SQL
            UPDATE %s
            SET
                ca = :ca,
                es = :es,
                fr = :fr,
                en = :en,
                it = :it,
                curs = :curs,
                ordre = :ordre
            WHERE id = :id
            LIMIT 1
        SQL;

        $q = sprintf($sql, qi(Tables::DB_HISTORIA_OBERTA_ARTICLES, $pdo));
        $stmt = $pdo->prepare($q);

        $bindNullableInt = static function (\PDOStatement $st, string $param, ?int $val): void {
            if ($val === null) $st->bindValue($param, null, PDO::PARAM_NULL);
            else $st->bindValue($param, $val, PDO::PARAM_INT);
        };

        $bindNullableInt($stmt, ':ca', $ca);
        $bindNullableInt($stmt, ':es', $es);
        $bindNullableInt($stmt, ':fr', $fr);
        $bindNullableInt($stmt, ':en', $en);
        $bindNullableInt($stmt, ':it', $it);

        $stmt->bindValue(':curs', $curs, PDO::PARAM_INT);
        $stmt->bindValue(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            Response::error(MissatgesAPI::error('errorBD'), [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ], 500);
            return;
        }

        Response::success(MissatgesAPI::success('update'), ['id' => $id], httpCode: 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    return;
    // si no hi ha cap endpoint valid, mostrar error:

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
