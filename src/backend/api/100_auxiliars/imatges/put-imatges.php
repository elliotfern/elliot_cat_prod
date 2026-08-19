<?php
/*
 * BACKEND IMATGES
 * FUNCIONS PUT IMATGES
 * @db_img
 */

use App\Utils\MissatgesAPI;
use App\Utils\Response;
use App\Utils\Uuid;
use App\Config\Database;
use Ramsey\Uuid\Uuid as RamseyUuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

header('Content-Type: application/json; charset=utf-8');

// ============================================================
// CORS
// ============================================================

$allowedOrigins = [
    'https://elliot.cat',
    'https://dev.elliot.cat',
    'https://elliot.local',
];

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    corsAllow($allowedOrigins);
    http_response_code(204);
    exit;
}

corsAllow($allowedOrigins);

if ($slug === 'imatges') {

    // ============================================================
    // VERIFICAR MÉTODO
    // ============================================================
    //
    // El formulario utiliza POST + _method=PUT porque necesitamos
    // multipart/form-data para poder recibir archivos.
    //

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {

        Response::error(
            'Mètode no vàlid.',
            [],
            httpCode: 405
        );

        exit;
    }

    // ============================================================
    // FUNCIONES AUXILIARES
    // ============================================================

    function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }


    // ============================================================
    // DATOS DEL FORMULARIO
    // ============================================================

    $errors = [];

    $id = trim($_POST['id'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $alt = trim($_POST['alt'] ?? '');

    $type = isset($_POST['typeImg'])
        ? (int) $_POST['typeImg']
        : 0;


    // ============================================================
    // VALIDAR ID
    // ============================================================

    if ($id === '') {
        $errors['id'] = 'required';
    } elseif (!isUuid($id)) {
        $errors['id'] = 'invalid_uuid';
    }


    // ============================================================
    // VALIDAR NOM
    // ============================================================

    if ($nom === '') {
        $errors['nom'] = 'required';
    }


    // ============================================================
    // TIPOS DE IMAGEN PERMITIDOS
    // ============================================================

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

    if (!isset($allowed_types[$type])) {
        $errors['typeImg'] = 'invalid_value';
    }


    // ============================================================
    // SI HAY ERRORES DE VALIDACIÓN
    // ============================================================

    if (!empty($errors)) {

        Response::error(
            MissatgesAPI::error('invalid_data'),
            $errors,
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // UUID BINARY
    // ============================================================

    $id_bin = Uuid::toBinary($id);


    // ============================================================
    // OBTENER REGISTRO ACTUAL
    // ============================================================

    try {

        $sql = "
        SELECT
            nameImg,
            extension,
            typeImg
        FROM db_img
        WHERE id = :id
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id_bin,
            PDO::PARAM_LOB
        );

        $stmt->execute();

        $currentImage = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentImage) {

            Response::error(
                'La imatge no existeix.',
                [
                    'id' => 'not_found'
                ],
                httpCode: 404
            );

            exit;
        }
    } catch (\Throwable $e) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // CONFIGURACIÓN DE RUTAS
    // ============================================================

    $servidorMedia = $_ENV['MEDIA_LOCAL_PATH'] ?? null;

    if (!$servidorMedia) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'MEDIA_LOCAL_PATH no está configurado.'
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // DATOS DEL ARCHIVO ACTUAL
    // ============================================================

    $oldNameImg = $currentImage['nameImg'];
    $oldExtension = $currentImage['extension'];
    $oldType = (int) $currentImage['typeImg'];

    $oldTypeName = $allowed_types[$oldType] ?? null;

    $oldTargetFile = null;

    if (
        $oldTypeName !== null &&
        $oldNameImg !== null &&
        $oldExtension !== null
    ) {
        $oldTargetFile =
            rtrim($servidorMedia, '/') .
            '/' .
            $oldTypeName .
            '/' .
            $oldNameImg .
            '.' .
            $oldExtension;
    }


    // ============================================================
    // COMPROBAR SI HAY ARCHIVO NUEVO
    // ============================================================

    $hasNewFile = (
        isset($_FILES['fileToUpload']) &&
        $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_NO_FILE
    );


    // ============================================================
    // VARIABLES DEL NUEVO ARCHIVO
    // ============================================================

    $newNameImg = $oldNameImg;
    $newExtension = $oldExtension;
    $newTargetFile = null;


    // ============================================================
    // PROCESAR ARCHIVO NUEVO
    // ============================================================

    if ($hasNewFile) {

        $file = $_FILES['fileToUpload'];


        // --------------------------------------------------------
        // Error de subida
        // --------------------------------------------------------

        if ($file['error'] !== UPLOAD_ERR_OK) {

            Response::error(
                MissatgesAPI::error('error_imatge_not_exists'),
                [
                    'file' => 'upload_error'
                ],
                httpCode: 400
            );

            exit;
        }


        // --------------------------------------------------------
        // Configuración
        // --------------------------------------------------------

        $max_file_size = 2 * 1024 * 1024; // 2 MB

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mimeType = $finfo->file(
            $file['tmp_name']
        );

        $allowed_mime_types = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
        ];


        // --------------------------------------------------------
        // Validar archivo
        // --------------------------------------------------------

        if (
            $file['size'] > $max_file_size ||
            !isset($allowed_mime_types[$mimeType])
        ) {

            Response::error(
                MissatgesAPI::error(
                    'El archivo es demasiado grande o no es un tipo de imagen permitido.'
                ),
                [
                    'file' => 'invalid'
                ],
                httpCode: 400
            );

            exit;
        }


        // --------------------------------------------------------
        // Tipo de extensión
        // --------------------------------------------------------

        $newExtension = $allowed_mime_types[$mimeType];


        // --------------------------------------------------------
        // Generar nuevo nombre
        // --------------------------------------------------------

        $newNameImg = bin2hex(
            random_bytes(16)
        );

        $uniqueName =
            $newNameImg .
            '.' .
            $newExtension;


        // --------------------------------------------------------
        // Directorio destino
        // --------------------------------------------------------

        $target_dir =
            rtrim($servidorMedia, '/') .
            '/' .
            $allowed_types[$type] .
            '/';


        if (!is_dir($target_dir)) {

            if (!mkdir($target_dir, 0777, true)) {

                Response::error(
                    MissatgesAPI::error(
                        'No se pudo crear el directorio.'
                    ),
                    [
                        'directory' => $target_dir
                    ],
                    httpCode: 500
                );

                exit;
            }
        }


        // --------------------------------------------------------
        // Permisos
        // --------------------------------------------------------

        if (!is_writable($target_dir)) {

            Response::error(
                MissatgesAPI::error(
                    'El directorio no tiene permisos de escritura.'
                ),
                [
                    'directory' => $target_dir
                ],
                httpCode: 500
            );

            exit;
        }


        // --------------------------------------------------------
        // Ruta nuevo archivo
        // --------------------------------------------------------

        $newTargetFile =
            $target_dir .
            $uniqueName;


        // --------------------------------------------------------
        // Mover archivo
        // --------------------------------------------------------

        if (!move_uploaded_file(
            $file['tmp_name'],
            $newTargetFile
        )) {

            Response::error(
                MissatgesAPI::error(
                    'Hubo un problema al mover el archivo al servidor.'
                ),
                [
                    'file' => 'move_failed'
                ],
                httpCode: 500
            );

            exit;
        }
    }


    // ============================================================
    // UPDATE BASE DE DATOS
    // ============================================================

    try {

        $dateModified = date('Y-m-d');


        $sql = "
        UPDATE db_img SET
            nameImg = :nameImg,
            extension = :extension,
            typeImg = :typeImg,
            alt = :alt,
            nom = :nom,
            dateModified = :dateModified
        WHERE id = :id
    ";

        $stmt = $pdo->prepare($sql);


        $stmt->bindValue(
            ':id',
            $id_bin,
            PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':nameImg',
            $newNameImg,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':extension',
            $newExtension,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':typeImg',
            $type,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':alt',
            $alt !== '' ? $alt : null,
            $alt !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':nom',
            $nom,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':dateModified',
            $dateModified,
            PDO::PARAM_STR
        );


        // ========================================================
        // EJECUTAR UPDATE
        // ========================================================

        $stmt->execute();


        // ========================================================
        // ELIMINAR ARCHIVO ANTIGUO
        // ========================================================

        if (
            $hasNewFile &&
            $oldTargetFile !== null &&
            $oldTargetFile !== $newTargetFile &&
            file_exists($oldTargetFile)
        ) {
            unlink($oldTargetFile);
        }


        // ========================================================
        // RESPUESTA
        // ========================================================

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id,
                'nom' => $nom,
            ],
            httpCode: 200
        );

        exit;
    } catch (\Throwable $e) {


        // ========================================================
        // SI FALLA LA BD, ELIMINAR NUEVO ARCHIVO
        // ========================================================

        if (
            $newTargetFile !== null &&
            file_exists($newTargetFile)
        ) {
            unlink($newTargetFile);
        }


        // ========================================================
        // ERROR
        // ========================================================

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            httpCode: 500
        );

        exit;
    }
} else if ($slug === 'galeria-imatges') {

    // ============================================================
    // ID DE LA GALERÍA
    // ============================================================

    $galleryIdString = trim($_POST['id'] ?? '');

    if ($galleryIdString === '') {

        Response::error(
            'L\'ID de la galeria és obligatori.',
            [
                'id' => 'L\'ID de la galeria és obligatori.'
            ],
            httpCode: 400
        );

        exit;
    }

    try {

        $galleryId = Uuid::toBinary($galleryIdString);
    } catch (\Throwable $e) {

        Response::error(
            'L\'ID de la galeria no és vàlid.',
            [
                'id' => 'L\'ID de la galeria no és vàlid.'
            ],
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // DATOS DE LA GALERÍA
    // ============================================================

    $nomGaleria = trim($_POST['nom'] ?? '');
    $altGaleria = trim($_POST['alt'] ?? '');
    $publica = isset($_POST['publica']) ? 1 : 0;
    $slugGaleria = trim($_POST['slug'] ?? '');

    // ============================================================
    // VALIDAR NOMBRE
    // ============================================================

    if ($nomGaleria === '') {

        Response::error(
            'El nom de la galeria és obligatori.',
            [
                'nomGaleria' => 'El nom de la galeria és obligatori.'
            ],
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // IMÁGENES RECIBIDAS
    // ============================================================

    $imatgesPost = $_POST['imatges'] ?? [];

    if (!is_array($imatgesPost)) {
        $imatgesPost = [];
    }


    // ============================================================
    // CONFIGURACIÓN
    // ============================================================

    $servidorMedia = $_ENV['MEDIA_LOCAL_PATH'] ?? null;

    if (!$servidorMedia) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'MEDIA_LOCAL_PATH no está configurado.'
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // DIRECTORIO BASE
    // ============================================================

    $baseGaleriesDir = rtrim($servidorMedia, '/')
        . '/galeria-imatges/';


    // ============================================================
    // OBTENER GALERÍA ACTUAL
    // ============================================================

    $sqlGallery = "
        SELECT
            id,
            nom,
            directori,
            alt,
            dateCreated,
            dateModified
        FROM db_img_galeries
        WHERE id = :id
        LIMIT 1
    ";

    $stmtGallery = $pdo->prepare($sqlGallery);

    $stmtGallery->bindValue(
        ':id',
        $galleryId,
        PDO::PARAM_LOB
    );

    $stmtGallery->execute();

    $gallery = $stmtGallery->fetch(PDO::FETCH_ASSOC);


    if (!$gallery) {

        Response::error(
            MissatgesAPI::error('not_found'),
            [],
            httpCode: 404
        );

        exit;
    }


    // ============================================================
    // DIRECTORIO DE LA GALERÍA
    //
    // IMPORTANTE:
    // El directorio NO se puede modificar después de crear
    // la galería.
    // ============================================================

    $targetDir = $baseGaleriesDir
        . $gallery['directori']
        . '/';


    // ============================================================
    // COMPROBAR DIRECTORIO
    // ============================================================

    if (!is_dir($targetDir)) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'El directori de la galeria no existeix.',
                'directori' => $targetDir
            ],
            httpCode: 500
        );

        exit;
    }


    if (!is_writable($targetDir)) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'El directori de la galeria no té permisos d\'escriptura.',
                'directori' => $targetDir
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // ARCHIVOS NUEVOS CREADOS
    // ============================================================

    $createdFiles = [];


    // ============================================================
    // ARCHIVOS ANTIGUOS QUE SE ELIMINARÁN
    // ============================================================

    $filesToDelete = [];


    try {

        // ========================================================
        // TRANSACCIÓN
        // ========================================================

        $pdo->beginTransaction();


        // ========================================================
        // OBTENER IMÁGENES ACTUALES DE LA GALERÍA
        // ========================================================

        $sqlImages = "
            SELECT
                i.id,
                i.nameImg,
                i.extension,
                i.nom,
                i.alt,
                gi.ordre
            FROM db_img_galeries_img gi
            INNER JOIN db_img i
                ON i.id = gi.imatge_id
            WHERE gi.galeria_id = :galeria_id
            ORDER BY gi.ordre ASC
        ";

        $stmtImages = $pdo->prepare($sqlImages);

        $stmtImages->bindValue(
            ':galeria_id',
            $galleryId,
            PDO::PARAM_LOB
        );

        $stmtImages->execute();

        $currentImages = $stmtImages->fetchAll(PDO::FETCH_ASSOC);


        // ========================================================
        // IDS RECIBIDOS DE IMÁGENES EXISTENTES
        // ========================================================

        $receivedImageIds = [];

        foreach ($imatgesPost as $imageData) {

            if (!is_array($imageData)) {
                continue;
            }

            $imageId = trim($imageData['id'] ?? '');

            if ($imageId !== '') {
                $receivedImageIds[] = $imageId;
            }
        }


        // ========================================================
        // ELIMINAR IMÁGENES QUE YA NO ESTÁN EN EL FORMULARIO
        // ========================================================

        foreach ($currentImages as $currentImage) {

            $currentImageId = RamseyUuid::fromBytes(
                $currentImage['id']
            )->toString();


            if (!in_array($currentImageId, $receivedImageIds, true)) {

                // ----------------------------------------------
                // Archivo físico
                // ----------------------------------------------

                $filePath = $targetDir
                    . $currentImage['nameImg']
                    . '.'
                    . $currentImage['extension'];

                $filesToDelete[] = $filePath;


                // ----------------------------------------------
                // Eliminar relación
                // ----------------------------------------------

                $stmtDeleteRelation = $pdo->prepare("
                    DELETE FROM db_img_galeries_img
                    WHERE galeria_id = :galeria_id
                    AND imatge_id = :imatge_id
                ");

                $stmtDeleteRelation->bindValue(
                    ':galeria_id',
                    $galleryId,
                    PDO::PARAM_LOB
                );

                $stmtDeleteRelation->bindValue(
                    ':imatge_id',
                    $currentImage['id'],
                    PDO::PARAM_LOB
                );

                $stmtDeleteRelation->execute();


                // ----------------------------------------------
                // Eliminar imagen
                // ----------------------------------------------

                $stmtDeleteImage = $pdo->prepare("
                    DELETE FROM db_img
                    WHERE id = :id
                ");

                $stmtDeleteImage->bindValue(
                    ':id',
                    $currentImage['id'],
                    PDO::PARAM_LOB
                );

                $stmtDeleteImage->execute();
            }
        }


        // ========================================================
        // PREPARAR INSERT IMAGEN
        // ========================================================

        $stmtInsertImage = $pdo->prepare("
            INSERT INTO db_img
            (
                id,
                nameImg,
                extension,
                typeImg,
                nom,
                alt,
                dateCreated
            )
            VALUES
            (
                :id,
                :nameImg,
                :extension,
                :typeImg,
                :nom,
                :alt,
                :dateCreated
            )
        ");


        // ========================================================
        // PREPARAR INSERT RELACIÓN
        // ========================================================

        $stmtInsertRelation = $pdo->prepare("
            INSERT INTO db_img_galeries_img
            (
                id,
                galeria_id,
                imatge_id,
                ordre
            )
            VALUES
            (
                :id,
                :galeria_id,
                :imatge_id,
                :ordre
            )
        ");


        // ========================================================
        // PREPARAR UPDATE IMAGEN
        // ========================================================

        $stmtUpdateImage = $pdo->prepare("
            UPDATE db_img
            SET
                nom = :nom,
                alt = :alt,
                dateModified = :dateModified
            WHERE id = :id
        ");


        // ========================================================
        // VALIDACIÓN MIME
        // ========================================================

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
        ];

        $maxFileSize = 10 * 1024 * 1024;

        $finfo = new finfo(FILEINFO_MIME_TYPE);


        // ========================================================
        // FECHA
        // ========================================================

        $dateModified = date('Y-m-d');


        // ========================================================
        // PROCESAR IMÁGENES RECIBIDAS
        // ========================================================

        foreach ($imatgesPost as $index => $imageData) {

            if (!is_array($imageData)) {
                continue;
            }


            $imageIdString = trim($imageData['id'] ?? '');

            $nomImatge = trim($imageData['nom'] ?? '');
            $altImatge = trim($imageData['alt'] ?? '');


            // ====================================================
            // VALIDAR NOMBRE
            // ====================================================

            if ($nomImatge === '') {

                throw new RuntimeException(
                    'El nom de la imagen número '
                        . ($index + 1)
                        . ' es obligatorio.'
                );
            }


            // ====================================================
            // IMAGEN EXISTENTE
            // ====================================================

            if ($imageIdString !== '') {

                try {

                    $imageId = Uuid::toBinary($imageIdString);
                } catch (\Throwable $e) {

                    throw new RuntimeException(
                        'El ID de la imagen número '
                            . ($index + 1)
                            . ' no es válido.'
                    );
                }


                // ------------------------------------------------
                // Comprobar que la imagen pertenece a esta galería
                // ------------------------------------------------

                $stmtCheckImage = $pdo->prepare("
                    SELECT 1
                    FROM db_img_galeries_img
                    WHERE galeria_id = :galeria_id
                    AND imatge_id = :imatge_id
                    LIMIT 1
                ");

                $stmtCheckImage->bindValue(
                    ':galeria_id',
                    $galleryId,
                    PDO::PARAM_LOB
                );

                $stmtCheckImage->bindValue(
                    ':imatge_id',
                    $imageId,
                    PDO::PARAM_LOB
                );

                $stmtCheckImage->execute();

                if (!$stmtCheckImage->fetchColumn()) {

                    throw new RuntimeException(
                        'La imagen número '
                            . ($index + 1)
                            . ' no pertenece a esta galería.'
                    );
                }


                // ------------------------------------------------
                // Actualizar imagen
                // ------------------------------------------------

                $stmtUpdateImage->bindValue(
                    ':id',
                    $imageId,
                    PDO::PARAM_LOB
                );

                $stmtUpdateImage->bindValue(
                    ':nom',
                    $nomImatge,
                    PDO::PARAM_STR
                );

                $stmtUpdateImage->bindValue(
                    ':alt',
                    $altImatge !== ''
                        ? $altImatge
                        : null,
                    $altImatge !== ''
                        ? PDO::PARAM_STR
                        : PDO::PARAM_NULL
                );

                $stmtUpdateImage->bindValue(
                    ':dateModified',
                    $dateModified,
                    PDO::PARAM_STR
                );

                $stmtUpdateImage->execute();

                continue;
            }


            // ====================================================
            // IMAGEN NUEVA
            // ====================================================

            $fileError = $_FILES['imatges']['error'][$index]['file'] ?? null;
            $tmpName   = $_FILES['imatges']['tmp_name'][$index]['file'] ?? null;
            $fileSize  = $_FILES['imatges']['size'][$index]['file'] ?? null;


            // ====================================================
            // VERIFICAR ESTRUCTURA
            // ====================================================

            if (
                $fileError === null ||
                $tmpName === null ||
                $fileSize === null
            ) {

                throw new RuntimeException(
                    'No se ha recibido el archivo de la imagen número '
                        . ($index + 1)
                );
            }


            // ====================================================
            // ERROR DE SUBIDA
            // ====================================================

            if ($fileError !== UPLOAD_ERR_OK) {

                throw new RuntimeException(
                    'Error al subir la imagen número '
                        . ($index + 1)
                        . '. Código de error PHP: '
                        . $fileError
                );
            }


            // ====================================================
            // TAMAÑO
            // ====================================================

            if ($fileSize > $maxFileSize) {

                throw new RuntimeException(
                    'La imagen número '
                        . ($index + 1)
                        . ' supera los 10 MB.'
                );
            }


            // ====================================================
            // MIME REAL
            // ====================================================

            $mimeType = $finfo->file($tmpName);

            if (!isset($allowedMimeTypes[$mimeType])) {

                throw new RuntimeException(
                    'La imagen número '
                        . ($index + 1)
                        . ' no es un formato permitido.'
                );
            }


            $extension = $allowedMimeTypes[$mimeType];


            // ====================================================
            // NOMBRE ALEATORIO
            // ====================================================

            $uniqueName = bin2hex(random_bytes(16));

            $targetFile = $targetDir
                . $uniqueName
                . '.'
                . $extension;


            // ====================================================
            // PROCESAR IMAGEN
            // ====================================================

            switch ($mimeType) {

                case 'image/jpeg':

                    $sourceImage = imagecreatefromjpeg($tmpName);

                    if ($sourceImage === false) {

                        throw new RuntimeException(
                            'No se pudo procesar la imagen JPEG número '
                                . ($index + 1)
                        );
                    }


                    // --------------------------------------------
                    // Redimensionar si es necesario
                    // --------------------------------------------

                    $maxWidth = 1920;
                    $maxHeight = 1920;

                    $width = imagesx($sourceImage);
                    $height = imagesy($sourceImage);

                    $scale = min(
                        1,
                        $maxWidth / $width,
                        $maxHeight / $height
                    );

                    if ($scale < 1) {

                        $newWidth = (int) round($width * $scale);
                        $newHeight = (int) round($height * $scale);

                        $resizedImage = imagecreatetruecolor(
                            $newWidth,
                            $newHeight
                        );

                        imagecopyresampled(
                            $resizedImage,
                            $sourceImage,
                            0,
                            0,
                            0,
                            0,
                            $newWidth,
                            $newHeight,
                            $width,
                            $height
                        );

                        $sourceImage = $resizedImage;
                    }


                    if (!imagejpeg(
                        $sourceImage,
                        $targetFile,
                        85
                    )) {

                        throw new RuntimeException(
                            'No se pudo guardar la imagen JPEG número '
                                . ($index + 1)
                        );
                    }

                    break;


                case 'image/png':

                    $sourceImage = imagecreatefrompng($tmpName);

                    if ($sourceImage === false) {

                        throw new RuntimeException(
                            'No se pudo procesar la imagen PNG número '
                                . ($index + 1)
                        );
                    }


                    // --------------------------------------------
                    // Redimensionar si es necesario
                    // --------------------------------------------

                    $maxWidth = 1920;
                    $maxHeight = 1920;

                    $width = imagesx($sourceImage);
                    $height = imagesy($sourceImage);

                    $scale = min(
                        1,
                        $maxWidth / $width,
                        $maxHeight / $height
                    );

                    if ($scale < 1) {

                        $newWidth = (int) round($width * $scale);
                        $newHeight = (int) round($height * $scale);

                        $resizedImage = imagecreatetruecolor(
                            $newWidth,
                            $newHeight
                        );

                        imagealphablending(
                            $resizedImage,
                            false
                        );

                        imagesavealpha(
                            $resizedImage,
                            true
                        );

                        imagecopyresampled(
                            $resizedImage,
                            $sourceImage,
                            0,
                            0,
                            0,
                            0,
                            $newWidth,
                            $newHeight,
                            $width,
                            $height
                        );

                        $sourceImage = $resizedImage;
                    }


                    imagealphablending(
                        $sourceImage,
                        false
                    );

                    imagesavealpha(
                        $sourceImage,
                        true
                    );


                    if (!imagepng(
                        $sourceImage,
                        $targetFile,
                        6
                    )) {

                        throw new RuntimeException(
                            'No se pudo guardar la imagen PNG número '
                                . ($index + 1)
                        );
                    }

                    break;


                default:

                    throw new RuntimeException(
                        'Formato de imagen no permitido.'
                    );
            }


            // Registrar archivo creado
            $createdFiles[] = $targetFile;


            // ====================================================
            // UUID IMAGEN
            // ====================================================

            $imageUuid = RamseyUuid::uuid7();

            $imageId = $imageUuid->getBytes();


            // ====================================================
            // INSERT IMAGEN
            // ====================================================

            $stmtInsertImage->bindValue(
                ':id',
                $imageId,
                PDO::PARAM_LOB
            );

            $stmtInsertImage->bindValue(
                ':nameImg',
                $uniqueName,
                PDO::PARAM_STR
            );

            $stmtInsertImage->bindValue(
                ':extension',
                $extension,
                PDO::PARAM_STR
            );

            $stmtInsertImage->bindValue(
                ':typeImg',
                22,
                PDO::PARAM_INT
            );

            $stmtInsertImage->bindValue(
                ':nom',
                $nomImatge,
                PDO::PARAM_STR
            );

            $stmtInsertImage->bindValue(
                ':alt',
                $altImatge !== ''
                    ? $altImatge
                    : null,
                $altImatge !== ''
                    ? PDO::PARAM_STR
                    : PDO::PARAM_NULL
            );

            $stmtInsertImage->bindValue(
                ':dateCreated',
                $dateModified,
                PDO::PARAM_STR
            );

            $stmtInsertImage->execute();


            // ====================================================
            // INSERT RELACIÓN
            // ====================================================

            $relationUuid = RamseyUuid::uuid7();

            $relationId = $relationUuid->getBytes();


            $stmtInsertRelation->bindValue(
                ':id',
                $relationId,
                PDO::PARAM_LOB
            );

            $stmtInsertRelation->bindValue(
                ':galeria_id',
                $galleryId,
                PDO::PARAM_LOB
            );

            $stmtInsertRelation->bindValue(
                ':imatge_id',
                $imageId,
                PDO::PARAM_LOB
            );

            $stmtInsertRelation->bindValue(
                ':ordre',
                $index + 1,
                PDO::PARAM_INT
            );

            $stmtInsertRelation->execute();
        }


        // ========================================================
        // ACTUALIZAR GALERÍA
        // ========================================================

        $stmtUpdateGallery = $pdo->prepare("
            UPDATE db_img_galeries
            SET
                nom = :nom,
                alt = :alt,
                publica = :publica,
                slug = :slug;
                dateModified = :dateModified
            WHERE id = :id
        ");


        $stmtUpdateGallery->bindValue(
            ':id',
            $galleryId,
            PDO::PARAM_LOB
        );

        $stmtUpdateGallery->bindValue(
            ':nom',
            $nomGaleria,
            PDO::PARAM_STR
        );

        $stmtUpdateGallery->bindValue(
            ':alt',
            $altGaleria !== ''
                ? $altGaleria
                : null,
            $altGaleria !== ''
                ? PDO::PARAM_STR
                : PDO::PARAM_NULL
        );

        $stmtGallery->bindValue(
            ':slug',
            $slugGaleria,
            PDO::PARAM_STR
        );

        $stmtGallery->bindValue(
            ':publica',
            $publica,
            PDO::PARAM_INT
        );

        $stmtUpdateGallery->bindValue(
            ':dateModified',
            $dateModified,
            PDO::PARAM_STR
        );

        $stmtUpdateGallery->execute();


        // ========================================================
        // COMMIT
        // ========================================================

        $pdo->commit();


        // ========================================================
        // ELIMINAR ARCHIVOS FÍSICOS ANTIGUOS
        // ========================================================

        foreach ($filesToDelete as $file) {

            if (is_file($file)) {
                @unlink($file);
            }
        }


        // ========================================================
        // RESPUESTA
        // ========================================================

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $galleryIdString,
                'directori' => $gallery['directori'],
                'imatges' => count($imatgesPost),
            ],
            httpCode: 200
        );

        exit;
    } catch (\Throwable $e) {

        // ========================================================
        // ROLLBACK
        // ========================================================

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }


        // ========================================================
        // ELIMINAR ARCHIVOS NUEVOS
        // ========================================================

        foreach ($createdFiles as $createdFile) {

            if (is_file($createdFile)) {
                @unlink($createdFile);
            }
        }


        // ========================================================
        // ERROR
        // ========================================================

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            httpCode: 500
        );

        exit;
    }
} else {
    Response::error(
        MissatgesAPI::error('internal_error'),
        [
            'message' => 'Error endpoint',
        ],
        httpCode: 500
    );
}
