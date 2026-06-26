<?php

use App\Services\CurriculumPdfService;
use App\Utils\PdfService;
use App\Config\DatabaseConnection;
use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

$db  = new Database();
$pdo = $db->getPdo();

// --- Connexió BD ---
$conn = DatabaseConnection::getConnection();
if (!$conn) {
    Response::error(MissatgesAPI::error('errorBD'), ['Connexió fallida'], 500);
    exit();
}

$id     = (int)($_GET['id'] ?? 1);
$locale = (int)($_GET['locale'] ?? 1);

$curriculum = new CurriculumPdfService();

$data = $curriculum->build($id, $locale);

// ==========================
// Consultes BD - Service
// ==========================
$perfil = $data['perfil'];
$perfilI18n = $data['perfilI18n'];
$links = $data['links'];
$habilitats = $data['habilitats'];
$experiencies = $data['experiencies'];
$educacions = $data['educacions'];

// ==========================
// Helpers
// ==========================

$HEADINGS = [
    'habilitats'  => [1 => 'Stack tecnològic', 2 => 'Skills',                    3 => 'Habilidades',            4 => 'Competenze'],
    'experiencia' => [1 => 'Experiència Professional', 2 => 'Work Experience',   3 => 'Experiencia Profesional', 4 => 'Esperienza Lavorativa'],
    'educacio'    => [1 => 'Educació i certificacions', 2 => 'Education',         3 => 'Educación',               4 => 'Istruzione'],
];

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
    $avatarUrl = $curriculum->imgUrl(
        'usuaris-avatar',
        $perfil['nameImg'],
        'jpg'
    );
    $avatarHtml = '<img src="' . $avatarUrl . '" class="avatar" alt="Foto">';
}

// Links
$linksHtml = '';
foreach ($links as $l) {
    $label = htmlspecialchars($l['label'] ?? '');
    $url   = htmlspecialchars($l['url']   ?? '');
    $icon  = '';
    if (!empty($l['nameImg'])) {
        $iconUrl = $curriculum->imgUrl(
            'web-icones',
            $l['nameImg'],
            'png'
        );
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
        $iconUrl = $curriculum->imgUrl(
            'web-icones',
            $h['nameImg'],
            'png'
        );
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

    $dataIni = $curriculum->fmtDateLocale(
        $exp['data_inici'],
        $locale
    );

    $dataFi = $exp['is_current']
        ? $curriculum->currentLabel($locale)
        : $curriculum->fmtDateLocale(
            $exp['data_fi'],
            $locale
        );

    $periode = htmlspecialchars($dataIni . ' - ' . $dataFi);

    $loc = htmlspecialchars(implode(', ', array_filter([$exp['ciutat'] ?? '', $exp['pais_ca'] ?? ''])));
    if ($loc) $periode .= ' · ' . $loc;

    $logoHtml = '';
    if (!empty($exp['nameImg'])) {
        $logoUrl = $curriculum->imgUrl(
            'logos-empreses',
            $exp['nameImg'],
            'png'
        );
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

    $dataIni = $curriculum->fmtDateLocale(
        $edu['data_inici'],
        $locale
    );

    $dataFi = $curriculum->fmtDateLocale(
        $edu['data_fi'],
        $locale
    );

    $periode = htmlspecialchars($dataIni . ' - ' . $dataFi);

    $logoHtml = '';
    if (!empty($edu['nameImg'])) {
        $logoUrl = $curriculum->imgUrl(
            'logos-empreses',
            $edu['nameImg'],
            'png'
        );

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

// ==========================
// Dompdf
// ==========================
$localeMap = [1 => 'ca', 2 => 'en', 3 => 'es', 4 => 'it'];
$langCode  = $localeMap[$locale] ?? 'ca';

$filename = "cv_elliot_fernandez_{$langCode}.pdf";

ob_start();

require __DIR__ . '/../../Views/pdf/curriculum.php';

$html = ob_get_clean();

$pdf = new PdfService();

$pdf->download($html, $filename);
