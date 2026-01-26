<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use Ramsey\Uuid\Uuid;


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


if ((isset($_GET['type']) && $_GET['type'] == 'convertirId')) {

    // Seleccionar registros que aún no tienen UUID (id NULL o en blanco)
    $query = "SELECT id, persona_id, grup_id FROM db_persones_grups_relacions";

    try {

        // Configuración de PDO
        $db = new Database();

        $params = [];
        $result = $db->getData($query, $params, false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            exit;
        }

        // Preparar statement de actualización
        global $conn;
        // Preparar statement de actualización
        $updateStmt = $conn->prepare("UPDATE db_persones_grups_relacions
            SET id = :id
            WHERE persona_id = :persona_id AND
            grup_id = :grup_id
        ");

        foreach ($result as $row) {
            $uuid = Uuid::uuid7()->getBytes(); // UUIDv7 en binario

            $updateStmt->execute([
                ':id' => $uuid,
                ':persona_id' => $row['persona_id'],
                ':grup_id' => $row['grup_id'],
            ]);
        }

        echo "IDs actualizados con éxito.\n";
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
        exit;
    }

    // 2) Llistat llibres
    // ruta GET => "https://elliot.cat/api/biblioteca/get/?type?totsLlibres"
} else if ((isset($_GET['type']) && $_GET['type'] == 'totsLlibres')) {
    try {
        $db = new Database();

        // 1) Libros (1 fila por libro)
        $queryBooks = "
        SELECT
            LOWER(CONCAT_WS('-',
                SUBSTR(HEX(b.id), 1, 8),
                SUBSTR(HEX(b.id), 9, 4),
                SUBSTR(HEX(b.id), 13, 4),
                SUBSTR(HEX(b.id), 17, 4),
                SUBSTR(HEX(b.id), 21)
            )) AS id,

            b.titol,
            b.any,
            b.slug,

            g.tema_ca AS nomGenCat,
            sg.sub_tema_ca

        FROM " . Tables::LLIBRES . " AS b
        LEFT JOIN " . Tables::AUX_SUB_TEMES . " AS sg ON b.sub_tema_id = sg.id
        LEFT JOIN " . Tables::AUX_TEMES . " AS g ON sg.tema_id = g.id
        LEFT JOIN " . Tables::LLIBRES_EDITORIALS . " AS be ON b.editorial_id = be.id

        WHERE b.tipus_id = UNHEX('0197ac5b7106704b96c60728ace151f3')
        ORDER BY b.titol ASC
    ";

        $books = $db->getData($queryBooks);

        // Sanititzar strings
        array_walk_recursive($books, function (&$v) {
            if (!is_string($v)) return;
            $v = str_replace("\0", '', $v);
            $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
        });

        if (empty($books)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        // 2) Autores por libro (N filas por libro)
        $queryAuthors = "
        SELECT
            LOWER(CONCAT_WS('-',
                SUBSTR(HEX(la.llibre_id), 1, 8),
                SUBSTR(HEX(la.llibre_id), 9, 4),
                SUBSTR(HEX(la.llibre_id), 13, 4),
                SUBSTR(HEX(la.llibre_id), 17, 4),
                SUBSTR(HEX(la.llibre_id), 21)
            )) AS llibre_id,

            LOWER(CONCAT_WS('-',
                SUBSTR(HEX(a.id), 1, 8),
                SUBSTR(HEX(a.id), 9, 4),
                SUBSTR(HEX(a.id), 13, 4),
                SUBSTR(HEX(a.id), 17, 4),
                SUBSTR(HEX(a.id), 21)
            )) AS id,

            a.nom,
            a.cognoms,
            a.slug

        FROM " . Tables::LLIBRES_AUTORS . " AS la
        INNER JOIN " . Tables::PERSONES . " AS a ON a.id = la.autor_id
        INNER JOIN " . Tables::LLIBRES . " AS b ON b.id = la.llibre_id

        WHERE b.tipus_id = UNHEX('0197ac5b7106704b96c60728ace151f3')
        ORDER BY a.cognoms, a.nom
    ";

        $authors = $db->getData($queryAuthors);

        array_walk_recursive($authors, function (&$v) {
            if (!is_string($v)) return;
            $v = str_replace("\0", '', $v);
            $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
        });

        // 3) Index autores por llibre_id
        $authorsByBook = [];
        foreach ($authors as $a) {
            $bookId = $a['llibre_id'] ?? null;
            if (!$bookId) continue;

            $authorsByBook[$bookId][] = [
                'id' => $a['id'] ?? null,
                'nom' => $a['nom'] ?? null,
                'cognoms' => $a['cognoms'] ?? null,
                'slug' => $a['slug'] ?? null,
            ];
        }

        // 4) Merge: añadir autors[] a cada libro
        foreach ($books as &$b) {
            $id = $b['id'] ?? null;
            $b['autors'] = ($id && isset($authorsByBook[$id])) ? $authorsByBook[$id] : [];
        }
        unset($b);

        Response::success(MissatgesAPI::success('get'), $books, 200);
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

    // Vista amb els autors d'un llibre
    // GET /api/biblioteca/get/?type=llibreAutors&slug=el-por-bien-del-imperio
} elseif ((isset($_GET['type']) && $_GET['type'] == 'llibreAutors')) {
    // Siempre JSON
    header('Content-Type: application/json; charset=utf-8');

    $slug = isset($_GET['slug']) ? (string)$_GET['slug'] : '';
    $slug = trim($slug);

    if ($slug === '') {
        Response::error(MissatgesAPI::error('bad_request'), ['slug' => 'required'], 400);
        exit;
    }

    try {
        $db = new Database();

        // 1) Libro (1 fila)
        $qBook = "
            SELECT
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(b.id), 1, 8),
                    SUBSTR(HEX(b.id), 9, 4),
                    SUBSTR(HEX(b.id), 13, 4),
                    SUBSTR(HEX(b.id), 17, 4),
                    SUBSTR(HEX(b.id), 21)
                )) AS id,
                b.slug,
                b.titol
            FROM " . Tables::LLIBRES . " AS b
            WHERE b.slug = :slug
            LIMIT 1
        ";

        $bookRows = $db->getData($qBook, [':slug' => $slug]);

        // Sanititzar (por si titol trae bytes raros)
        array_walk_recursive($bookRows, function (&$v) {
            if (!is_string($v)) return;
            $v = str_replace("\0", '', $v);
            $v = @iconv('UTF-8', 'UTF-8//IGNORE', $v) ?: $v;
        });

        if (empty($bookRows)) {
            Response::error(MissatgesAPI::error('not_found'), ['slug' => $slug], 404);
            exit;
        }

        $book = $bookRows[0];

        // 2) Autores del libro (N filas)
        // Usamos rel.id (AUTO_INCREMENT) como rel_id para poder borrar fácil luego
        $qAuthors = "
            SELECT
                la.id AS rel_id,

                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(a.id), 1, 8),
                    SUBSTR(HEX(a.id), 9, 4),
                    SUBSTR(HEX(a.id), 13, 4),
                    SUBSTR(HEX(a.id), 17, 4),
                    SUBSTR(HEX(a.id), 21)
                )) AS id,

                a.nom,
                a.cognoms,
                a.slug

            FROM " . Tables::LLIBRES_AUTORS . " AS la
            INNER JOIN " . Tables::LLIBRES . " AS b ON b.id = la.llibre_id
            INNER JOIN " . Tables::PERSONES . " AS a ON a.id = la.autor_id

            WHERE b.slug = :slug
            ORDER BY a.cognoms ASC, a.nom ASC
        ";

        $authors = $db->getData($qAuthors, [':slug' => $slug]);

        array_walk_recursive($authors, function (&$v) {
            if (!is_string($v)) return;
            $v = str_replace("\0", '', $v);
            $v = @iconv('UTF-8', 'UTF-8//IGNORE', $v) ?: $v;
        });

        Response::success(
            MissatgesAPI::success('get'),
            [
                'llibre' => $book,
                'autors' => $authors,
            ],
            200
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
    // ruta GET => "https://elliot.cat/api/biblioteca/get/authors"
} else if (isset($_GET['type']) && in_array($_GET['type'], ['totsAutors', 'autors'], true)) {

    try {
        $db = new Database();
        $autorGroupUuid = '0197b088-1a25-72c4-8b5b-d7e2ee27de7c';

        $query =
            "SELECT 
             LOWER(CONCAT_WS('-', 
                SUBSTR(HEX(a.id), 1, 8),
                SUBSTR(HEX(a.id), 9, 4),
                SUBSTR(HEX(a.id), 13, 4),
                SUBSTR(HEX(a.id), 17, 4),
                SUBSTR(HEX(a.id), 21) )) AS id,
            LOWER(CONCAT_WS('-', 
                SUBSTR(HEX(c.id), 1, 8),
                SUBSTR(HEX(c.id), 9, 4),
                SUBSTR(HEX(c.id), 13, 4),
                SUBSTR(HEX(c.id), 17, 4),
                SUBSTR(HEX(c.id), 21) )) AS idCountry,
            a.nom AS AutNom, a.cognoms AS AutCognom1, TRIM(CONCAT_WS(' ', a.nom, a.cognoms)) AS autor_nom_complet, a.slug, a.any_naixement AS yearBorn, a.any_defuncio AS yearDie, c.pais_ca AS country, i.nameImg,
        GROUP_CONCAT(DISTINCT g.grup_ca ORDER BY g.grup_ca SEPARATOR ', ') AS grup
        FROM " . Tables::PERSONES . " AS a
        LEFT JOIN " . Tables::GEO_PAISOS . " AS c ON a.pais_autor_id = c.id
        LEFT JOIN " . Tables::IMG . " AS i ON a.img_id = i.id
        LEFT JOIN " . Tables::PERSONES_GRUPS_RELACIONS . " AS rel ON a.id = rel.persona_id
        LEFT JOIN " . Tables::PERSONES_GRUPS . " AS g ON rel.grup_id = g.id
        WHERE EXISTS (
            SELECT 1
            FROM " . Tables::PERSONES_GRUPS_RELACIONS . " r2
            WHERE r2.persona_id = a.id
            AND r2.grup_id = UNHEX(REPLACE(:autor_grup_uuid, '-', ''))
        )
        GROUP BY a.id
        ORDER BY a.cognoms";

        $params = [':autor_grup_uuid' => $autorGroupUuid];

        $result = $db->getData($query, $params);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
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


        if (empty($result)) {
            header('Content-Type: application/json; charset=utf-8');
            Response::error(MissatgesAPI::error('not_found'), ['slug' => $slug], 404);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        Response::success(MissatgesAPI::success('get'), $result, 200);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }

    // 4) Authors page > list of books
    // ruta GET => "https://control.elliotfern/api/library/authors/books/9"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'autorLlibres') && (isset($_GET['id']))) {
    $db = new Database();

    // Quitar guiones del UUID
    $id = str_replace('-', '', $_GET['id']);

    $query = "SELECT b.any, b.titol, b.slug
                FROM db_llibres AS b
                LEFT JOIN db_llibres_autors AS la ON b.id = la.llibre_id
                WHERE la.autor_id = UNHEX(:id)
                ORDER BY b.any ASC";

    try {
        $params = [':id' => $id];
        $result = $db->getData($query, $params, false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            exit;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
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
    // ruta GET => "/api/biblioteca/get/?llibreSlug=el-por-bien-del-imperio"
} else if (isset($_GET['llibreSlug'])) {

    $slug = (string) $_GET['llibreSlug'];

    try {
        $db = new Database();

        $query = "SELECT 
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(b.id), 1, 8),
                    SUBSTR(HEX(b.id), 9, 4),
                    SUBSTR(HEX(b.id), 13, 4),
                    SUBSTR(HEX(b.id), 17, 4),
                    SUBSTR(HEX(b.id), 21)
                    )) AS id,
                 LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(b.tipus_id), 1, 8),
                    SUBSTR(HEX(b.tipus_id), 9, 4),
                    SUBSTR(HEX(b.tipus_id), 13, 4),
                    SUBSTR(HEX(b.tipus_id), 17, 4),
                    SUBSTR(HEX(b.tipus_id), 21)
                    )) AS tipus_id,
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(b.editorial_id), 1, 8),
                    SUBSTR(HEX(b.editorial_id), 9, 4),
                    SUBSTR(HEX(b.editorial_id), 13, 4),
                    SUBSTR(HEX(b.editorial_id), 17, 4),
                    SUBSTR(HEX(b.editorial_id), 21)
                    )) AS editorial_id,
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(b.sub_tema_id), 1, 8),
                    SUBSTR(HEX(b.sub_tema_id), 9, 4),
                    SUBSTR(HEX(b.sub_tema_id), 13, 4),
                    SUBSTR(HEX(b.sub_tema_id), 17, 4),
                    SUBSTR(HEX(b.sub_tema_id), 21)
                    )) AS sub_tema_id,
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(b.estat), 1, 8),
                    SUBSTR(HEX(b.estat), 9, 4),
                    SUBSTR(HEX(b.estat), 13, 4),
                    SUBSTR(HEX(b.estat), 17, 4),
                    SUBSTR(HEX(b.estat), 21)
                    )) AS estat,
                b.titol,
                b.slug as llibreSlug,
                b.slug,
                b.any,
                b.dateCreated,
                b.dateModified,
                b.lang,
                b.img,
                i.nameImg,
                t.nomTipus,
                e.editorial,
                id.idioma_ca,
                el.estat AS nomEstat,
                sub_tema.sub_tema_ca,
                tema.tema_ca,

                   -- autor (1 fila por autor)
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(p.id), 1, 8),
                    SUBSTR(HEX(p.id), 9, 4),
                    SUBSTR(HEX(p.id), 13, 4),
                    SUBSTR(HEX(p.id), 17, 4),
                    SUBSTR(HEX(p.id), 21)
                )) AS autor_id,
                p.nom AS autor_nom,
                p.cognoms AS autor_cognoms,
                p.slug AS autor_slug

            FROM " . Tables::LLIBRES . " AS b
            LEFT JOIN " . Tables::LLIBRES_AUTORS . " AS la ON la.llibre_id = b.id
            LEFT JOIN " . Tables::PERSONES . " AS p ON p.id = la.autor_id
            LEFT JOIN " . Tables::IMG . " AS i ON b.img = i.id
            LEFT JOIN " . Tables::LLIBRES_TIPUS . " AS t ON b.tipus_id = t.id
            LEFT JOIN " . Tables::LLIBRES_EDITORIALS . " AS e ON b.editorial_id = e.id
            LEFT JOIN " . Tables::LLIBRES_ESTAT . " AS el ON b.estat = el.id
            LEFT JOIN " . Tables::AUX_IDIOMES . " AS id ON b.lang = id.id
            LEFT JOIN " . Tables::AUX_SUB_TEMES . " AS sub_tema ON sub_tema.id =  b.sub_tema_id
            LEFT JOIN " . Tables::AUX_TEMES . " AS tema ON sub_tema.tema_id = tema.id
            WHERE b.slug = :slug";

        $params = [':slug' => $slug];

        $rows = $db->getData($query, $params);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($rows, function (&$v) {
            if (!is_string($v)) return;
            $v = str_replace("\0", '', $v);

            if (!mb_check_encoding($v, 'UTF-8')) {
                $v2 = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $v);
                if ($v2 !== false) $v = $v2;
                else {
                    $v3 = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                    if ($v3 !== false) $v = $v3;
                }
            } else {
                $v2 = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                if ($v2 !== false) $v = $v2;
            }
        });

        if (empty($rows)) {
            header('Content-Type: application/json; charset=utf-8');
            Response::error(MissatgesAPI::error('not_found'), ['slug' => $slug], 404);
            exit;
        }

        // 1) Datos del libro = primera fila
        $first = $rows[0];

        $result = [
            'id'          => $first['id'],
            'titol'       => $first['titol'],
            'slug'        => $first['slug'],
            'any'         => $first['any'],
            'dateCreated' => $first['dateCreated'],
            'dateModified' => $first['dateModified'],
            'lang'        => $first['lang'],
            'img'         => $first['img'],
            'estat'       => $first['estat'],      // int
            'nomEstat'    => $first['nomEstat'],   // texto

            'tipus_id'    => $first['tipus_id'],
            'editorial_id' => $first['editorial_id'],
            'sub_tema_id' => $first['sub_tema_id'],

            'nameImg'     => $first['nameImg'],
            'nomTipus'    => $first['nomTipus'],
            'editorial'   => $first['editorial'],
            'idioma_ca'   => $first['idioma_ca'],

            'sub_tema_ca' => $first['sub_tema_ca'],
            'tema_ca'     => $first['tema_ca'],

            // 2) autores
            'autors'      => [],
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

        header('Content-Type: application/json; charset=utf-8');
        Response::success(MissatgesAPI::success('get'), $result, 200);
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
    // ruta GET => "/api/biblioteca/?type=auxiliarImatgesAutor"
} else if ((isset($_GET['type']) && $_GET['type'] == 'auxiliarImatgesAutor')) {

    try {

        $db = new Database();

        $query = "SELECT i.id, CONCAT(i.nom, ' (', t.name, ')') AS alt
            FROM db_img AS i
            LEFT JOIN db_img_type AS t ON i.typeImg = t.id
            WHERE i.typeImg IN (1, 5, 9, 14)
            ORDER BY i.nom";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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

    // 10) ruta estat del llibre
    // ruta GET => "/api/biblioteca/auxiliars/?estatLlibre"
} else if ((isset($_GET['type']) && $_GET['type'] == 'estatLlibre')) {

    try {
        $db = new Database();

        $query = "SELECT 
           LOWER(CONCAT_WS('-', 
                SUBSTR(HEX(e.id), 1, 8),
                SUBSTR(HEX(e.id), 9, 4),
                SUBSTR(HEX(e.id), 13, 4),
                SUBSTR(HEX(e.id), 17, 4),
                SUBSTR(HEX(e.id), 21) )) AS id,
            e.estat
            FROM " . Tables::LLIBRES_ESTAT . " AS e
            ORDER BY e.estat";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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

    // 11) Llibre imatge
    // ruta GET => "/api/biblioteca/auxiliars/?type=imatgesLlibres"
} else if ((isset($_GET['type']) && $_GET['type'] == 'imatgesLlibres')) {

    try {
        $db = new Database();

        $query = "SELECT i.id, i.alt
                FROM db_img AS i
                WHERE i.typeImg = 2
                ORDER BY i.alt ASC";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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

    // 11) Editorials
    // ruta GET => "/api/biblioteca/auxiliars/?type=editorials"
} else if ((isset($_GET['type']) && $_GET['type'] == 'editorials')) {

    try {
        $db = new Database();

        $query =
            "SELECT LOWER(CONCAT_WS('-',
                SUBSTR(HEX(e.id), 1, 8),
                SUBSTR(HEX(e.id), 9, 4),
                SUBSTR(HEX(e.id), 13, 4),
                SUBSTR(HEX(e.id), 17, 4),
                SUBSTR(HEX(e.id), 21)
                )) AS id, e.editorial
                FROM " . Tables::LLIBRES_EDITORIALS . " AS e
                ORDER BY e.editorial ASC";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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

    // 11) Gèneres
    // ruta GET => "/api/biblioteca/auxiliars/?type=llengues"
} else if ((isset($_GET['type']) && $_GET['type'] == 'llengues')) {

    try {
        $db = new Database();

        $query =  "SELECT l.id, l.idioma_ca 
                    FROM " . Tables::AUX_IDIOMES . " AS l
                    ORDER BY l.idioma_ca ASC";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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

    // 11) Gèneres
    // ruta GET => "/api/biblioteca/auxiliars/?type=tipus"
} else if ((isset($_GET['type']) && $_GET['type'] == 'tipus')) {

    try {
        $db = new Database();

        $query = "SELECT LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(t.id), 1, 8),
                    SUBSTR(HEX(t.id), 9, 4),
                    SUBSTR(HEX(t.id), 13, 4),
                    SUBSTR(HEX(t.id), 17, 4),
                    SUBSTR(HEX(t.id), 21)
                    )) AS id, t.nomTipus
                    FROM " . Tables::LLIBRES_TIPUS . " AS t
                    ORDER BY t.nomTipus ASC";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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


    // 11) genere llibre
    // ruta GET => "/api/biblioteca/auxiliars/?type=temes"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'temes')) {

    try {
        $db = new Database();

        $query = "SELECT 
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(t.id), 1, 8),
                    SUBSTR(HEX(t.id), 9, 4),
                    SUBSTR(HEX(t.id), 13, 4),
                    SUBSTR(HEX(t.id), 17, 4),
                    SUBSTR(HEX(t.id), 21) )) AS id,
                    TRIM(CONCAT_WS(' - ', te.tema_ca, t.sub_tema_ca)) AS tema_complet
                    FROM " . Tables::AUX_SUB_TEMES . " AS t
                    LEFT JOIN " . Tables::AUX_TEMES . " AS te ON t.tema_id = te.id
                    ORDER BY te.tema_ca ASC";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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

    // 12) classificació grup persona
    // ruta GET => "/api/biblioteca/auxiliars/?type=grup"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'grup')) {

    try {
        $db = new Database();

        $query = "SELECT 
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(id), 1, 8),
                    SUBSTR(HEX(id), 9, 4),
                    SUBSTR(HEX(id), 13, 4),
                    SUBSTR(HEX(id), 17, 4),
                    SUBSTR(HEX(id), 21)
                )) AS id,
                grup_ca
              FROM " . Tables::PERSONES_GRUPS . "
              ORDER BY grup_ca ASC";

        $result = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($result, function (&$v) {
            if (is_string($v)) {
                // Converteix a UTF-8 vàlid (ignora bytes trencats)
                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v);
            }
        });

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit; // IMPORTANTE
        }

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

    // 11) sexe
    // ruta GET => "/api/biblioteca/auxiliars/?type=sexe"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'sexe')) {

    $query = "SELECT s.id, s.genereCa
                    FROM aux_persones_genere AS s
                    ORDER BY s.genereCa ASC";
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
