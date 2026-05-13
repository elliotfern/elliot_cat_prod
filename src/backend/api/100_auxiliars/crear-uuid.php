<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

// Preparar statement de actualización
global $conn;

while (true) {
    $stmt = $conn->query('SELECT id FROM db_cinema_seriestv_actors');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) break;

    $upd = $conn->prepare('UPDATE db_cinema_seriestv_actors SET id2 = :id2 WHERE id = :id');

    foreach ($rows as $row) {
        $id2 = Uuid::uuid7()->getBytes(); // UUIDv7 en binario
        // ——— Elige UNA de las dos líneas según tu columna:
        // $upd->bindValue(':uuid', $uuid->toBinary(), PDO::PARAM_LOB); // BINARY(16)
        $upd->bindValue(':id2', $id2, PDO::PARAM_STR);   // CHAR(36)

        $upd->bindValue(':id', (int)$row['id'], PDO::PARAM_INT);
        $upd->execute();
    }
}

echo "Backfill OK\n";
