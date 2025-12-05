<?php
// cron/agenda_resum_dia.php

use App\Config\Database;
use App\Config\Tables;

try {
    $db  = new Database();
    $pdo = $db->getPdo();
} catch (Throwable $e) {

    exit(1);
}

// Zona horaria (ajusta si hace falta)
$tz = new DateTimeZone('Europe/Madrid'); // o Europe/Rome, según uses
$now = new DateTime('now', $tz);
$today = $now->format('Y-m-d');

$start = $today . ' 00:00:00';
$end   = $today . ' 23:59:59';

// 1) Obtener todos los eventos de hoy con su usuario
$sql = <<<SQL
    SELECT 
        e.id_esdeveniment,
        e.usuari_id,
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
        e.data_inici >= :start
        AND e.data_inici <= :end
        AND e.estat <> 'cancel·lat'
    ORDER BY e.data_inici ASC
SQL;

$query = sprintf(
    $sql,
    qi(Tables::AGENDA_ESDEVENIMENTS, $pdo)
);


// 👉 AQUÍ definimos $params
$params = [
    ':start' => $start,
    ':end'   => $end,
];


try {
    $stmt = $pdo->prepare($query);
    $ok = $stmt->execute($params);

    if (!$ok) {
        $errorInfo = $stmt->errorInfo();
        exit(1);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit(1);
}

// Seguridad extra
if (!is_array($rows)) {

    exit(1);
}

if (empty($rows)) {
    exit(0);
}


// 2) Solo un destinatari (tu) per ara
$YOUR_EMAIL = 'elliot@hispantic.com';
$YOUR_NAME  = 'Elliot';


if (empty($rows)) {

    exit(0);
}

// 3) Enviar email por usuario
$subject = "Agenda del dia $today";

$bodyText = buildAgendaEmailText($YOUR_NAME, $today, $rows);
$bodyHtml = buildAgendaEmailHtml($YOUR_NAME, $today, $rows);

$boundary = uniqid('np');

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "From: Agenda <no-reply@elliot.cat>\r\n";
$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

$message  = "This is a MIME encoded message.\r\n\r\n";
$message .= "--" . $boundary . "\r\n";
$message .= "Content-type: text/plain;charset=utf-8\r\n\r\n";
$message .= $bodyText . "\r\n\r\n";
$message .= "--" . $boundary . "\r\n";
$message .= "Content-type: text/html;charset=utf-8\r\n\r\n";
$message .= $bodyHtml . "\r\n\r\n";
$message .= "--" . $boundary . "--";

$sent = @mail(
    $YOUR_EMAIL,
    "=?UTF-8?B?" . base64_encode($subject) . "?=",
    $message,
    $headers
);

if ($sent) {
} else {
}


/**
 * Construye cuerpo de texto plano
 *
 * @param string $nomUsuari
 * @param string $today
 * @param array<int,array<string,mixed>> $events
 * @return string
 */
function buildAgendaEmailText(string $nomUsuari, string $today, array $events): string
{
    $lines = [];
    $lines[] = "$nomUsuari,";
    $lines[] = "";
    $lines[] = "Aquests són els esdeveniments previstos per avui ($today):";
    $lines[] = "";

    foreach ($events as $ev) {
        $start = new DateTime($ev['data_inici']);
        $end   = new DateTime($ev['data_fi']);

        $horaIni = $start->format('H:i');
        $horaFi  = $end->format('H:i');

        $horaText = $ev['tot_el_dia'] ? 'Tot el dia' : "$horaIni - $horaFi";

        $titol = $ev['titol'];
        $lloc  = $ev['lloc'] ?: '';
        $tipus = $ev['tipus'];

        $line = "- [$horaText] $titol";
        if ($lloc !== '') {
            $line .= " · $lloc";
        }
        $line .= " ($tipus)";

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
 * Construye cuerpo en HTML
 *
 * @param string $nomUsuari
 * @param string $today
 * @param array<int,array<string,mixed>> $events
 * @return string
 */
function buildAgendaEmailHtml(string $nomUsuari, string $today, array $events): string
{
    $rowsHtml = '';

    foreach ($events as $ev) {
        $start = new DateTime($ev['data_inici']);
        $end   = new DateTime($ev['data_fi']);

        $horaIni = $start->format('H:i');
        $horaFi  = $end->format('H:i');
        $horaText = $ev['tot_el_dia'] ? 'Tot el dia' : "$horaIni - $horaFi";

        $titol = htmlspecialchars($ev['titol'] ?? '', ENT_QUOTES, 'UTF-8');
        $lloc  = htmlspecialchars($ev['lloc'] ?? '', ENT_QUOTES, 'UTF-8');
        $tipus = htmlspecialchars($ev['tipus'] ?? '', ENT_QUOTES, 'UTF-8');

        $rowsHtml .= '<tr>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:13px;white-space:nowrap;">' . $horaText . '</td>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:13px;font-weight:600;">' . $titol . '</td>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:12px;color:#4b5563;">' . $lloc . '</td>';
        $rowsHtml .= '<td style="padding:6px 8px;font-size:12px;color:#6b7280;">' . $tipus . '</td>';
        $rowsHtml .= '</tr>';
    }

    $html = <<<HTML
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Agenda del dia $today</title>
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;color:#111827;background:#f3f4f6;padding:16px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;padding:16px 18px 20px;box-shadow:0 10px 25px rgba(15,23,42,0.18);">
        <h1 style="font-size:18px;margin:0 0 8px 0;">Hola {$nomUsuari},</h1>
        <p style="margin:0 0 12px 0;font-size:14px;color:#374151;">
            Aquests són els esdeveniments previstos per avui <strong>($today)</strong>:
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

    return $html;
}
