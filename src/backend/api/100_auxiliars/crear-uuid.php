<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

global $conn;

// Obtener filas
$stmt = $conn->query('
    SELECT id_esdeveniment
    FROM db_agenda_esdeveniments
');

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    exit("No rows found\n");
}

// Preparar UPDATE
$upd = $conn->prepare('
    UPDATE db_agenda_esdeveniments
    SET id = :id
    WHERE id_esdeveniment = :id_esdeveniment
');

foreach ($rows as $row) {

    $uuidBinary = Uuid::uuid7()->getBytes();

    $upd->bindValue(
        ':id',
        $uuidBinary,
        PDO::PARAM_LOB
    );

    $upd->bindValue(
        ':id_esdeveniment',
        (int)$row['id_esdeveniment'],
        PDO::PARAM_INT
    );

    $upd->execute();
}

echo "Backfill OK\n";
