<?php

use App\Config\DatabaseConnection;
use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use Dompdf\Dompdf;
use Dompdf\Options;

$db  = new Database();
$pdo = $db->getPdo();

$id     = (int)($_GET['id']     ?? 1);
$locale = (int)($_GET['locale'] ?? 1);

// --- Connexió BD ---
$conn = DatabaseConnection::getConnection();
if (!$conn) {
    Response::error(MissatgesAPI::error('errorBD'), ['Connexió fallida'], 500);
    exit();
}

// ==========================
// Helpers
// ==========================

$HEADINGS = [
    'habilitats'  => [1 => 'Stack tecnològic', 2 => 'Skills',                    3 => 'Habilidades',            4 => 'Competenze'],
    'experiencia' => [1 => 'Experiència Professional', 2 => 'Work Experience',   3 => 'Experiencia Profesional', 4 => 'Esperienza Lavorativa'],
    'educacio'    => [1 => 'Educació i certificacions', 2 => 'Education',         3 => 'Educación',               4 => 'Istruzione'],
];

function imgUrl(string $subdir, string $name, string $ext = 'png'): string
{
    $base = rtrim($_ENV['MEDIA_LOCAL_PATH'] ?? '', '/');
    return $base . '/' . $subdir . '/' . $name . '.' . $ext;
}

function fmtDateLocale(?string $dateStr, int $locale): string
{
    if (!$dateStr) return '';
    $d    = new DateTime($dateStr);
    $langs = [1 => 'ca-ES', 2 => 'en-US', 3 => 'es-ES', 4 => 'it-IT'];
    $lang  = $langs[$locale] ?? 'ca-ES';
    $fmt   = new IntlDateFormatter($lang, IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN, 'LLLL yyyy');
    return ucfirst($fmt->format($d));
}

function currentLabel(int $locale): string
{
    return match ($locale) {
        1 => 'Actualitat',
        2 => 'Present',
        3 => 'Actualidad',
        4 => 'Presente',
        default => 'Present',
    };
}

function idiomasText(int $locale): string
{
    return match ($locale) {
        1 => "• Català i castellà: nivell natiu.\n• Anglès: nivell professional\n• Italià: nivell avançat.",
        2 => "• Catalan and Spanish: native level.\n• English: professional level\n• Italian: advanced level.",
        3 => "• Catalán y castellano: nivel nativo.\n• Inglés: nivel profesional\n• Italiano: nivel avanzado.",
        4 => "• Catalano e spagnolo: livello madrelingua.\n• Inglese: livello professionale\n• Italiano: livello avanzato.",
    };
}

function footerAuthText(int $locale): string
{
    return match ($locale) {
        1 => 'Autorizo el tractament de les meves dades personals d\'acord amb el Reglament europeu de protecció de dades personals.',
        2 => 'I authorize the processing of my personal data in accordance with the European Regulation on the protection of personal data.',
        3 => 'Autorizo el tratamiento de mis datos personales de acuerdo con el Reglamento europeo de protección de datos personales.',
        4 => 'Autorizzo il trattamento dei miei dati personali ai sensi del Decreto Legislativo 30 giugno 2003, n. 196 "Codice in materia di protezione dei dati personali".',
        default => 'I authorize the processing of my personal data in accordance with the European Regulation on the protection of personal data.',
    };
}

function lastUpdateLabel(int $locale): string
{
    return match ($locale) {
        1 => 'Darrera actualització:',
        2 => 'Last updated:',
        3 => 'Última actualización:',
        4 => 'Ultimo aggiornamento:',
        default => 'Last updated:',
    };
}

function idiomasLabel(int $locale): string
{
    return match ($locale) {
        1 => 'Idiomes',
        2 => 'Languages',
        3 => 'Idiomas',
        4 => 'Lingue',
        default => 'Languages',
    };
}

// ==========================
// Consultes BD
// ==========================

$sql = sprintf(
    "
    SELECT c.nom_complet, c.email, c.tel, c.web, ci.ciutat, i.nameImg
    FROM %s c
    LEFT JOIN %s i  ON c.img_perfil          = i.id
    LEFT JOIN %s ci ON c.localitzacio_ciutat  = ci.id
    WHERE c.id = :id LIMIT 1",
    qi(Tables::CURRICULUM_PERFIL, $pdo),
    qi(Tables::DB_IMATGES, $pdo),
    qi(Tables::DB_CIUTATS, $pdo)
);
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = sprintf(
    "
    SELECT titular, sumari
    FROM %s
    WHERE perfil_id = :id AND locale = :locale LIMIT 1",
    qi(Tables::CURRICULUM_PERFIL_I18N, $pdo)
);
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':locale', $locale, PDO::PARAM_INT);
$stmt->execute();
$perfilI18n = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = sprintf(
    "
    SELECT l.label, l.url, i.nameImg
    FROM %s AS l
    LEFT JOIN %s i ON l.icon_id = i.id
    WHERE l.perfil_id = :id AND l.visible = 1
    ORDER BY l.posicio ASC",
    qi(Tables::CURRICULUM_LINKS, $pdo),
    qi(Tables::DB_IMATGES, $pdo)
);
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = sprintf(
    "
    SELECT h.nom, i.nameImg
    FROM %s AS h
    LEFT JOIN %s AS i ON h.imatge_id = i.id
    ORDER BY h.posicio ASC",
    qi(Tables::CURRICULUN_HABILITATS, $pdo),
    qi(Tables::DB_IMATGES, $pdo)
);
$stmt = $conn->query($sql);
$habilitats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = sprintf(
    "
    SELECT e.id, e.empresa, e.empresa_url, e.data_inici, e.data_fi, e.is_current,
           i.nameImg, c.ciutat, co.pais_en AS pais_ca
    FROM %s AS e
    LEFT JOIN %s i  ON e.logo_empresa        = i.id
    LEFT JOIN %s c  ON e.empresa_localitzacio = c.id
    LEFT JOIN %s co ON c.pais_id              = co.id
    ORDER BY e.posicio DESC",
    qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL, $pdo),
    qi(Tables::DB_IMATGES, $pdo),
    qi(Tables::DB_CIUTATS, $pdo),
    qi(Tables::DB_PAISOS, $pdo)
);
$stmt = $conn->query($sql);
$experiencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($experiencies as $index => $exp) {
    $sql = sprintf(
        "
        SELECT rol_titol, sumari, fites
        FROM %s
        WHERE experiencia_id = :id AND locale = :locale LIMIT 1",
        qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL_I18N, $pdo)
    );
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $exp['id'], PDO::PARAM_INT);
    $stmt->bindParam(':locale', $locale, PDO::PARAM_INT);
    $stmt->execute();
    $experiencies[$index]['i18n'] = $stmt->fetch(PDO::FETCH_ASSOC);
}

$sql = sprintf(
    "
    SELECT e.id, e.institucio, e.institucio_url, e.data_inici, e.data_fi,
           (SELECT nameImg   FROM %s WHERE id = e.logo_id                   LIMIT 1) AS nameImg,
           (SELECT ciutat_ca FROM %s WHERE id = e.institucio_localitzacio   LIMIT 1) AS ciutat,
           (SELECT pais_ca   FROM %s WHERE id =
               (SELECT pais_ca FROM %s WHERE id = e.institucio_localitzacio LIMIT 1)
           LIMIT 1) AS pais_ca
    FROM %s e
    ORDER BY e.posicio DESC",
    qi(Tables::DB_IMATGES, $pdo),
    qi(Tables::DB_CIUTATS, $pdo),
    qi(Tables::DB_PAISOS, $pdo),
    qi(Tables::DB_CIUTATS, $pdo),
    qi(Tables::CURRICULUM_EDUCACIO, $pdo)
);
$stmt = $conn->query($sql);
$educacions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($educacions as $index => $edu) {
    $sql = sprintf(
        "
        SELECT grau, notes
        FROM %s
        WHERE educacio_id = :id AND locale = :locale LIMIT 1",
        qi(Tables::CURRICULUM_EDUCACIO_I18N, $pdo)
    );
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $edu['id'], PDO::PARAM_INT);
    $stmt->bindParam(':locale', $locale, PDO::PARAM_INT);
    $stmt->execute();
    $educacions[$index]['i18n'] = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ==========================
// Data formatada
// ==========================

$langs       = [1 => 'ca-ES', 2 => 'en-US', 3 => 'es-ES', 4 => 'it-IT'];
$langCode    = $langs[$locale] ?? 'ca-ES';
$fmtDate     = new IntlDateFormatter($langCode, IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN);
$formattedDate = $fmtDate->format(new DateTime('now'));

// ==========================
// Construcció HTML
// ==========================

$nomComplet = htmlspecialchars($perfil['nom_complet'] ?? '');
$titular    = htmlspecialchars($perfilI18n['titular'] ?? '');
$sumari     = htmlspecialchars($perfilI18n['sumari']  ?? '');
$contacte   = htmlspecialchars(implode(' | ', array_filter([
    $perfil['email']  ?? '',
    $perfil['web']    ?? '',
    $perfil['tel']    ?? '',
    $perfil['ciutat'] ?? '',
])));

// Avatar
$avatarHtml = '';
if (!empty($perfil['nameImg'])) {
    $avatarUrl  = imgUrl('usuaris-avatar', $perfil['nameImg'], 'jpg');
    $avatarHtml = '<img src="' . $avatarUrl . '" class="avatar" alt="Foto">';
}

// Links
$linksHtml = '';
foreach ($links as $l) {
    $label = htmlspecialchars($l['label'] ?? '');
    $url   = htmlspecialchars($l['url']   ?? '');
    $icon  = '';
    if (!empty($l['nameImg'])) {
        $iconUrl = imgUrl('web-icones', $l['nameImg'], 'png');
        $icon    = '<img src="' . $iconUrl . '" class="icon" alt=""> ';
    }
    $linksHtml .= '<a href="' . $url . '" class="link">' . $icon . $label . '</a>';
}

// Habilitats
$habilitatHtml = '';
foreach ($habilitats as $h) {
    $nom  = htmlspecialchars($h['nom'] ?? '');
    $icon = '';
    if (!empty($h['nameImg'])) {
        $iconUrl = imgUrl('web-icones', $h['nameImg'], 'png');
        $icon    = '<img src="' . $iconUrl . '" class="skill-icon" alt="' . $nom . '">';
    }
    $habilitatHtml .= '<div class="skill">' . $icon . '<span>' . $nom . '</span></div>';
}

// Experiències
$expHtml = '';
foreach ($experiencies as $exp) {
    $empresa  = htmlspecialchars($exp['empresa'] ?? '');
    $rol      = htmlspecialchars($exp['i18n']['rol_titol'] ?? '');
    $sumariExp = htmlspecialchars($exp['i18n']['sumari']   ?? '');
    $fites    = $exp['i18n']['fites'] ?? ''; // HTML — no escapar

    $dataIni = fmtDateLocale($exp['data_inici'], $locale);
    $dataFi  = $exp['is_current'] ? currentLabel($locale) : fmtDateLocale($exp['data_fi'], $locale);
    $periode = htmlspecialchars($dataIni . ' - ' . $dataFi);

    $loc = htmlspecialchars(implode(', ', array_filter([$exp['ciutat'] ?? '', $exp['pais_ca'] ?? ''])));
    if ($loc) $periode .= ' · ' . $loc;

    $logoHtml = '';
    if (!empty($exp['nameImg'])) {
        $logoUrl  = imgUrl('logos-empreses', $exp['nameImg'], 'png');
        $logoHtml = '<img src="' . $logoUrl . '" class="logo-empresa" alt="' . $empresa . '">';
    }

    $expHtml .= '
    <div class="block">
        <div class="block-logo">' . $logoHtml . '</div>
        <div class="block-text">
            <div class="block-title">' . $empresa . ' · ' . $rol . '</div>
            <div class="block-periode">' . $periode . '</div>'
        . (!empty($sumariExp) ? '<div class="block-sumari">' . $sumariExp . '</div>' : '')
        . (!empty($fites)     ? '<div class="block-fites">'  . $fites     . '</div>' : '')
        . '</div>
    </div>
    <hr class="divider">';
}

// Educació
$eduHtml = '';
foreach ($educacions as $edu) {
    $institucio = htmlspecialchars($edu['institucio']       ?? '');
    $grau       = htmlspecialchars($edu['i18n']['grau']     ?? '');
    $notes      = htmlspecialchars($edu['i18n']['notes']    ?? '');

    $dataIni = fmtDateLocale($edu['data_inici'], $locale);
    $dataFi  = fmtDateLocale($edu['data_fi'],    $locale);
    $periode = htmlspecialchars($dataIni . ' - ' . $dataFi);

    $logoHtml = '';
    if (!empty($edu['nameImg'])) {
        $logoUrl  = imgUrl('logos-empreses', $edu['nameImg'], 'png');
        $logoHtml = '<img src="' . $logoUrl . '" class="logo-empresa" alt="' . $institucio . '">';
    }

    $eduHtml .= '
    <div class="block">
        <div class="block-logo">' . $logoHtml . '</div>
        <div class="block-text">
            <div class="block-title">' . $institucio . ' · ' . $grau . '</div>
            <div class="block-periode">' . $periode . '</div>'
        . (!empty($notes) ? '<div class="block-sumari">' . $notes . '</div>' : '')
        . '</div>
    </div>
    <hr class="divider">';
}

$html = '<!DOCTYPE html>
<html lang="' . htmlspecialchars($langCode) . '">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 18mm 15mm 18mm 15mm;
    }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        line-height: 1.5;
        color: #111;
        margin: 0;
        padding: 0;
    }

    /* Header */
    .header-table { width: 100%; margin-bottom: 10px; }
    .header-table td { vertical-align: top; padding: 0; border: none; }
    .nom { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
    .titular { font-size: 12px; margin-bottom: 4px; }
    .contacte { font-size: 9px; color: #444; margin-bottom: 6px; }
    .sumari { font-size: 10px; margin-bottom: 8px; }
    .avatar { width: 30mm; height: 30mm; object-fit: cover; border-radius: 4px; }

    /* Links */
    .links { margin-bottom: 10px; }
    .link { font-size: 9px; color: #1a56db; text-decoration: none; margin-right: 12px; }
    .icon { width: 10px; height: 10px; vertical-align: middle; }

    /* Divider */
    .divider { border: none; border-top: 1px solid #ccc; margin: 6px 0; }

    /* Section heading */
    .section-title { font-size: 12px; font-weight: bold; margin: 12px 0 6px 0; }

    /* Habilitats */
    .skills { margin-bottom: 10px; }
    .skill { display: inline-block; margin-right: 10px; margin-bottom: 6px; text-align: center; font-size: 9px; }
    .skill-icon { width: 18px; height: 18px; display: block; margin: 0 auto 2px auto; }

    /* Blocs exp / edu */
    .block { width: 100%; margin-bottom: 6px; }
    .block-logo { width: 14mm; float: left; }
    .block-logo img.logo-empresa { width: 12mm; height: 12mm; object-fit: contain; }
    .block-text { margin-left: 16mm; }
    .block-title { font-size: 11px; font-weight: bold; margin-bottom: 2px; }
    .block-periode { font-size: 9px; font-style: italic; color: #444; margin-bottom: 3px; }
    .block-sumari { font-size: 10px; margin-bottom: 3px; }
    .block-fites { font-size: 10px; }
    .block-fites ul { margin: 2px 0; padding-left: 14px; }
    .block-fites li { margin-bottom: 2px; }

    /* Footer */
    .footer-auth { font-size: 8px; font-style: italic; text-align: center; margin-top: 14px; color: #555; }
    .footer-date { font-size: 8px; font-style: italic; text-align: center; color: #555; margin-top: 4px; }
</style>
</head>
<body>

<!-- Header -->
<table class="header-table">
    <tr>
        <td style="width: 75%;">
            <div class="nom">' . $nomComplet . '</div>
            <div class="titular">' . $titular . '</div>
            <div class="contacte">' . $contacte . '</div>
            ' . (!empty($sumari) ? '<div class="sumari">' . $sumari . '</div>' : '') . '
        </td>
        <td style="width: 25%; text-align: right;">' . $avatarHtml . '</td>
    </tr>
</table>

<!-- Links -->
<div class="links">' . $linksHtml . '</div>

<hr class="divider">

<!-- Habilitats -->
<div class="section-title">' . htmlspecialchars($HEADINGS['habilitats'][$locale] ?? 'Skills') . '</div>
<div class="skills">' . $habilitatHtml . '</div>

<hr class="divider">

<!-- Experiència -->
<div class="section-title">' . htmlspecialchars($HEADINGS['experiencia'][$locale] ?? 'Work Experience') . '</div>
' . $expHtml . '

<!-- Educació -->
<div class="section-title">' . htmlspecialchars($HEADINGS['educacio'][$locale] ?? 'Education') . '</div>
' . $eduHtml . '

<!-- Idiomes -->
<div class="section-title">' . htmlspecialchars(idiomasLabel($locale)) . '</div>
<div style="font-size:10px; white-space: pre-line;">' . htmlspecialchars(idiomasText($locale)) . '</div>

<hr class="divider">

<!-- Autorització -->
<div class="footer-auth">' . htmlspecialchars(footerAuthText($locale)) . '</div>
<div class="footer-date">' . htmlspecialchars(lastUpdateLabel($locale)) . ' ' . htmlspecialchars($formattedDate) . '</div>

</body>
</html>';

// ==========================
// Dompdf
// ==========================
$chroot = rtrim($_ENV['MEDIA_CHROOT'] ?? '/', '/');

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('chroot', $chroot);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ==========================
// Output
// ==========================

$localeMap = [1 => 'ca', 2 => 'en', 3 => 'es', 4 => 'it'];
$langCode  = $localeMap[$locale] ?? 'ca';
$filename  = "cv_elliot_fernandez_{$langCode}.pdf";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($dompdf->output()));
echo $dompdf->output();
exit;
