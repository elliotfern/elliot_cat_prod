<?php


use App\Config\Database;
use Ramsey\Uuid\Uuid;

$db = new Database();
$pdo = $db->getPdo();


// 2. Obtener registros
$stmt = $pdo->query("SELECT id FROM db_historia_esdeveniments");

$update = $pdo->prepare("UPDATE db_historia_esdeveniments SET id2 = :uuid WHERE id = :id");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // generar UUID v7
    $uuid = Uuid::uuid7();

    // convertir a binario
    $uuidBinary = $uuid->getBytes();

    // actualizar
    $update->execute([
        'uuid' => $uuidBinary,
        'id'   => $row['id']
    ]);
}

echo "UUIDs generados correctamente.\n";
