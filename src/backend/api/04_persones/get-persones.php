<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;


// Siempre JSON
header('Content-Type: application/json; charset=utf-8');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// 1) Base de dades persones: Llistat complet
// ruta GET => "https://elliot.cat/api/persones/get/llistatPersones"
if (isset($_GET['type']) && $_GET['type'] == 'llistatPersones') {
    global $conn;

    // Consulta SQL base
    $query = "SELECT 
            a.id, a.nom, a.cognoms, a.slug, 
            a.anyNaixement AS yearBorn, a.anyDefuncio AS yearDie, 
            c.pais_cat,
            i.nameImg,
            GROUP_CONCAT(DISTINCT g.grup_ca ORDER BY g.grup_ca SEPARATOR ', ') AS grup
            FROM db_persones AS a
            LEFT JOIN db_countries AS c ON a.paisAutor = c.id
            LEFT JOIN db_img AS i ON a.img = i.id
            LEFT JOIN db_persones_grups_relacions AS rel ON a.id2 = rel.persona_id
            LEFT JOIN db_persones_grups AS g ON rel.grup_id = g.id
            WHERE a.visibilitat = 1
            GROUP BY a.id
            ORDER BY a.cognoms";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($query);

    $stmt->execute();

    // Obtener los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar los resultados como JSON
    echo json_encode($data);

    // ruta GET => "/api/persones/get/?persona=josep-fontana"
} elseif (isset($_GET['persona'])) {

    $autorSlug = $_GET['persona'] ?? null;

    try {
        if (empty($autorSlug)) {
            Response::error(MissatgesAPI::error('bad_request'), ['persona' => 'required'], 400);
            exit;
        }

        $db = new Database();

        $query = "SELECT LOWER(CONCAT_WS('-',
        SUBSTR(HEX(a.id), 1, 8),
        SUBSTR(HEX(a.id), 9, 4),
        SUBSTR(HEX(a.id), 13, 4),
        SUBSTR(HEX(a.id), 17, 4),
        SUBSTR(HEX(a.id), 21)
    )) AS id,
        a.cognoms,
        a.nom,
        p.pais_ca,
        a.any_naixement,
        a.any_defuncio,
        i.nameImg,
        i.alt,
        a.web,
        a.created_at,
        a.updated_at,
        a.descripcio,
        a.slug,
        a.img_id,
        a.sexe_id,
        a.mes_naixement,
        a.dia_naixement,
        a.mes_defuncio,
        a.dia_defuncio,
        c1.ciutat_ca AS ciutatNaixement,
        c2.ciutat_ca AS ciutatDefuncio,
        GROUP_CONCAT(
            DISTINCT LOWER(CONCAT_WS('-',
                SUBSTR(HEX(g.id), 1, 8),
                SUBSTR(HEX(g.id), 9, 4),
                SUBSTR(HEX(g.id), 13, 4),
                SUBSTR(HEX(g.id), 17, 4),
                SUBSTR(HEX(g.id), 21)
            ))
            ORDER BY g.grup_ca
            SEPARATOR ','
        ) AS grup_ids,
        GROUP_CONCAT(DISTINCT g.grup_ca ORDER BY g.grup_ca SEPARATOR ', ') AS grup,

    LOWER(CONCAT_WS('-',
        SUBSTR(HEX(a.pais_autor_id), 1, 8),
        SUBSTR(HEX(a.pais_autor_id), 9, 4),
        SUBSTR(HEX(a.pais_autor_id), 13, 4),
        SUBSTR(HEX(a.pais_autor_id), 17, 4),
        SUBSTR(HEX(a.pais_autor_id), 21)
    )) AS pais_autor_id,

    LOWER(CONCAT_WS('-',
        SUBSTR(HEX(a.ciutat_defuncio_id), 1, 8),
        SUBSTR(HEX(a.ciutat_defuncio_id), 9, 4),
        SUBSTR(HEX(a.ciutat_defuncio_id), 13, 4),
        SUBSTR(HEX(a.ciutat_defuncio_id), 17, 4),
        SUBSTR(HEX(a.ciutat_defuncio_id), 21)
    )) AS ciutat_defuncio_id,

    LOWER(CONCAT_WS('-',
        SUBSTR(HEX(a.ciutat_naixement_id), 1, 8),
        SUBSTR(HEX(a.ciutat_naixement_id), 9, 4),
        SUBSTR(HEX(a.ciutat_naixement_id), 13, 4),
        SUBSTR(HEX(a.ciutat_naixement_id), 17, 4),
        SUBSTR(HEX(a.ciutat_naixement_id), 21)
    )) AS ciutat_naixement_id

    FROM " . Tables::PERSONES . " AS a
    LEFT JOIN " . Tables::GEO_PAISOS . " AS p ON a.pais_autor_id = p.id
    LEFT JOIN " . Tables::IMG . " AS i ON a.img_id = i.id
    LEFT JOIN " . Tables::GEO_CIUTATS . " AS c1 ON a.ciutat_naixement_id = c1.id
    LEFT JOIN " . Tables::GEO_CIUTATS . " AS c2 ON a.ciutat_defuncio_id = c2.id
    LEFT JOIN " . Tables::PERSONES_GRUPS_RELACIONS . " AS rel ON a.id = rel.persona_id
    LEFT JOIN " . Tables::PERSONES_GRUPS . " AS g ON rel.grup_id = g.id
    WHERE a.slug = :slug
    GROUP BY a.id
    LIMIT 1";

        $params = [':slug' => $autorSlug];

        // getData(..., true) si tu helper soporta "single row".
        // Como tu ejemplo usa getData($query) a secas, lo hacemos igual:
        $rows = $db->getData($query, $params);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($rows, function (&$v) {
            if (!is_string($v)) return;

            // Quitar NULs (muy típicos si hubo UTF-32 / bytes raros)
            $v = str_replace("\0", '', $v);

            // Intentar normalizar a UTF-8 válido
            // 1) Si ya es UTF-8 válido, lo deja igual
            if (!mb_check_encoding($v, 'UTF-8')) {
                // 2) Intenta desde ISO-8859-1 (latin1) -> UTF-8 (común en legacy)
                $v2 = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $v);
                if ($v2 !== false) {
                    $v = $v2;
                } else {
                    // 3) Último recurso: limpia bytes inválidos asumiendo UTF-8
                    $v3 = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                    if ($v3 !== false) $v = $v3;
                }
            } else {
                // Aun siendo UTF-8 válido, limpia bytes raros si los hubiera
                $v2 = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                if ($v2 !== false) $v = $v2;
            }
        });

        if (empty($rows)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        // Como viene LIMIT 1, nos quedamos con la primera fila
        $result = $rows[0];

        // Convertir grup_ids CSV a array
        $result['grup_ids'] = !empty($result['grup_ids'])
            ? array_values(array_filter(explode(',', $result['grup_ids'])))
            : [];

        Response::success(MissatgesAPI::success('get'), $result, 200);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
