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
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

// Dominio permitido
$allowedOrigin = APP_DOMAIN;

// Verificar referer
checkReferer($allowedOrigin);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * GET : Esdeveniment per ID
 * URL: https://tu-dominio/api/agenda/get/esdevenimentId?id=1
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
                e.usuari_id,
                e.titol,
                e.descripcio,
                e.tipus,
                e.lloc,
                e.url_videotrucada,
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
    $usuariId = isset($_GET['usuari_id']) ? (int)$_GET['usuari_id'] : null;

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
                e.usuari_id,
                e.titol,
                e.descripcio,
                e.tipus,
                e.lloc,
                e.url_videotrucada,
                e.data_inici,
                e.data_fi,
                e.tot_el_dia,
                e.estat,
                e.creat_el,
                e.actualitzat_el
            FROM %s AS e
            WHERE 
                e.usuari_id  = :usuari_id
                AND e.data_inici >= NOW()
            ORDER BY e.data_inici ASC
        SQL;

    $query = sprintf(
        $sql,
        qi(Tables::AGENDA_ESDEVENIMENTS, $pdo)
    );

    try {
        $params = [
            ':usuari_id' => $usuariId,
        ];

        $rows = $db->getData($query, $params);

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

    $usuariId = isset($_GET['usuari_id']) ? (int)$_GET['usuari_id'] : null;
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
                e.usuari_id,
                e.titol,
                e.descripcio,
                e.tipus,
                e.lloc,
                e.url_videotrucada,
                e.data_inici,
                e.data_fi,
                e.tot_el_dia,
                e.estat,
                e.creat_el,
                e.actualitzat_el
            FROM %s AS e
            WHERE 
                e.usuari_id   = :usuari_id
                AND e.data_inici >= :from
                AND e.data_inici <= :to
            ORDER BY e.data_inici ASC
        SQL;

    $query = sprintf(
        $sql,
        qi(Tables::AGENDA_ESDEVENIMENTS, $pdo)
    );

    try {
        $params = [
            ':usuari_id' => $usuariId,
            ':from'      => $fromDateTime,
            ':to'        => $toDateTime,
        ];

        $rows = $db->getData($query, $params);

        if (empty($rows)) {
            // Para un calendario vacío puede ser útil devolver 200 con []
            Response::success(
                MissatgesAPI::success('get'),
                [],
                200
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $rows,
            200
        );
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
