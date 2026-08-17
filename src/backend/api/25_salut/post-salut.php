<?php

use App\Config\Database;
use App\Utils\Mailer;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;
use Ramsey\Uuid\Uuid as RamseyUuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

/*
 * BACKEND SALUT
 * POST SALUT
 */

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * POST : Demanar recepta de medicaments
 * URL: https://elliot.cat/api/salut/post/receptaMedicament?id=1
 */
if ($slug === "receptaMedicament") {
    try {

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            Response::error(
                MissatgesAPI::error('bad_request'),
                ['id' => 'required'],
                400
            );
            return;
        }

        $sql = <<<SQL
                SELECT
                    m.id, m.medicament, m.quantitat_defecte,
                    f.nom AS facultatiuNom, f.email AS facultatiuEmail, f.genere AS facultatiuGenere,
                    p.patologia, p.genere
                FROM %s AS m
                LEFT JOIN %s AS f ON f.id = m.facultatiu_id
                LEFT JOIN %s AS pm ON pm.medicament_id = m.id
                LEFT JOIN %s AS p ON p.id = pm.patologia_id
                WHERE m.id = :id
                LIMIT 1
            SQL;

        $query = sprintf(
            $sql,
            qi(Tables::DB_SALUT_MEDICAMENTS, $pdo),
            qi(Tables::DB_SALUT_FACULTATIUS, $pdo),
            qi(Tables::DB_SALUT_PATOLOGIES_MEDICAMENTS, $pdo),
            qi(Tables::DB_SALUT_PATOLOGIES, $pdo)
        );

        $params = [':id' => Uuid::toBinary($id)];
        $rows = $db->getData($query, $params, false);

        if (!$rows) {
            Response::error(
                MissatgesAPI::error('notFound'),
                ['id' => 'not_found'],
                404
            );
            return;
        }

        $r = $rows[0];
        $medicamentIdBin = Uuid::toBinary($id);

        function logSollicitud($pdo, $medicamentIdBin, $quantitat, $estat, $errorMissatge = null)
        {
            $logUuid = RamseyUuid::uuid7();

            $sqlLog = "INSERT INTO " . Tables::DB_SALUT_RECEPTES . " (
                        id, medicament_id, quantitat, estat, error_missatge
                    ) VALUES (
                        :id, :medicament_id, :quantitat, :estat, :error_missatge
                    )";

            $stmtLog = $pdo->prepare($sqlLog);

            $stmtLog->bindValue(':id', $logUuid->getBytes(), PDO::PARAM_LOB);
            $stmtLog->bindValue(':medicament_id', $medicamentIdBin, PDO::PARAM_LOB);
            $stmtLog->bindValue(':quantitat', $quantitat, $quantitat === null || $quantitat === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtLog->bindValue(':estat', $estat, PDO::PARAM_STR);
            $stmtLog->bindValue(':error_missatge', $errorMissatge, $errorMissatge === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            $stmtLog->execute();
        }

        if (empty($r['facultatiuEmail'])) {
            logSollicitud($pdo, $medicamentIdBin, $r['quantitat_defecte'] ?? null, 'error', 'Medicament sense facultatiu o facultatiu sense email');

            Response::error(
                MissatgesAPI::error('invalid_data'),
                ['facultatiu' => 'medicament sense facultatiu o facultatiu sense email'],
                400
            );
            return;
        }

        // Dades fixes de moment (no hi ha taula de "pacient", és una app d'ús personal)
        $pazienteNom = 'Elliot Fernandez Hernandez';
        $telefon     = '377 813 0921';
        $subject     = 'Richiesta rinnovo ricetta';

        $genere     = $r['genere'] ?? 'f';
        $connettore = $genere === 'm' ? 'del mio' : 'della mia';
        $patologia  = $r['patologia'] ?? null;
        $quantitat  = $r['quantitat_defecte'] ?? '';

        $facultatiuGenere  = $r['facultatiuGenere'] ?? 'm';
        $facultatiuArticle = $facultatiuGenere === 'f' ? 'della' : 'del';
        $facultatiuTitol   = $facultatiuGenere === 'f' ? 'Dott.ssa' : 'Dott.';

        $frasePatologia = $patologia
            ? "Avrei bisogno di una nuova ricetta per il trattamento {$connettore} {$patologia}.\n"
            : "Avrei bisogno di una nuova ricetta.\n";

        $bodyText = "All'attenzione {$facultatiuArticle} {$facultatiuTitol} {$r['facultatiuNom']},\n\n"
            . "Buongiorno,\n"
            . $frasePatologia
            . "Il farmaco è il seguente:\n\n"
            . "* " . trim("{$quantitat} di {$r['medicament']}") . "\n\n"
            . "La ringrazio anticipatamente per la disponibilità.\n\n"
            . "Cordiali saluti,\n"
            . "Paziente assistito: {$pazienteNom}\n"
            . "Telefono: {$telefon}";

        $bodyHtml = nl2br(htmlspecialchars($bodyText));

        $mailer = new Mailer();

        $sent = $mailer->send(
            to: $r['facultatiuEmail'],
            toName: $r['facultatiuNom'] ?? '',
            subject: $subject,
            htmlBody: $bodyHtml,
            plainText: $bodyText,
            bcc: ['elliot@hispantic.com' => 'Còpia sol·licitud recepta'],
            replyTo: 'elliot@hispantic.com',
        );

        if (!$sent) {
            logSollicitud($pdo, $medicamentIdBin, $quantitat, 'error', 'Mailer::send() ha fallat (revisa error_log del servidor)');

            Response::error(
                MissatgesAPI::error('errorEnviament'),
                ['mail' => 'send_failed'],
                500
            );
            return;
        }

        logSollicitud($pdo, $medicamentIdBin, $quantitat, 'enviada');

        Response::success(
            message: MissatgesAPI::success('create'),
            data: ['sent' => true],
            httpCode: 200
        );
    } catch (Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    /**
     * POST : Alta facultatiu
     * URL: https://elliot.cat/api/salut/post/facultatiu
     */
} else if ($slug === "facultatiu") {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
        exit;
    }

    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return $data[$key];
    }

    function optionalField(array $data, string $key)
    {
        return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
            ? $data[$key]
            : null;
    }

    function isUuid($s)
    {
        return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
    }

    $errors = [];

    $nom      = requireField($data, 'nom', $errors);
    $genereFacultatiu = $data['genere'] ?? 'm';
    $especialitat = requireField($data, 'especialitat', $errors);
    $direccio = optionalField($data, 'direccio');
    $email    = optionalField($data, 'email');
    $telefon  = optionalField($data, 'telefon');
    $ciutatId = optionalField($data, 'ciutat_id');

    if ($ciutatId !== null && !isUuid($ciutatId)) {
        $errors['ciutat_id'] = 'invalid_uuid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    $uuid = RamseyUuid::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = $uuid->toString();

    $sql = "INSERT INTO " . Tables::DB_SALUT_FACULTATIUS . " (
                id, nom, genere, direccio, ciutat_id, email, telefon, especialitat, created_at, updated_at
            ) VALUES (
                :id, :nom, :genere, :direccio, :ciutat_id, :email, :telefon, :especialitat, NOW(), NOW()
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':genere', $genereFacultatiu, PDO::PARAM_STR);
        $stmt->bindValue(':especialitat', $especialitat, PDO::PARAM_STR);
        $stmt->bindValue(':direccio', $direccio, $direccio === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':ciutat_id', $ciutatId === null ? null : App\Utils\Uuid::toBinary($ciutatId), $ciutatId === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
        $stmt->bindValue(':email', $email, $email === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':telefon', $telefon, $telefon === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($stmt->execute()) {
            Response::success(
                MissatgesAPI::success('create'),
                [
                    'id'       => $uuidString,
                    'nom'      => $nom,
                    'direccio' => $direccio,
                    'email'    => $email,
                    'telefon'  => $telefon,
                    'ciutat_id' => $ciutatId,
                ],
                httpCode: 201
            );
            exit;
        }

        Response::error(
            MissatgesAPI::error('db_error'),
            [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ],
            500
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }

    /**
     * POST : Alta medicament
     * URL: https://elliot.cat/api/salut/post/medicament
     */
} else if ($slug === "medicament") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
        exit;
    }

    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return $data[$key];
    }

    function optionalField(array $data, string $key)
    {
        return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
            ? $data[$key]
            : null;
    }

    function isUuid($s)
    {
        return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
    }

    $errors = [];

    $medicament        = requireField($data, 'medicament', $errors);
    $dosis             = optionalField($data, 'dosis');
    $quantitatDefecte  = optionalField($data, 'quantitat_defecte');
    $facultatiuId      = optionalField($data, 'facultatiu_id');
    $necessitaRecepta  = array_key_exists('necessita_recepta', $data) ? (bool) $data['necessita_recepta'] : false;

    if ($facultatiuId !== null && !isUuid($facultatiuId)) {
        $errors['facultatiu_id'] = 'invalid_uuid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    $uuid = Ramsey\Uuid\Uuid::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = $uuid->toString();

    $sql = "INSERT INTO " . Tables::DB_SALUT_MEDICAMENTS . " (
                id, medicament, dosis, necessita_recepta, quantitat_defecte, facultatiu_id, created_at, updated_at
            ) VALUES (
                :id, :medicament, :dosis, :necessita_recepta, :quantitat_defecte, :facultatiu_id, NOW(), NOW()
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':medicament', $medicament, PDO::PARAM_STR);
        $stmt->bindValue(':dosis', $dosis, $dosis === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':necessita_recepta', $necessitaRecepta, PDO::PARAM_BOOL);
        $stmt->bindValue(':quantitat_defecte', $quantitatDefecte, $quantitatDefecte === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':facultatiu_id', $facultatiuId === null ? null : App\Utils\Uuid::toBinary($facultatiuId), $facultatiuId === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);

        if ($stmt->execute()) {

            Response::success(
                MissatgesAPI::success('create'),
                [
                    'id'         => $uuidString,
                    'medicament' => $medicament,
                ],
                httpCode: 201
            );
            exit;
        }

        Response::error(
            MissatgesAPI::error('db_error'),
            [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ],
            500
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }

    /**
     * POST : Alta patologia i associacio a medicaments
     * URL: https://elliot.cat/api/salut/post/patologia
     */
} else if ($slug === "patologia") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
        exit;
    }

    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return $data[$key];
    }

    function isUuid($s)
    {
        return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
    }

    $errors = [];

    $patologia = requireField($data, 'patologia', $errors);
    $genere    = requireField($data, 'genere', $errors);

    if ($genere !== null && !in_array($genere, ['f', 'm'], true)) {
        $errors['genere'] = 'invalid_value';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    $uuid = Ramsey\Uuid\Uuid::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = $uuid->toString();

    $sql = "INSERT INTO " . Tables::DB_SALUT_PATOLOGIES . " (
                id, patologia, genere, created_at, updated_at
            ) VALUES (
                :id, :patologia, :genere, NOW(), NOW()
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':patologia', $patologia, PDO::PARAM_STR);
        $stmt->bindValue(':genere', $genere, PDO::PARAM_STR);

        if ($stmt->execute()) {

            // ===============================
            // MEDICAMENTS (RELACIÓ N:M)
            // ===============================

            $medicaments = $data['medicaments'] ?? [];

            if (!is_array($medicaments)) {
                $medicaments = [];
            }

            if (!empty($medicaments)) {

                $sqlMedicament = "
                    INSERT IGNORE INTO " . Tables::DB_SALUT_PATOLOGIES_MEDICAMENTS . "
                    (patologia_id, medicament_id)
                    VALUES
                    (:patologia_id, :medicament_id)
                ";

                $stmtMedicament = $pdo->prepare($sqlMedicament);

                foreach ($medicaments as $medicamentId) {

                    if (!isUuid($medicamentId)) continue;

                    $stmtMedicament->execute([
                        ':patologia_id'  => $uuidBytes,
                        ':medicament_id' => App\Utils\Uuid::toBinary($medicamentId),
                    ]);
                }
            }

            Response::success(
                MissatgesAPI::success('create'),
                [
                    'id'        => $uuidString,
                    'patologia' => $patologia,
                ],
                httpCode: 201
            );
            exit;
        }

        Response::error(
            MissatgesAPI::error('db_error'),
            [
                'sqlState' => $stmt->errorCode(),
                'info' => $stmt->errorInfo(),
            ],
            500
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );
        exit;
    }
} else {
    // Slug no reconocido
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
