<?php


use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$db = new Database();
$pdo = $db->getPdo();
$slug = $routeParams[0];

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


// 1) Llistat categories enllaços
// ruta GET => "/api/links/llistatTemes"
if ($slug === 'llistatTemes') {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(t.id) AS id, tema_ca
            FROM %s AS t
            ORDER BY t.tema_ca ASC
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

    // 1) Llistat enllaços
    // ruta GET => "/api/links/llistatLinks"
} else if ($slug === 'llistatLinks') {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(l.id) AS id, l.nom, l.web, l.dateCreated, l.dateModified, st.tema_ca, s.sub_tema_ca, t.tipus_ca, i.idioma_ca
            FROM %s AS l
            LEFT JOIN %s AS s ON s.id = l.sub_tema_id
            LEFT JOIN %s AS st ON s.tema_id = st.id
            LEFT JOIN %s AS t ON l.tipus = t.id
            LEFT JOIN %s AS i ON l.lang = i.id
            ORDER BY l.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_LINKS, $pdo),
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo),
        qi(Tables::DB_LINKS_TIPUS, $pdo),
        qi(Tables::DB_IDIOMES, $pdo),
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

    // 2) Llistat enllaços segons una categoria en concret
    // ruta GET => "/api/links/?type=categoria$id=11"
} else if ($slug === 'categoriaId') {
    $id = $_GET['id'];

    $stmt = "SELECT t.id AS idTema, t.tema_ca AS tema, g.categoria_ca AS genre
            FROM aux_temes AS t
            INNER JOIN aux_categories AS g ON t.idGenere = g.id
            INNER JOIN db_links AS l ON l.cat = t.id
            WHERE t.idGenere=?
            GROUP BY t.id
            ORDER BY t.tema_ca ASC";


    // 3) Llistat enllaços segons un topic concret
    // ruta GET => "/api/links/?type=topic$id=11"
} else if ($slug === 'temaId') {

    $id = $_GET['id'];

    $stmt = "SELECT l.web AS url, l.nom, t.id AS idTema, t.tema_ca AS tema, l.id AS linkId, l.lang, i.idioma_ca, ty.id AS idType, ty.type_ca, g.categoria_ca AS genre, g.id AS idCategoria, l.dateCreated
        FROM aux_temes AS t
        INNER JOIN aux_categories AS g ON t.idGenere = g.id
        INNER JOIN db_links AS l ON l.cat = t.id
        LEFT JOIN db_links_type AS ty ON ty.id = l.tipus
        LEFT JOIN aux_idiomes AS i ON l.lang = i.id
        WHERE t.id = :id
        ORDER BY l.nom ASC";


    // 4) Llistat de topics
    // ruta GET => "/api/adreces/get/?type=all-topics"
} else if ($slug === 'llistatTemes') {
    global $conn;

    $stmt = "SELECT t.id AS idTema, t.tema_ca AS tema, g.categoria_ca AS genre, g.id AS idGenre
            FROM aux_temes AS t
            INNER JOIN aux_categories AS g ON t.idGenere = g.id
            INNER JOIN db_links AS l ON l.cat = t.id
            GROUP BY t.id
            ORDER BY t.tema_ca ASC";


    // 5) Ruta para sacar 1 enlace y actualizarlo 
    // ruta GET => "/api/adreces/?linkId=11"
} elseif ((isset($_GET['linkId']))) {
    $id = $_GET['linkId'];
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT l.id, l.web, l.nom, l.cat, l.tipus, l.lang
            FROM db_links AS l
            WHERE l.id=?"
    );

    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(null);  // Devuelve un objeto JSON nulo si no hay resultados
    } else {
        // Solo obtenemos la primera fila ya que parece ser una búsqueda por ID
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row);  // Codifica la fila como un objeto JSON
    }
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
