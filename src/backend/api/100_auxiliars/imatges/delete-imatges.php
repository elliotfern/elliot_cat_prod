<?php

/*
 * BACKEND IMATGES
 * FUNCIONS DELETE IMATGES
 * @db_img
 */

use App\Utils\MissatgesAPI;
use App\Utils\Response;
use App\Utils\Uuid;
use App\Config\Database;

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

corsAllow($allowedOrigins);


// ============================================================
// MÉTODO HTTP
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {

    Response::error(
        'Mètode no vàlid.',
        [],
        httpCode: 405
    );

    exit;
}


// ============================================================
// ENDPOINT
// ============================================================

if ($slug === 'imatgeId') {


    // ========================================================
    // TIPOS DE IMAGEN
    // ========================================================

    $allowedTypes = [
        1  => 'persona',
        2  => 'biblioteca-llibre',
        3  => 'historia-imatge',
        4  => 'historia-esdeveniment',
        6  => 'historia-organitzacio',
        7  => 'cinema-serie',
        8  => 'cinema-pelicula',
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


    // ========================================================
    // ID
    // ========================================================

    $id = trim($_GET['id'] ?? '');


    // ========================================================
    // VALIDAR ID
    // ========================================================

    if ($id === '') {

        Response::error(
            MissatgesAPI::error('invalid_data'),
            [
                'id' => 'required'
            ],
            httpCode: 400
        );

        exit;
    }


    // ========================================================
    // CONVERTIR UUID A BINARY
    // ========================================================

    try {

        $idBin = Uuid::toBinary($id);
    } catch (\Throwable $e) {

        Response::error(
            MissatgesAPI::error('invalid_data'),
            [
                'id' => 'invalid_uuid'
            ],
            httpCode: 400
        );

        exit;
    }


    // ========================================================
    // OBTENER DATOS DE LA IMAGEN
    // ========================================================

    try {

        $sql = "
            SELECT
                id,
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
            $idBin,
            PDO::PARAM_LOB
        );

        $stmt->execute();

        $image = $stmt->fetch(PDO::FETCH_ASSOC);


        // ====================================================
        // IMAGEN NO EXISTE
        // ====================================================

        if (!$image) {

            Response::error(
                MissatgesAPI::error('not_found'),
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


    // ========================================================
    // OBTENER RUTA MEDIA
    // ========================================================

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


    // ========================================================
    // DATOS DEL ARCHIVO
    // ========================================================

    $nameImg = $image['nameImg'];
    $extension = $image['extension'];
    $typeImg = (int) $image['typeImg'];


    // ========================================================
    // VALIDAR TIPO DE IMAGEN
    // ========================================================

    if (!isset($allowedTypes[$typeImg])) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'El tipus d\'imatge no és vàlid.',
                'typeImg' => $typeImg
            ],
            httpCode: 500
        );

        exit;
    }

    // ========================================================
    // IMÁGENES DE GALERÍA
    // ========================================================

    if ($typeImg === 22) {

        Response::error(
            'La imatge pertany a una galeria i no es pot eliminar des d\'aquí.',
            [
                'id' => 'gallery_image'
            ],
            httpCode: 400
        );

        exit;
    }


    // ========================================================
    // VALIDAR DATOS DEL ARCHIVO
    // ========================================================

    if (
        $nameImg === null ||
        $nameImg === '' ||
        $extension === null ||
        $extension === ''
    ) {

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => 'La imatge no té informació del fitxer.'
            ],
            httpCode: 500
        );

        exit;
    }


    // ========================================================
    // CONSTRUIR RUTA DEL ARCHIVO
    // ========================================================

    $typeDirectory = $allowedTypes[$typeImg];

    $targetFile =
        rtrim($servidorMedia, '/') .
        '/' .
        $typeDirectory .
        '/' .
        $nameImg .
        '.' .
        $extension;


    // ========================================================
    // ELIMINAR REGISTRO DE BD
    // ========================================================

    try {

        $pdo->beginTransaction();


        // ====================================================
        // DELETE DB
        // ====================================================

        $sqlDelete = "
            DELETE FROM db_img
            WHERE id = :id
        ";

        $stmtDelete = $pdo->prepare($sqlDelete);

        $stmtDelete->bindValue(
            ':id',
            $idBin,
            PDO::PARAM_LOB
        );

        $stmtDelete->execute();


        // ====================================================
        // COMPROBAR DELETE
        // ====================================================

        if ($stmtDelete->rowCount() !== 1) {

            throw new RuntimeException(
                'No se pudo eliminar la imagen de la base de datos.'
            );
        }


        // ====================================================
        // COMMIT
        // ====================================================

        $pdo->commit();
    } catch (\Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            httpCode: 500
        );

        exit;
    }


    // ========================================================
    // ELIMINAR ARCHIVO FÍSICO
    // ========================================================

    if (is_file($targetFile)) {

        if (!unlink($targetFile)) {

            /*
             * La imagen ya ha sido eliminada de la BD,
             * pero no se ha podido eliminar el archivo físico.
             *
             * No devolvemos error 500 porque la operación
             * principal ya se ha completado.
             */

            Response::success(
                MissatgesAPI::success('delete'),
                [
                    'id' => $id,
                    'file_deleted' => false,
                    'message' => 'La imatge s\'ha eliminat de la base de dades, però no s\'ha pogut eliminar el fitxer físic.'
                ],
                httpCode: 200
            );

            exit;
        }
    }


    // ========================================================
    // RESPUESTA CORRECTA
    // ========================================================

    Response::success(
        MissatgesAPI::success('delete'),
        [
            'id' => $id,
            'file_deleted' => true,
        ],
        httpCode: 200
    );

    exit;
}


// ============================================================
// ENDPOINT NO VÁLIDO
// ============================================================

Response::error(
    MissatgesAPI::error('internal_error'),
    [
        'message' => 'Error endpoint',
    ],
    httpCode: 500
);

exit;
