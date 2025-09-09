<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;

$slug = $routeParams[0];

/*
 * BACKEND DB CURRICULUM
 * FUNCIONS
 * @
 */

$conn = DatabaseConnection::getConnection();

if (!$conn) {
    die("No se pudo establecer conexión a la base de datos.");
}

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Requiere ADMIN por token (user_type === 1)
if (!isAuthenticatedAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autoritzat (admin requerit)']);
    exit;
}

$userUuid = getAuthenticatedUserUuid(); // para auditoría, si la soportas

// POST : Perfil curriculum
// URL: https://elliot.cat/api/curriculum/post/perfilCV
if ($slug === "perfilCV") {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers de normalización
    $trimOrNull = static function ($v): ?string {
        if ($v === null || $v === '' || (is_string($v) && trim($v) === '')) return null;
        return is_string($v) ? trim($v) : (string)$v;
    };
    $toIntOrNull = static function ($v): ?int {
        if ($v === null || $v === '' || $v === false) return null;
        if (is_numeric($v)) return (int)$v;
        return null;
    };
    $toBool = static function ($v): int {
        return ($v === true || $v === 1 || $v === '1' || $v === 'on' || $v === 'true') ? 1 : 0;
    };

    // Datos entrantes (id por defecto 1: tabla “1 fila”)
    $id                  = isset($data['id']) ? (int)$data['id'] : 1;
    $nom_complet         = $trimOrNull($data['nom_complet'] ?? null);
    $email               = $trimOrNull($data['email'] ?? null);
    $tel                 = $trimOrNull($data['tel'] ?? null);
    $web                 = $trimOrNull($data['web'] ?? null);
    $localitzacio_ciutat = $trimOrNull($data['localitzacio_ciutat'] ?? null);
    $img_perfil          = $toIntOrNull($data['img_perfil'] ?? null);
    $disponibilitat      = $toIntOrNull($data['disponibilitat'] ?? null);
    $visibilitat         = $toBool($data['visibilitat'] ?? 1);

    // Validación
    $errors = [];
    if (!$nom_complet) $errors[] = ValidacioErrors::requerit('nom_complet');
    if (!$email) {
        $errors[] = ValidacioErrors::requerit('email');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = ValidacioErrors::invalid('email');
    }
    if ($web && !preg_match('~^https?://~i', $web)) {
        // opcional: normaliza a https si falta esquema
        $web = 'https://' . $web;
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn;
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Conflicto si ya existe ese id (evita PK duplicate)
        $chk = $conn->prepare("SELECT 1 FROM db_curriculum_perfil WHERE id = :id");
        $chk->bindValue(':id', $id, PDO::PARAM_INT);
        $chk->execute();
        if ($chk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('duplicat'), ["Perfil amb id {$id} ja existeix"], 409);
        }

        // INSERT
        $sql = "INSERT INTO db_curriculum_perfil
                  (id, nom_complet, email, tel, web, localitzacio_ciutat, img_perfil, disponibilitat, visibilitat)
                VALUES
                  (:id, :nom_complet, :email, :tel, :web, :localitzacio_ciutat, :img_perfil, :disponibilitat, :visibilitat)";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nom_complet', $nom_complet, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        if ($tel === null)                 $stmt->bindValue(':tel', null, PDO::PARAM_NULL);
        else                               $stmt->bindValue(':tel', $tel, PDO::PARAM_STR);

        if ($web === null)                 $stmt->bindValue(':web', null, PDO::PARAM_NULL);
        else                               $stmt->bindValue(':web', $web, PDO::PARAM_STR);

        if ($localitzacio_ciutat === null) $stmt->bindValue(':localitzacio_ciutat', null, PDO::PARAM_NULL);
        else                               $stmt->bindValue(':localitzacio_ciutat', $localitzacio_ciutat, PDO::PARAM_STR);

        if ($img_perfil === null)          $stmt->bindValue(':img_perfil', null, PDO::PARAM_NULL);
        else                               $stmt->bindValue(':img_perfil', $img_perfil, PDO::PARAM_INT);

        if ($disponibilitat === null)      $stmt->bindValue(':disponibilitat', null, PDO::PARAM_NULL);
        else                               $stmt->bindValue(':disponibilitat', $disponibilitat, PDO::PARAM_INT);

        $stmt->bindValue(':visibilitat', $visibilitat, PDO::PARAM_INT);

        $stmt->execute();
        $id = $conn->lastInsertId();

        // Auditoría
        $tipusOperacio = "INSERT";
        $detalls = "Creació perfil CV: {$nom_complet} ({$email})";

        Audit::registrarCanvi(
            $conn,
            $userUuid,
            $tipusOperacio,
            $detalls,
            Tables::CURRICULUM_PERFIL,
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $id],
            200
        );
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // POST : Perfil i18n curriculum
    // URL: https://elliot.cat/api/curriculum/post/perfilCVi18n
} else if ($slug === "perfilCVi18n") {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // —— Normalización
    $trimOrNull = static function ($v): ?string {
        if ($v === null || $v === '' || (is_string($v) && trim($v) === '')) return null;
        return is_string($v) ? trim($v) : (string)$v;
    };
    $toInt = static function ($v): ?int {
        if ($v === null || $v === '' || $v === false) return null;
        if (is_numeric($v)) return (int)$v;
        return null;
    };

    // Datos
    $perfil_id = $toInt($data['perfil_id'] ?? 1) ?? 1;      // tu perfil suele ser 1
    $locale    = $toInt($data['locale'] ?? null);           // INT (p. ej. 1=ca,2=es,3=en)
    $titular   = $trimOrNull($data['titular'] ?? null);     // VARCHAR(200) NULL
    $sumari    = $trimOrNull($data['sumari'] ?? null);      // MEDIUMTEXT NULL

    // —— Validación
    $errors = [];
    if (!$perfil_id || $perfil_id < 1) {
        $errors[] = ValidacioErrors::requerit('perfil_id');
    }
    if ($locale === null) {
        $errors[] = ValidacioErrors::requerit('locale');
    } elseif ($locale < 1) {
        $errors[] = ValidacioErrors::invalid('locale');
    }

    if ($titular === null)                    $errors[] = ValidacioErrors::requerit('titular');
    elseif (mb_strlen($titular) > 200)        $errors[] = ValidacioErrors::massaLlarg('titular', 200);

    if ($sumari === null)                     $errors[] = ValidacioErrors::requerit('sumari');

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Duplicado lógico: perfil + locale
        $sqlChk = "SELECT id FROM db_curriculum_perfil_i18n WHERE perfil_id = :perfil_id AND locale = :locale LIMIT 1";
        $stChk = $conn->prepare($sqlChk);
        $stChk->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);
        $stChk->bindValue(':locale', $locale, PDO::PARAM_INT);
        $stChk->execute();
        $existsId = $stChk->fetchColumn();

        if ($existsId) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('duplicat'), ['perfil_id' => $perfil_id, 'locale' => $locale], 409);
        }

        // INSERT (sin FKs, solo valores)
        $sql = "INSERT INTO db_curriculum_perfil_i18n
                    (perfil_id, locale, titular, sumari)
                VALUES
                    (:perfil_id, :locale, :titular, :sumari)";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);
        $stmt->bindValue(':locale', $locale, PDO::PARAM_INT);

        if ($titular === null) $stmt->bindValue(':titular', null, PDO::PARAM_NULL);
        else                   $stmt->bindValue(':titular', $titular, PDO::PARAM_STR);

        if ($sumari === null)  $stmt->bindValue(':sumari', null, PDO::PARAM_NULL);
        else                   $stmt->bindValue(':sumari', $sumari, PDO::PARAM_STR);

        $stmt->execute();
        $newId = $conn->lastInsertId();


        // Auditoría
        $detalls = sprintf("Creació perfil_i18n perfil_id=%d, locale=%d, titular=%s", $perfil_id, $locale, (string)($titular ?? ''));
        Audit::registrarCanvi(
            $conn,
            $userUuid,                        // UUID textual; tu Audit lo convierte a BINARY(16)
            "INSERT",
            $detalls,
            'db_curriculum_perfil_i18n',      // nombre tabla directo
            $newId
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $newId, 'perfil_id' => $perfil_id, 'locale' => $locale],
            200
        );
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
    // POST :links curriculum
    // URL: https://elliot.cat/api/curriculum/post/linkCV
} else if ($slug === "linkCV") {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Normalizadores
    $trimOrNull = static function ($v): ?string {
        if ($v === null) return null;
        if (is_string($v) && trim($v) === '') return null;
        if ($v === '') return null;
        return is_string($v) ? trim($v) : (string)$v;
    };
    $toIntOrNull = static function ($v): ?int {
        if ($v === null || $v === '' || $v === false) return null;
        if (is_numeric($v)) return (int)$v;
        return null;
    };
    $toBoolInt = static function ($v): int {
        return ($v === true || $v === 1 || $v === '1' || $v === 'on' || $v === 'true') ? 1 : 0;
    };

    // Datos
    $perfil_id = $toIntOrNull($data['perfil_id'] ?? 1) ?? 1; // tu perfil suele ser 1
    $label     = $trimOrNull($data['label'] ?? null);        // VARCHAR(120) NULL
    $url       = $trimOrNull($data['url'] ?? null);          // VARCHAR(512) NOT NULL
    $posicio   = $toIntOrNull($data['posicio'] ?? 0) ?? 0;   // INT (orden)
    $visible   = $toBoolInt($data['visible'] ?? 1);          // 0/1

    // Validaciones
    $errors = [];
    if (!$perfil_id || $perfil_id < 1)                $errors[] = ValidacioErrors::requerit('perfil_id');

    if ($label !== null && mb_strlen($label) > 120)   $errors[] = ValidacioErrors::massaLlarg('label', 120);

    if ($url === null) {
        $errors[] = ValidacioErrors::requerit('url');
    } else {
        // si falta esquema, añade https://
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }
        if (mb_strlen($url) > 512) {
            $errors[] = ValidacioErrors::massaLlarg('url', 512);
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = ValidacioErrors::invalid('url');
        }
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Evitar duplicados por perfil + url (ajusta si prefieres (perfil,label,url))
        $sqlChk = "SELECT id FROM db_curriculum_links WHERE perfil_id = :perfil_id AND url = :url LIMIT 1";
        $stChk  = $conn->prepare($sqlChk);
        $stChk->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);
        $stChk->bindValue(':url',       $url,       PDO::PARAM_STR);
        $stChk->execute();
        if ($stChk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('duplicat'), ['perfil_id' => $perfil_id, 'url' => $url], 409);
        }

        // INSERT
        $sql = "INSERT INTO db_curriculum_links
                    (perfil_id, label, url, posicio, visible)
                VALUES
                    (:perfil_id, :label, :url, :posicio, :visible)";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);

        if ($label === null) $stmt->bindValue(':label', null, PDO::PARAM_NULL);
        else                 $stmt->bindValue(':label', $label, PDO::PARAM_STR);

        $stmt->bindValue(':url',     $url,     PDO::PARAM_STR);
        $stmt->bindValue(':posicio', $posicio, PDO::PARAM_INT);
        $stmt->bindValue(':visible', $visible, PDO::PARAM_INT);

        $stmt->execute();

        $newId = (int)$conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació link perfil_id=%d, label=%s, url=%s", $perfil_id, (string)($label ?? ''), $url);
        Audit::registrarCanvi(
            $conn,
            $userUuid,                  // UUID textual → Audit lo convierte a BINARY(16)
            "INSERT",
            $detalls,
            'db_curriculum_links',
            $newId
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $newId],
            200
        );
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
