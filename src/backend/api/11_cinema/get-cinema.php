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

    $query = "SELECT c.id, c.pelicula, c.pelicula_es, c.any, c.dataVista, d.nom, d.cognoms, p.pais_cat, g.genere_ca, i.idioma_ca, c.slug
            FROM 11_db_pelicules AS c
            LEFT JOIN db_persones AS d ON c.director = d.id
            LEFT JOIN db_countries AS p ON c.pais = p.id
            LEFT JOIN 11_aux_cinema_generes AS g ON c.genere = g.id
            LEFT JOIN aux_idiomes AS i ON c.lang = i.id
            ORDER BY c.any DESC";

    $result = getData($query);
    echo json_encode($result);

    // GET : llistat de gèneres
    // URL: https://elliot.cat/api/cinema/get/generes
} else if ($slug === "generes") {
    $query = "SELECT c.id, c.pelicula, c.pelicula_es, c.any, c.dataVista, d.nom, d.cognoms, p.pais_cat, g.genere_ca, i.idioma_ca, c.slug
            FROM 11_db_pelicules AS c
            INNER JOIN 11_aux_cinema_directors AS d ON c.director = d.id
            INNER JOIN db_countries AS p ON c.pais = p.id
            LEFT JOIN 11_aux_cinema_generes AS g ON c.genere = g.id
            LEFT JOIN aux_idiomes AS i ON c.lang = i.id
            WHERE c.genere = $idGen
            ORDER BY c.any DESC";

    $result = getData($query);
    echo json_encode($result);

    // GET : llistat de sèries tv
    // URL: https://elliot.cat/api/cinema/get/series
} else if ($slug === "series") {

    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT tv.id, tv.name, tv.startYear, tv.endYear,tv.season, tv.chapter, d.nom, d.cognoms, id.idioma_ca, g.genere, c.pais_ca, tv.slug, d.slug
                FROM %s AS tv
                LEFT JOIN %s AS d ON tv.director_id = d.id
                LEFT JOIN %s AS c ON tv.pais_id = c.id
                LEFT JOIN %s AS id ON tv.lang = id.id
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
    // URL: https://elliot.cat/api/cinema/get/serie?slug=benvinguts-a-la-familia
} else if ($slug === "serie") {
    $query = "SELECT tv.id, tv.name, tv.startYear, tv.endYear, tv.season, tv.chapter, d.nom, d.cognoms, id.idioma_ca, tv.genre, tv.producer, c.pais_cat, img.nameImg, g.genere_ca, tv.descripcio, d.id AS idDirector, d.slug AS slugDirector, c.id AS idPais, img.id AS idImg, id.id As idLang, g.id AS idGen, pr.id AS idProductora, pr.productora, tv.dateCreated, tv.dateModified, tv.slug
            FROM 11_db_cinema_series_tv AS tv
            INNER JOIN db_persones AS d ON tv.director = d.id
            INNER JOIN db_countries AS c ON tv.country = c.id
            LEFT JOIN db_img AS img ON tv.img = img.id
            INNER JOIN aux_idiomes AS id ON tv.lang = id.id
            LEFT JOIN 11_aux_cinema_generes AS g ON tv.genre = g.id
            LEFT JOIN 11_aux_cinema_productores AS pr ON tv.producer = pr.id
            WHERE tv.slug = :slug";

    $result = getData($query, ['slug' => $param], true);
    echo json_encode($result);

    // GET : fitxa pel·lícula
    // URL: https://elliot.cat/api/cinema/get/pelicula?slug=io-capitano
} else if ($slug === "pelicula") {
    $query = "SELECT p.id, p.pelicula, p.slug, p.pelicula_es, p.any, p.descripcio, p.dataVista, p.dateCreated, p.dateModified, d.nom, d.cognoms, id.idioma_ca, c.pais_cat, img.nameImg, g.genere_ca, d.id AS idDirector, c.id AS idPais, img.id AS idImg, id.id As idLang, g.id AS idGen, d.slug AS slugDirector
            FROM 11_db_pelicules AS p
            LEFT JOIN db_persones AS d ON p.director = d.id
            LEFT JOIN db_countries AS c ON p.pais = c.id
            LEFT JOIN db_img AS img ON p.img = img.id
            LEFT JOIN aux_idiomes AS id ON p.lang = id.id
            LEFT JOIN 11_aux_cinema_generes AS g ON p.genere = g.id
            WHERE p.slug = :slug";

    $result = getData($query, ['slug' => $param], true);
    echo json_encode($result);

    // GET : Actors que participen en una serie determinada
    // URL: "https://elliot.cat/api/cinema/get/actors-serie?slug=benvinguts-a-la-familia"
} else if ($slug === "actors-serie") {
    $query = "SELECT a.nom, a.cognoms, a.id AS idActor, sa.role, img.nameImg, sa.id AS idCast, a.slug
        FROM 11_db_cinema_series_tv AS s
        LEFT JOIN 11_aux_cinema_actors_seriestv AS sa on s.id = sa.idSerie
        INNER JOIN db_persones AS a ON a.id = sa.idActor
        LEFT JOIN db_img AS img ON a.img = img.id
        WHERE s.slug = :slug";

    $result = getData($query, ['slug' => $param], true);
    echo json_encode($result);

    // 2) Actors que participen en una pelicula
    // ruta GET => "/api/cinema/get/?actors-pelicula=35"
} elseif (isset($_GET['actors-pelicula'])) {
} else if ($slug === "actors-serie") {
    $query = "SELECT a.nom, a.cognoms, a.id AS idActor, sa.role, img.nameImg, sa.id AS idCast, a.slug
        FROM 11_db_pelicules AS s
        INNER JOIN 11_aux_cinema_actors_pelicules AS sa on s.id = sa.idMovie
        LEFT JOIN db_persones AS a ON a.id = sa.idActor
        LEFT JOIN db_img AS img ON a.img = img.id
        WHERE s.slug = :slug
        ORDER BY a.cognoms ASC";

    $result = getData($query, ['slug' => $param], true);
    echo json_encode($result);

    // 1) Llistat actors
    // ruta GET => "/api/cinema/get/?actors"
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

    // 1) Llistat directors
    // ruta GET => "/api/cinema/get/?directors"
} else if ($slug === "directors") {

    $query = "SELECT a.id, CONCAT(a.cognoms, ', ', a.nom) AS nomComplet, i.nameImg, c.pais_cat, a.slug, a.anyNaixement, a.anyDefuncio 
            FROM db_persones AS a
            LEFT JOIN db_img AS i ON a.img = i.id
            LEFT JOIN db_countries AS c ON a.paisAutor = c.id
            WHERE grup = '2'
            ORDER BY a.cognoms ASC";

    $result = getData($query);
    echo json_encode($result);

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
    // ruta GET => "/api/cinema/get/actor-pelicules?actor=nao-albert"
} else if ($slug === "actor-pelicules") {
    $actor = $_GET['actor'];
    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT p.pelicula AS titol, sa.role, p.any AS anyInici, p.slug
                FROM %s AS p
                LEFT JOIN %s AS sa ON p.id = sa.idMovie
                LEFT JOIN %s AS pe ON pe.id = sa.idActor 
                WHERE pe.slug = :slug
                ORDER BY p.pelicula ASC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_PELICULES, $pdo),
        qi(Tables::CINEMA_ACTORS_PELICULES, $pdo),
        qi(Tables::DB_PERSONES, $pdo)
    );

    try {
        $params = [':slug' => $actor];
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
    // ruta GET => "/api/cinema/get/actor-series?actor=nao-albert"
} else if ($slug === "actor-series") {

    $actor = $_GET['actor'];
    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT s.name AS titol, sa.role, s.startYear AS anyInici, s.endYear AS anyFi, s.slug
                FROM %s AS s
                LEFT JOIN %s AS sa ON s.id = sa.idSerie
                LEFT JOIN %s AS pe ON pe.id = sa.idActor 
                WHERE pe.slug = :slug
                ORDER BY s.name ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_SERIES_TV, $pdo),
        qi(Tables::CINEMA_ACTORS_SERIES, $pdo),
        qi(Tables::DB_PERSONES, $pdo)
    );

    try {
        $params = [':slug' => $actor];
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
