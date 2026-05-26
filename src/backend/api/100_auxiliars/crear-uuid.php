<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

// Preparar statement de actualización
global $conn;

while (true) {
    $stmt = $conn->query('SELECT id FROM db_agenda_esdeveniments');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) break;

    $upd = $conn->prepare('UPDATE db_agenda_esdeveniments SET id = :id WHERE id_esdeveniment = :id_esdeveniment');

    foreach ($rows as $row) {
        $id = Uuid::uuid7()->getBytes(); // UUIDv7 en binario
        // ——— Elige UNA de las dos líneas según tu columna:
        // $upd->bindValue(':uuid', $uuid->toBinary(), PDO::PARAM_LOB); // BINARY(16)
        $upd->bindValue(':id', $id, PDO::PARAM_STR);   // CHAR(36)

        $upd->bindValue(':id_esdeveniment', (int)$row['id_esdeveniment'], PDO::PARAM_INT);
        $upd->execute();
    }
}

echo "Backfill OK\n";
