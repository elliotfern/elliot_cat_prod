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
header("Access-Control-Allow-Methods: PUT");

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

// PUT : Perfil curriculum
// URL: https://elliot.cat/api/curriculum/post/perfilCV
if ($slug === "perfilCV") {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
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

    // Datos entrantes
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
        $web = 'https://' . $web;
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn;
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Comprobar que el registro existe
        $chk = $conn->prepare("SELECT 1 FROM db_curriculum_perfil WHERE id = :id");
        $chk->bindValue(':id', $id, PDO::PARAM_INT);
        $chk->execute();
        if (!$chk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["Perfil amb id {$id} no existeix"], 404);
        }

        // UPDATE
        $sql = "UPDATE db_curriculum_perfil
                   SET nom_complet = :nom_complet,
                       email = :email,
                       tel = :tel,
                       web = :web,
                       localitzacio_ciutat = :localitzacio_ciutat,
                       img_perfil = :img_perfil,
                       disponibilitat = :disponibilitat,
                       visibilitat = :visibilitat
                 WHERE id = :id";
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

        // Auditoría
        $tipusOperacio = "UPDATE";
        $detalls = "Actualització perfil CV: {$nom_complet} ({$email})";

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
            MissatgesAPI::success('update'),
            ['id' => $id],
            200
        );
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // PUT : Perfil i18n curriculum
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
    $perfil_id = $toInt($data['perfil_id'] ?? 1) ?? 1;
    $locale    = $toInt($data['locale'] ?? null);
    $titular   = $trimOrNull($data['titular'] ?? null);
    $sumari    = $trimOrNull($data['sumari'] ?? null);
    $id    = $data['id'];

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

        // Verificar que existe ese perfil_id + locale
        $sqlChk = "SELECT id FROM db_curriculum_perfil_i18n WHERE perfil_id = :perfil_id AND locale = :locale LIMIT 1";
        $stChk = $conn->prepare($sqlChk);
        $stChk->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);
        $stChk->bindValue(':locale', $locale, PDO::PARAM_INT);
        $stChk->execute();
        $existsId = $stChk->fetchColumn();

        if (!$existsId) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ['perfil_id' => $perfil_id, 'locale' => $locale], 404);
        }

        // UPDATE
        $sql = "UPDATE db_curriculum_perfil_i18n
                   SET titular = :titular,
                       sumari = :sumari,
                       locale = :locale
                 WHERE id = :id";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':$id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':$locale', $locale, PDO::PARAM_INT);

        if ($titular === null) $stmt->bindValue(':titular', null, PDO::PARAM_NULL);
        else                   $stmt->bindValue(':titular', $titular, PDO::PARAM_STR);

        if ($sumari === null)  $stmt->bindValue(':sumari', null, PDO::PARAM_NULL);
        else                   $stmt->bindValue(':sumari', $sumari, PDO::PARAM_STR);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Actualització perfil_i18n perfil_id=%d, locale=%d, titular=%s", $perfil_id, $locale, (string)($titular ?? ''));
        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "UPDATE",
            $detalls,
            'db_curriculum_perfil_i18n',
            $existsId
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $existsId, 'perfil_id' => $perfil_id, 'locale' => $locale],
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
    // PUT :links curriculum
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
    $id        = $toIntOrNull($data['id'] ?? null);           // ID del link a actualizar
    $perfil_id = $toIntOrNull($data['perfil_id'] ?? 1) ?? 1;  // tu perfil suele ser 1
    $label     = $trimOrNull($data['label'] ?? null);         // VARCHAR(120) NULL
    $url       = $trimOrNull($data['url'] ?? null);           // VARCHAR(512) NOT NULL
    $posicio   = $toIntOrNull($data['posicio'] ?? 0) ?? 0;    // INT (orden)
    $visible   = $toBoolInt($data['visible'] ?? 1);           // 0/1

    // Validaciones
    $errors = [];
    if (!$id || $id < 1)                           $errors[] = ValidacioErrors::requerit('id');
    if (!$perfil_id || $perfil_id < 1)             $errors[] = ValidacioErrors::requerit('perfil_id');

    if ($label !== null && mb_strlen($label) > 120) $errors[] = ValidacioErrors::massaLlarg('label', 120);

    if ($url === null) {
        $errors[] = ValidacioErrors::requerit('url');
    } else {
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

        // Verificar que existe el registro
        $sqlChk = "SELECT id FROM db_curriculum_links WHERE id = :id AND perfil_id = :perfil_id LIMIT 1";
        $stChk  = $conn->prepare($sqlChk);
        $stChk->bindValue(':id',        $id,        PDO::PARAM_INT);
        $stChk->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);
        $stChk->execute();
        $existsId = $stChk->fetchColumn();

        if (!$existsId) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ['id' => $id], 404);
        }

        // UPDATE
        $sql = "UPDATE db_curriculum_links
                   SET label = :label,
                       url = :url,
                       posicio = :posicio,
                       visible = :visible,
                       perfil_id = :perfil_id
                 WHERE id = :id";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id',        $id,        PDO::PARAM_INT);
        $stmt->bindValue(':perfil_id', $perfil_id, PDO::PARAM_INT);

        if ($label === null) $stmt->bindValue(':label', null, PDO::PARAM_NULL);
        else                 $stmt->bindValue(':label', $label, PDO::PARAM_STR);

        $stmt->bindValue(':url',     $url,     PDO::PARAM_STR);
        $stmt->bindValue(':posicio', $posicio, PDO::PARAM_INT);
        $stmt->bindValue(':visible', $visible, PDO::PARAM_INT);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Update link id=%d perfil_id=%d label=%s url=%s", $id, $perfil_id, (string)($label ?? ''), $url);
        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "UPDATE",
            $detalls,
            'db_curriculum_links',
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            200
        );
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // PUT : habilitats cv
    // URL: https://elliot.cat/api/curriculum/post/habilitat
} else if ($slug === "habilitat") {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Normalizadores
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toIntOrNull = static fn($v): ?int    => (is_numeric($v) ? (int)$v : null);

    // Datos
    $id        = $toIntOrNull($data['id'] ?? null);      // obligatorio para UPDATE
    $nom       = $trimOrNull($data['nom'] ?? null);
    $imatge_id = $toIntOrNull($data['imatge_id'] ?? null);
    $posicio   = $toIntOrNull($data['posicio'] ?? 0) ?? 0;

    // Validaciones
    $errors = [];
    if (!$id || $id < 1) {
        $errors[] = ValidacioErrors::requerit('id');
    }
    if ($nom === null) {
        $errors[] = ValidacioErrors::requerit('nom');
    } elseif (mb_strlen($nom) > 100) {
        $errors[] = ValidacioErrors::massaLlarg('nom', 100);
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Verificar que existe
        $sqlChk = "SELECT id FROM db_curriculum_habilitats WHERE id = :id LIMIT 1";
        $stChk  = $conn->prepare($sqlChk);
        $stChk->bindValue(':id', $id, PDO::PARAM_INT);
        $stChk->execute();
        $existsId = $stChk->fetchColumn();

        if (!$existsId) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["id $id no trobat"], 404);
        }

        // UPDATE
        $sql = "UPDATE db_curriculum_habilitats
                   SET nom = :nom,
                       imatge_id = :imatge_id,
                       posicio = :posicio
                 WHERE id = :id";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);

        if ($imatge_id === null) $stmt->bindValue(':imatge_id', null, PDO::PARAM_NULL);
        else                     $stmt->bindValue(':imatge_id', $imatge_id, PDO::PARAM_INT);

        $stmt->bindValue(':posicio', $posicio, PDO::PARAM_INT);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Update habilitat id=%d, nom=%s, posicio=%d", $id, $nom, $posicio);
        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "UPDATE",
            $detalls,
            'db_curriculum_habilitats',
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            200
        );
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "experiencia") {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Normalizadores
    $trimOrNull = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toIntOrNull = static fn($v): ?int => (is_numeric($v) ? (int)$v : null);
    $toBoolInt = static fn($v): int => ($v === true || $v === 1 || $v === '1' || $v === 'on' || $v === 'true') ? 1 : 0;

    // Datos
    $empresa              = $trimOrNull($data['empresa'] ?? null);
    $empresa_url          = $trimOrNull($data['empresa_url'] ?? null);
    $empresa_localitzacio = $toIntOrNull($data['empresa_localitzacio'] ?? null);
    $data_inici           = $trimOrNull($data['data_inici'] ?? null);
    $data_fi              = $trimOrNull($data['data_fi'] ?? null);
    $is_current           = $toBoolInt($data['is_current'] ?? 0);
    $logo_empresa         = $toIntOrNull($data['logo_empresa'] ?? null);
    $posicio              = $toIntOrNull($data['posicio'] ?? 0) ?? 0;
    $visible              = $toBoolInt($data['visible'] ?? 1);

    // Validaciones
    $errors = [];
    if ($empresa === null) {
        $errors[] = ValidacioErrors::requerit('empresa');
    } elseif (mb_strlen($empresa) > 190) {
        $errors[] = ValidacioErrors::massaLlarg('empresa', 190);
    }

    if ($empresa_url !== null && mb_strlen($empresa_url) > 255) {
        $errors[] = ValidacioErrors::massaLlarg('empresa_url', 255);
    } elseif ($empresa_url !== null && !filter_var($empresa_url, FILTER_VALIDATE_URL)) {
        $errors[] = ValidacioErrors::invalid('empresa_url');
    }

    if ($data_inici === null) {
        $errors[] = ValidacioErrors::requerit('data_inici');
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inici)) {
        $errors[] = ValidacioErrors::dataNoValida('data_inici');
    }

    if ($data_fi !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fi)) {
        $errors[] = ValidacioErrors::dataNoValida('data_fi');
    }

    if ($data_inici && $data_fi) {
        if (strtotime($data_fi) < strtotime($data_inici)) {
            $errors[] = "La data de fi no pot ser anterior a la data d'inici.";
        }
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        /** @var PDO $conn */
        $conn->beginTransaction();

        $sql = "INSERT INTO db_curriculum_experiencia_professional
                    (empresa, empresa_url, empresa_localitzacio, data_inici, data_fi, is_current, logo_empresa, posicio, visible)
                VALUES
                    (:empresa, :empresa_url, :empresa_localitzacio, :data_inici, :data_fi, :is_current, :logo_empresa, :posicio, :visible)";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':empresa', $empresa, PDO::PARAM_STR);
        $stmt->bindValue(':empresa_url', $empresa_url, $empresa_url !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':empresa_localitzacio', $empresa_localitzacio, $empresa_localitzacio !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':data_inici', $data_inici, PDO::PARAM_STR);
        $stmt->bindValue(':data_fi', $data_fi, $data_fi !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':is_current', $is_current, PDO::PARAM_INT);
        $stmt->bindValue(':logo_empresa', $logo_empresa, $logo_empresa !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':posicio', $posicio, PDO::PARAM_INT);
        $stmt->bindValue(':visible', $visible, PDO::PARAM_INT);

        $stmt->execute();
        $newId = (int) $conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació experiència empresa=%s, data_inici=%s", $empresa, $data_inici);
        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "INSERT",
            $detalls,
            'db_curriculum_experiencia_professional',
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
} else if ($slug === "experienciaI18n") {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toIntOrNull = static fn($v): ?int    => (is_numeric($v) ? (int)$v : null);

    // Datos (id es obligatorio para actualizar)
    $id             = $toIntOrNull($data['id'] ?? null);
    $experiencia_id = $toIntOrNull($data['experiencia_id'] ?? null);
    $locale         = $toIntOrNull($data['locale'] ?? null);
    $rol_titol      = $trimOrNull($data['rol_titol'] ?? null);
    $sumari         = $trimOrNull($data['sumari'] ?? null);
    $fites          = $trimOrNull($data['fites'] ?? null);

    // Validaciones
    $errors = [];
    if ($id === null || $id < 1) {
        $errors[] = ValidacioErrors::requerit('id');
    }
    if ($experiencia_id === null) {
        $errors[] = ValidacioErrors::requerit('experiencia_id');
    }
    if ($locale === null) {
        $errors[] = ValidacioErrors::requerit('locale');
    }
    if ($rol_titol === null) {
        $errors[] = ValidacioErrors::requerit('rol_titol');
    } elseif (mb_strlen($rol_titol) > 190) {
        $errors[] = ValidacioErrors::massaLlarg('rol_titol', 190);
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Comprobar que existe
        $sqlChk = "SELECT id FROM db_curriculum_experiencia_professional_i18n WHERE id = :id LIMIT 1";
        $stChk  = $conn->prepare($sqlChk);
        $stChk->bindValue(':id', $id, PDO::PARAM_INT);
        $stChk->execute();
        if (!$stChk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["experiencia_i18n id=$id no trobada"], 404);
        }

        // UPDATE
        $sql = "UPDATE db_curriculum_experiencia_professional_i18n
                   SET experiencia_id = :experiencia_id,
                       locale = :locale,
                       rol_titol = :rol_titol,
                       sumari = :sumari,
                       fites = :fites
                 WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id',             $id,             PDO::PARAM_INT);
        $stmt->bindValue(':experiencia_id', $experiencia_id, PDO::PARAM_INT);
        $stmt->bindValue(':locale',         $locale,         PDO::PARAM_INT);
        $stmt->bindValue(':rol_titol',      $rol_titol,      PDO::PARAM_STR);

        if ($sumari === null) $stmt->bindValue(':sumari', null, PDO::PARAM_NULL);
        else                  $stmt->bindValue(':sumari', $sumari, PDO::PARAM_STR);

        if ($fites === null)  $stmt->bindValue(':fites', null, PDO::PARAM_NULL);
        else                  $stmt->bindValue(':fites', $fites, PDO::PARAM_STR);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf(
            "Update experiència_i18n id=%d, experiencia_id=%d, locale=%d, rol=%s",
            $id,
            $experiencia_id,
            $locale,
            $rol_titol
        );
        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "UPDATE",
            $detalls,
            'db_curriculum_experiencia_professional_i18n',
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            200
        );
    } catch (PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "educacio") {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    $errors = [];

    // 🔎 Validacions bàsiques
    $id = isset($data['id']) ? (int)$data['id'] : null;
    if (!$id || $id < 1) {
        $errors[] = ValidacioErrors::requerit('id');
    }

    if (empty($data['institucio'])) {
        $errors[] = ValidacioErrors::requerit('institucio');
    }

    if (empty($data['institucio_url'])) {
        $errors[] = ValidacioErrors::requerit('institucio_url');
    } elseif (!filter_var($data['institucio_url'], FILTER_VALIDATE_URL)) {
        $errors[] = ValidacioErrors::invalid('institucio_url');
    }

    if (!empty($errors)) {
        Response::error(
            MissatgesAPI::error('validacio'),
            $errors,
            400
        );
    }

    // 📌 Assignació de valors
    $institucio              = $data['institucio'];
    $institucio_url          = $data['institucio_url'];
    $institucio_localitzacio = !empty($data['institucio_localitzacio']) ? (int)$data['institucio_localitzacio'] : null;
    $data_inici              = !empty($data['data_inici']) ? $data['data_inici'] : null;
    $data_fi                 = !empty($data['data_fi']) ? $data['data_fi'] : null;
    $logo_id                 = !empty($data['logo_id']) ? (int)$data['logo_id'] : null;
    $posicio                 = isset($data['posicio']) ? (int)$data['posicio'] : 0;
    $visible                 = isset($data['visible']) ? (int)!!$data['visible'] : 1;

    try {
        $conn->beginTransaction();

        // Comprovar que existeix
        $chk = $conn->prepare("SELECT id FROM db_curriculum_educacio WHERE id = :id LIMIT 1");
        $chk->bindValue(':id', $id, PDO::PARAM_INT);
        $chk->execute();
        if (!$chk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["Educació id=$id no trobada"], 404);
        }

        // UPDATE
        $sql = "UPDATE db_curriculum_educacio
                   SET institucio = :institucio,
                       institucio_url = :institucio_url,
                       institucio_localitzacio = :institucio_localitzacio,
                       data_inici = :data_inici,
                       data_fi = :data_fi,
                       logo_id = :logo_id,
                       posicio = :posicio,
                       visible = :visible
                 WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':institucio', $institucio, PDO::PARAM_STR);
        $stmt->bindValue(':institucio_url', $institucio_url, PDO::PARAM_STR);

        if ($institucio_localitzacio === null) $stmt->bindValue(':institucio_localitzacio', null, PDO::PARAM_NULL);
        else                                   $stmt->bindValue(':institucio_localitzacio', $institucio_localitzacio, PDO::PARAM_INT);

        if ($data_inici === null) $stmt->bindValue(':data_inici', null, PDO::PARAM_NULL);
        else                      $stmt->bindValue(':data_inici', $data_inici, PDO::PARAM_STR);

        if ($data_fi === null) $stmt->bindValue(':data_fi', null, PDO::PARAM_NULL);
        else                   $stmt->bindValue(':data_fi', $data_fi, PDO::PARAM_STR);

        if ($logo_id === null) $stmt->bindValue(':logo_id', null, PDO::PARAM_NULL);
        else                   $stmt->bindValue(':logo_id', $logo_id, PDO::PARAM_INT);

        $stmt->bindValue(':posicio', $posicio, PDO::PARAM_INT);
        $stmt->bindValue(':visible', $visible, PDO::PARAM_INT);

        $stmt->execute();

        // Auditoría
        $tipusOperacio = "UPDATE";
        $detalls = "Actualització educació id=$id: " . $institucio;

        Audit::registrarCanvi(
            $conn,
            $userUuid,
            $tipusOperacio,
            $detalls,
            Tables::CURRICULUM_EDUCACIO,
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
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
} else if ($slug === 'educacioI18') {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    $errors = [];

    // 🔎 Validacions bàsiques
    $id = isset($data['id']) ? (int)$data['id'] : null;
    if (!$id || $id < 1) {
        $errors[] = ValidacioErrors::requerit('id');
    }

    if (empty($data['educacio_id'])) {
        $errors[] = ValidacioErrors::requerit('educacio_id');
    }
    if (empty($data['locale'])) {
        $errors[] = ValidacioErrors::requerit('locale');
    }
    if (empty($data['grau'])) {
        $errors[] = ValidacioErrors::requerit('grau');
    } elseif (strlen($data['grau']) > 190) {
        $errors[] = ValidacioErrors::massaLlarg('grau', 190);
    }

    if (!empty($errors)) {
        Response::error(
            MissatgesAPI::error('validacio'),
            $errors,
            400
        );
    }

    // 📌 Assignació de valors
    $educacio_id = (int)$data['educacio_id'];
    $locale      = (int)$data['locale'];
    $grau        = $data['grau'];
    $notes       = !empty($data['notes']) ? $data['notes'] : null;

    try {
        $conn->beginTransaction();

        // Comprovar que existeix la fila
        $chk = $conn->prepare("SELECT id FROM db_curriculum_educacio_i18n WHERE id = :id LIMIT 1");
        $chk->bindValue(':id', $id, PDO::PARAM_INT);
        $chk->execute();
        if (!$chk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["Traducció educació id=$id no trobada"], 404);
        }

        // UPDATE
        $sql = "UPDATE db_curriculum_educacio_i18n
                   SET educacio_id = :educacio_id,
                       locale = :locale,
                       grau = :grau,
                       notes = :notes
                 WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':educacio_id', $educacio_id, PDO::PARAM_INT);
        $stmt->bindParam(':locale', $locale, PDO::PARAM_INT);
        $stmt->bindParam(':grau', $grau, PDO::PARAM_STR);
        if ($notes === null) {
            $stmt->bindValue(':notes', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
        }

        $stmt->execute();

        // Auditoría
        $tipusOperacio = "UPDATE";
        $detalls = "Actualització traducció educació id=$id (grau: $grau)";

        Audit::registrarCanvi(
            $conn,
            $userUuid,
            $tipusOperacio,
            $detalls,
            Tables::CURRICULUM_EDUCACIO_I18N,
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
