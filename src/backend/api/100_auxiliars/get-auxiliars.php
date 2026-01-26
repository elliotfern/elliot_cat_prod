<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
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

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$db = new Database();
$pdo = $db->getPdo();
$slug = $routeParams[0];

/*
 * BACKEND DB AUXILIARS
 * FUNCIONS
 * @
 */


// 1) AUXILIARS
// Llistat directors
// ruta GET => "/api/auxiliars/get/?type=directors"
if (isset($_GET['type']) && $_GET['type'] == 'directors') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT d.id, CONCAT(d.cognoms, ', ', d.nom) AS nomComplet
            FROM 11_aux_cinema_directors AS d
            ORDER BY d.cognoms ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat productors
    // ruta GET => "/api/cinema/get/auxiliars/?type=productores"
} elseif (isset($_GET['type']) && $_GET['type'] == 'productores') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT p.id, p.productora
            FROM 11_aux_cinema_productores AS p
            ORDER BY p.productora ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat imatges pelicules
    // ruta GET => "/api/cinema/get/auxiliars/?type=imgPelis"
} elseif (isset($_GET['type']) && $_GET['type'] == 'imgPelis') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT i.id, i.alt
            FROM db_img AS i
            WHERE i.typeImg = 8
            ORDER BY i.alt ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat imatges series
    // ruta GET => "/api/cinema/get/auxiliars/?type=imgSeries"
} elseif (isset($_GET['type']) && $_GET['type'] == 'imgSeries') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT i.id, i.alt
            FROM db_img AS i
            WHERE i.typeImg = 7
            ORDER BY i.alt ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat generes pelicules
    // ruta GET => "/api/cinema/get/auxiliars/?type=generesPelis"
} elseif (isset($_GET['type']) && $_GET['type'] == 'generesPelis') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT g.id, g.genere_ca
            FROM 11_aux_cinema_generes AS g
            ORDER BY g.genere_ca ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat idiomes pelicules
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

    // Llistat paisos
    // ruta GET => "/api/cinema/get/auxiliars/paisos"
} else if ($slug === "pais" || $slug === "paisos") {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(p.id) AS id, p.pais_ca
            FROM %s AS p
            ORDER BY p.pais_ca ASC
            SQL;

    $query = sprintf(
        $sql,
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

    // Llistat provincies
    // ruta GET => "/api/cinema/get/auxiliars/provincies"
} else if ($slug === "provincies") {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(p.id) AS id, p.provincia_ca
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

    // Llistat complet imatges
    // ruta GET => "/api/auxiliars/get/?llistatCompletImatges"
} else if (isset($_GET['llistatCompletImatges'])) {

    $query = "SELECT i.id, i.typeImg, i.nom, t.name, i.dateCreated, i.nameImg
            FROM db_img AS i
            LEFT JOIN db_img_type AS t ON i.typeImg = t.id
            ORDER BY i.nom ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);

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

    // GET : llistat ciutats
    // URL: https://elliot.cat/api/auxiliars/get/ciutats
} else if ($slug === "ciutats") {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(c.id) AS id, c.ciutat_ca, c.updated_at, c.created_at, c.ciutat_en, c.ciutat, uuid_bin_to_text(p.id) AS idPais, p.pais_ca
            FROM %s AS c
            LEFT JOIN %s AS p ON c.pais_id = p.id
            ORDER BY c.ciutat ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo),

    );

    try {

        $result = $db->getData($query);

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

    // GET : llistat ciutats
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
          uuid_bin_to_text(c.id) AS id,
          c.ciutat,
          c.ciutat_ca,
          c.ciutat_en,
          c.descripcio,
          uuid_bin_to_text(c.pais_id) AS pais_id
        FROM %s AS c
        WHERE c.id = uuid_text_to_bin(:id)
        LIMIT 1
        SQL;

    $query = sprintf($sql, qi(Tables::DB_CIUTATS, $pdo));

    try {
        $params = [':id' => $id];
        $row = $db->getData($query, $params, true); // true => una sola fila

        if (!$row) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
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

    // GET : llistat perfils cv
    // URL: https://elliot.cat/api/auxiliars/get/perfilsCV
} else if ($slug === "perfilsCV") {

    $db = new Database();
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

    // GET : llistat imatges empreses curriculums
    // URL: https://elliot.cat/api/auxiliars/get/imatgesEmpreses
} else if ($slug === "imatgesEmpreses") {

    $db = new Database();
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

    // GET : llistat Experiencies
    // URL: https://elliot.cat/api/auxiliars/get/experiencies
} else if ($slug === "experiencies") {

    $db = new Database();
    $query = "SELECT e.id, e.empresa
              FROM db_curriculum_experiencia_professional AS e
              ORDER BY e.empresa ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat Educacions
    // URL: https://elliot.cat/api/auxiliars/get/educacions
} else if ($slug === "educacions") {

    $db = new Database();
    $query = "SELECT e.id, CONCAT(institucio, ' · ', data_inici) AS institucio_periode
              FROM db_curriculum_educacio AS e
              ORDER BY e.id ASC";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat Educacions
    // URL: https://elliot.cat/api/auxiliars/get/auxiliarImatgesAutor
} else if ($slug === "auxiliarImatgesAutor") {

    $db = new Database();
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

        Response::success(MissatgesAPI::success('get'), $row, 200);
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

        Response::success(MissatgesAPI::success('get'), $row, 200);
        return;
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

        Response::success(MissatgesAPI::success('get'), $row, 200);
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

        Response::success(MissatgesAPI::success('get'), $rows, 200);
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

        Response::success(MissatgesAPI::success('get'), $rows, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Estats clients
    // ruta => "https://elliot.cat/api/comptabilitat/get/estatsClients"
} else if ($slug === 'estatsClients') {

    $sql = <<<SQL
            SELECT s.estatNom AS estat_ca, s.id
            FROM %s AS s
            ORDER BY s.estatNom ASC
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

    // Llistat estat facturació
    // ruta GET => "/api/cinema/get/auxiliars/estatFacturacio"
} else if ($slug === "estatFacturacio") {

    $sql = <<<SQL
            SELECT c.id, c.estat
            FROM %s AS c
            ORDER BY c.estat ASC
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

    // Llistat tipus pagament
    // ruta GET => "/api/cinema/get/auxiliars/tipusPagament"
} else if ($slug === "tipusPagament") {

    $sql = <<<SQL
            SELECT c.id, c.tipusNom
            FROM %s AS c
            ORDER BY c.tipusNom ASC
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

    // Llistat Factures productes
    // ruta GET => "/api/cinema/get/auxiliars/productes"
} else if ($slug === "productes") {

    $sql = <<<SQL
            SELECT c.id, c.producte
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

    // GET : llistat subtemes
    // URL: https://elliot.cat/api/auxiliars/get/subtemes
} else if ($slug === "subtemes") {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(s.id) AS id, s.sub_tema_ca
            FROM %s AS s
            ORDER BY s.sub_tema_ca ASC
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

    // GET : llistat temes
    // URL: https://elliot.cat/api/auxiliars/get/temes
} else if ($slug === "temes") {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(s.id) AS id, s.tema_ca
            FROM %s AS s
            ORDER BY s.tema_ca ASC
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

    // GET : llistat tipus links
    // URL: https://elliot.cat/api/auxiliars/get/tipusLinks
} else if ($slug === "tipusLinks") {

    $sql = <<<SQL
            SELECT s.id, s.tipus_ca
            FROM %s AS s
            ORDER BY s.tipus_ca ASC
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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
