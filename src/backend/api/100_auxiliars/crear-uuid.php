<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

// Preparar statement de actualización
global $conn;

while (true) {
    $stmt = $conn->query('SELECT id FROM db_geo_ciutats IS NULL OR uuid = ""');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) break;

    $upd = $conn->prepare('UPDATE db_geo_ciutats SET uuid = :uuid WHERE id = :id');

    foreach ($rows as $row) {
        $uuid = Uuid::uuid7()->getBytes(); // UUIDv7 en binario
        // ——— Elige UNA de las dos líneas según tu columna:
        // $upd->bindValue(':uuid', $uuid->toBinary(), PDO::PARAM_LOB); // BINARY(16)
        $upd->bindValue(':uuid', $uuid, PDO::PARAM_STR);   // CHAR(36)

        $upd->bindValue(':id', (int)$row['id'], PDO::PARAM_INT);
        $upd->execute();
    }
}

echo "Backfill OK\n";
