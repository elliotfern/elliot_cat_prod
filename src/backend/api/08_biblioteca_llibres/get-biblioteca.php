<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

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

// API GET Llistat llibres
// https://elliot.cat/api/biblioteca/totsLlibres
if ($slug === 'totsLlibres') {

    $tipusId = Uuid::toBinary('0197ac5b7106704b96c60728ace151f3');

    try {

        $sql = <<<SQL
        SELECT
            b.id,
            b.titol_original,
            b.titol_catala,
            b.any,
            b.slug,
            g.tema AS nomGenCat,
            sg.sub_tema
        FROM %s AS b
        LEFT JOIN %s AS sg ON b.sub_tema_id = sg.id
        LEFT JOIN %s AS g ON sg.tema_id = g.id
        LEFT JOIN %s AS be ON b.editorial_id = be.id
        WHERE b.tipus_id = :tipus_id
        ORDER BY b.titol_original ASC
        SQL;

        $queryBooks = sprintf(
            $sql,
            qi(Tables::LLIBRES, $pdo),
            qi(Tables::AUX_SUB_TEMES, $pdo),
            qi(Tables::AUX_TEMES, $pdo),
            qi(Tables::LLIBRES_EDITORIALS, $pdo)
        );

        // 1) Libros
        $params = [':tipus_id' => $tipusId];
        $books = $db->getData($queryBooks, $params);

        if (empty($books)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        // normalizar libros (UUID primero)
        foreach ($books as &$b) {
            $b['id'] = Uuid::toString($b['id']);
        }
        unset($b);

        // 2) Autores
        $sql = <<<SQL
            SELECT
                la.llibre_id,
                la.autor_id,
                a.nom,
                a.cognoms,
                a.slug
            FROM %s AS la
            INNER JOIN %s AS a ON a.id = la.autor_id
            SQL;

        $queryAuthors = sprintf(
            $sql,
            qi(Tables::LLIBRES_AUTORS, $pdo),
            qi(Tables::PERSONES, $pdo)
        );

        $authors = $db->getData($queryAuthors);

        foreach ($authors as &$a) {
            $a['llibre_id'] = Uuid::toString($a['llibre_id']);
            $a['autor_id']  = Uuid::toString($a['autor_id']);
        }
        unset($a);

        // 3) Col·leccions (grup) — ahora N:M vía taula intermèdia
        $sql = <<<SQL
            SELECT
                lg.llibre_id,
                lg.grup_id,
                g.nom,
                g.slug
            FROM %s AS lg
            INNER JOIN %s AS g ON g.id = lg.grup_id
            SQL;

        $queryGrups = sprintf(
            $sql,
            qi(Tables::LLIBRES_GRUP_LLIBRES, $pdo),
            qi(Tables::LLIBRES_GRUP, $pdo)
        );

        $grups = $db->getData($queryGrups);

        foreach ($grups as &$g) {
            $g['llibre_id'] = Uuid::toString($g['llibre_id']);
            $g['grup_id']   = Uuid::toString($g['grup_id']);
        }
        unset($g);

        // 3.5) Etiquetes — misma lógica que grups, query aparte
        $sql = <<<SQL
            SELECT
                le.llibre_id,
                le.etiqueta_id,
                e.nom,
                e.slug
            FROM %s AS le
            INNER JOIN %s AS e ON e.id = le.etiqueta_id
            SQL;

        $queryEtiquetes = sprintf(
            $sql,
            qi(Tables::LLIBRES_ETIQUETES_LLIBRES, $pdo),
            qi(Tables::LLIBRES_ETIQUETES, $pdo)
        );

        $etiquetes = $db->getData($queryEtiquetes);

        foreach ($etiquetes as &$e) {
            $e['llibre_id']   = Uuid::toString($e['llibre_id']);
            $e['etiqueta_id'] = Uuid::toString($e['etiqueta_id']);
        }
        unset($e);

        // 4) indexación
        $authorsByBook = [];
        foreach ($authors as $a) {
            $authorsByBook[$a['llibre_id']][] = [
                'id' => $a['autor_id'],
                'nom' => $a['nom'],
                'cognoms' => $a['cognoms'],
                'slug' => $a['slug'],
            ];
        }

        $grupsByBook = [];
        foreach ($grups as $g) {
            $grupsByBook[$g['llibre_id']][] = [
                'id' => $g['grup_id'],
                'nom' => $g['nom'],
                'slug' => $g['slug'],
            ];
        }

        $etiquetesByBook = [];
        foreach ($etiquetes as $e) {
            $etiquetesByBook[$e['llibre_id']][] = [
                'id' => $e['etiqueta_id'],
                'nom' => $e['nom'],
                'slug' => $e['slug'],
            ];
        }

        // 5) merge final
        foreach ($books as &$b) {
            $b['autors'] = $authorsByBook[$b['id']] ?? [];
            $b['grups']  = $grupsByBook[$b['id']] ?? [];
            $b['etiquetes'] = $etiquetesByBook[$b['id']] ?? [];
        }
        unset($b);

        // response
        Response::success(
            message: MissatgesAPI::success('get'),
            data: $books,
            httpCode: 200
        );
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }

    // 2) Llistat llibre Autors
    // ruta GET => "https://elliot.cat/api/biblioteca/get/llibreAutors"
} else if ($slug === 'llibreAutors') {

    $autorSlug = trim((string)($_GET['slug'] ?? ''));

    if ($slug === '') {
        Response::error(MissatgesAPI::error('bad_request'), ['slug' => 'required'], 400);
        exit;
    }

    try {

        $qBook = <<<SQL
                SELECT
                    b.id,
                    b.slug,
                    b.titol_original
                FROM %s AS b
                WHERE b.slug = :slug
                LIMIT 1
                SQL;

        $query = sprintf(
            $qBook,
            qi(Tables::LLIBRES, $pdo),
        );

        // ejecutar
        // 1) Libros
        $params = [':slug' => $autorSlug];
        $book = $db->getData($query, $params);

        if (!$book) {
            Response::error(MissatgesAPI::error('not_found'), ['slug' => $slug], 404);
            exit;
        }

        $qAuthors = <<<SQL
                        SELECT
                        la.id AS rel_id,
                        a.id AS id,
                        a.nom,
                        a.cognoms,
                        a.slug
                        FROM %s AS la
                        INNER JOIN %s  AS b ON b.id = la.llibre_id
                        INNER JOIN %s  AS a ON a.id = la.autor_id
                        WHERE b.slug = :slug
                        ORDER BY a.cognoms ASC, a.nom ASC
                SQL;

        $query = sprintf(
            $qAuthors,
            qi(Tables::LLIBRES_AUTORS, $pdo),
            qi(Tables::LLIBRES, $pdo),
            qi(Tables::PERSONES, $pdo),
        );

        $params = [':slug' => $autorSlug];
        $authors = $db->getData($query, $params);

        Response::success(
            MissatgesAPI::success('get'),
            [
                'llibre' => $book,
                'autors' => $authors,
            ],
            httpCode: 200
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }


    // 3) Llistat autors
    // ruta GET => "https://elliot.cat/api/biblioteca/get/totsAutors"
} else if ($slug === 'totsAutors') {

    try {
        $autorGroupUuidBin = Uuid::toBinary('0197b088-1a25-72c4-8b5b-d7e2ee27de7c');

        $query = <<<SQL
            SELECT 
                a.id,
                c.id AS idCountry,
                a.nom,
                a.cognoms,
                TRIM(CONCAT_WS(' ', a.nom, a.cognoms)) AS autor_nom_complet,
                a.slug,
                a.any_naixement,
                a.any_defuncio,
                c.pais_ca,
                i.nameImg,
                GROUP_CONCAT(DISTINCT g.grup_ca ORDER BY g.grup_ca SEPARATOR ', ') AS grup
            FROM %s AS a
            LEFT JOIN %s AS c ON a.pais_autor_id = c.id
            LEFT JOIN %s AS i ON a.img_id = i.id
            LEFT JOIN %s AS rel ON a.id = rel.persona_id
            LEFT JOIN %s AS g ON rel.grup_id = g.id
            WHERE EXISTS (
                SELECT 1
                FROM %s r2
                WHERE r2.persona_id = a.id
                AND r2.grup_id = :autor_grup_id
            )
            GROUP BY a.id
            ORDER BY a.cognoms
        SQL;

        $sql = sprintf(
            $query,
            qi(Tables::PERSONES, $pdo),
            qi(Tables::GEO_PAISOS, $pdo),
            qi(Tables::IMG, $pdo),
            qi(Tables::PERSONES_GRUPS_RELACIONS, $pdo),
            qi(Tables::PERSONES_GRUPS, $pdo),
            qi(Tables::PERSONES_GRUPS_RELACIONS, $pdo)
        );

        $params = [':autor_grup_id' => $autorGroupUuidBin];
        $result = $db->getData($sql, $params);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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

    // 4) Authors page > list of books
    // ruta GET => "https://elliot.cat/api/biblioteca/get/autorLlibres?id=998889"
} else if ($slug === 'autorLlibres') {

    // Quitar guiones del UUID
    $id = $_GET['id'];
    $idBin = Uuid::toBinary($id);

    try {

        $query = <<<SQL
                    SELECT 
                        b.any,
                        b.titol_original AS titol,
                        b.titol_catala,
                        b.slug,
                        b.id
                    FROM %s AS b
                    LEFT JOIN %s AS la ON b.id = la.llibre_id
                    WHERE la.autor_id = :autor_id
                    ORDER BY b.any ASC
                    SQL;

        $sql = sprintf(
            $query,
            qi(Tables::LLIBRES, $pdo),
            qi(Tables::LLIBRES_AUTORS, $pdo)
        );

        $params = [':autor_id' => $idBin];
        $result = $db->getData($sql, $params, false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            exit;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
        exit;
    }

    // 6) Book page
    // ruta GET => "/api/biblioteca/get/llibreSlug?llibre=el-por-bien-del-imperio"
} else if ($slug === 'llibreSlug') {

    $slugLlibre = $_GET['llibre'];

    try {

        $sql = <<<SQL
                SELECT 
                    b.id,
                    b.titol_original,
                    b.titol_catala,
                    b.tipus_id,
                    b.editorial_id,
                    b.sub_tema_id,
                    b.estat_id,
                    b.slug,
                    b.any,
                    b.dateCreated,
                    b.dateModified,
                    b.idioma_id,
                    b.img_id,
                    i.nameImg,
                    i.alt,
                    t.nomTipus,
                    e.editorial,
                    id.idioma_ca,
                    el.estat AS nomEstat,
                    sub_tema.sub_tema,
                    tema.tema,
                    p.id AS autor_id,
                    p.nom AS autor_nom,
                    p.cognoms AS autor_cognoms,
                    p.slug AS autor_slug
                FROM %s AS b
                LEFT JOIN %s AS la ON la.llibre_id = b.id
                LEFT JOIN %s AS p ON p.id = la.autor_id
                LEFT JOIN %s AS i ON b.img_id = i.id
                LEFT JOIN %s AS t ON b.tipus_id = t.id
                LEFT JOIN %s AS e ON b.editorial_id = e.id
                LEFT JOIN %s AS el ON b.estat_id = el.id
                LEFT JOIN %s AS id ON b.idioma_id = id.id
                LEFT JOIN %s AS sub_tema ON sub_tema.id = b.sub_tema_id
                LEFT JOIN %s AS tema ON sub_tema.tema_id = tema.id
                WHERE b.slug = :slug
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES, $pdo),
            qi(Tables::LLIBRES_AUTORS, $pdo),
            qi(Tables::PERSONES, $pdo),
            qi(Tables::IMG, $pdo),
            qi(Tables::LLIBRES_TIPUS, $pdo),
            qi(Tables::LLIBRES_EDITORIALS, $pdo),
            qi(Tables::LLIBRES_ESTAT, $pdo),
            qi(Tables::AUX_IDIOMES, $pdo),
            qi(Tables::AUX_SUB_TEMES, $pdo),
            qi(Tables::AUX_TEMES, $pdo)
        );

        $params = [':slug' => $slugLlibre];
        $rows = $db->getData($query, $params);

        if (empty($rows)) {
            header('Content-Type: application/json; charset=utf-8');
            Response::error(MissatgesAPI::error('not_found'), ['slug' => $slug], 404);
            exit;
        }

        // 1) Datos del libro = primera fila
        $first = $rows[0];

        $result = [
            'id'          => $first['id'],
            'titol_original'       => $first['titol_original'],
            'titol_catala' => $first['titol_catala'],
            'slug'        => $first['slug'],
            'any'         => $first['any'],
            'dateCreated' => $first['dateCreated'],
            'dateModified' => $first['dateModified'],
            'idioma_id'        => $first['idioma_id'],
            'img_id'         => $first['img_id'],
            'alt'         => $first['alt'],
            'estat_id'       => $first['estat_id'],      // int
            'nomEstat'    => $first['nomEstat'],   // texto

            'tipus_id'    => $first['tipus_id'],
            'editorial_id' => $first['editorial_id'],
            'sub_tema_id' => $first['sub_tema_id'],

            'nameImg'     => $first['nameImg'],
            'nomTipus'    => $first['nomTipus'],
            'editorial'   => $first['editorial'],
            'idioma_ca'   => $first['idioma_ca'],

            'sub_tema' => $first['sub_tema'],
            'tema'     => $first['tema'],

            // 2) autores
            'autors'      => [],

            // 3) col·leccions
            'grups'       => [],

            // 4) etiquetes
            'etiquetes'   => [],
        ];

        // 2) Construir array de autores (deduplicado por seguridad)
        $seen = [];

        foreach ($rows as $r) {
            if (empty($r['autor_id'])) continue; // libro sin autores

            $aid = $r['autor_id'];
            if (isset($seen[$aid])) continue;

            $seen[$aid] = true;

            $result['autors'][] = [
                'id'      => $aid,
                'nom'     => $r['autor_nom'],
                'cognoms' => $r['autor_cognoms'],
                'slug'    => $r['autor_slug'],
            ];
        }

        // 3) Col·leccions (grup) — query aparte para evitar producto cartesiano con autors
        $sqlGrups = <<<SQL
                SELECT
                    g.id,
                    g.nom,
                    g.slug
                FROM %s AS lg
                INNER JOIN %s AS g ON g.id = lg.grup_id
                WHERE lg.llibre_id = :id
            SQL;

        $queryGrups = sprintf(
            $sqlGrups,
            qi(Tables::LLIBRES_GRUP_LLIBRES, $pdo),
            qi(Tables::LLIBRES_GRUP, $pdo)
        );

        $grupRows = $db->getData($queryGrups, [':id' => $first['id']]);

        foreach ($grupRows as $g) {
            $result['grups'][] = [
                'id'   => Uuid::toString($g['id']),
                'nom'  => $g['nom'],
                'slug' => $g['slug'],
            ];
        }

        // 4) Etiquetes — mismo patrón que grups, query aparte
        $sqlEtiquetes = <<<SQL
                SELECT
                    e.id,
                    e.nom,
                    e.slug
                FROM %s AS le
                INNER JOIN %s AS e ON e.id = le.etiqueta_id
                WHERE le.llibre_id = :id
            SQL;

        $queryEtiquetes = sprintf(
            $sqlEtiquetes,
            qi(Tables::LLIBRES_ETIQUETES_LLIBRES, $pdo),
            qi(Tables::LLIBRES_ETIQUETES, $pdo)
        );

        $etiquetaRows = $db->getData($queryEtiquetes, [':id' => $first['id']]);

        foreach ($etiquetaRows as $e) {
            $result['etiquetes'][] = [
                'id'   => Uuid::toString($e['id']),
                'nom'  => $e['nom'],
                'slug' => $e['slug'],
            ];
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }

    // 6) Book page
    // ruta GET => "/api/biblioteca/get/llibreId?id=444443"
} else if ($slug === 'llibreId') {

    $id = $_GET['id'] ?? null;

    if (!$id) {
        Response::error('Missing id', [], 400);
        exit;
    };

    try {

        $sql = <<<SQL
                SELECT 
                    b.id,
                    b.titol_original,
                    b.titol_catala,
                    b.tipus_id,
                    b.editorial_id,
                    b.sub_tema_id,
                    b.estat_id,
                    b.slug,
                    b.any,
                    b.dateCreated,
                    b.dateModified,
                    b.idioma_id,
                    b.img_id,
                    p.id AS autor_id,
                    p.nom AS autor_nom,
                    p.cognoms AS autor_cognoms,
                    p.slug AS autor_slug,
                    i.nameImg,
                    i.alt
                FROM %s AS b
                LEFT JOIN %s AS la ON la.llibre_id = b.id
                LEFT JOIN %s AS p ON p.id = la.autor_id
                LEFT JOIN %s AS i ON b.img_id = i.id
                WHERE b.id = :id
            SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES, $pdo),
            qi(Tables::LLIBRES_AUTORS, $pdo),
            qi(Tables::PERSONES, $pdo),
            qi(Tables::IMG, $pdo)
        );

        $params = [':id' => uuid::toBinary($id)];
        $rows = $db->getData($query, $params);

        // 1) Datos del libro = primera fila
        $first = $rows[0];

        $result = [
            'id'          => $first['id'],
            'titol_original'       => $first['titol_original'],
            'titol_catala' => $first['titol_catala'],
            'slug'        => $first['slug'],
            'any'         => $first['any'],
            'dateCreated' => $first['dateCreated'],
            'dateModified' => $first['dateModified'],
            'idioma_id'        => $first['idioma_id'],
            'img_id'         => $first['img_id'],
            'alt'         => $first['alt'],
            'estat_id'       => $first['estat_id'],      // int
            'tipus_id'    => $first['tipus_id'],
            'editorial_id' => $first['editorial_id'],
            'sub_tema_id' => $first['sub_tema_id'],
            'nameImg'     => $first['nameImg'],

            // 2) autores
            'autors'      => [],

            // 3) col·leccions
            'grups'       => [],

            // 4) Etiquetes
            'etiqueres'   => [],
        ];

        // 2) Construir array de autores (deduplicado por seguridad)
        $seen = [];

        foreach ($rows as $r) {
            if (empty($r['autor_id'])) continue; // libro sin autores

            $aid = $r['autor_id'];
            if (isset($seen[$aid])) continue;

            $seen[$aid] = true;

            $result['autors'][] = [
                'id'      => $aid,
                'nom'     => $r['autor_nom'],
                'cognoms' => $r['autor_cognoms'],
                'slug'    => $r['autor_slug'],
            ];
        }

        // 3) Col·leccions (grup) — query aparte para evitar producto cartesiano con autors
        $sqlGrups = <<<SQL
                SELECT
                    g.id,
                    g.nom,
                    g.slug
                FROM %s AS lg
                INNER JOIN %s AS g ON g.id = lg.grup_id
                WHERE lg.llibre_id = :id
            SQL;

        $queryGrups = sprintf(
            $sqlGrups,
            qi(Tables::LLIBRES_GRUP_LLIBRES, $pdo),
            qi(Tables::LLIBRES_GRUP, $pdo)
        );

        $grupRows = $db->getData($queryGrups, [':id' => uuid::toBinary($id)]);

        foreach ($grupRows as $g) {
            $result['grups'][] = [
                'id'   => Uuid::toString($g['id']),
                'nom'  => $g['nom'],
                'slug' => $g['slug'],
            ];
        }

        // 4) Etiquetes — mismo patrón que grups, query aparte
        $sqlEtiquetes = <<<SQL
                SELECT
                    e.id,
                    e.nom,
                    e.slug
                FROM %s AS le
                INNER JOIN %s AS e ON e.id = le.etiqueta_id
                WHERE le.llibre_id = :id
            SQL;

        $queryEtiquetes = sprintf(
            $sqlEtiquetes,
            qi(Tables::LLIBRES_ETIQUETES_LLIBRES, $pdo),
            qi(Tables::LLIBRES_ETIQUETES, $pdo)
        );

        $etiquetaRows = $db->getData($queryEtiquetes, [':id' => uuid::toBinary($id)]);

        foreach ($etiquetaRows as $e) {
            $result['etiquetes'][] = [
                'id'   => Uuid::toString($e['id']),
                'nom'  => $e['nom'],
                'slug' => $e['slug'],
            ];
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }

    //TotsGrups

} else if ($slug === 'totsGrups') {

    try {

        $sql = <<<SQL
        SELECT
            id,
            nom,
            slug
        FROM %s
        ORDER BY nom ASC
        SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES_GRUP, $pdo)
        );

        $grups = $db->getData($query);

        foreach ($grups as &$g) {
            $g['id'] = Uuid::toString($g['id']);
        }
        unset($g);

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $grups,
            httpCode: 200
        );
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }

    // 10) image author
    // ruta GET => "/api/biblioteca/get/auxiliarImatgesAutor"
} else if ($slug == 'auxiliarImatgesAutor') {

    try {
        $sql = <<<SQL
                    SELECT 
                        i.id,
                        CONCAT(i.nom, ' (', t.name, ')') AS alt
                    FROM %s AS i
                    LEFT JOIN %s AS t ON i.typeImg = t.id
                    WHERE i.typeImg IN (1, 5, 9, 14)
                    ORDER BY i.nom
                    SQL;

        $query = sprintf(
            $sql,
            qi(Tables::DB_IMATGES, $pdo),
            qi(Tables::DB_IMATGES_TIPUS, $pdo)
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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

    // 10) ruta estat del llibre
    // ruta GET => "/api/biblioteca/get/estatLlibre"
} else if ($slug === 'estatLlibre') {

    try {

        $sql = <<<SQL
        SELECT 
            e.id,
            e.estat
        FROM %s AS e
        ORDER BY e.estat
        SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES_ESTAT, $pdo)
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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

    // 10) ruta grup llibre
    // ruta GET => "/api/biblioteca/get/grupLlibre"
} else if ($slug === 'grupLlibre') {

    try {

        $sql = <<<SQL
                SELECT 
                    e.id,
                    e.nom
                FROM %s AS e
                ORDER BY e.nom
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES_GRUP, $pdo)
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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


    // 11) Gèneres
    // ruta GET => "/api/biblioteca/get/llengues"
} else if ($slug === 'llengues') {

    try {
        $sql = <<<SQL
                SELECT l.id, l.idioma_ca 
                FROM %s AS l
                ORDER BY l.idioma_ca ASC
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::AUX_IDIOMES, $pdo)
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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

    // 11) Gèneres
    // ruta GET => "/api/biblioteca/get/tipus"
} else if ($slug === 'tipus') {

    try {

        $sql = <<<SQL
                SELECT t.id AS id, t.nomTipus
                FROM %s AS t
                ORDER BY t.nomTipus ASC
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES_TIPUS, $pdo)
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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


    // 11) genere llibre
    // ruta GET => "/api/biblioteca/get/temes"
} else if ($slug === 'temes') {

    try {

        $sql = <<<SQL
                    SELECT 
                    t.id AS id,
                    TRIM(CONCAT_WS(' - ', te.tema, t.sub_tema)) AS tema_complet
                    FROM %s AS t
                    LEFT JOIN %s AS te ON t.tema_id = te.id
                    ORDER BY te.tema ASC
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::AUX_SUB_TEMES, $pdo),
            qi(Tables::AUX_TEMES, $pdo)
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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

    // 12) classificació grup persona
    // ruta GET => "/api/biblioteca/get/grup"
} else if ($slug === 'grup') {

    try {

        $sql = <<<SQL
                SELECT g.id, g.grup_ca
                FROM %s AS g
                ORDER BY g.grup_ca ASC
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::PERSONES_GRUPS, $pdo),
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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

    // 11) sexe
    // ruta GET => "/api/biblioteca/get/sexe"
} else if ($slug === 'sexe') {

    try {

        $sql = <<<SQL
                    SELECT s.id, s.genereCa
                    FROM %s AS s
                    ORDER BY s.genereCa ASC
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::DB_PERSONES_GENERES, $pdo),
        );

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
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

    // 12) Llistat Etiquetes llibres
    // ruta GET => "/api/biblioteca/get/totsEtiquetes"
} else if ($slug === 'totsEtiquetes') {

    try {

        $sql = <<<SQL
        SELECT
            id,
            nom,
            slug
        FROM %s
        ORDER BY nom ASC
        SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES_ETIQUETES, $pdo)
        );

        $etiquetes = $db->getData($query);

        foreach ($etiquetes as &$e) {
            $e['id'] = Uuid::toString($e['id']);
        }
        unset($e);

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $etiquetes,
            httpCode: 200
        );
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
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
