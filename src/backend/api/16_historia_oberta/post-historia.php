<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0] ?? '';

$db  = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// a) Inserir esdeveniment
if (isset($_GET['esdeveniment'])) {

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

    $esdeNom       = !empty($data['esdeNom']) ? data_input($data['esdeNom']) : ($hasError = true);
    $esdeNomCast   = !empty($data['esdeNomCast']) ? data_input($data['esdeNomCast']) : '';
    $esdeNomEng    = !empty($data['esdeNomEng']) ? data_input($data['esdeNomEng']) : '';
    $esdeNomIt     = !empty($data['esdeNomIt']) ? data_input($data['esdeNomIt']) : '';
    $slug          = !empty($data['slug']) ? data_input($data['slug']) : ($hasError = true);
    $esdeDataIDia  = isset($data['esdeDataIDia']) ? (int) $data['esdeDataIDia'] : null;
    $esdeDataIMes  = isset($data['esdeDataIMes']) ? (int) $data['esdeDataIMes'] : null;
    $esdeDataIAny  = isset($data['esdeDataIAny']) ? (int) $data['esdeDataIAny'] : ($hasError = true);
    $esdeDataFDia  = isset($data['esdeDataFDia']) ? (int) $data['esdeDataFDia'] : null;
    $esdeDataFMes  = isset($data['esdeDataFMes']) ? (int) $data['esdeDataFMes'] : null;
    $esdeDataFAny  = isset($data['esdeDataFAny']) ? (int) $data['esdeDataFAny'] : null;
    $esSubEtapa    = isset($data['esSubEtapa']) ? (int) $data['esSubEtapa'] : null;
    $esdeCiutat    = !empty($data['esdeCiutat']) ? data_input($data['esdeCiutat']) : '';
    $img           = !empty($data['img']) ? data_input($data['img']) : '';
    $descripcio    = !empty($data['descripcio']) ? data_input($data['descripcio']) : '';

    $timestamp = date('Y-m-d');
    $dateCreated = $timestamp;
    $dateModified = $timestamp;

    if (!$hasError) {
        global $conn;
        $sql = "INSERT INTO db_historia_esdeveniments 
        SET esdeNom = :esdeNom, 
            esdeNomCast = :esdeNomCast, 
            esdeNomEng = :esdeNomEng, 
            esdeNomIt = :esdeNomIt, 
            slug = :slug, 
            esdeDataIDia = :esdeDataIDia, 
            esdeDataIMes = :esdeDataIMes, 
            esdeDataIAny = :esdeDataIAny, 
            esdeDataFDia = :esdeDataFDia, 
            esdeDataFMes = :esdeDataFMes, 
            esdeDataFAny = :esdeDataFAny, 
            esSubEtapa = :esSubEtapa, 
            esdeCiutat = :esdeCiutat,
            img = :img,
            descripcio = :descripcio,
            dateCreated = :dateCreated,
            dateModified = :dateModified";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":esdeNom", $esdeNom, PDO::PARAM_STR);
        $stmt->bindParam(":esdeNomCast", $esdeNomCast, PDO::PARAM_STR);
        $stmt->bindParam(":esdeNomEng", $esdeNomEng, PDO::PARAM_STR);
        $stmt->bindParam(":esdeNomIt", $esdeNomIt, PDO::PARAM_STR);
        $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
        $stmt->bindParam(":esdeDataIDia", $esdeDataIDia, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataIMes", $esdeDataIMes, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataIAny", $esdeDataIAny, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataFDia", $esdeDataFDia, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataFMes", $esdeDataFMes, PDO::PARAM_INT);
        $stmt->bindParam(":esdeDataFAny", $esdeDataFAny, PDO::PARAM_INT);
        $stmt->bindParam(":esSubEtapa", $esSubEtapa, PDO::PARAM_INT);
        $stmt->bindParam(":esdeCiutat", $esdeCiutat, PDO::PARAM_STR);
        $stmt->bindParam(":img", $img, PDO::PARAM_INT);
        $stmt->bindParam(":descripcio", $descripcio, PDO::PARAM_STR);
        $stmt->bindParam(":dateCreated", $dateCreated, PDO::PARAM_STR);
        $stmt->bindParam(":dateModified", $dateModified, PDO::PARAM_STR);

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

    $idEsdev     = !empty($data['idEsdev']) ? data_input($data['idEsdev']) : ($hasError = true);
    $idPersona   = !empty($data['idPersona']) ? data_input($data['idPersona']) : ($hasError = true);

    if (!$hasError) {
        global $conn;
        $sql = "INSERT INTO db_historia_esdeveniment_persones 
        SET idEsdev = :idEsdev, 
            idPersona = :idPersona";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":idEsdev", $idEsdev, PDO::PARAM_INT);
        $stmt->bindParam(":idPersona", $idPersona, PDO::PARAM_INT);

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

    // b) Inserir esdeveniment/organitzacio
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

    $idEsde  = !empty($data['idEsde']) ? data_input($data['idEsde']) : ($hasError = true);
    $idOrg   = !empty($data['idOrg']) ? data_input($data['idOrg']) : ($hasError = true);

    if (!$hasError) {
        global $conn;
        $sql = "INSERT INTO db_historia_esdeveniment_organitzacio 
        SET idEsde = :idEsde, 
            idOrg = :idOrg";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":idEsde", $idEsde, PDO::PARAM_INT);
        $stmt->bindParam(":idOrg", $idOrg, PDO::PARAM_INT);

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

    // b) Inserir persona / carrec
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

    $idPersona = !empty($data['idPersona']) ? data_input($data['idPersona']) : ($hasError = true);
    $carrecNom = !empty($data['carrecNom']) ? data_input($data['carrecNom']) : ($hasError = true);
    $carrecNomCast = !empty($data['carrecNomCast']) ? data_input($data['carrecNomCast']) : ($hasError = false);
    $carrecNomEng = !empty($data['carrecNomEng']) ? data_input($data['carrecNomEng']) : ($hasError = false);
    $carrecNomIt = !empty($data['carrecNomIt']) ? data_input($data['carrecNomIt']) : ($hasError = false);
    $carrecInici = !empty($data['carrecInici']) ? data_input($data['carrecInici']) : ($hasError = true);
    $carrecFi = !empty($data['carrecFi']) ? data_input($data['carrecFi']) : ($hasError = false);
    $idOrg = !empty($data['idOrg']) ? data_input($data['idOrg']) : ($hasError = false);

    if (!$hasError) {
        global $conn;
        $sql = "INSERT INTO aux_persones_carrecs
        SET idOrg = :idOrg,
            idPersona = :idPersona,
            carrecNom = :carrecNom,
            carrecNomCast = :carrecNomCast,
            carrecNomEng = :carrecNomEng,
            carrecNomIt = :carrecNomIt,
            carrecInici = :carrecInici,
            carrecFi = :carrecFi";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":idPersona", $idPersona, PDO::PARAM_INT);
        $stmt->bindParam(":carrecNom", $carrecNom, PDO::PARAM_STR);
        $stmt->bindParam(":carrecNomCast", $carrecNomCast, PDO::PARAM_STR);
        $stmt->bindParam(":carrecNomEng", $carrecNomEng, PDO::PARAM_STR);
        $stmt->bindParam(":carrecNomIt", $carrecNomIt, PDO::PARAM_STR);
        $stmt->bindParam(":carrecInici", $carrecInici, PDO::PARAM_STR);
        $stmt->bindParam(":carrecFi", $carrecFi, PDO::PARAM_STR);
        $stmt->bindParam(":idOrg", $idOrg, PDO::PARAM_INT);

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

    $nomOrg = !empty($data['nomOrg']) ? data_input($data['nomOrg']) : ($hasError = true);
    $nomOrgCast = !empty($data['nomOrgCast']) ? data_input($data['nomOrgCast']) : ($hasError = false);
    $nomOrgEng = !empty($data['nomOrgEng']) ? data_input($data['nomOrgEng']) : ($hasError = false);
    $nomOrgIt = !empty($data['nomOrgIt']) ? data_input($data['nomOrgIt']) : ($hasError = false);
    $slug = !empty($data['slug']) ? data_input($data['slug']) : ($hasError = true);
    $orgSig = !empty($data['orgSig']) ? data_input($data['orgSig']) : ($hasError = false);
    $dataFunda = !empty($data['dataFunda']) ? data_input($data['dataFunda']) : ($hasError = true);
    $dataDiss = !empty($data['dataDiss']) ? data_input($data['dataDiss']) : ($hasError = false);
    $orgPais = !empty($data['orgPais']) ? data_input($data['orgPais']) : ($hasError = true);
    $orgCiutat = !empty($data['orgCiutat']) ? data_input($data['orgCiutat']) : ($hasError = true);
    $orgSubEtapa = !empty($data['orgSubEtapa']) ? data_input($data['orgSubEtapa']) : ($hasError = true);
    $orgTipus = !empty($data['orgTipus']) ? data_input($data['orgTipus']) : ($hasError = true);
    $orgIdeologia = !empty($data['orgIdeologia']) ? data_input($data['orgIdeologia']) : ($hasError = false);
    $img = !empty($data['img']) ? data_input($data['img']) : ($hasError = true);

    $timestamp = date('Y-m-d');
    $dateCreated = $timestamp;
    $dateModified = $timestamp;

    if (!$hasError) {
        global $conn;
        $sql = "INSERT INTO db_historia_organitzacions
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
            dateCreated = :dateCreated,
            dateModified = :dateModified";

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
        $stmt->bindParam(":dateCreated", $dateCreated, PDO::PARAM_STR);
        $stmt->bindParam(":dateModified", $dateModified, PDO::PARAM_STR);

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
     * POST : Crear slot curs-article (db_historia_oberta_articles)
     * URL: /api/historia/post/createCursArticle
     * BODY:
     * {
     *   "curs": 3,
     *   "ordre": 1,
     *   "ca": 123,
     *   "es": null,
     *   "en": null,
     *   "fr": null,
     *   "it": null
     * }
     */
} else if ($slug === 'createCursArticle') {

    // Auth (si lo usas en intranet)
    $userUuid = getAuthenticatedUserUuid();
    if (!$userUuid) {
        Response::error(MissatgesAPI::error('validacio'), ['Usuari no autenticat'], 401);
        return;
    }

    // (Opcional) exigir admin
    if (!isUserAdmin()) {
        Response::error(MissatgesAPI::error('validacio'), ['Permís denegat'], 403);
        return;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        return;
    }

    // Helpers
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

    $curs  = $requireInt($data, 'curs', $errors);
    $ordre = $requireInt($data, 'ordre', $errors);

    $ca = $optIntOrNull($data['ca'] ?? null);
    $es = $optIntOrNull($data['es'] ?? null);
    $en = $optIntOrNull($data['en'] ?? null);
    $fr = $optIntOrNull($data['fr'] ?? null);
    $it = $optIntOrNull($data['it'] ?? null);

    // Validaciones básicas de nullables: si viene no-null debe ser >0
    foreach (['ca' => $ca, 'es' => $es, 'en' => $en, 'fr' => $fr, 'it' => $it] as $k => $v) {
        if ($v !== null && $v <= 0) $errors[$k] = 'must_be_gt_0_or_null';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        return;
    }

    try {
        // (Opcional recomendado) Validar que el curs existe
        $sqlCurs = sprintf(
            "SELECT id FROM %s WHERE id = :id LIMIT 1",
            qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo)
        );
        $exists = $db->getData($sqlCurs, [':id' => $curs], true);
        if (empty($exists)) {
            Response::error(MissatgesAPI::error('invalid_data'), ['curs' => 'not_found'], 400);
            return;
        }

        // (Opcional recomendado) Validar artículos en db_blog (lang + post_type)
        $validateBlog = static function (\PDO $pdo, Database $db, int $blogId, int $expectedLang): bool {
            $q = sprintf(
                "SELECT id FROM %s WHERE id = :id AND lang = :lang AND post_type = 'historia_oberta' LIMIT 1",
                qi(Tables::BLOG, $pdo)
            );
            $r = $db->getData($q, [':id' => $blogId, ':lang' => $expectedLang], true);
            return !empty($r);
        };

        // lang mapping: ca=1 en=2 es=3 it=4 fr=7
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

        // Insert
        $sql = <<<SQL
            INSERT INTO %s (ca, es, fr, en, it, curs, ordre)
            VALUES (:ca, :es, :fr, :en, :it, :curs, :ordre)
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

        if (!$stmt->execute()) {
            Response::error(MissatgesAPI::error('errorBD'), [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ], 500);
            return;
        }

        $newId = (int)$pdo->lastInsertId();

        Response::success(MissatgesAPI::success('create'), ['id' => $newId], 201);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
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
