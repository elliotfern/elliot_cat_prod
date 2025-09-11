<?php

use App\Config\DatabaseConnection;
use App\Utils\Response;
use App\Utils\MissatgesAPI;

// --- Clase extendida para footer ---
class CVPDF extends TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Pàgina ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$id = (int)($_GET['id'] ?? 1);
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
    'habilitats'  => [1 => 'Stack tecnològic', 2 => 'Skills', 3 => 'Habilidades', 4 => 'Competenze'],
    'experiencia' => [1 => 'Experiència Professional', 2 => 'Work Experience', 3 => 'Experiencia Profesional', 4 => 'Esperienza Lavorativa'],
    'educacio'    => [1 => 'Educació i certificacions', 2 => 'Education', 3 => 'Educación', 4 => 'Istruzione'],
];

function fmtDateLocale(?string $dateStr, int $locale): string
{
    if (!$dateStr) return '';
    $d = new DateTime($dateStr);
    $langs = [
        1 => 'ca-ES',
        2 => 'en-US',
        3 => 'es-ES',
        4 => 'it-IT'
    ];
    $lang = $langs[$locale] ?? 'ca-ES';
    $formatter = new IntlDateFormatter(
        $lang,
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        'UTC',
        IntlDateFormatter::GREGORIAN,
        'LLLL yyyy'
    );
    return ucfirst($formatter->format($d));
}

function currentLabel(int $locale): string
{
    return match ($locale) {
        1 => 'Actualitat',
        2 => 'Present',
        3 => 'Actualidad',
        4 => 'Presente',
        default => 'Present'
    };
}

function hrLine($pdf)
{
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);
}

// ==========================
// Consultes BD
// ==========================

// Perfil
$sqlPerfil = "SELECT c.nom_complet, c.email, c.tel, c.web, ci.city, i.nameImg
              FROM db_curriculum_perfil c
              LEFT JOIN db_img i ON c.img_perfil = i.id
              LEFT JOIN db_cities ci ON c.localitzacio_ciutat = ci.id
              WHERE c.id = :id LIMIT 1";
$stmt = $conn->prepare($sqlPerfil);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

// Perfil traduït
$sqlPerfilI18n = "SELECT titular, sumari FROM db_curriculum_perfil_i18n WHERE perfil_id = :id AND locale = :locale LIMIT 1";
$stmt = $conn->prepare($sqlPerfilI18n);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':locale', $locale, PDO::PARAM_INT);
$stmt->execute();
$perfilI18n = $stmt->fetch(PDO::FETCH_ASSOC);

// Links
$sqlLinks = "SELECT l.label, l.url, i.nameImg
             FROM db_curriculum_links AS l
             LEFT JOIN db_img i ON l.icon_id = i.id
             WHERE l.perfil_id = :id AND l.visible = 1 ORDER BY l.posicio ASC";
$stmt = $conn->prepare($sqlLinks);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Habilitats
$sqlHabilitats = "SELECT h.nom, i.nameImg
                  FROM db_curriculum_habilitats h
                  LEFT JOIN db_img i ON h.imatge_id = i.id
                  ORDER BY h.posicio ASC";
$stmt = $conn->query($sqlHabilitats);
$habilitats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Experiència professional
$sqlExp = "SELECT e.id, e.empresa, e.empresa_url, e.data_inici, e.data_fi, e.is_current, i.nameImg, c.city, co.pais_cat
           FROM db_curriculum_experiencia_professional e
           INNER JOIN db_img i ON e.logo_empresa = i.id
           INNER JOIN db_cities c ON e.empresa_localitzacio = c.id
           INNER JOIN db_countries co ON c.country = co.id
           ORDER BY e.posicio DESC";
$stmt = $conn->query($sqlExp);
$experiencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($experiencies as $index => $exp) {
    $sqlExpI18n = "SELECT rol_titol, sumari, fites
                   FROM db_curriculum_experiencia_professional_i18n
                   WHERE experiencia_id = :id AND locale = :locale
                   LIMIT 1";
    $stmt = $conn->prepare($sqlExpI18n);
    $stmt->bindParam(':id', $exp['id'], PDO::PARAM_INT);
    $stmt->bindParam(':locale', $locale, PDO::PARAM_INT);
    $stmt->execute();
    $experiencies[$index]['i18n'] = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Educació
$sqlEdu = "SELECT e.id, e.institucio, e.institucio_url, e.data_inici, e.data_fi,
                  (SELECT nameImg FROM db_img WHERE id = e.logo_id LIMIT 1) AS nameImg,
                  (SELECT city FROM db_cities WHERE id = e.institucio_localitzacio LIMIT 1) AS city,
                  (SELECT pais_cat FROM db_countries WHERE id = 
                       (SELECT country FROM db_cities WHERE id = e.institucio_localitzacio LIMIT 1)
                   LIMIT 1) AS pais_cat
           FROM db_curriculum_educacio e
           ORDER BY e.posicio DESC";

$stmt = $conn->query($sqlEdu);
$educacions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($educacions as $index => $edu) {
    $sqlEduI18n = "SELECT grau, notes 
                   FROM db_curriculum_educacio_i18n 
                   WHERE educacio_id = :id AND locale = :locale 
                   LIMIT 1";
    $stmt = $conn->prepare($sqlEduI18n);
    $stmt->bindParam(':id', $edu['id'], PDO::PARAM_INT);
    $stmt->bindParam(':locale', $locale, PDO::PARAM_INT);
    $stmt->execute();

    $educacions[$index]['i18n'] = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ==========================
// Construcció del PDF
// ==========================

$pdf = new CVPDF();
$pdf->SetCreator('Curriculum Generator');
$pdf->SetAuthor($perfil['nom_complet']);
$pdf->SetTitle('Curriculum Vitae');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// --- Header ---
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(120, 10, $perfil['nom_complet'], 0, 0, 'L');
if (!empty($perfil['nameImg'])) {
    $fotoUrl = IMG_ROOT . "/usuaris-avatar/{$perfil['nameImg']}.jpg";
    $pdf->Image($fotoUrl, 150, 20, 30, 30, 'JPG');
}
$pdf->Ln(20);

$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(120, 8, $perfilI18n['titular'] ?? '', 0, 'L');

$pdf->SetFont('helvetica', '', 10);
$contacte = "{$perfil['email']} | {$perfil['web']} | {$perfil['tel']} | {$perfil['city']}";
$pdf->MultiCell(120, 6, $contacte, 0, 'L');
$pdf->Ln(5);

if (!empty($perfilI18n['sumari'])) {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $perfilI18n['sumari'], 0, 'L');
}
$pdf->Ln(8);

// --- Links ---
foreach ($links as $l) {
    if (!empty($l['nameImg'])) {
        $iconUrl = IMG_ROOT . "/web-icones/{$l['nameImg']}.png";
        $pdf->Image($iconUrl, $pdf->GetX(), $pdf->GetY(), 5, 5, 'PNG');
        $pdf->SetX($pdf->GetX() + 7);
    }
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Write(6, $l['label'], $l['url']);
    $pdf->SetX($pdf->GetX() + 10);
}
$pdf->Ln(12);

hrLine($pdf);

// --- Habilitats ---
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, $HEADINGS['habilitats'][$locale] ?? 'Skills', 0, 1);
$pdf->Ln(3);

foreach ($habilitats as $h) {
    if (!empty($h['nameImg'])) {
        $icon = IMG_ROOT . "/web-icones/{$h['nameImg']}.png";
        if (is_file($icon)) {
            $pdf->Image($icon, $pdf->GetX(), $pdf->GetY(), 13, 4, 'PNG');
            $pdf->SetX($pdf->GetX() + 16);
        }
    }
}
$pdf->Ln(20);

hrLine($pdf);

// --- Experiència ---
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, $HEADINGS['experiencia'][$locale] ?? 'Work Experience', 0, 1);
$pdf->Ln(3);

foreach ($experiencies as $exp) {
    $xStart     = $pdf->GetX();
    $yStart     = $pdf->GetY();
    $textX      = $xStart + 16;
    $logoHeight = 12; // altura mínima del logo

    // --- Calcular altura necesaria ---
    $blockText  = $exp['empresa'] . " · " . ($exp['i18n']['rol_titol'] ?? '') . "\n";
    $periode    = fmtDateLocale($exp['data_inici'], $locale) . " - " .
        ($exp['is_current'] ? currentLabel($locale) : fmtDateLocale($exp['data_fi'], $locale));
    $loc        = implode(', ', array_filter([$exp['city'], $exp['pais_cat']]));
    $periodeLoc = $periode . ($loc ? " · " . $loc : "");
    $blockText .= $periodeLoc . "\n";

    if (!empty($exp['i18n']['sumari'])) {
        $blockText .= $exp['i18n']['sumari'] . "\n";
    }
    if (!empty($exp['i18n']['fites'])) {
        // strip_tags para estimar altura, el HTML real se escribe después
        $blockText .= strip_tags($exp['i18n']['fites']);
    }

    $textHeight = $pdf->getStringHeight(180, $blockText, false, true, '', 1);
    $neededHeight = max($textHeight, $logoHeight) + 5;

    // --- Si no cabe en la página, salto ---
    if ($yStart + $neededHeight > ($pdf->getPageHeight() - $pdf->getBreakMargin())) {
        $pdf->AddPage();
        $yStart = $pdf->GetY();
    }

    // --- Render real ---
    if (!empty($exp['nameImg'])) {
        $logoUrl = IMG_ROOT . "/logos-empreses/{$exp['nameImg']}.png";
        if (is_file($logoUrl)) {
            $pdf->Image($logoUrl, $xStart, $yStart, 12, 12, 'PNG');
        }
    }

    $pdf->SetXY($textX, $yStart);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->MultiCell(0, 6, $exp['empresa'] . " · " . ($exp['i18n']['rol_titol'] ?? ''), 0, 'L');

    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetX($textX);
    $pdf->MultiCell(0, 5, $periodeLoc, 0, 'L');

    if (!empty($exp['i18n']['sumari'])) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetX($textX);
        $pdf->MultiCell(0, 5, $exp['i18n']['sumari'], 0, 'L');
        $pdf->Ln(2); // 🔹 Espacio extra después del sumari
    }

    if (!empty($exp['i18n']['fites'])) {
        $pdf->SetX($textX);
        $pdf->writeHTMLCell(0, 0, $textX, $pdf->GetY(), $exp['i18n']['fites'], 0, 1, 0, true, 'L', true);
    }


    $pdf->Ln(5);
    hrLine($pdf);
}


// --- Educació ---
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, $HEADINGS['educacio'][$locale] ?? 'Education', 0, 1);
$pdf->Ln(3);

foreach ($educacions as $edu) {
    $periode = fmtDateLocale($edu['data_inici'], $locale) . " - " . fmtDateLocale($edu['data_fi'], $locale);

    $xStart = $pdf->GetX();
    $yStart = $pdf->GetY();

    $logoHeight = 0;
    if (!empty($edu['nameImg'])) {
        $logoUrl = IMG_ROOT . "/logos-empreses/{$edu['nameImg']}.png";
        if (is_file($logoUrl)) {
            $pdf->Image($logoUrl, $xStart, $yStart, 12, 12, 'PNG');
            $logoHeight = 12;
        }
    }

    // Texto a la derecha del logo
    $pdf->SetXY($xStart + 16, $yStart);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->MultiCell(0, 6, $edu['institucio'] . " · " . ($edu['i18n']['grau'] ?? ''), 0, 'L');

    // Fecha justo debajo del título
    $pdf->SetX($xStart + 16);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->MultiCell(0, 5, $periode, 0, 'L');

    // Notas
    $pdf->SetFont('helvetica', '', 10);
    if (!empty($edu['i18n']['notes'])) {
        $pdf->SetX($xStart + 16);
        $pdf->MultiCell(0, 5, $edu['i18n']['notes'], 0, 'L');
    }

    // Asegurar salto suficiente
    $pdf->Ln(5);
    $currentY = $pdf->GetY();
    $pdf->SetY(max($currentY, $yStart + $logoHeight + 2));

    hrLine($pdf);
}

// --- Idiomes ---
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, match ($locale) {
    1 => "Idiomes",
    2 => "Languages",
    3 => "Idiomas",
    4 => "Lingue",
}, 0, 1);
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$languages = match ($locale) {
    1 => "• Català i castellà: nivell natiu.\n• Anglès: nivell professional\n• Italià: nivell avançat.",
    2 => "• Catalan and Spanish: native level.\n• English: professional level\n• Italian: advanced level.",
    3 => "• Catalán y castellano: nivel nativo.\n• Inglés: nivel profesional\n• Italiano: nivel avanzado.",
    4 => "• Catalano e spagnolo: livello madrelingua.\n• Inglese: livello professionale\n• Italiano: livello avanzato.",
};
$pdf->MultiCell(0, 6, $languages, 0, 'L');
$pdf->Ln(5);

hrLine($pdf);

// --- Autorització final ---
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 9);

$footerText = match ($locale) {
    1 => 'Autorizo el tractament de les meves dades personals d\'acord amb el Reglament europeu de protecció de dades personals.',
    2 => 'I authorize the processing of my personal data in accordance with the European Regulation on the protection of personal data.',
    3 => 'Autorizo el tratamiento de mis datos personales de acuerdo con el Reglamento europeo de protección de datos personales.',
    4 => 'Autorizzo il trattamento dei miei dati personali ai sensi del Decreto Legislativo 30 giugno 2003, n. 196 "Codice in materia di protezione dei dati personali".',
    default => 'I authorize the processing of my personal data in accordance with the European Regulation on the protection of personal data.',
};
$pdf->MultiCell(0, 6, $footerText, 0, 'C');

// --- Output ---
$pdf->Output("cv_{$id}_{$locale}.pdf", "I");
