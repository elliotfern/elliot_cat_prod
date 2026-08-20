<?php
/*
 * BACKEND IMATGES
 * FUNCIONS POST IMATGES
 * @db_img
 */

use App\Utils\MissatgesAPI;
use App\Utils\Response;
use Ramsey\Uuid\Uuid;
use App\Config\Database;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

header('Content-Type: application/json; charset=utf-8');

// CORS
$allowedOrigins = [
    'https://elliot.cat',
    'https://dev.elliot.cat',
    'https://elliot.local',
];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow($allowedOrigins);
    http_response_code(204);
    exit;
}

corsAllow($allowedOrigins);

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'errors' => [],
        'meta' => [],
        'data' => null,
    ]);

    exit;
}

// Endpoint POST IMATGE
if ($slug === 'imatges') {

    // ============================================================
    // VERIFICAR ARCHIVO
    // ============================================================

    if (
        empty($_FILES['fileToUpload']) ||
        $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK
    ) {

        Response::error(
            MissatgesAPI::error('error_imatge_not_exists'),
            [
                'fileToUpload' => 'No se ha recibido correctamente el archivo.'
            ],
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // DATOS DEL FORMULARIO
    // ============================================================

    $nom = trim($_POST['nom'] ?? '');
    $alt = trim($_POST['alt'] ?? '');

    // Año opcional
    $any = trim($_POST['any'] ?? '');

    if ($nom === '') {

        Response::error(
            'El nom és obligatori.',
            [
                'nom' => 'El nom és obligatori.'
            ],
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // VALIDAR AÑO
    // ============================================================

    $anyValue = null;

    if ($any !== '') {

        if (
            !ctype_digit($any) ||
            (int) $any < 1000 ||
            (int) $any > 9999
        ) {

            Response::error(
                'L\'any no és vàlid.',
                [
                    'any' => 'L\'any ha de ser un valor entre 1000 i 9999.'
                ],
                httpCode: 400
            );

            exit;
        }

        $anyValue = (int) $any;
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
    // TIPO DE IMAGEN
    // ============================================================

    $type = isset($_POST['typeImg'])
        ? (int) $_POST['typeImg']
        : 0;

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
        22 => 'galeria-imatges',
    ];

    if (!isset($allowed_types[$type])) {

        Response::error(
            'Tipus d\'imatge no vàlid.',
            [
                'typeImg' => $type
            ],
            httpCode: 400
        );

        exit;
    }

    $typeName = $allowed_types[$type];


    // ============================================================
    // DIRECTORIO DE DESTINO
    // ============================================================

    $target_dir = rtrim($servidorMedia, '/') . '/' . $typeName . '/';

    if (!is_dir($target_dir)) {

        if (!mkdir($target_dir, 0777, true)) {

            Response::error(
                MissatgesAPI::error('No se pudo crear el directorio.'),
                [
                    $target_dir
                ],
                httpCode: 500
            );

            exit;
        }
    }


    // ============================================================
    // VERIFICAR PERMISOS
    // ============================================================

    if (!is_writable($target_dir)) {

        Response::error(
            MissatgesAPI::error(
                'El directorio no tiene permisos de escritura.'
            ),
            [
                $target_dir
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // ARCHIVO
    // ============================================================

    $file = $_FILES['fileToUpload'];

    $max_file_size = 10 * 1024 * 1024;

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeType = $finfo->file($file['tmp_name']);
    $maxWidth = 2000;
    $maxHeight = 2000;

    // ============================================================
    // TIPOS MIME PERMITIDOS
    // ============================================================

    // GIF eliminado intencionadamente.
    $allowed_mime_types = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    ];

    if (
        $file['size'] > $max_file_size ||
        !isset($allowed_mime_types[$mimeType])
    ) {

        Response::error(
            MissatgesAPI::error(
                'El archivo es demasiado grande o no es un tipo de imagen permitido.'
            ),
            [
                'fileToUpload' => 'Solo se permiten imágenes JPEG y PNG de máximo 10 MB.'
            ],
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // OBTENER FECHA EXIF
    // ============================================================

    $dataImatge = null;

    if (
        $mimeType === 'image/jpeg' &&
        function_exists('exif_read_data')
    ) {

        $exif = @exif_read_data(
            $file['tmp_name'],
            'EXIF',
            true
        );

        if (
            isset($exif['EXIF']['DateTimeOriginal']) &&
            is_string($exif['EXIF']['DateTimeOriginal'])
        ) {

            $dateOriginal = DateTime::createFromFormat(
                'Y:m:d H:i:s',
                $exif['EXIF']['DateTimeOriginal']
            );

            if ($dateOriginal !== false) {

                $dataImatge = $dateOriginal->format(
                    'Y-m-d H:i:s'
                );
            }
        }
    }


    // ============================================================
    // GENERAR NOMBRE ÚNICO
    // ============================================================

    $extension = $allowed_mime_types[$mimeType];

    $nameImg = bin2hex(random_bytes(16));

    $targetFile = $target_dir
        . $nameImg
        . '.'
        . $extension;


    // ============================================================
    // PROCESAR, REDIMENSIONAR Y OPTIMIZAR IMAGEN
    // ============================================================

    switch ($mimeType) {

        // --------------------------------------------------------
        // JPEG
        // --------------------------------------------------------

        case 'image/jpeg':

            $sourceImage = imagecreatefromjpeg($file['tmp_name']);

            if ($sourceImage === false) {

                Response::error(
                    MissatgesAPI::error('internal_error'),
                    [
                        'message' => 'No se pudo procesar la imagen JPEG.'
                    ],
                    httpCode: 400
                );

                exit;
            }

            break;


        // --------------------------------------------------------
        // PNG
        // --------------------------------------------------------

        case 'image/png':

            $sourceImage = imagecreatefrompng($file['tmp_name']);

            if ($sourceImage === false) {

                Response::error(
                    MissatgesAPI::error('internal_error'),
                    [
                        'message' => 'No se pudo procesar la imagen PNG.'
                    ],
                    httpCode: 400
                );

                exit;
            }

            // Mantener transparencia
            imagealphablending(
                $sourceImage,
                false
            );

            imagesavealpha(
                $sourceImage,
                true
            );

            break;


        default:

            Response::error(
                MissatgesAPI::error('internal_error'),
                [
                    'message' => 'Formato de imagen no permitido.'
                ],
                httpCode: 400
            );

            exit;
    }


    // ============================================================
    // DIMENSIONES ORIGINALES
    // ============================================================

    $originalWidth = imagesx($sourceImage);
    $originalHeight = imagesy($sourceImage);


    // ============================================================
    // CALCULAR NUEVAS DIMENSIONES
    // ============================================================

    $newWidth = $originalWidth;
    $newHeight = $originalHeight;

    if (
        $originalWidth > $maxWidth ||
        $originalHeight > $maxHeight
    ) {

        $scale = min(
            $maxWidth / $originalWidth,
            $maxHeight / $originalHeight
        );

        $newWidth = (int) round($originalWidth * $scale);
        $newHeight = (int) round($originalHeight * $scale);
    }


    // ============================================================
    // CREAR IMAGEN DESTINO
    // ============================================================

    $destinationImage = imagecreatetruecolor(
        $newWidth,
        $newHeight
    );


    // ============================================================
    // MANTENER TRANSPARENCIA PNG
    // ============================================================

    if ($mimeType === 'image/png') {

        imagealphablending(
            $destinationImage,
            false
        );

        imagesavealpha(
            $destinationImage,
            true
        );

        $transparent = imagecolorallocatealpha(
            $destinationImage,
            0,
            0,
            0,
            127
        );

        imagefill(
            $destinationImage,
            0,
            0,
            $transparent
        );
    }


    // ============================================================
    // REDIMENSIONAR
    // ============================================================

    if (!imagecopyresampled(
        $destinationImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $originalWidth,
        $originalHeight
    )) {

        unset($sourceImage);
        unset($destinationImage);

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'No se pudo redimensionar la imagen.'
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // GUARDAR IMAGEN OPTIMIZADA
    // ============================================================

    if ($mimeType === 'image/jpeg') {

        if (!imagejpeg(
            $destinationImage,
            $targetFile,
            85
        )) {

            unset($sourceImage);
            unset($destinationImage);

            Response::error(
                MissatgesAPI::error('internal_error'),
                [
                    'message' => 'No se pudo guardar la imagen JPEG.'
                ],
                httpCode: 500
            );

            exit;
        }
    } elseif ($mimeType === 'image/png') {

        if (!imagepng(
            $destinationImage,
            $targetFile,
            6
        )) {

            unset($sourceImage);
            unset($destinationImage);

            Response::error(
                MissatgesAPI::error('internal_error'),
                [
                    'message' => 'No se pudo guardar la imagen PNG.'
                ],
                httpCode: 500
            );

            exit;
        }
    }


    // ============================================================
    // LIBERAR RECURSOS GD
    // ============================================================

    unset($sourceImage);
    unset($destinationImage);


    // ============================================================
    // INSERTAR DATOS EN LA BASE DE DATOS
    // ============================================================

    try {

        $dateCreated = date('Y-m-d');


        // ========================================================
        // UUID v7
        // ========================================================

        $uuid = Uuid::uuid7();

        // BINARY(16)
        $id = $uuid->getBytes();

        // UUID texto para API
        $idString = $uuid->toString();


        // ========================================================
        // INSERT
        // ========================================================

        $sql = "
            INSERT INTO db_img
            (
                id,
                nameImg,
                extension,
                typeImg,
                alt,
                nom,
                dateCreated,
                dataImatge,
                any
            )
            VALUES
            (
                :id,
                :nameImg,
                :extension,
                :typeImg,
                :alt,
                :nom,
                :dateCreated,
                :dataImatge,
                :any
            )
        ";

        $stmt = $pdo->prepare($sql);


        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':nameImg',
            $nameImg,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':extension',
            $extension,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':typeImg',
            $type,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':alt',
            $alt !== ''
                ? $alt
                : null,
            $alt !== ''
                ? PDO::PARAM_STR
                : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':nom',
            $nom,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':dateCreated',
            $dateCreated,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':dataImatge',
            $dataImatge,
            $dataImatge !== null
                ? PDO::PARAM_STR
                : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':any',
            $anyValue,
            $anyValue !== null
                ? PDO::PARAM_INT
                : PDO::PARAM_NULL
        );


        // ========================================================
        // EJECUTAR
        // ========================================================

        $stmt->execute();


        // ========================================================
        // RESPUESTA
        // ========================================================

        Response::success(
            MissatgesAPI::success('create'),
            [
                'id' => $idString,
                'dataImatge' => $dataImatge,
                'any' => $anyValue,
            ],
            httpCode: 201
        );

        exit;
    } catch (\Throwable $e) {

        // ========================================================
        // SI FALLA BD, ELIMINAR ARCHIVO
        // ========================================================

        if (file_exists($targetFile)) {
            unlink($targetFile);
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

    // ============================================================
    // ENDPOINT
    // Galeria imatges
    // ============================================================
} else if ($slug === 'galeria-imatges') {

    // ============================================================
    // DATOS DE LA GALERÍA
    // ============================================================

    $nomGaleria = trim($_POST['nom'] ?? '');
    $publica = isset($_POST['publica']) ? 1 : 0;
    $slugGaleria = trim($_POST['slug'] ?? '');
    $directori = trim($_POST['directori'] ?? '');

    // Normalizar nombre del directorio
    $directori = iconv(
        'UTF-8',
        'ASCII//TRANSLIT//IGNORE',
        $directori
    );

    $directori = strtolower($directori);

    // Espacios → guiones
    $directori = preg_replace('/\s+/', '-', $directori);

    // Eliminar caracteres no permitidos
    $directori = preg_replace('/[^a-z0-9_-]/', '', $directori);

    // Evitar varios guiones consecutivos
    $directori = preg_replace('/-+/', '-', $directori);

    // Eliminar guiones al principio y al final
    $directori = trim($directori, '-_');

    $altGaleria = trim($_POST['alt'] ?? '');


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
    // VALIDAR DIRECTORIO
    // ============================================================

    if ($directori === '') {

        Response::error(
            'El directori és obligatori.',
            [
                'directori' => 'El directori és obligatori.'
            ],
            httpCode: 400
        );

        exit;
    }

    // El directorio solo puede contener:
    // letras, números, guión y guión bajo.
    //
    // No permitimos:
    // /
    // \
    // espacios
    // ..
    // caracteres especiales

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $directori)) {

        Response::error(
            'El nom del directori no és vàlid.',
            [
                'directori' => 'Només es permeten lletres, números, guions i guions baixos.'
            ],
            httpCode: 400
        );

        exit;
    }

    // ============================================================
    // ARCHIVOS
    // ============================================================

    if (
        empty($_FILES['imatges']) ||
        !isset($_FILES['imatges']['name']) ||
        !is_array($_FILES['imatges']['name'])
    ) {

        Response::error(
            'No s\'han rebut imatges.',
            [
                'imatges' => 'Cal seleccionar almenys una imatge.'
            ],
            httpCode: 400
        );

        exit;
    }


    $files = $_FILES['imatges'];

    $totalImages = count($files['name']);

    if ($totalImages === 0) {

        Response::error(
            'La galeria no conté imatges.',
            [],
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // DATOS INDIVIDUALES DE LAS IMÁGENES
    // ============================================================

    $noms = [];
    $alts = [];
    $anys = [];

    if (isset($_POST['imatges']) && is_array($_POST['imatges'])) {

        foreach ($_POST['imatges'] as $index => $imatge) {

            $noms[$index] = trim($imatge['nom'] ?? '');
            $alts[$index] = trim($imatge['alt'] ?? '');
            $anys[$index] = trim($imatge['any'] ?? '');
        }
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
    // DIRECTORIO
    // ============================================================

    $targetDir = rtrim($servidorMedia, '/')
        . '/galeria-imatges/'
        . $directori
        . '/';


    // ============================================================
    // COMPROBAR SI EL DIRECTORIO YA EXISTE
    // ============================================================

    if (is_dir($targetDir)) {

        Response::error(
            'El directori de la galeria ja existeix.',
            [
                'directori' => $directori
            ],
            httpCode: 400
        );

        exit;
    }


    // ============================================================
    // CREAR DIRECTORIO
    // ============================================================

    if (!mkdir($targetDir, 0777, true)) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'No se pudo crear el directorio.',
                'directori' => $targetDir
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // PERMISOS
    // ============================================================

    if (!is_writable($targetDir)) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'El directorio no tiene permisos de escritura.',
                'directori' => $targetDir
            ],
            httpCode: 500
        );

        exit;
    }


    // ============================================================
    // VALIDACIÓN MIME
    // ============================================================

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    ];

    $maxFileSize = 10 * 1024 * 1024; // 10 MB

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $maxWidth = 2000;
    $maxHeight = 2000;

    // ============================================================
    // ARCHIVOS CREADOS
    // ============================================================

    $createdFiles = [];


    // ============================================================
    // TRANSACCIÓN BD
    // ============================================================

    try {

        $pdo->beginTransaction();


        // ========================================================
        // CREAR GALERÍA
        // ========================================================

        $galleryUuid = Uuid::uuid7();

        $galleryId = $galleryUuid->getBytes();

        $galleryIdString = $galleryUuid->toString();

        $dateCreated = date('Y-m-d');


        $sqlGallery = "
        INSERT INTO db_img_galeries
        (
            id,
            nom,
            slug,
            publica,
            directori,
            alt,
            dateCreated
        )
        VALUES
        (
            :id,
            :nom,
            :slug,
            :publica,
            :directori,
            :alt,
            :dateCreated
        )
    ";

        $stmtGallery = $pdo->prepare($sqlGallery);

        $stmtGallery->bindValue(
            ':id',
            $galleryId,
            PDO::PARAM_LOB
        );

        $stmtGallery->bindValue(
            ':nom',
            $nomGaleria,
            PDO::PARAM_STR
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

        $stmtGallery->bindValue(
            ':directori',
            $directori,
            PDO::PARAM_STR
        );

        $stmtGallery->bindValue(
            ':alt',
            $altGaleria !== '' ? $altGaleria : null,
            $altGaleria !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmtGallery->bindValue(
            ':dateCreated',
            $dateCreated,
            PDO::PARAM_STR
        );

        $stmtGallery->execute();


        // ========================================================
        // PREPARAR INSERT IMAGEN
        // ========================================================

        $stmtImage = $pdo->prepare("
        INSERT INTO db_img
        (
            id,
            nameImg,
            extension,
            typeImg,
            nom,
            alt,
            dateCreated,
            dataImatge,
            any
        )
        VALUES
        (
            :id,
            :nameImg,
            :extension,
            :typeImg,
            :nom,
            :alt,
            :dateCreated,
            :dataImatge,
            :any
        )
    ");


        // ========================================================
        // PREPARAR INSERT RELACIÓN
        // ========================================================

        $stmtRelation = $pdo->prepare("
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
        // PROCESAR IMÁGENES
        // ========================================================

        foreach ($files['name'] as $index => $imageData) {

            // ----------------------------------------------------
            // Datos del archivo
            // ----------------------------------------------------

            $originalName = $files['name'][$index]['file'] ?? null;
            $tmpName      = $files['tmp_name'][$index]['file'] ?? null;
            $fileSize     = $files['size'][$index]['file'] ?? null;
            $fileError    = $files['error'][$index]['file'] ?? null;


            // ----------------------------------------------------
            // Verificar estructura
            // ----------------------------------------------------

            if (
                $originalName === null ||
                $tmpName === null ||
                $fileSize === null ||
                $fileError === null
            ) {

                throw new RuntimeException(
                    "Estructura de archivo no válida en la imagen número "
                        . ($index + 1)
                );
            }


            // ----------------------------------------------------
            // Error de subida
            // ----------------------------------------------------

            if ($fileError !== UPLOAD_ERR_OK) {

                throw new RuntimeException(
                    "Error al subir la imagen número "
                        . ($index + 1)
                        . ". Código de error PHP: "
                        . $fileError
                );
            }

            // ----------------------------------------------------
            // Tamaño
            // ----------------------------------------------------

            if ($fileSize > $maxFileSize) {

                throw new RuntimeException(
                    "La imagen número " . ($index + 1) . " supera los 10 MB."
                );
            }


            // ----------------------------------------------------
            // MIME real
            // ----------------------------------------------------

            $mimeType = $finfo->file($tmpName);

            if (!isset($allowedMimeTypes[$mimeType])) {

                throw new RuntimeException(
                    "La imagen número " . ($index + 1) . " no es un formato permitido."
                );
            }


            $extension = $allowedMimeTypes[$mimeType];

            // ----------------------------------------------------
            // Obtener fecha EXIF
            // ----------------------------------------------------

            $dataImatge = null;

            if (
                $mimeType === 'image/jpeg' &&
                function_exists('exif_read_data')
            ) {
                $exif = @exif_read_data(
                    $tmpName,
                    'EXIF',
                    true
                );

                if (
                    isset($exif['EXIF']['DateTimeOriginal']) &&
                    is_string($exif['EXIF']['DateTimeOriginal'])
                ) {
                    $dateOriginal = DateTime::createFromFormat(
                        'Y:m:d H:i:s',
                        $exif['EXIF']['DateTimeOriginal']
                    );

                    if ($dateOriginal !== false) {
                        $dataImatge = $dateOriginal->format(
                            'Y-m-d H:i:s'
                        );
                    }
                }
            }


            // ----------------------------------------------------
            // Nombre aleatorio
            // ----------------------------------------------------

            $uniqueName = bin2hex(random_bytes(16));

            $targetFile = $targetDir
                . $uniqueName
                . '.'
                . $extension;


            // ----------------------------------------------------
            // Mover archivo
            // ----------------------------------------------------

            // ----------------------------------------------------
            // Procesar y optimizar imagen con GD
            // ----------------------------------------------------

            // ----------------------------------------------------
            // Procesar y optimizar imagen con GD
            // ----------------------------------------------------

            switch ($mimeType) {

                case 'image/jpeg':

                    $sourceImage = imagecreatefromjpeg($tmpName);

                    if ($sourceImage === false) {

                        throw new RuntimeException(
                            "No se pudo procesar la imagen JPEG número " . ($index + 1)
                        );
                    }

                    break;


                case 'image/png':

                    $sourceImage = imagecreatefrompng($tmpName);

                    if ($sourceImage === false) {

                        throw new RuntimeException(
                            "No se pudo procesar la imagen PNG número " . ($index + 1)
                        );
                    }

                    // Mantener transparencia
                    imagealphablending($sourceImage, false);
                    imagesavealpha($sourceImage, true);

                    break;


                default:

                    throw new RuntimeException(
                        "Formato de imagen no permitido en la imagen número "
                            . ($index + 1)
                    );
            }


            // ----------------------------------------------------
            // Obtener dimensiones originales
            // ----------------------------------------------------

            $originalWidth = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);


            // ----------------------------------------------------
            // Calcular nuevas dimensiones
            // ----------------------------------------------------

            $newWidth = $originalWidth;
            $newHeight = $originalHeight;

            if (
                $originalWidth > $maxWidth ||
                $originalHeight > $maxHeight
            ) {

                $scale = min(
                    $maxWidth / $originalWidth,
                    $maxHeight / $originalHeight
                );

                $newWidth = (int) round($originalWidth * $scale);
                $newHeight = (int) round($originalHeight * $scale);
            }


            // ----------------------------------------------------
            // Crear imagen destino
            // ----------------------------------------------------

            $destinationImage = imagecreatetruecolor(
                $newWidth,
                $newHeight
            );


            // ----------------------------------------------------
            // Mantener transparencia PNG
            // ----------------------------------------------------

            if ($mimeType === 'image/png') {

                imagealphablending($destinationImage, false);
                imagesavealpha($destinationImage, true);

                $transparent = imagecolorallocatealpha(
                    $destinationImage,
                    0,
                    0,
                    0,
                    127
                );

                imagefill(
                    $destinationImage,
                    0,
                    0,
                    $transparent
                );
            }


            // ----------------------------------------------------
            // Redimensionar
            // ----------------------------------------------------

            if (!imagecopyresampled(
                $destinationImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            )) {

                throw new RuntimeException(
                    "No se pudo redimensionar la imagen número "
                        . ($index + 1)
                );
            }


            // ----------------------------------------------------
            // Guardar imagen optimizada
            // ----------------------------------------------------

            if ($mimeType === 'image/jpeg') {

                if (!imagejpeg(
                    $destinationImage,
                    $targetFile,
                    85
                )) {

                    throw new RuntimeException(
                        "No se pudo guardar la imagen JPEG número "
                            . ($index + 1)
                    );
                }
            } elseif ($mimeType === 'image/png') {

                if (!imagepng(
                    $destinationImage,
                    $targetFile,
                    6
                )) {

                    throw new RuntimeException(
                        "No se pudo guardar la imagen PNG número "
                            . ($index + 1)
                    );
                }
            }


            // ----------------------------------------------------
            // Liberar recursos
            // ----------------------------------------------------

            unset($sourceImage);
            unset($destinationImage);


            // Registrar archivo creado
            $createdFiles[] = $targetFile;


            // ----------------------------------------------------
            // Datos imagen
            // ----------------------------------------------------

            $imageUuid = Uuid::uuid7();

            $imageId = $imageUuid->getBytes();

            $imageIdString = $imageUuid->toString();

            $nameImg = $uniqueName;

            $nomImatge = trim($noms[$index] ?? '');
            $altImatge = trim($alts[$index] ?? '');
            $anyImatge = trim($anys[$index] ?? '');

            $anyValue = null;

            if ($anyImatge !== '') {
                if (
                    !ctype_digit($anyImatge) ||
                    (int) $anyImatge < 1000 ||
                    (int) $anyImatge > 2040
                ) {
                    throw new RuntimeException(
                        "L'any de la imagen número " . ($index + 1) . " no és vàlid."
                    );
                }

                $anyValue = (int) $anyImatge;
            }


            if ($nomImatge === '') {

                throw new RuntimeException(
                    "El nom de la imagen número " . ($index + 1) . " es obligatorio."
                );
            }


            // ----------------------------------------------------
            // INSERT db_img
            // ----------------------------------------------------

            $stmtImage->bindValue(
                ':id',
                $imageId,
                PDO::PARAM_LOB
            );

            $stmtImage->bindValue(
                ':nameImg',
                $nameImg,
                PDO::PARAM_STR
            );

            $stmtImage->bindValue(
                ':extension',
                $extension,
                PDO::PARAM_STR
            );

            $stmtImage->bindValue(
                ':typeImg',
                22,
                PDO::PARAM_INT
            );

            $stmtImage->bindValue(
                ':nom',
                $nomImatge,
                PDO::PARAM_STR
            );

            $stmtImage->bindValue(
                ':alt',
                $altImatge !== '' ? $altImatge : null,
                $altImatge !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
            );

            $stmtImage->bindValue(
                ':dateCreated',
                $dateCreated,
                PDO::PARAM_STR
            );

            $stmtImage->bindValue(
                ':dataImatge',
                $dataImatge,
                $dataImatge !== null
                    ? PDO::PARAM_STR
                    : PDO::PARAM_NULL
            );

            $stmtImage->bindValue(
                ':any',
                $anyValue,
                $anyValue !== null
                    ? PDO::PARAM_INT
                    : PDO::PARAM_NULL
            );

            $stmtImage->execute();


            // ----------------------------------------------------
            // INSERT relación
            // ----------------------------------------------------

            $relationUuid = Uuid::uuid7();

            $relationId = $relationUuid->getBytes();


            $stmtRelation->bindValue(
                ':id',
                $relationId,
                PDO::PARAM_LOB
            );

            $stmtRelation->bindValue(
                ':galeria_id',
                $galleryId,
                PDO::PARAM_LOB
            );

            $stmtRelation->bindValue(
                ':imatge_id',
                $imageId,
                PDO::PARAM_LOB
            );

            $stmtRelation->bindValue(
                ':ordre',
                $index + 1,
                PDO::PARAM_INT
            );

            $stmtRelation->execute();
        }


        // ========================================================
        // COMMIT
        // ========================================================

        $pdo->commit();


        // ========================================================
        // RESPUESTA
        // ========================================================

        Response::success(
            MissatgesAPI::success('create'),
            [
                'id' => $galleryIdString,
                'directori' => $directori,
                'imatges' => $totalImages,
            ],
            httpCode: 201
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
        // ELIMINAR ARCHIVOS
        // ========================================================

        foreach ($createdFiles as $createdFile) {

            if (is_file($createdFile)) {
                @unlink($createdFile);
            }
        }


        // ========================================================
        // ELIMINAR DIRECTORIO SI QUEDA VACÍO
        // ========================================================

        if (is_dir($targetDir)) {

            $contents = scandir($targetDir);

            if ($contents !== false && count($contents) === 2) {
                @rmdir($targetDir);
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
    //
}
