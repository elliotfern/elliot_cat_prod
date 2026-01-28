<?php
// cron/agenda_resum_dia.php

declare(strict_types=1);

use App\Config\Database;
use App\Config\Tables;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * LOG: escribe siempre en error_log del servidor.
 * En cron suele acabar en el error_log principal del hosting o en el del directorio.
 */
function cron_log(string $msg, array $ctx = []): void
{
    $prefix = '[agenda_resum_dia] ';
    if ($ctx) {
        $msg .= ' | ' . json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    error_log($prefix . $msg);
}

/**
 * Captura fatal errors y excepciones no controladas y las manda a error_log.
 */
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    cron_log('PHP error', ['severity' => $severity, 'message' => $message, 'file' => $file, 'line' => $line]);
    return false; // deja que PHP también lo gestione
});

set_exception_handler(static function (Throwable $e): void {
    cron_log('Uncaught exception', [
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    exit(1);
});

register_shutdown_function(static function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        cron_log('Fatal shutdown error', $err);
    }
});

// 0) Zona horaria coherente en TODO el script
$TZ_NAME = 'Europe/Madrid';
date_default_timezone_set($TZ_NAME);
$tz = new DateTimeZone($TZ_NAME);

// 0.1) Asegura que PHPMailer (Composer) se carga
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    cron_log('Missing composer autoload', ['path' => $autoload]);
    exit(1);
}
require_once $autoload;

// 1) Cargar secret Brevo (mejor loguear si está vacío)
$brevoApi = (string)($_ENV['BREVO_API'] ?? '');
// Alternativa robusta por si $_ENV no está poblado en cron:
// $brevoApi = $brevoApi !== '' ? $brevoApi : (getenv('BREVO_API') ?: '');

if ($brevoApi === '') {
    cron_log('BREVO_API vacío/no definido (no se puede enviar por SMTP)');
    exit(1);
}

try {
    $db  = new Database();
    $pdo = $db->getPdo();
} catch (Throwable $e) {
    cron_log('DB connection failed', [
        'type' => get_class($e),
        'message' => $e->getMessage(),
    ]);
    exit(1);
}

// 2) Rango del día (intervalo semiabierto [start, end))
$now   = new DateTime('now', $tz);
$today = $now->format('Y-m-d');

$start = $today . ' 00:00:00';
$end   = (new DateTime($today, $tz))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

cron_log('Run', ['today' => $today, 'start' => $start, 'end' => $end]);

// 3) Eventos que SOLAPAN con el día
$sql = <<<SQL
SELECT
    e.id_esdeveniment,
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
    e.data_inici <  :end
    AND e.data_fi    >= :start
    AND e.estat <> 'cancel·lat'
ORDER BY e.data_inici ASC
SQL;

$query = sprintf($sql, qi(Tables::AGENDA_ESDEVENIMENTS, $pdo));

$params = [
    ':start' => $start,
    ':end'   => $end,
];

try {
    $stmt = $pdo->prepare($query);

    if (!$stmt->execute($params)) {
        $err = $stmt->errorInfo();
        cron_log('SQL execute failed', ['errorInfo' => $err]);
        exit(1);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    cron_log('SQL exception', [
        'type' => get_class($e),
        'message' => $e->getMessage(),
        // 'query' => $query, // si quieres, descomenta para ver la query
    ]);
    exit(1);
}

if (!is_array($rows)) {
    cron_log('Fetch returned non-array');
    exit(1);
}

cron_log('Events fetched', ['count' => count($rows)]);

if (empty($rows)) {
    cron_log('No events today -> no email sent');
    exit(0);
}

// 4) Destinatario
$YOUR_EMAIL = 'elliot@hispantic.com';
$YOUR_NAME  = 'Elliot Fernandez';

// 5) Construir email
$subject  = "Agenda del dia $today";
$bodyText = buildAgendaEmailText($YOUR_NAME, $today, $rows, $tz, $start, $end);
$bodyHtml = buildAgendaEmailHtml($YOUR_NAME, $today, $rows, $tz, $start, $end);

// 6) Enviar con Brevo + PHPMailer (SMTP)
try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '7a0605001@smtp-brevo.com';
    $mail->Password   = $brevoApi;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Log SMTP en error_log (sin mostrarlo en web)
    $mail->SMTPDebug  = 2; // 0 en producción cuando ya funcione
    $mail->Debugoutput = static function (string $str, int $level) {
        cron_log("SMTP[$level] $str");
    };

    // From / To
    $mail->setFrom('elliot@hispantic.com', 'Agenda');
    $mail->addAddress($YOUR_EMAIL, $YOUR_NAME);
    $mail->addReplyTo($YOUR_EMAIL, $YOUR_NAME);

    // Contenido
    $mail->Subject = $subject;
    $mail->Body    = $bodyHtml;
    $mail->AltBody = $bodyText;
    $mail->isHTML(true);

    $mail->send();
    cron_log('Email sent', ['to' => $YOUR_EMAIL, 'subject' => $subject]);
    exit(0);
} catch (MailException $e) {
    cron_log('Mailer error', [
        'type' => get_class($e),
        'message' => $e->getMessage(),
    ]);
    exit(1);
} catch (Throwable $e) {
    cron_log('Unknown mail error', [
        'type' => get_class($e),
        'message' => $e->getMessage(),
    ]);
    exit(1);
}


/**
 * Texto plano
 *
 * @param array<int,array<string,mixed>> $events
 */
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

            $startsBeforeDay = $start < $dayStart;
            $endsAfterDay    = $end > $dayEnd;

            if ($startsBeforeDay && !$endsAfterDay) {
                $horaText = "↦ fins {$horaFi}";
            } elseif (!$startsBeforeDay && $endsAfterDay) {
                $horaText = "{$horaIni} ↦";
            } elseif ($startsBeforeDay && $endsAfterDay) {
                $horaText = "↦ (solapa tot el dia)";
            } else {
                $horaText = "{$horaIni} - {$horaFi}";
            }
        }

        $titol = (string)($ev['titol'] ?? '');
        $lloc  = (string)($ev['lloc'] ?? '');
        $tipus = (string)($ev['tipus'] ?? '');

        $line = "- [{$horaText}] {$titol}";
        if (trim($lloc) !== '') {
            $line .= " · {$lloc}";
        }
        if (trim($tipus) !== '') {
            $line .= " ({$tipus})";
        }
        $lines[] = $line;
    }

    $lines[] = "";
    $lines[] = "Que tinguis un bon dia!";
    $lines[] = "";
    $lines[] = "--";
    $lines[] = "Recordatori automàtic de l'agenda";

    return implode("\n", $lines);
}

/**
 * HTML
 *
 * @param array<int,array<string,mixed>> $events
 */
function buildAgendaEmailHtml(
    string $nomUsuari,
    string $today,
    array $events,
    DateTimeZone $tz,
    string $startDay,
    string $endDay
): string {
    $dayStart = new DateTime($startDay, $tz);
    $dayEnd   = new DateTime($endDay, $tz);

    $rowsHtml = '';

    foreach ($events as $ev) {
        $start = new DateTime((string)($ev['data_inici'] ?? ''), $tz);
        $end   = new DateTime((string)($ev['data_fi'] ?? ''), $tz);

        $totElDia = (int)($ev['tot_el_dia'] ?? 0) === 1;

        if ($totElDia) {
            $horaText = 'Tot el dia';
        } else {
            $horaIni = $start->format('H:i');
            $horaFi  = $end->format('H:i');

            $startsBeforeDay = $start < $dayStart;
            $endsAfterDay    = $end > $dayEnd;

            if ($startsBeforeDay && !$endsAfterDay) {
                $horaText = "↦ fins {$horaFi}";
            } elseif (!$startsBeforeDay && $endsAfterDay) {
                $horaText = "{$horaIni} ↦";
            } elseif ($startsBeforeDay && $endsAfterDay) {
                $horaText = "↦ (solapa tot el dia)";
            } else {
                $horaText = "{$horaIni} - {$horaFi}";
            }
        }

        $titol = htmlspecialchars((string)($ev['titol'] ?? ''), ENT_QUOTES, 'UTF-8');
        $lloc  = htmlspecialchars((string)($ev['lloc'] ?? ''), ENT_QUOTES, 'UTF-8');
        $tipus = htmlspecialchars((string)($ev['tipus'] ?? ''), ENT_QUOTES, 'UTF-8');
        $hora  = htmlspecialchars($horaText, ENT_QUOTES, 'UTF-8');

        $rowsHtml .= '<tr>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:13px;white-space:nowrap;">' . $hora . '</td>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:13px;font-weight:600;">' . $titol . '</td>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:12px;color:#4b5563;">' . $lloc . '</td>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:12px;color:#6b7280;">' . $tipus . '</td>';
        $rowsHtml .= '</tr>';
    }

    $nomUsuariEsc = htmlspecialchars($nomUsuari, ENT_QUOTES, 'UTF-8');
    $todayEsc     = htmlspecialchars($today, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Agenda del dia {$todayEsc}</title>
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;color:#111827;background:#f3f4f6;padding:16px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;padding:16px 18px 20px;box-shadow:0 10px 25px rgba(15,23,42,0.18);">
        <h1 style="font-size:18px;margin:0 0 8px 0;">Hola {$nomUsuariEsc},</h1>
        <p style="margin:0 0 12px 0;font-size:14px;color:#374151;">
            Aquests són els esdeveniments previstos per avui <strong>({$todayEsc})</strong>:
        </p>

        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:8px;">
            <thead>
                <tr>
                    <th align="left" style="font-size:12px;color:#6b7280;padding:4px 8px;border-bottom:1px solid #e5e7eb;">Hora</th>
                    <th align="left" style="font-size:12px;color:#6b7280;padding:4px 8px;border-bottom:1px solid #e5e7eb;">Esdeveniment</th>
                    <th align="left" style="font-size:12px;color:#6b7280;padding:4px 8px;border-bottom:1px solid #e5e7eb;">Lloc</th>
                    <th align="left" style="font-size:12px;color:#6b7280;padding:4px 8px;border-bottom:1px solid #e5e7eb;">Tipus</th>
                </tr>
            </thead>
            <tbody>
                {$rowsHtml}
            </tbody>
        </table>

        <p style="margin-top:16px;font-size:13px;color:#6b7280;">
            Que tinguis un bon dia!<br>
            <span style="font-size:12px;color:#9ca3af;">Recordatori automàtic de l'agenda</span>
        </p>
    </div>
</body>
</html>
HTML;
}
