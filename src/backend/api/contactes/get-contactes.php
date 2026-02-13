<?php


// 1) Llistat contactes
// ruta GET => "/api/contactes/get/?type=contactes"
if (isset($_GET['contactes'])) {
    global $conn;

    $query = "SELECT c.id, c.nom, c.cognoms, c.email, c.tel_1, c.tel_2, c.tel_3, c.data_naixement, c.web, t.tipus, p.pais_ca AS country, c.adreca
            FROM db_contactes AS c
            LEFT JOIN aux_contactes_tipus AS t ON c.tipus = t.id
            LEFT JOIN db_geo_paisos AS p ON c.pais = p.id
            ORDER BY c.cognoms ASC";

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

    // 3) Contacte ID
    // ruta GET => "/api/contactes/get/?type=contacte&id=1"
} elseif (isset($_GET['type']) && $_GET['type'] == 'contacte' && isset($_GET['id'])) {
    $id = $_GET['id'];
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT c.id, c.nom, c.cognoms, c.email, c.tel_1, c.tel_2, c.tel_3, c.data_naixement, c.web, t.id AS tipus_id, t.tipus, c.adreca, p.id AS pais_id, p.pais_ca AS country
            FROM db_contactes AS c
            LEFT JOIN aux_contactes_tipus AS t ON c.tipus = t.id
            LEFT JOIN db_geo_paisos AS p ON c.pais = p.id
            WHERE c.id = $id"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // 4) Llistat tipus de contacte
    // ruta GET => "/api/contactes/get/?type=tipus-contacte"
} elseif (isset($_GET['type']) && $_GET['type'] == 'tipus-contacte') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT t.id, t.tipus
            FROM aux_contactes_tipus AS t
            ORDER BY t.tipus ASC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);
    // 5) Llistat paisos
    // ruta GET => "/api/contactes/get/?type=paisos"
} elseif (isset($_GET['type']) && $_GET['type'] == 'paisos') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT c.id, c.pais_ca AS country
            FROM db_geo_paisos AS c
            ORDER BY c.pais_ca ASC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
