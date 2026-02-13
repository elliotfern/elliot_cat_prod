<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0] ?? null;

$db  = new Database();
$pdo = $db->getPdo();

/*
 * BACKEND AGENDA
 * GET ESDEVENIMENTS
 */

// Configuración de cabeceras para aceptar JSON y responder JSON
// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * GET : Esdeveniment per ID
 * URL: https://elliot.cat/api/agenda/get/esdevenimentId?id=1
 */
if ($slug === "esdevenimentId") {

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$id) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Paràmetre id requerit'],
            400
        );
        return;
    }

    $sql = <<<SQL
            SELECT 
                e.id_esdeveniment,
                e.titol,
                e.descripcio,
                e.tipus,
                e.lloc,
                e.data_inici,
                e.data_fi,
                e.tot_el_dia,
                e.estat,
                e.creat_el,
                e.actualitzat_el
            FROM %s AS e
            WHERE e.id_esdeveniment = :id
            LIMIT 1
        SQL;

    $query = sprintf(
        $sql,
        qi(Tables::AGENDA_ESDEVENIMENTS, $pdo)
    );

    try {
        $params = [':id' => $id];
        $row    = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $row,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    /**
     * GET : Llistat d’esdeveniments futurs
     * URL: https://tu-dominio/api/agenda/get/esdevenimentsFuturs?usuari_id=1
     */
} else if ($slug === "esdevenimentsFuturs") {
    $usuariId = 1;

    if (!$usuariId) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Paràmetre requerit: usuari_id'],
            400
        );
        return;
    }

    $sql = <<<SQL
            SELECT 
                e.id_esdeveniment,
                e.titol,
                e.descripcio,
                e.tipus,
                e.lloc,
                e.data_inici,
                e.data_fi,
                e.tot_el_dia,
                e.estat,
                e.creat_el,
                e.actualitzat_el
            FROM %s AS e
            WHERE 
                 e.data_inici >= NOW()
            ORDER BY e.data_inici ASC
        SQL;

    $query = sprintf(
        $sql,
        qi(Tables::AGENDA_ESDEVENIMENTS, $pdo)
    );

    try {

        $rows = $db->getData($query);

        // Para agenda puede tener sentido devolver [] con 200 aunque no haya nada
        Response::success(
            MissatgesAPI::success('get'),
            $rows ?: [],
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    /**
     * GET : Llistat d’esdeveniments per rang de dates
     * URL: https://tu-dominio/api/agenda/get/esdevenimentsRang?usuari_id=1&from=2025-01-01&to=2025-01-31
     */
} else if ($slug === "esdevenimentsRang") {

    $usuariId = 1;
    $from     = $_GET['from'] ?? null; // format: YYYY-MM-DD
    $to       = $_GET['to']   ?? null; // format: YYYY-MM-DD

    if (!$usuariId || !$from || !$to) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Paràmetres requerits: usuari_id, from, to'],
            400
        );
        return;
    }

    // Normalizamos a rang complet del dia
    $fromDateTime = $from . ' 00:00:00';
    $toDateTime   = $to   . ' 23:59:59';

    $sql = <<<SQL
            SELECT 
                e.id_esdeveniment,
                e.titol,
                e.descripcio,
                e.tipus,
                e.lloc,
                e.data_inici,
                e.data_fi,
                e.tot_el_dia,
                e.estat,
                e.creat_el,
                e.actualitzat_el
            FROM %s AS e
            WHERE 
                e.data_inici >= :from
                AND e.data_inici <= :to
            ORDER BY e.data_inici ASC
        SQL;

    // Cumpleaños virtuales desde contactos (data_naixement = DATE)
    $sqlBirthdays = <<<SQL
        SELECT
        (-c.id) AS id_esdeveniment,
        CONCAT('🎂 ', c.nom, ' ', c.cognoms) AS titol,
        NULL AS descripcio,
        'aniversari' AS tipus,
        NULL AS lloc,

        CONCAT(occ.ymd, ' 00:00:00') AS data_inici,
        CONCAT(occ.ymd, ' 23:59:59') AS data_fi,

        1 AS tot_el_dia,
        'confirmat' AS estat,
        NOW() AS creat_el,
        NOW() AS actualitzat_el,
        'contacte' AS origen,
        c.id AS contacte_id
        FROM db_contactes c
        CROSS JOIN (
        SELECT
            CASE
            WHEN MONTH(c.data_naixement) = 2 AND DAY(c.data_naixement) = 29
                THEN DATE(LAST_DAY(CONCAT(YEAR(:fromDate), '-02-01')))
            ELSE DATE(CONCAT(
                YEAR(:fromDate), '-',
                LPAD(MONTH(c.data_naixement), 2, '0'), '-',
                LPAD(DAY(c.data_naixement), 2, '0')
            ))
            END AS ymd
        ) AS occ
        WHERE
        c.data_naixement IS NOT NULL
        AND occ.ymd BETWEEN DATE(:fromDate) AND DATE(:toDate)
        SQL;

    $query = sprintf(
        $sql,
        qi(Tables::AGENDA_ESDEVENIMENTS, $pdo)
    );

    try {
        $params = [
            ':from'      => $fromDateTime,
            ':to'        => $toDateTime,
        ];

        // 1) eventos reales (tu query actual)
        $events = $db->getData($query, $params) ?: [];

        // 2) Aniversari
        $birthdays = $db->getData(
            $sqlBirthdays,
            [
                ':fromDate' => $fromDateTime, // 'YYYY-MM-DD 00:00:00'
                ':toDate'   => $toDateTime,   // 'YYYY-MM-DD 23:59:59'
            ]
        ) ?: [];

        if (empty($rows)) {
            // Para un calendario vacío puede ser útil devolver 200 con []
            Response::success(
                MissatgesAPI::success('get'),
                [],
                200
            );
            return;
        }

        // 3) merge + ordenar por data_inici
        $all = array_merge($events, $birthdays);
        usort($all, fn($a, $b) => strcmp((string)$a['data_inici'], (string)$b['data_inici']));

        Response::success(MissatgesAPI::success('get'), $all, 200);
        return;
    } catch (PDOException $e) {
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
