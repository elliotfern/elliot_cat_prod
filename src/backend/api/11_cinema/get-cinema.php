<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Uuid;
use App\Utils\AdminMiddleware;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Access-Control-Allow-Methods: GET");

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


// GET : llistat de pelicules
// URL: https://elliot.cat/api/cinema/get/pelicules
if ($slug === "pelicules") {

    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT c.id, c.pelicula, c.pelicula_ca, c.any, d.nom, d.cognoms, p.pais_ca, g.genere, i.idioma_ca, c.slug, d.slug AS director_slug
                FROM %s AS c
                LEFT JOIN %s AS d ON c.director_id = d.id
                LEFT JOIN %s AS p ON c.pais_id = p.id
                LEFT JOIN %s AS g ON c.genere_id = g.id
                LEFT JOIN %s AS i ON c.idioma_id = i.id
                ORDER BY c.any DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_PELICULES, $pdo),
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::CINEMA_GENERES, $pdo),
        qi(Tables::DB_IDIOMES, $pdo)

    );

    try {
        $result = $db->getData($query, [], false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat de sèries tv
    // URL: https://elliot.cat/api/cinema/get/series
} else if ($slug === "series") {

    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT tv.id, tv.name, tv.startYear, tv.endYear,tv.season, tv.chapter, d.nom, d.cognoms, id.idioma_ca, g.genere, c.pais_ca, tv.slug, d.slug AS slugDirector
                FROM %s AS tv
                LEFT JOIN %s AS d ON tv.director_id = d.id
                LEFT JOIN %s AS c ON tv.pais_id = c.id
                LEFT JOIN %s AS id ON tv.idioma_id = id.id
                LEFT JOIN %s AS g ON tv.genere_id = g.id
                ORDER BY tv.startYear DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_SERIES_TV, $pdo),
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_IDIOMES, $pdo),
        qi(Tables::CINEMA_GENERES, $pdo)
    );

    try {

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : fitxa sèrie tv
    // URL: https://elliot.cat/api/cinema/get/serie?serieSlug=benvinguts-a-la-familia
} else if ($slug === "serie") {

    $serie = $_GET['serieSlug'];
    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT tv.id, tv.name, tv.slug, tv.startYear, tv.endYear, tv.season, tv.chapter, tv.director_id, tv.idioma_id, tv.genere_id, tv.pais_id, tv.img_id, tv.descripcio, tv.dateCreated, tv.dateModified,
                d.nom, d.cognoms, id.idioma_ca, c.pais_ca, img.nameImg, g.genere, d.id AS idDirector, d.slug AS slugDirector
                FROM %s AS tv
                LEFT JOIN %s AS d ON tv.director_id = d.id
                LEFT JOIN %s AS c ON tv.pais_id = c.id
                LEFT JOIN %s AS img ON tv.img_id = img.id
                LEFT JOIN %s AS id ON tv.idioma_id = id.id
                LEFT JOIN %s AS g ON tv.genere_id = g.id
                WHERE tv.slug = :slug;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_SERIES_TV, $pdo),
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_IDIOMES, $pdo),
        qi(Tables::CINEMA_GENERES, $pdo)
    );

    try {

        $params = [':slug' => $serie];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : fitxa sèrie tv
    // URL: https://elliot.cat/api/cinema/get/serieIntranet?id=333523523
} else if ($slug === "serieIntranet") {

    $id = $_GET['id'];
    AdminMiddleware::handle();

    /**
     * =========================
     * QUERY PRINCIPAL SÈRIE TV
     * =========================
     */

    $sql = <<<SQL
        SELECT
            tv.id, tv.name, tv.slug, tv.startYear, tv.endYear, tv.season, tv.chapter, tv.director_id, tv.idioma_id, tv.genere_id, tv.pais_id, tv.img_id, tv.descripcio, tv.dateCreated, tv.dateModified
        FROM %s AS tv
        WHERE tv.id = :id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_SERIES_TV, $pdo)
    );

    try {

        $params = [
            ':id' => Uuid::toBinary($id)
        ];

        $result = $db->getData($query, $params);

        if (empty($result)) {

            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );

            return;
        }

        /**
         * getData devuelve array
         * queremos solo 1 registro
         */

        $serie = $result[0];

        /**
         * =========================
         * QUERY ACTORS
         * =========================
         */

        $sqlActors = <<<SQL
            SELECT
                p.id,
                p.nom,
                p.cognoms,
                p.slug,
                sa.role

            FROM %s AS sa
            INNER JOIN %s AS p ON p.id = sa.actor_id
            WHERE sa.serie_id = :serie_id
            ORDER BY p.cognoms ASC, p.nom ASC
        SQL;

        $queryActors = sprintf(
            $sqlActors,
            qi(Tables::CINEMA_ACTORS_SERIES, $pdo),
            qi(Tables::DB_PERSONES, $pdo)
        );

        $actors = $db->getData(
            $queryActors,
            [
                ':serie_id' => Uuid::toBinary($id)
            ]
        );

        /**
         * Afegim actors a la resposta
         */

        $serie['actors'] = $actors;

        /**
         * RESPONSE
         */

        Response::success(
            MissatgesAPI::success('get'),
            $serie,
            200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : fitxa pelicula Intranet
    // URL: https://elliot.cat/api/cinema/get/peliculaIntranet?id=333523523
} else if ($slug === "peliculaIntranet") {

    $id = $_GET['id'];
    AdminMiddleware::handle();

    /**
     * =========================
     * QUERY PRINCIPAL PELICULA
     * =========================
     */

    $sql = <<<SQL
        SELECT id, pelicula, pelicula_ca, slug, director_id, genere_id, pais_id, idioma_id, imatge_id, any, descripcio, dateCreated, dateModified
        FROM %s
        WHERE id = :id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_PELICULES, $pdo)
    );

    try {

        $params = [
            ':id' => Uuid::toBinary($id)
        ];

        $result = $db->getData($query, $params);

        if (empty($result)) {

            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );

            return;
        }

        /**
         * getData devuelve array
         * queremos solo 1 registro
         */

        $serie = $result[0];

        /**
         * =========================
         * QUERY ACTORS
         * =========================
         */

        $sqlActors = <<<SQL
            SELECT
                p.id,
                p.nom,
                p.cognoms,
                p.slug,
                s.role

            FROM %s AS s
            INNER JOIN %s AS p ON p.id = s.actor_id
            WHERE s.pelicula_id = :pelicula_id
            ORDER BY p.cognoms ASC, p.nom ASC
        SQL;

        $queryActors = sprintf(
            $sqlActors,
            qi(Tables::CINEMA_ACTORS_PELICULES, $pdo),
            qi(Tables::DB_PERSONES, $pdo)
        );

        $actors = $db->getData(
            $queryActors,
            [
                ':pelicula_id' => Uuid::toBinary($id)
            ]
        );

        /**
         * Afegim actors a la resposta
         */

        $serie['actors'] = $actors;

        /**
         * RESPONSE
         */

        Response::success(
            MissatgesAPI::success('get'),
            $serie,
            200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
    // GET : fitxa pel·lícula
    // URL: https://elliot.cat/api/cinema/get/pelicula?peliSlug=io-capitano
} else if ($slug === "pelicula") {

    AdminMiddleware::handle();
    $peli = $_GET['peliSlug'];

    $sql = <<<SQL
                SELECT p.id, p.pelicula, p.slug, p.pelicula_ca, p.any, p.descripcio, p.dateCreated, p.dateModified, p.director_id, p.genere_id, p.pais_id, p.idioma_id, p.imatge_id, d.nom, d.cognoms, id.idioma_ca, c.pais_ca, img.nameImg, g.genere, d.slug AS slugDirector
                FROM %s AS p
                LEFT JOIN %s AS d ON p.director_id = d.id
                LEFT JOIN %s AS c ON p.pais_id = c.id
                LEFT JOIN %s AS img ON p.imatge_id = img.id
                LEFT JOIN %s AS id ON p.idioma_id = id.id
                LEFT JOIN %s AS g ON p.genere_id = g.id
                WHERE p.slug = :slug;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_PELICULES, $pdo),
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_IDIOMES, $pdo),
        qi(Tables::CINEMA_GENERES, $pdo)

    );

    try {
        $params = [':slug' => $peli];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : Actors que participen en una serie determinada
    // URL: "https://elliot.cat/api/cinema/get/actors-serie?serie=id"
} else if ($slug === "actors-serie") {
    $serie = $_GET['serie'];
    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT a.nom, a.cognoms, a.id AS actor_id, sa.role, img.nameImg, sa.id, a.slug
                FROM %s AS s
                LEFT JOIN %s AS sa on s.id = sa.serie_id
                LEFT JOIN %s AS a ON a.id = sa.actor_id
                LEFT JOIN %s AS img ON a.img_id = img.id
                WHERE s.slug = :slug;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_SERIES_TV, $pdo),
        qi(Tables::CINEMA_ACTORS_SERIES, $pdo),
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_IMATGES, $pdo)
    );

    try {

        $params = [':slug' => $serie];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 2) Actors que participen en una pelicula
    // ruta GET => "/api/cinema/get/actors-pelicula?peli=id"
} elseif ($slug === 'actors-pelicula') {

    $peli = $_GET['peli'];
    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT a.nom, a.cognoms, a.id AS actor_id, sa.role, img.nameImg, sa.id, a.slug
                FROM %s AS s
                INNER JOIN %s AS sa on s.id = sa.pelicula_id
                INNER JOIN %s AS a ON a.id = sa.actor_id
                LEFT JOIN %s AS img ON a.img_id = img.id
                WHERE s.slug = :peli;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_PELICULES, $pdo),
        qi(Tables::CINEMA_ACTORS_PELICULES, $pdo),
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_IMATGES, $pdo)
    );

    try {

        $params = [':peli' => $peli];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 1) Llistat actors
    // ruta GET => "/api/cinema/get/actors"
} else if ($slug === "actors") {

    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT a.id, a.cognoms, a.nom, CONCAT(a.cognoms, ', ', a.nom) AS nomComplet, c.pais_ca, i.nameImg, a.any_naixement, a.any_defuncio, a.slug
                FROM %s AS a
                INNER JOIN %s g ON a.id = g.persona_id
                LEFT JOIN %s AS c ON a.pais_autor_id = c.id
                LEFT JOIN %s AS i ON a.img_id = i.id
                WHERE g.grup_id = :grup
                ORDER BY a.cognoms ASC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_PERSONES_GRUPS_RELACIONS, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
    );

    try {
        $id_grup = '0197b0881a27723c8ca798b4d32a01ee';
        $id_grup_bin = uuid::toBinary($id_grup);
        $params = [':grup' => $id_grup_bin];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    // 1) Fitxa director
    // ruta GET => "/api/cinema/get/?director=?arron-sorkin"
} else if ($slug === "director") {

    $query = "SELECT a.id, a.cognoms, a.nom, i.nameImg, c.pais_cat, a.slug, a.anyNaixement, a.anyDefuncio, a.dateCreated, a.dateModified, pro.professio_ca, a.web, a.descripcio
            FROM db_persones AS a
            LEFT JOIN db_img AS i ON a.img = i.id
            LEFT JOIN db_countries AS c ON a.paisAutor = c.id
            LEFT JOIN aux_professions AS pro ON a.ocupacio = pro.id
            WHERE a.slug = :slug";

    $result = getData($query, ['slug' => $param], true);
    echo json_encode($result);

    // 1) Fitxa director: pelicules
    // ruta GET => "/api/cinema/get/?directorPelicules=?arron-sorkin"
} else if ($slug === "directorPelicules") {

    $query = "SELECT p.id, p.pelicula AS name, p.slug, p.any AS anyInici, i.nameImg, c.pais_cat, g.genere_ca
            FROM 11_db_pelicules AS p
            LEFT JOIN db_img AS i ON p.img = i.id
            LEFT JOIN db_countries AS c ON p.pais = c.id
            LEFT JOIN 11_aux_cinema_generes AS g ON p.genere = g.id
            WHERE p.director = :id";

    $result = getData($query, ['id' => $id]);
    echo json_encode($result);

    // 1) Fitxa director: series
    // ruta GET => "/api/cinema/get/?directorSeries=?arron-sorkin"
} else if ($slug === "directorSeries") {

    $query = "SELECT s.id, s.name AS name, s.slug, s.startYear AS anyInici, s.endYear, i.nameImg, c.pais_cat, g.genere_ca
            FROM 11_db_cinema_series_tv AS s
            LEFT JOIN db_img AS i ON s.img = i.id
            LEFT JOIN db_countries AS c ON s.country = c.id
            LEFT JOIN 11_aux_cinema_generes AS g ON s.genre = g.id
            WHERE s.director = :id";

    $result = getData($query, ['id' => $id]);
    echo json_encode($result);


    // 2) Llistat pelicules per actor
    // ruta GET => "/api/cinema/get/actor-pelicules?actor=id"
} else if ($slug === "actor-pelicules") {
    $actor = $_GET['actor'];
    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT p.pelicula AS titol, sa.role, p.any AS anyInici, p.slug
                FROM %s AS p
                LEFT JOIN %s AS sa ON p.id = sa.pelicula_id
                LEFT JOIN %s AS pe ON pe.id = sa.actor_id
                WHERE sa.actor_id = :id
                ORDER BY p.pelicula ASC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_PELICULES, $pdo),
        qi(Tables::CINEMA_ACTORS_PELICULES, $pdo),
        qi(Tables::DB_PERSONES, $pdo)
    );

    try {
        $params = [':id' => uuid::toBinary($actor)];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    // 2) Llistat series tv per actor
    // ruta GET => "/api/cinema/get/actor-series?actor=id"
} else if ($slug === "actor-series") {

    $actor = $_GET['actor'];
    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT s.name AS titol, sa.role, s.startYear AS anyInici, s.endYear AS anyFi, s.slug
                FROM %s AS s
                LEFT JOIN %s AS sa ON s.id = sa.serie_id
                LEFT JOIN %s AS pe ON pe.id = sa.actor_id 
                WHERE sa.actor_id = :id
                ORDER BY s.name ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_SERIES_TV, $pdo),
        qi(Tables::CINEMA_ACTORS_SERIES, $pdo),
        qi(Tables::DB_PERSONES, $pdo)
    );

    try {
        $params = [':id' => uuid::toBinary($actor)];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 2) Actor-pelicula
    // ruta GET => "/api/cinema/get/?actorPelicula=35"
} else if ($slug === "actorPelicula") {

    $query = "SELECT cap.idMovie, cap.idActor, cap.role, p.pelicula, cap.id
        FROM 11_aux_cinema_actors_pelicules AS cap
        LEFT JOIN 11_db_pelicules AS p ON cap.idMovie = p.id
        WHERE cap.id = :id";

    $result = getData($query, ['id' => $id]);
    echo json_encode($result);

    // 2) Actor-serie tv
    // ruta GET => "/api/cinema/get/?actorSerie=35"
} else if ($slug === "actorSerie") {


    $query = "SELECT cas.idSerie, cas.idActor, cas.role, cas.id, s.name
        FROM 11_aux_cinema_actors_seriestv AS cas
        LEFT JOIN 11_db_cinema_series_tv AS s ON cas.idSerie = s.id
        WHERE cas.id = :id";

    $result = getData($query, ['id' => $id]);
    echo json_encode($result);
}
