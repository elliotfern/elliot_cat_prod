<?php

use App\Config\Database;

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
} else {


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
        $autorSlug = $_GET['persona'];
        $db = new Database();

        $query = "SELECT LOWER(CONCAT_WS('-', 
        SUBSTR(HEX(a.id2), 1, 8),
        SUBSTR(HEX(a.id2), 9, 4),
        SUBSTR(HEX(a.id2), 13, 4),
        SUBSTR(HEX(a.id2), 17, 4),
        SUBSTR(HEX(a.id2), 21)
         )) AS id, a.cognoms, a.nom, p.pais_cat, a.anyNaixement, a.anyDefuncio, p.id AS idPais, i.nameImg, i.alt, a.web, a.dateCreated, a.dateModified, a.descripcio, a.slug, a.img AS idImg,
        a.sexe AS idSexe, a.mesNaixement, a.diaNaixement, a.mesDefuncio, a.diaDefuncio, c1.city AS ciutatNaixement, c2.city AS ciutatDefuncio, a.descripcioCast, a.descripcioEng, a.descripcioIt, a.ciutatNaixement AS idCiutatNaixement, a.ciutatDefuncio AS idCiutatDefuncio,
          GROUP_CONCAT(
            DISTINCT LOWER(CONCAT_WS('-',
            SUBSTR(HEX(g.id), 1, 8),
            SUBSTR(HEX(g.id), 9, 4),
            SUBSTR(HEX(g.id), 13, 4),
            SUBSTR(HEX(g.id), 17, 4),
            SUBSTR(HEX(g.id), 21)
            )) ORDER BY g.grup_ca SEPARATOR ','
        ) AS grup_ids,
         GROUP_CONCAT(DISTINCT g.grup_ca ORDER BY g.grup_ca SEPARATOR ', ') AS grup
                FROM db_persones AS a
                LEFT JOIN db_countries AS p ON a.paisAutor = p.id
                LEFT JOIN db_img AS i ON a.img = i.id
                LEFT JOIN db_cities AS c1 ON a.ciutatNaixement = c1.id
                LEFT JOIN db_cities AS c2 ON a.ciutatDefuncio = c2.id
                LEFT JOIN db_persones_grups_relacions AS rel ON a.id2 = rel.persona_id
                LEFT JOIN db_persones_grups AS g ON rel.grup_id = g.id
                WHERE a.slug = :slug";

        $params = [':slug' => $autorSlug];
        $result = $db->getData($query, $params, true);

        if ($result) {
            // Convertir grup_ids string a array
            $result['grup_ids'] = $result['grup_ids'] ? explode(',', $result['grup_ids']) : [];
        }

        echo json_encode($result);
        exit();
    } else {
        // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Something get wrong']);
        exit();
    }
}
