<?php

use App\Application\Pais\Presenter\PaisResponse;
use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Pais\MysqlPaisRepository;
use App\Utils\AdminMiddleware;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = DatabaseConnection::getConnection();

$paisRepository = new MysqlPaisRepository($pdo);

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

// Llistat directors
// ruta GET => "/api/auxiliars/get/directors"
if ($slug === 'directors') {

    $grup = '0197b0881a27723c8ca798b4d2fe6c29';
    $groupBin = Uuid::toBinary($grup);

    $sql = <<<SQL
            SELECT p.id, CONCAT(p.cognoms, ', ', p.nom) AS nomComplet
            FROM %s p
            INNER JOIN %s r ON r.persona_id = p.id
            WHERE r.grup_id = :group_id;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_PERSONES, $pdo),
        qi(Tables::DB_PERSONES_GRUPS_RELACIONS, $pdo),

    );

    try {
        $params = [':group_id' => $groupBin];
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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat imatges pelicules
    // ruta GET => "/api/cinema/get/auxiliars/imgPelis"
} else if ($slug === 'imgPelis') {
    $sql = <<<SQL
            SELECT i.id, i.alt
            FROM %s AS i
            WHERE i.typeImg = :img
            ORDER BY i.alt ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_IMATGES, $pdo),
    );

    try {
        $img = 8;
        $params = [':img' => $img];
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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat imatges series
    // ruta GET => "/api/cinema/get/auxiliars/imgSeries"
} elseif ($slug === 'imgSeries') {

    $sql = <<<SQL
            SELECT i.id, i.alt
            FROM %s AS i
            WHERE i.typeImg = :img
            ORDER BY i.alt ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_IMATGES, $pdo),
    );

    try {
        $img = 7;
        $params = [':img' => $img];
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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat generes pelicules
    // ruta GET => "/api/cinema/get/auxiliars/?type=generesPelis"
} elseif ($slug === 'generesPelis') {

    $sql = <<<SQL
                SELECT g.id, g.genere
                FROM %s AS g
                ORDER BY g.genere ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CINEMA_GENERES, $pdo),
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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat idiomes
    // ruta GET => "/api/cinema/get/auxiliars/?type=llengues"
} else if ($slug === "llengues") {

    $db = new Database();
    $query = "SELECT i.id, i.idioma_ca
            FROM aux_idiomes AS i
            ORDER BY i.idioma_ca ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat Estat llibre
    // ruta GET => "/api/cinema/get/auxiliars/?type=estatLlibre"
} else if ($slug === "estatLlibre") {

    $db = new Database();
    $query = "SELECT i.id, i.estat
            FROM db_llibres_estats AS i
            ORDER BY i.estat ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat Editorials
    // ruta GET => "/api/auxiliars/get/editorials"
} else if ($slug === 'editorials') {

    try {

        $sql = <<<SQL
                SELECT e.id AS id, e.editorial
                FROM %s AS e
                ORDER BY e.editorial ASC
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::LLIBRES_EDITORIALS, $pdo)
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


    // Llistat Tipus llibre
    // ruta GET => "/api/auxiliars/get/tipusLlibre"
} else if ($slug === "tipusLlibre") {

    $db = new Database();
    $query = "SELECT i.id, i.nomTipus
            FROM db_llibres_tipus AS i
            ORDER BY i.nomTipus ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
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

    // 11) Llibre imatge
    // ruta GET => "/api/biblioteca/get/imatgesLlibres"
} else if ($slug === 'imatgesLlibres') {

    try {

        $sql = <<<SQL
                SELECT i.id, i.alt
                FROM %s AS i
                WHERE i.typeImg = 2
                ORDER BY i.alt ASC
                SQL;

        $query = sprintf(
            $sql,
            qi(Tables::DB_IMATGES, $pdo)
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

    // Llistat paisos
    // ruta GET => "/api/cinema/get/auxiliars/paisos"
} else if ($slug === "pais" || $slug === "paisos") {

    AdminMiddleware::handle();

    $paisos = $paisRepository->findAll();

    $data = array_map(
        fn($pais) => PaisResponse::toArray($pais),
        $paisos
    );

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );

    // Llistat provincies
    // ruta GET => "/api/cinema/get/auxiliars/provincies"
} else if ($slug === "provincies") {

    $sql = <<<SQL
            SELECT p.id, p.provincia_ca
            FROM %s AS p
            ORDER BY p.provincia_ca ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_PROVINCIES, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat complet imatges
    // ruta GET => "/api/auxiliars/get/llistatCompletImatges"
} else if ($slug === 'llistatCompletImatges') {


    $sql = <<<SQL
            SELECT i.id, i.typeImg, i.nom, t.name, i.dateCreated, i.nameImg, i.alt
            FROM %s AS i
            LEFT JOIN db_img_type AS t ON i.typeImg = t.id
            ORDER BY i.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_IMATGES_TIPUS, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    // GET : llistat imatges usuaris
    // URL: https://elliot.cat/api/auxiliars/get/imatgesUsuaris
} else if ($slug === "imatgesUsuaris") {

    $db = new Database();
    $query = "SELECT 
	      	i.id, i.nameImg, i.nom
            FROM db_img AS i
            WHERE i.typeImg = 18
            ORDER BY i.nom ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat imatges icones
    // URL: https://elliot.cat/api/auxiliars/get/imatgesIcones
} else if ($slug === "imatgesIcones") {

    $db = new Database();
    $query = "SELECT 
	      	i.id, i.nameImg, i.nom
            FROM db_img AS i
            WHERE i.typeImg = 19
            ORDER BY i.nom ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat ciutats
    // URL: https://elliot.cat/api/auxiliars/get/ciutats
} else if ($slug === "ciutats") {

    $sql = <<<SQL
            SELECT c.id, CONCAT(
                COALESCE(NULLIF(c.ciutat_ca, ''), c.ciutat),
                IF(p.pais_ca IS NOT NULL, CONCAT(' (', p.pais_ca, ')'), '')
            ) AS ciutat
            FROM %s AS c
            LEFT JOIN %s AS p ON c.pais_id = p.id
            ORDER BY ciutat COLLATE utf8mb4_unicode_ci ASC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat ciutats
    // URL: https://elliot.cat/api/auxiliars/get/llistatCiutats
} else if ($slug === "llistatCiutats") {

    $sql = <<<SQL
            SELECT c.id, c.ciutat, c.ciutat_ca, c.ciutat_en, c.descripcio, p.id AS idPais, c.created_at, c.updated_at, p.pais_ca
            FROM %s AS c
            LEFT JOIN %s AS p ON c.pais_id = p.id
            ORDER BY ciutat COLLATE utf8mb4_unicode_ci ASC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : Ciutat ID informació
    // URL: https://elliot.cat/api/auxiliars/get/ciutatId?id=33
} else if ($slug === "ciutatId") {

    $id = $_GET['id'] ?? null;

    // Validación rápida del UUID texto
    if (!$id || !preg_match('~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~i', $id)) {
        Response::error(MissatgesAPI::error('validacio'), ['Parametre "id" no és un UUID vàlid'], 400);
        return;
    }

    $sql = <<<SQL
        SELECT
          c.id,
          c.ciutat,
          c.ciutat_ca,
          c.ciutat_en,
          c.descripcio,
          c.pais_id
        FROM %s AS c
        WHERE c.id = :id
        LIMIT 1
        SQL;

    $query = sprintf($sql, qi(Tables::DB_CIUTATS, $pdo));

    try {
        $params = [':id' => uuid::toBinary($id)];
        $row = $db->getData($query, $params, true); // true => una sola fila

        if (!$row) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : Pais ID informació
    // URL: https://elliot.cat/api/auxiliars/get/paisId?id=33
} else if ($slug === "paisId") {

    $id = $_GET['id'] ?? null;

    // Validación rápida del UUID texto
    if (!$id || !preg_match('~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~i', $id)) {
        Response::error(MissatgesAPI::error('validacio'), ['Parametre "id" no és un UUID vàlid'], 400);
        return;
    }

    $sql = <<<SQL
        SELECT
          c.id, c.pais_ca, c.pais_en
        FROM %s AS c
        WHERE c.id = :id
        LIMIT 1
        SQL;

    $query = sprintf($sql, qi(Tables::DB_PAISOS, $pdo));

    try {
        $params = [':id' => uuid::toBinary($id)];
        $row = $db->getData($query, $params, true); // true => una sola fila

        if (!$row) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat perfils cv
    // URL: https://elliot.cat/api/auxiliars/get/perfilsCV
} else if ($slug === "perfilsCV") {

    $query = "SELECT p.id, p.nom_complet
            FROM db_curriculum_perfil AS p
            ORDER BY p.nom_complet ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat imatges empreses curriculums
    // URL: https://elliot.cat/api/auxiliars/get/imatgesEmpreses
} else if ($slug === "imatgesEmpreses") {

    $query = "SELECT 
	      	i.id, i.nameImg, i.nom
            FROM db_img AS i
            WHERE i.typeImg = 20
            ORDER BY i.nom ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat Experiencies
    // URL: https://elliot.cat/api/auxiliars/get/experiencies
} else if ($slug === "experiencies") {

    $query = "SELECT e.id, e.empresa
              FROM db_curriculum_experiencia_professional AS e
              ORDER BY e.empresa ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat Educacions
    // URL: https://elliot.cat/api/auxiliars/get/educacions
} else if ($slug === "educacions") {

    $query = "SELECT e.id, CONCAT(institucio, ' · ', data_inici) AS institucio_periode
              FROM db_curriculum_educacio AS e
              ORDER BY e.id ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat Educacions
    // URL: https://elliot.cat/api/auxiliars/get/auxiliarImatgesAutor
} else if ($slug === "auxiliarImatgesAutor") {

    $query = "SELECT 
	      	i.id, i.nom AS alt
            FROM db_img AS i
            WHERE i.typeImg = 1
            ORDER BY i.nom ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );;
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
    // GET : llistat Educacions
    // URL: https://elliot.cat/api/auxiliars/get/auxiliarImatgesSeries
} else if ($slug === "auxiliarImatgesSeries") {

    $query = "SELECT 
	      	i.id, i.nom AS alt
            FROM db_img AS i
            WHERE i.typeImg = 7
            ORDER BY i.nom ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }


    // GET : Auxiliar imatges Viatges i espais
    // URL: https://elliot.cat/api/auxiliars/get/auxiliarImatgesEspais
} else if ($slug === "auxiliarImatgesEspais") {

    $query = "SELECT 
	      	i.id, i.nom AS alt
            FROM db_img AS i
            WHERE i.typeImg IN (11, 17)
            ORDER BY i.nom ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // 4. Imatges espais
    // ruta GET => "/api/viatges/get/?llistatTipusEspais"
} else if ($slug === 'llistatTipusEspais') {

    $query = "SELECT t.id, t.tipus
    FROM db_viatges_espais_tipus AS t
    ORDER BY t.tipus ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // 4. Viatges: espais
    // ruta GET => "/api/viatges/get/espais"
} else if ($slug === 'espais') {

    $query = "SELECT t.id, t.nom
    FROM db_viatges_espais AS t
    ORDER BY t.nom ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // 4. Viatges
    // ruta GET => "/api/viatges/get/viatges"
} else if ($slug === 'viatges') {

    $query = "SELECT t.id, t.viatge
    FROM db_viatges_llistat AS t
    ORDER BY t.viatge ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat grups persones
    // URL: https://elliot.cat/api/auxiliars/get/grups
} else if ($slug === "grups") {

    $db = new Database();
    $query = "SELECT 
	        LOWER(CONCAT_WS('-',
            HEX(SUBSTR(id, 1, 4)),
            HEX(SUBSTR(id, 5, 2)),
            HEX(SUBSTR(id, 7, 2)),
            HEX(SUBSTR(id, 9, 2)),
            HEX(SUBSTR(id, 11, 6))
            )) AS id, grup_ca
            FROM db_persones_grups
            ORDER BY grup_ca ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat sexes
    // URL: https://elliot.cat/api/auxiliars/get/sexes
} else if ($slug === "sexes") {

    $query = [
        ["id" => 1, "nom" => "Home"],
        ["id" => 2, "nom" => "Dona"],
    ];

    try {
        $row = $query;

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $row,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat mesos
    // URL: https://elliot.cat/api/auxiliars/get/calendariDies
} else if ($slug === "calendariDies") {

    try {

        $rows = array_map(
            fn(int $d) => ["id" => $d, "dia" => $d],
            range(1, 31)
        );

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $rows,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat dies
    // URL: https://elliot.cat/api/auxiliars/get/calendariMesos
} else if ($slug === "calendariMesos") {

    try {
        $noms = [
            'Gener',
            'Febrer',
            'Març',
            'Abril',
            'Maig',
            'Juny',
            'Juliol',
            'Agost',
            'Setembre',
            'Octubre',
            'Novembre',
            'Desembre'
        ];

        $rows = [];
        foreach ($noms as $i => $nom) {
            $rows[] = ["id" => $i + 1, "mes" => $nom];
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $rows,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Estats clients
    // ruta => "https://elliot.cat/api/comptabilitat/get/estatsClients"
} else if ($slug === 'estatsClients') {

    $sql = <<<SQL
            SELECT s.estat, s.id
            FROM %s AS s
            ORDER BY s.ordre ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo)
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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat clients empresa
    // ruta GET => "/api/cinema/get/auxiliars/clients"
} else if ($slug === "clients") {

    $sql = <<<SQL
            SELECT c.id, c.clientEmpresa
            FROM %s AS c
            ORDER BY c.clientEmpresa ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat tipus IVA
    // ruta GET => "/api/cinema/get/auxiliars/tipusIVA"
} else if ($slug === "tipusIVA") {

    $sql = <<<SQL
            SELECT c.id, c.ivaPercen
            FROM %s AS c
            ORDER BY c.ivaPercen ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat estat facturació
    // ruta GET => "/api/cinema/get/auxiliars/estatFacturacio"
} else if ($slug === "estatFacturacio") {

    $sql = <<<SQL
            SELECT c.id, c.estat
            FROM %s AS c
            ORDER BY c.ordre ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat tipus pagament
    // ruta GET => "/api/cinema/get/auxiliars/tipusPagament"
} else if ($slug === "tipusPagament") {

    $sql = <<<SQL
            SELECT c.id, CONCAT(c.tipus, ' - ', c.notes) AS tipus_notes
            FROM %s AS c
            ORDER BY c.id ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat Factures clients
    // ruta GET => "/api/cinema/get/auxiliars/facturesClients"
} else if ($slug === "facturesClients") {

    $sql = <<<SQL
            SELECT c.id, CONCAT(c.id, ' - ', c.facConcepte) AS facConcepte
            FROM %s AS c
            ORDER BY c.id DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat Factures productes
    // ruta GET => "/api/cinema/get/auxiliars/productes"
} else if ($slug === "productes") {

    $sql = <<<SQL
            SELECT c.id2 AS id, c.producte
            FROM %s AS c
            ORDER BY c.producte ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat subtemes
    // URL: https://elliot.cat/api/auxiliars/get/subtemes
} else if ($slug === "subtemes") {

    $sql = <<<SQL
            SELECT s.id, s.sub_tema
            FROM %s AS s
            ORDER BY s.sub_tema ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SUBTEMES, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat temes
    // URL: https://elliot.cat/api/auxiliars/get/temes
} else if ($slug === "temes") {

    $sql = <<<SQL
            SELECT s.id, s.tema
            FROM %s AS s
            ORDER BY s.tema ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_TEMES, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat tipus links
    // URL: https://elliot.cat/api/auxiliars/get/tipusLinks
} else if ($slug === "tipusLinks") {

    $sql = <<<SQL
            SELECT s.id, s.tipus
            FROM %s AS s
            ORDER BY s.tipus ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_LINKS_TIPUS, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat tipus agenda
    // URL: https://elliot.cat/api/auxiliars/get/subtemes
} else if ($slug === "tipusAgenda") {

    try {
        $result = [
            [
                'id' => 'reunio',
                'tipus_ca' => 'Reunió'
            ],
            [
                'id' => 'visita_medica',
                'tipus_ca' => 'Visita mèdica'
            ],
            [
                'id' => 'videotrucada',
                'tipus_ca' => 'Videotrucada'
            ],
            [
                'id' => 'altre',
                'tipus_ca' => 'Altre'
            ]
        ];

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat estats agenda
    // URL: https://elliot.cat/api/auxiliars/get/estatsAgenda
} else if ($slug === "estatsAgenda") {

    try {
        $result = [
            [
                'id' => 'pendent',
                'estat_ca' => 'Pendent'
            ],
            [
                'id' => 'confirmat',
                'estat_ca' => 'Confirmat'
            ],
            [
                'id' => 'cancel·lat',
                'estat_ca' => 'Cancel·lat'
            ]
        ];

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : llistat imatges empreses curriculums
    // URL: https://elliot.cat/api/auxiliars/get/budgets
} else if ($slug === "budgets") {

    $db = new Database();
    $query = "SELECT 
	      	p.id, p.concepte
            FROM db_comptabilitat_pressupostos AS p
            ORDER BY concepte ASC";

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat Factures clients
    // ruta GET => "/api/cinema/get/auxiliars/projectes_categories"
} else if ($slug === "projectes_categories") {

    $sql = <<<SQL
            SELECT c.id, c.name
            FROM %s AS c
            ORDER BY c.id DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::PROJECTES_CATEGORIES, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat Projectes
    // ruta GET => "/api/auxiliars/projectes"
} else if ($slug === "projectes") {

    $sql = <<<SQL
            SELECT c.id, c.name
            FROM %s AS c
            ORDER BY c.id DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::PROJECTES, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    // GET : estat publicacio
    // URL: https://elliot.cat/api/auxiliars/get/estatsPublicacio
} else if ($slug === "estatsPublicacio") {

    try {
        $result = [
            [
                'id' => 'publicat',
                'post_status' => 'Publicat'
            ],
            [
                'id' => 'esborrany',
                'post_status' => 'Esborrany'
            ],
            [
                'id' => 'cancel·lat',
                'post_status' => 'Cancel·lat'
            ]
        ];

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : tipus publicacio
    // URL: https://elliot.cat/api/auxiliars/get/tipusPublicacio
} else if ($slug === "tipusPublicacio") {

    try {
        $result = [
            [
                'id' => 'post',
                'post_type' => 'Article'
            ],
            [
                'id' => 'historia_oberta',
                'post_type' => 'Història Oberta'
            ],
            [
                'id' => 'microblogging',
                'post_type' => 'Microblogging'
            ]
        ];

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat complet imatges
    // ruta GET => "/api/auxiliars/get/historiaCursos"
} else if ($slug === 'historiaCursos') {


    $sql = <<<SQL
            SELECT c.id, c.nameCa AS nomCurs
            FROM %s AS c
            ORDER BY c.nameCa ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo)

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat complet articles historia cat
    // ruta GET => "/api/auxiliars/get/blogArticlesCa"
} else if ($slug === 'blogArticlesCa') {

    $sql = <<<SQL
                SELECT id, post_title
                FROM %s
                WHERE lang = 1
                AND post_type = 'historia_oberta'
                ORDER BY post_date DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::BLOG, $pdo)

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat complet articles historia es
    // ruta GET => "/api/auxiliars/get/blogArticlesEs"
} else if ($slug === 'blogArticlesEs') {

    $sql = <<<SQL
                SELECT id, post_title
                FROM %s
                WHERE lang = 3
                AND post_type = 'historia_oberta'
                ORDER BY post_date DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::BLOG, $pdo)

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat complet articles historia en
    // ruta GET => "/api/auxiliars/get/blogArticlesEn"
} else if ($slug === 'blogArticlesEn') {

    $sql = <<<SQL
                SELECT id, post_title
                FROM %s
                WHERE lang = 2
                AND post_type = 'historia_oberta'
                ORDER BY post_date DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::BLOG, $pdo)

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat complet articles historia es
    // ruta GET => "/api/auxiliars/get/blogArticlesIt"
} else if ($slug === 'blogArticlesIt') {

    $sql = <<<SQL
                SELECT id, post_title
                FROM %s
                WHERE lang = 4
                AND post_type = 'historia_oberta'
                ORDER BY post_date DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::BLOG, $pdo)

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat EMissors factures
    // ruta GET => "/api/auxiliars/get/emissors"
} else if ($slug === 'emissors') {

    $sql = <<<SQL
                SELECT id, nom
                FROM %s
                ORDER BY nom DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo)

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat proveidors comptabilitat
    // ruta GET => "/api/cinema/get/auxiliars/proveidors"
} else if ($slug === "proveidors") {

    $sql = <<<SQL
            SELECT p.id, p.nom
            FROM %s AS p
            ORDER BY p.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
    // Llistat categories despeses
    // ruta GET => "/api/cinema/get/auxiliars/categories_despeses"
} else if ($slug === "categories_despeses") {

    $sql = <<<SQL
            SELECT p.id, p.nom
            FROM %s AS p
            ORDER BY p.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CATEGORIES_DESPESA, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat sub-categories despeses
    // ruta GET => "/api/cinema/get/auxiliars/sub_categories_despeses"
} else if ($slug === "sub_categories_despeses") {

    $sql = <<<SQL
            SELECT p.id, p.nom
            FROM %s AS p
            ORDER BY p.ordre ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_SUBCATEGORIES_DESPESA, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    // Llistat sub-categories despeses
    // ruta GET => "/api/cinema/get/auxiliars/sub_categories_despeses"
} else if ($slug === "sub_categories_despeses") {

    $sql = <<<SQL
            SELECT p.id, p.nom
            FROM %s AS p
            ORDER BY p.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_SUBCATEGORIES_DESPESA, $pdo),

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
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat mètodes pagament despeses
    // ruta GET => "/api/cinema/get/auxiliars/metodes_pagament_despeses"
} else if ($slug === "metodes_pagament_despeses") {

    // Array estático de métodos de pago
    $metodes = [
        ['id' => 'transferencia', 'label' => 'Transferència'],
        ['id' => 'targeta',       'label' => 'Targeta'],
        ['id' => 'efectiu',       'label' => 'Efectiu'],
        ['id' => 'domiciliacio',  'label' => 'Domiciliació'],
        ['id' => 'altres',        'label' => 'Altres'],
    ];

    try {

        if (empty($metodes)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $metodes,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat tipus de despeses
    // ruta GET => "/api/cinema/get/auxiliars/tipus_despeses"
} else if ($slug === "tipus_despeses") {

    // Array estático de métodos de pago
    $metodes = [
        ['id' => 'professional', 'label' => 'Professional'],
        ['id' => 'personal',    'label' => 'Personal'],
    ];

    try {

        if (empty($metodes)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $metodes,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat tipus de frequencies pagament
    // ruta GET => "/api/cinema/get/auxiliars/frequencies"
} else if ($slug === "frequencies") {

    // Array estático de métodos de pago
    $metodes = [
        ['id' => 'cap', 'label' => 'Cap'],
        ['id' => 'mensual',    'label' => 'Mensual'],
        ['id' => 'trimestral',    'label' => 'Trimestral'],
        ['id' => 'anual',    'label' => 'Anual'],
    ];

    try {

        if (empty($metodes)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $metodes,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
