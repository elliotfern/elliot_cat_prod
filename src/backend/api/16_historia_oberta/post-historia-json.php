<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

header('Content-Type: application/json; charset=utf-8');

function slugify($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    return trim($text, '-');
}

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
            :dateCreated,
            :dateModified
        )
    ";

    $stmt = $conn->prepare($sql);

    $now = date('Y-m-d H:i:s');

    foreach ($events as $e) {
        $stmt->bindValue(':esdeNom', $e['esdeNom'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', slugify($e['esdeNom']), PDO::PARAM_STR);
        $stmt->bindValue(':img', 0, PDO::PARAM_INT);

        // fechas seguras (null-safe)
        $stmt->bindValue(':esdeDataIDia', $e['esdeDataIDia'] ?? null);
        $stmt->bindValue(':esdeDataIMes', $e['esdeDataIMes'] ?? null);
        $stmt->bindValue(':esdeDataIAny', $e['esdeDataIAny'] ?? null);

        $stmt->bindValue(':esdeDataFDia', $e['esdeDataFDia'] ?? null);
        $stmt->bindValue(':esdeDataFMes', $e['esdeDataFMes'] ?? null);
        $stmt->bindValue(':esdeDataFAny', $e['esdeDataFAny'] ?? null);

        $stmt->bindValue(':dateCreated', $now);
        $stmt->bindValue(':dateModified', $now);

        if (!$stmt->execute()) {
            throw new \Exception("Insert failed for event: " . $nom);
        }
    }

    $conn->commit();

    Response::success(
        MissatgesAPI::success('import'),
        ['imported' => count($events)],
        200
    );
    exit;
} catch (\Throwable $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    Response::error(
        MissatgesAPI::error('internal_error'),
        ['message' => $e->getMessage()],
        500
    );
    exit;
}
