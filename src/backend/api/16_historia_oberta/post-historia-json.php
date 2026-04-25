<?php

use Ramsey\Uuid\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

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

function slugify($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    return trim($text, '-');
}

if (isset($_GET['importDades'])) {

    $filePath = 'https://elliot.cat/dades.json';

    $json = @file_get_contents($filePath);

    if ($json === false) {
        Response::error(MissatgesAPI::error('not_found'), ['file' => 'dades.json not accessible'], 404);
        exit;
    }

    $events = json_decode($json, true);

    if (!is_array($events)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid format'], 400);
        exit;
    }

    global $conn;

    try {
        $conn->beginTransaction();

        $sql = "
            INSERT INTO " . Tables::HISTORIA_ESDEVENIMENTS . " (
                id,
                esdeNom,
                slug,
                img,
                esdeDataIDia,
                esdeDataIMes,
                esdeDataIAny,
                esdeDataFDia,
                esdeDataFMes,
                esdeDataFAny,
                esSubEtapa,
                esdeCiutat,
                descripcio,
                dateCreated,
                dateModified
            ) VALUES (
                :id,
                :esdeNom,
                :slug,
                :img,
                :esdeDataIDia,
                :esdeDataIMes,
                :esdeDataIAny,
                :esdeDataFDia,
                :esdeDataFMes,
                :esdeDataFAny,
                NULL,
                NULL,
                NULL,
                CURDATE(),
                CURDATE()
            )
        ";

        $stmt = $conn->prepare($sql);

        foreach ($events as $e) {

            $uuid = Uuid::uuid7();
            $idBin = $uuid->getBytes();

            $nom = $e['Event'] ?? '';

            if ($nom === '') continue;

            $slug = slugify($nom);

            // imagen fija o null (según tu sistema)
            $img = 0;

            $stmt->bindValue(':id', $idBin, PDO::PARAM_LOB);
            $stmt->bindValue(':esdeNom', $nom, PDO::PARAM_STR);
            $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
            $stmt->bindValue(':img', $img, PDO::PARAM_INT);

            // 🔥 FECHAS DIRECTAS (SIN PARSING)
            $stmt->bindValue(':esdeDataFDia', $e['esdeDataFDia'], $e['esdeDataFDia'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':esdeDataFMes', $e['esdeDataFMes'], $e['esdeDataFMes'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':esdeDataFAny', $e['esdeDataFAny'], PDO::PARAM_INT);

            $stmt->bindValue(':esdeDataIDia', $e['esdeDataIDia'], $e['esdeDataIDia'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':esdeDataIMes', $e['esdeDataIMes'], $e['esdeDataIMes'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':esdeDataIAny', $e['esdeDataIAny'], PDO::PARAM_INT);


            $stmt->execute();
        }

        $conn->commit();

        Response::success(
            MissatgesAPI::success('import'),
            ['imported' => count($events)],
            200
        );
        exit;
    } catch (\Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage()
            ],
            500
        );
        exit;
    }
}

Response::error(MissatgesAPI::error('bad_request'), [], 400);
exit;
