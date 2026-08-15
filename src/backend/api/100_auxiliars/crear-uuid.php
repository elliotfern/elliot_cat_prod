<?php

use App\Config\Database;
use Ramsey\Uuid\Uuid as Ramsey;
use App\Utils\Uuid;

// ─────────────────────────────────────────────
// CONFIGURACIÓ: ajusta aquests valors
// ─────────────────────────────────────────────
$taula = 'db_vault_type';       // Nom de la taula a actualitzar
$columnaId = 'id';          // Nom de la columna PK (BINARY(16))
$columnaFiltre = 'id';      // Columna que fem servir per identificar la fila (ex: alguna PK antiga o rowid)
// ─────────────────────────────────────────────

$db = new Database(); // O com instanciïs la teva connexió
$pdo = $db->getPdo();

try {
    $pdo->beginTransaction();

    // 1. Seleccionar files que necessiten un nou UUID
    //    (ajusta el WHERE segons el teu cas: id IS NULL, id = '', etc.)
    $stmtSelect = $pdo->prepare("SELECT `$columnaFiltre` FROM `$taula` WHERE `$columnaId`");
    $stmtSelect->execute();
    $files = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    if (empty($files)) {
        echo "No hi ha files per actualitzar.\n";
        $pdo->rollBack();
        exit(0);
    }

    // 2. Preparar la sentència d'UPDATE
    $stmtUpdate = $pdo->prepare(
        "UPDATE `$taula` SET `$columnaId` = :nouId WHERE `$columnaFiltre` = :filtre"
    );

    $comptador = 0;

    foreach ($files as $fila) {
        $nouUuid = Ramsey::uuid7();
        $nouUuidBinari = Uuid::toBinary($nouUuid->toString());

        $stmtUpdate->bindValue(':nouId', $nouUuidBinari, PDO::PARAM_LOB);
        $stmtUpdate->bindValue(':filtre', $fila[$columnaFiltre]);
        $stmtUpdate->execute();

        $comptador++;
    }

    $pdo->commit();

    echo "Actualitzades $comptador files amb nou UUID v7.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
