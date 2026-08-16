<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;


/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

/*
 * BACKEND SALUT
 * GET SALUT
 */

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * GET : Llistat de medicaments
 * URL: https://elliot.cat/api/salut/get/llistatMedicaments
 */
if ($slug === "llistatMedicaments") {

    try {

        // De moment no hi ha taula a la BD per a això — dades d'exemple fixes.
        // Quan es creï la taula corresponent, substituir aquest bloc per una query real.
        $data = [
            [
                'id'          => '1',
                'patologia'   => 'Ipercolesterolemia',
                'medicaments' => 'EZETIMIBE e ATORVASTATINA DOC 10 mg/20 mg capsule rigide.',
                'dosis'       => '1 al dia',
                'recepta'     => 'Si',
            ],
        ];

        if (!$data) {
            Response::error(
                MissatgesAPI::error('notFound'),
                ['Esdeveniment no trobat'],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $data,
            httpCode: 200
        );
    } catch (Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else {
    // Slug no reconocido
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
