<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use Ramsey\Uuid\Uuid;

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);


// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


if ((isset($_GET['type']) && $_GET['type'] == 'convertirId')) {
    // Configuración de PDO
    $db = new Database();

    // Seleccionar registros que aún no tienen UUID (id NULL o en blanco)
    $query = "SELECT id, persona_id, grup_id FROM db_persones_grups_relacions";

    try {
        $params = [];
        $result = $db->getData($query, $params, false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        var_dump($result);

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
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 2) Llistat llibres
    // ruta GET => "/api/library/books/all"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'totsLlibres')) {
    $db = new Database();

    $query = "SELECT LOWER(CONCAT_WS('-', 
        SUBSTR(HEX(b.id), 1, 8),
        SUBSTR(HEX(b.id), 9, 4),
        SUBSTR(HEX(b.id), 13, 4),
        SUBSTR(HEX(b.id), 17, 4),
        SUBSTR(HEX(b.id), 21) )) AS id, b.titol, b.any, b.slug, 
        a.id AS idAutor, a.cognoms AS AutCognom1, a.nom AS AutNom, a.slug AS slugAuthor,
        g.tema_ca AS nomGenCat,
        sg.sub_tema_ca
        FROM " . Tables::LLIBRES . " AS b
        LEFT JOIN " . Tables::LLIBRES_AUTORS . " AS al ON b.id = al.llibre_id
        LEFT JOIN " . Tables::PERSONES . " AS a ON al.autor_id = a.id2
        LEFT JOIN " . Tables::AUX_SUB_TEMES . " AS sg ON b.sub_tema_id = sg.id2
        LEFT JOIN " . Tables::AUX_TEMES . " AS g ON sg.tema_id = g.id2
        LEFT JOIN " . Tables::LLIBRES_EDITORIALS . " AS be ON b.editorial_id = be.id
        WHERE b.tipus_id = UNHEX('0197ac5b7106704b96c60728ace151f3')
        ORDER BY b.titol ASC";

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

    // 3) Llistat autors
    // ruta GET => "https://elliot.cat/api/biblioteca/get/authors"
} elseif (isset($_GET['type']) && $_GET['type'] == 'totsAutors') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT a.id, a.nom AS AutNom, a.cognoms AS AutCognom1, a.slug, a.anyNaixement AS yearBorn, a.anyDefuncio AS yearDie, c.pais_cat AS country, c.id AS idCountry, i.nameImg,
        GROUP_CONCAT(DISTINCT g.grup_ca ORDER BY g.grup_ca SEPARATOR ', ') AS grup
        FROM db_persones AS a
        LEFT JOIN db_countries AS c ON a.paisAutor = c.id
        LEFT JOIN db_img AS i ON a.img = i.id
        LEFT JOIN db_persones_grups_relacions AS rel ON a.id2 = rel.persona_id
        LEFT JOIN db_persones_grups AS g ON rel.grup_id = g.id
        WHERE g.id IN (
            UNHEX(REPLACE('0197b088-1a25-72c4-8b5b-d7e2ee27de7c', '-', '')),
            UNHEX(REPLACE('0197b088-1a27-723c-8ca7-98b4d2fe6c29', '-', ''))
            )  
        GROUP BY a.id
        ORDER BY a.cognoms"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

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


    // 5) Authors page
    // ruta GET => "/api/biblioteca/get/?autorSlug=josep-fontana"
} elseif (isset($_GET['autorSlug'])) {
    $autorSlug = $_GET['autorSlug'];
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT a.id, a.cognoms, a.nom, p.pais_cat, a.anyNaixement, a.anyDefuncio, p.id AS idPais, o.professio_ca, i.nameImg, a.web, a.dateCreated, a.dateModified, a.descripcio, a.slug, a.img AS idImg, a.ocupacio AS idOcupacio, a.grup AS idGrup,
        a.sexe, a.mesNaixement, a.diaNaixement, a.mesDefuncio, a.diaDefuncio, c1.city AS ciutatNaixement, c2.city AS ciutatDefuncio, a.descripcioCast, a.descripcioEng, a.descripcioIt
                FROM db_persones AS a
                LEFT JOIN db_countries AS p ON a.paisAutor = p.id
                LEFT JOIN aux_professions AS o ON a.ocupacio = o.id
                LEFT JOIN db_img AS i ON a.img = i.id
                LEFT JOIN db_cities AS c1 ON a.ciutatNaixement = c1.id
                LEFT JOIN db_cities AS c2 ON a.ciutatDefuncio = c2.id
                WHERE a.slug = :slug");
    $stmt->execute(['slug' => $autorSlug]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(null);  // Devuelve un objeto JSON nulo si no hay resultados
    } else {
        // Solo obtenemos la primera fila ya que parece ser una búsqueda por ID
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row);  // Codifica la fila como un objeto JSON
    }

    // 6) Book page
    // ruta GET => "/api/biblioteca/get/?llibreSlug=el-por-bien-del-imperio"
} elseif ((isset($_GET['llibreSlug']))) {
    $slug = $_GET['llibreSlug'];
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT b.id,  b.titol, b.titolEng, b.slug, b.any, b.dateCreated, b.dateModified, b.idGen, b.subGen, b.lang, b.tipus, b.estat, b.idEd, b.img,
        a.nom, a.cognoms, a.id AS idAutor, a.slug AS slugAutor, i.nameImg, t.nomTipus, e.editorial, g.genere_cat, id.idioma_ca, a.slug AS slugAutor, sg.sub_genere_cat,
        el.estat
                FROM 08_db_biblioteca_llibres AS b
                INNER JOIN db_img AS i ON b.img = i.id
                INNER JOIN db_persones AS a ON b.autor = a.id
                INNER JOIN 08_aux_biblioteca_tipus as t on b.tipus = t.id
                INNER JOIN 08_aux_biblioteca_editorials AS e ON b.idEd = e.id
                INNER JOIN 08_aux_biblioteca_generes_literaris AS g ON b.idGen = g.id
                LEFT JOIN 08_aux_biblioteca_sub_generes_literaris AS sg ON b.subGen = sg.id
                LEFT JOIN 08_aux_biblioteca_estat_llibre AS el ON b.estat = el.id
                INNER JOIN aux_idiomes AS id ON b.lang = id.id
                WHERE b.slug = :slug");
    $stmt->execute(['slug' => $slug]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(null);  // Devuelve un objeto JSON nulo si no hay resultados
    } else {
        // Solo obtenemos la primera fila ya que parece ser una búsqueda por ID
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row);  // Codifica la fila como un objeto JSON
    }


    // 8) Movement author
    // ruta GET => "/api/library/moviment"
} elseif (isset($_GET['moviment'])) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT m.id, m.moviment_ca AS movement_ca
                FROM 08_aux_biblioteca_moviments AS m
                ORDER BY m.moviment_ca"
    );
    $stmt->execute();

    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // 9) country
    // ruta GET => "/api/places/pais"
} elseif (isset($_GET['pais'])) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT c.id, c.pais_cat AS pais_ca
                FROM db_countries AS c
                ORDER BY c.pais_cat"
    );
    $stmt->execute();

    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // 10) image author
    // ruta GET => "/api/biblioteca/?type=auxiliarImatgesAutor"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'auxiliarImatgesAutor')) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT i.id, CONCAT(i.nom, ' (', t.name, ')') AS alt
            FROM db_img AS i
            LEFT JOIN db_img_type AS t ON i.typeImg = t.id
            WHERE i.typeImg IN (1, 5, 9, 14)
            ORDER BY i.nom"
    );
    $stmt->execute();

    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // 10) ruta estat del llibre
    // ruta GET => "/api/biblioteca/auxiliars/?estatLlibre"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'estatLlibre')) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT e.id, e.estat
            FROM 08_aux_biblioteca_estat_llibre AS e
            ORDER BY e.estat"
    );
    $stmt->execute();

    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);


    // 11) Llibre imatge
    // ruta GET => "/api/biblioteca/auxiliars/?type=oficis"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'imatgesLlibres')) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT i.id, i.alt
                FROM db_img AS i
                WHERE i.typeImg = 2
                ORDER BY i.alt ASC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // 11) Editorials
    // ruta GET => "/api/biblioteca/auxiliars/?type=editorials"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'editorials')) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT e.id, e.editorial
                FROM 08_aux_biblioteca_editorials AS e
                ORDER BY e.editorial ASC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // 11) Gèneres
    // ruta GET => "/api/biblioteca/auxiliars/?type=llengues"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'llengues')) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT l.id, l.idioma_ca 
                    FROM aux_idiomes AS l
                    ORDER BY l.idioma_ca ASC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // 11) Gèneres
    // ruta GET => "/api/biblioteca/auxiliars/?type=tipus"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'tipus')) {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT t.nomTipus, t.id
                    FROM 08_aux_biblioteca_tipus AS t
                    ORDER BY t.nomTipus ASC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);


    // 11) PROFESSIO
    // ruta GET => "/api/biblioteca/auxiliars/?type=tipus"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'professio')) {

    /** @var PDO $conn */
    global $conn;
    $query = "SELECT p.id, p.professio_ca
                    FROM aux_professions AS p
                    ORDER BY p.professio_ca ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

    // 11) moviment
    // ruta GET => "/api/biblioteca/auxiliars/?type=tipus"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'moviment')) {

    /** @var PDO $conn */
    global $conn;
    $query = "SELECT m.id, m.moviment_ca
                    FROM 08_aux_biblioteca_moviments AS m
                    ORDER BY m.moviment_ca ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

    // 11) pais
    // ruta GET => "/api/biblioteca/auxiliars/?type=tipus"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'pais')) {

    /** @var PDO $conn */
    global $conn;
    $query = "SELECT c.id, c.pais_cat
                    FROM  db_countries AS c
                    ORDER BY c.pais_cat ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

    // 12) classificació grup persona
    // ruta GET => "/api/biblioteca/auxiliars/?type=grup"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'grup')) {

    /** @var PDO $conn */
    global $conn;
    $query = "SELECT 
                LOWER(CONCAT_WS('-',
                    SUBSTR(HEX(id), 1, 8),
                    SUBSTR(HEX(id), 9, 4),
                    SUBSTR(HEX(id), 13, 4),
                    SUBSTR(HEX(id), 17, 4),
                    SUBSTR(HEX(id), 21)
                )) AS id,
                grup_ca
              FROM db_persones_grups
              ORDER BY grup_ca ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

    // 11) sexe
    // ruta GET => "/api/biblioteca/auxiliars/?type=sexe"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'sexe')) {

    /** @var PDO $conn */
    global $conn;
    $query = "SELECT s.id, s.genereCa
                    FROM aux_persones_genere AS s
                    ORDER BY s.genereCa ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

    // 11) ciutats
    // ruta GET => "/api/biblioteca/auxiliars/?type=ciutat"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'ciutat')) {

    /** @var PDO $conn */
    global $conn;
    $query = "SELECT c.id, c.city
                    FROM db_cities AS c
                    ORDER BY c.city ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

    // 11) calendari: dies
    // ruta GET => "/api/biblioteca/auxiliars/?type=calendariDies"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'calendariDies')) {
    function obtenerDias()
    {
        $dias = [];
        for ($i = 1; $i <= 31; $i++) {
            $dias[] = [
                'id' => $i,
                'dia' => $i
            ];
        }
        return $dias;
    }

    $data = obtenerDias();

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

    // 11) calendari: mesos
    // ruta GET => "/api/biblioteca/auxiliars/?type=calendariMesos"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'calendariMesos')) {
    function obtenerMesos()
    {
        $meses = [
            ['id' => 1, 'mes' => 'Gener'],
            ['id' => 2, 'mes' => 'Febrer'],
            ['id' => 3, 'mes' => 'Març'],
            ['id' => 4, 'mes' => 'Abril'],
            ['id' => 5, 'mes' => 'Maig'],
            ['id' => 6, 'mes' => 'Juny'],
            ['id' => 7, 'mes' => 'Juliol'],
            ['id' => 8, 'mes' => 'Agost'],
            ['id' => 9, 'mes' => 'Setembre'],
            ['id' => 10, 'mes' => 'Octubre'],
            ['id' => 11, 'mes' => 'Novembre'],
            ['id' => 12, 'mes' => 'Desembre']
        ];
        return $meses;
    }

    $data = obtenerMesos();

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
