<?php

use App\Config\Database;
use Ramsey\Uuid\Uuid as Ramsey;
use App\Utils\Uuid;

// ─────────────────────────────────────────────
// CONFIGURACIÓ
// ─────────────────────────────────────────────
$taula = 'db_projectes';
$columnaId = 'id2';              // BINARY(16), nou UUID
$columnaFiltre = 'id';      // Identificador actual/antic de la fila
// ─────────────────────────────────────────────

$db = new Database();
$pdo = $db->getPdo();

try {
    $pdo->beginTransaction();

    // 1. Seleccionar les files
    $stmtSelect = $pdo->prepare(
        "SELECT `$columnaFiltre` FROM `$taula`"
    );
    $stmtSelect->execute();

    $files = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    if (empty($files)) {
        echo "No hi ha files per actualitzar.\n";
        $pdo->rollBack();
        exit(0);
    }

    // 2. Preparar UPDATE
    $stmtUpdate = $pdo->prepare(
        "UPDATE `$taula`
         SET `$columnaId` = :nouId
         WHERE `$columnaFiltre` = :filtre"
    );

    $comptador = 0;

    foreach ($files as $fila) {

        // Generar un UUID v7 nou per aquesta fila
        $nouUuid = Ramsey::uuid7();

        // Convertir UUID textual a BINARY(16)
        $nouUuidBinari = Uuid::toBinary(
            $nouUuid->toString()
        );

        $stmtUpdate->bindValue(
            ':nouId',
            $nouUuidBinari,
            PDO::PARAM_LOB
        );

        $stmtUpdate->bindValue(
            ':filtre',
            $fila[$columnaFiltre]
        );

        $stmtUpdate->execute();

        $comptador++;
    }

    $pdo->commit();

    echo "Actualitzades $comptador files amb nou UUID v7.\n";
} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
