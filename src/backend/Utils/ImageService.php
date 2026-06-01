<?php

namespace App\Utils;

use Ramsey\Uuid\Uuid;
use PDO;
use Throwable;
use Exception;

class ImageService
{
    public static function createFromUpload(array $file, int $type, string $nom, string $alt, PDO $conn): string
    {
        // =========================
        // CONFIG
        // =========================
        $servidorMedia = '/home/epgylzqu/media.elliotfern.com/img/';

        $allowed_types = [
            1 => 'persona',
            2 => 'biblioteca-llibre',
            3 => 'historia-imatge',
            4 => 'historia-esdeveniment',
            6 => 'historia-organitzacio',
            7 => 'cinema-serie',
            8 => 'cinema-pelicula',
            10 => 'historia-imatge-min',
            11 => 'viatge',
            12 => 'historia-mapa',
            13 => 'blog',
            15 => 'historia-infografia',
            16 => 'historia-cronologia',
            17 => 'viatge-espai',
            18 => 'usuaris-avatar',
            19 => 'web-icones',
            20 => 'logos-empreses',
        ];

        $typeName = $allowed_types[$type] ?? 'elliotfern';

        $target_dir = rtrim($servidorMedia, '/') . '/' . $typeName . '/';

        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                throw new Exception("No se pudo crear el directorio: $target_dir");
            }
        }

        if (!is_writable($target_dir)) {
            throw new Exception("El directorio no tiene permisos de escritura: $target_dir");
        }

        // =========================
        // VALIDACIÓN FILE
        // =========================
        $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_file_size = 2 * 1024 * 1024;

        if ($file['size'] > $max_file_size || !in_array($file['type'], $allowed_mime_types)) {
            throw new Exception("Archivo inválido o demasiado grande");
        }

        // =========================
        // NAME FILE
        // =========================
        $uniqueName = basename($file['name']);
        $targetFile = $target_dir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            throw new Exception("Error moviendo archivo al servidor");
        }

        // =========================
        // DB INSERT
        // =========================
        $nameImg = pathinfo($uniqueName, PATHINFO_FILENAME);

        $dateCreated = date('Y-m-d');

        $id = Uuid::uuid7()->getBytes(); // binary
        $uuidString = Uuid::toString($id);

        $sql = "INSERT INTO db_img
                (id, nameImg, typeImg, alt, nom, dateCreated)
                VALUES
                (:id, :nameImg, :typeImg, :alt, :nom, :dateCreated)";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_LOB);
        $stmt->bindValue(":nameImg", $nameImg);
        $stmt->bindValue(":typeImg", $type);
        $stmt->bindValue(":alt", $alt);
        $stmt->bindValue(":nom", $nom);
        $stmt->bindValue(":dateCreated", $dateCreated);

        if (!$stmt->execute()) {
            throw new Exception("Error insertando imagen en DB");
        }

        return $uuidString;
    }
}
