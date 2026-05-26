<?php
// URL: https://elliot.cat/api/cron/agenda-dia

declare(strict_types=1);

use App\Config\Database;
use App\Utils\Tables;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

// ======================================================
// CONFIG
// ======================================================

$TZ_NAME = 'Europe/Madrid';

date_default_timezone_set($TZ_NAME);
$tz = new DateTimeZone($TZ_NAME);

// DEBUG TEMPORAL
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ======================================================
// AUTOLOAD
// ======================================================

require_once __DIR__ . '../../../../../vendor/autoload.php';

// ======================================================
// PHPMailer
// ======================================================

require_once __DIR__ . '../../../../../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '../../../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '../../../../../vendor/phpmailer/phpmailer/src/SMTP.php';

// ======================================================
// ENV
// ======================================================

$brevoApi = (string)($_ENV['BREVO_API'] ?? '');

if ($brevoApi === '') {
    error_log('[agenda_resum_dia] BREVO_API vacío');
    exit(1);
}

// ======================================================
// DB
// ======================================================

try {
    $db  = new Database();
    $pdo = $db->getPdo();

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    error_log('[agenda_resum_dia] DB error: ' . $e->getMessage());
    exit(1);
}

// ======================================================
// FECHAS
// ======================================================

$now   = new DateTime('now', $tz);
$today = $now->format('Y-m-d');

$start = $today . ' 00:00:00';

$end = (new DateTime($today, $tz))
    ->modify('+1 day')
    ->format('Y-m-d') . ' 00:00:00';

error_log('[agenda_resum_dia] START');
error_log('[agenda_resum_dia] TODAY=' . $today);

// ======================================================
// EVENTOS
// ======================================================

try {

    $sql = <<<SQL
SELECT
    e.id,
    e.titol,
    e.descripcio,
    e.tipus,
    e.lloc,
    e.data_inici,
    e.data_fi,
    e.tot_el_dia,
    e.estat
FROM %s AS e
WHERE
    e.data_inici < :end
    AND (
        e.data_fi IS NULL
        OR e.data_fi >= :start
    )
    AND e.estat <> 'cancel·lat'
ORDER BY e.data_inici ASC
SQL;

    $query = sprintf(
        $sql,
        qi(Tables::AGENDA_ESDEVENIMENTS, $pdo)
    );

    $stmt = $pdo->prepare($query);

    $stmt->execute([
        ':start' => $start,
        ':end'   => $end
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log('[agenda_resum_dia] EVENTS=' . count($rows));

    // ======================================================
    // CUMPLEAÑOS
    // ======================================================

    $todayObj   = new DateTime($today, $tz);

    $todayMonth = (int)$todayObj->format('m');
    $todayDay   = (int)$todayObj->format('d');

    $todayYear = (int)$todayObj->format('Y');

    $isLeap =
        ($todayYear % 4 === 0)
        && (
            $todayYear % 100 !== 0
            || $todayYear % 400 === 0
        );

    $isFeb28NonLeap =
        $todayMonth === 2
        && $todayDay === 28
        && !$isLeap;

    $sqlB = "SELECT
    (-c.id) AS id_esdeveniment,
    CONCAT('🎂 ', c.nom, ' ', c.cognoms) AS titol,
    NULL AS descripcio,
    'aniversari' AS tipus,
    NULL AS lloc,
    CONCAT(:todayStart, ' 00:00:00') AS data_inici,
    CONCAT(:todayEnd, ' 23:59:59') AS data_fi,
    1 AS tot_el_dia,
    'confirmat' AS estat
        FROM db_contactes c
        WHERE c.data_naixement IS NOT NULL
        AND (
            (
                MONTH(c.data_naixement) = :month
                AND DAY(c.data_naixement) = :day
            )
        ";

    // 29 febrero → celebrar el 28 en años no bisiestos
    if ($isFeb28NonLeap) {

        $sqlB .= "
    OR (
        MONTH(c.data_naixement) = 2
        AND DAY(c.data_naixement) = 29
    )
";
    }

    $sqlB .= "
)
ORDER BY c.nom ASC, c.cognoms ASC
";

    $stmtB = $pdo->prepare($sqlB);

    $stmtB->execute([
        ':todayStart' => $today,
        ':todayEnd'   => $today,
        ':month'      => $todayMonth,
        ':day'        => $todayDay
    ]);

    $birthdays = $stmtB->fetchAll(PDO::FETCH_ASSOC);

    error_log('[agenda_resum_dia] BIRTHDAYS=' . count($birthdays));
} catch (Throwable $e) {

    error_log('[agenda_resum_dia] SQL ERROR: ' . $e->getMessage());

    exit(1);
}

// ======================================================
// MERGE
// ======================================================

$rows       = is_array($rows) ? $rows : [];
$birthdays  = is_array($birthdays) ? $birthdays : [];

$all = array_merge($rows, $birthdays);

// ======================================================
// SIN EVENTOS
// ======================================================

if (empty($all)) {

    error_log('[agenda_resum_dia] NO EVENTS');

    exit(0);
}

// ======================================================
// SORT
// ======================================================

usort($all, function ($a, $b) {

    $ta = (int)($a['tot_el_dia'] ?? 0);
    $tb = (int)($b['tot_el_dia'] ?? 0);

    if ($ta !== $tb) {
        return $tb <=> $ta;
    }

    $da = (string)($a['data_inici'] ?? '');
    $db = (string)($b['data_inici'] ?? '');

    if ($da !== $db) {
        return strcmp($da, $db);
    }

    return strcmp(
        (string)($a['titol'] ?? ''),
        (string)($b['titol'] ?? '')
    );
});

$rows = $all;

// ======================================================
// DESTINATARIO
// ======================================================

$YOUR_EMAIL = 'elliot@hispantic.com';
$YOUR_NAME  = 'Elliot Fernandez';

// ======================================================
// EMAIL
// ======================================================

$subject  = "Agenda del dia {$today}";

$bodyText = buildAgendaEmailText(
    $YOUR_NAME,
    $today,
    $rows,
    $tz,
    $start,
    $end
);

$bodyHtml = buildAgendaEmailHtml(
    $YOUR_NAME,
    $today,
    $rows,
    $tz,
    $start,
    $end
);

// ======================================================
// SMTP
// ======================================================

try {

    error_log('[agenda_resum_dia] ABOUT TO SEND');

    $mail = new PHPMailer(true);

    $mail->CharSet = 'UTF-8';

    $mail->isSMTP();

    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '7a0605001@smtp-brevo.com';
    $mail->Password   = $brevoApi;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // DEBUG SMTP
    $mail->SMTPDebug = 0;

    $mail->setFrom(
        'elliot@hispantic.com',
        'Agenda'
    );

    $mail->addAddress(
        $YOUR_EMAIL,
        $YOUR_NAME
    );

    $mail->addReplyTo(
        $YOUR_EMAIL,
        $YOUR_NAME
    );

    $mail->Subject = $subject;

    $mail->Body    = $bodyHtml;
    $mail->AltBody = $bodyText;

    $mail->isHTML(true);

    $mail->send();

    error_log('[agenda_resum_dia] MAIL SENT OK');

    exit(0);
} catch (MailException $e) {

    error_log('[agenda_resum_dia] MAIL ERROR: ' . $e->getMessage());

    exit(1);
} catch (Throwable $e) {

    error_log('[agenda_resum_dia] UNKNOWN MAIL ERROR: ' . $e->getMessage());

    exit(1);
}

// ======================================================
// FUNCTIONS
// ======================================================

function buildAgendaEmailText(
    string $nomUsuari,
    string $today,
    array $events,
    DateTimeZone $tz,
    string $startDay,
    string $endDay
): string {

    $dayStart = new DateTime($startDay, $tz);
    $dayEnd   = new DateTime($endDay, $tz);

    $lines   = [];

    $lines[] = "{$nomUsuari},";
    $lines[] = "";
    $lines[] = "Aquests són els esdeveniments previstos per avui ({$today}):";
    $lines[] = "";

    foreach ($events as $ev) {

        $start = new DateTime((string)($ev['data_inici'] ?? ''), $tz);
        $end   = new DateTime((string)($ev['data_fi'] ?? ''), $tz);

        $totElDia = (int)($ev['tot_el_dia'] ?? 0) === 1;

        if ($totElDia) {

            $horaText = 'Tot el dia';
        } else {

            $horaIni = $start->format('H:i');
            $horaFi  = $end->format('H:i');

            $horaText = "{$horaIni} - {$horaFi}";
        }

        $titol = (string)($ev['titol'] ?? '');
        $lloc  = (string)($ev['lloc'] ?? '');
        $tipus = (string)($ev['tipus'] ?? '');

        $line = "- [{$horaText}] {$titol}";

        if ($lloc !== '') {
            $line .= " · {$lloc}";
        }

        if ($tipus !== '') {
            $line .= " ({$tipus})";
        }

        $lines[] = $line;
    }

    $lines[] = "";
    $lines[] = "Que tinguis un bon dia!";

    return implode("\n", $lines);
}

function buildAgendaEmailHtml(
    string $nomUsuari,
    string $today,
    array $events,
    DateTimeZone $tz,
    string $startDay,
    string $endDay
): string {

    $rowsHtml = '';

    foreach ($events as $ev) {

        $start = new DateTime((string)($ev['data_inici'] ?? ''), $tz);
        $end   = new DateTime((string)($ev['data_fi'] ?? ''), $tz);

        $totElDia = (int)($ev['tot_el_dia'] ?? 0) === 1;

        if ($totElDia) {

            $horaText = 'Tot el dia';
        } else {

            $horaText =
                $start->format('H:i')
                . ' - '
                . $end->format('H:i');
        }

        $titol = htmlspecialchars((string)($ev['titol'] ?? ''), ENT_QUOTES, 'UTF-8');
        $lloc  = htmlspecialchars((string)($ev['lloc'] ?? ''), ENT_QUOTES, 'UTF-8');
        $tipus = htmlspecialchars((string)($ev['tipus'] ?? ''), ENT_QUOTES, 'UTF-8');
        $hora  = htmlspecialchars($horaText, ENT_QUOTES, 'UTF-8');

        $rowsHtml .= '
<tr>
<td style="padding:6px 8px;">' . $hora . '</td>
<td style="padding:6px 8px;font-weight:600;">' . $titol . '</td>
<td style="padding:6px 8px;">' . $lloc . '</td>
<td style="padding:6px 8px;">' . $tipus . '</td>
</tr>
';
    }

    return '
<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<title>Agenda</title>
</head>
<body style="font-family:Arial,sans-serif;">

<h2>Agenda del dia ' . htmlspecialchars($today, ENT_QUOTES, 'UTF-8') . '</h2>

<table width="100%" cellspacing="0" cellpadding="0" border="1" style="border-collapse:collapse;">
<thead>
<tr>
<th align="left">Hora</th>
<th align="left">Esdeveniment</th>
<th align="left">Lloc</th>
<th align="left">Tipus</th>
</tr>
</thead>
<tbody>
' . $rowsHtml . '
</tbody>
</table>

</body>
</html>
';
}
