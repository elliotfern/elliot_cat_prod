<?php

ini_set('default_charset', 'UTF-8');

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Mailer;

$slug      = isset($routeParams[0]) ? (string)$routeParams[0] : '';
$idInvoice = isset($routeParams[1]) && ctype_digit((string)$routeParams[1]) ? (int)$routeParams[1] : 0;
$lang      = strtolower($routeParams[2] ?? 'ca');

/* -----------------------------------------------------------
   Helpers: I18N + fetch datos + HTML PDF + render Dompdf
----------------------------------------------------------- */

function i18nInvoice(string $lang): array
{
  $allowed = ['ca', 'es', 'en', 'it'];
  if (!in_array($lang, $allowed, true)) $lang = 'ca';

  $i18n = [
    'ca' => [
      'pdf_title'            => 'PDF de factura',
      'page'                 => 'Pàgina',
      'invoice_number'       => 'Número de factura',
      'invoice_date'         => 'Data de la factura',
      'due_date'             => 'Data de venciment',
      'payment_method'       => 'Forma de pagament',
      'billed_to'            => 'Facturat a:',
      'details'              => 'DETALLS DE LA FACTURA',
      'description'          => 'Descripció',
      'total'                => 'Total',
      'subtotal'             => 'Subtotal',
      'vat'                  => 'IVA',
      'paid_by_bank_transfer' => 'Pagament: ',
      'bank'                 => 'BANC',
      'iban'                 => 'IBAN',
      'bic'                  => 'BIC-SWIFT',
      'footer_owner'         => 'Hispantic - Elliot Fernandez Hernandez',
    ],
    'es' => [
      'pdf_title'            => 'PDF de factura',
      'page'                 => 'Página',
      'invoice_number'       => 'Número de factura',
      'invoice_date'         => 'Fecha de la factura',
      'due_date'             => 'Fecha de vencimiento',
      'payment_method'       => 'Forma de pago',
      'billed_to'            => 'Facturado a:',
      'details'              => 'DETALLES DE LA FACTURA',
      'description'          => 'Descripción',
      'total'                => 'Total',
      'subtotal'             => 'Subtotal',
      'vat'                  => 'IVA',
      'paid_by_bank_transfer' => 'Pago: ',
      'bank'                 => 'BANCO',
      'iban'                 => 'IBAN',
      'bic'                  => 'BIC-SWIFT',
      'footer_owner'         => 'Hispantic - Elliot Fernandez Hernandez',
    ],
    'en' => [
      'pdf_title'            => 'Invoice PDF',
      'page'                 => 'Page',
      'invoice_number'       => 'Invoice number',
      'invoice_date'         => 'Invoice date',
      'due_date'             => 'Due date',
      'payment_method'       => 'Payment method',
      'billed_to'            => 'Billed to:',
      'details'              => 'INVOICE DETAILS',
      'description'          => 'Description',
      'total'                => 'Total',
      'subtotal'             => 'Subtotal',
      'vat'                  => 'VAT',
      'paid_by_bank_transfer' => 'Payment: ',
      'bank'                 => 'BANK',
      'iban'                 => 'IBAN',
      'bic'                  => 'BIC-SWIFT',
      'footer_owner'         => 'Hispantic - Elliot Fernandez Hernandez',
    ],
    'it' => [
      'pdf_title'            => 'PDF della fattura',
      'page'                 => 'Pagina',
      'invoice_number'       => 'Numero fattura',
      'invoice_date'         => 'Data fattura',
      'due_date'             => 'Scadenza',
      'payment_method'       => 'Metodo di pagamento',
      'billed_to'            => 'Fatturato a:',
      'details'              => 'DETTAGLI DELLA FATTURA',
      'description'          => 'Descrizione',
      'total'                => 'Totale',
      'subtotal'             => 'Subtotale',
      'vat'                  => 'IVA',
      'paid_by_bank_transfer' => 'Pagamento: ',
      'bank'                 => 'BANCA',
      'iban'                 => 'IBAN',
      'bic'                  => 'BIC-SWIFT',
      'footer_owner'         => 'Hispantic - Elliot Fernandez Hernandez',
    ],
  ];

  return $i18n[$lang];
}

function fetchInvoiceAndProducts(int $idInvoice): array
{
  $url     = "https://elliot.cat/api/comptabilitat/get/facturaCompleta?id={$idInvoice}";
  $payload = hacerLlamadaAPI($url);

  $obj  = $payload['factura']  ?? null;
  $arr2 = $payload['productes'] ?? [];

  if (!$obj || empty($obj['id'])) {
    throw new RuntimeException('Factura no trobada');
  }

  if (!is_array($arr2) || array_values($arr2) !== $arr2) {
    $arr2 = [$arr2];
  }

  return [$obj, $arr2];
}

function buildInvoiceHtml(array $obj, array $arr2, array $T): string
{
  $id_factura    = $obj['numero_factura'];
  $empresa       = $obj['clientEmpresa']    ?? '';
  $nomClient     = $obj['clientNom']        ?? '';
  $cognoms       = $obj['clientCognoms']    ?? '';
  $adreca        = $obj['clientAdreca']     ?? '';
  $ciutat        = $obj['clientCiutat']     ?? '';
  $provincia     = $obj['clientProvincia']  ?? '';
  $pais          = $obj['clientPais']       ?? '';
  $nif           = $obj['clientNIF']        ?? '';
  $cp            = $obj['clientCP']         ?? '';

  $facDate_net   = !empty($obj['yearInvoice'])      ? date('d/m/Y', strtotime($obj['yearInvoice']))      : '';
  $facDue_net    = !empty($obj['data_venciment'])   ? date('d/m/Y', strtotime($obj['data_venciment']))   : '';

  $tipusPagament  = $obj['tipusNom']      ?? '';
  $notesPagament  = $obj['metodeNotes']   ?? '';

  $subTotal      = (float)($obj['base_imposable'] ?? 0);
  $facVAT        = (float)($obj['import_iva']     ?? 0);
  $total         = (float)($obj['total_factura']  ?? 0);

  $emissorNom        = $obj['emissorNom']        ?? '';
  $emissorNIF        = $obj['emissorNIF']        ?? '';
  $emissorNumeroIVA  = $obj['emissorNumeroIVA']  ?? '';
  $emissorPais       = $obj['emissorPais']       ?? '';
  $emissorAdreca     = $obj['emissorAdreca']     ?? '';
  $emissorTelefon    = $obj['emissorTelefon']    ?? '';
  $emissorEmail      = $obj['emissorEmail']      ?? '';

  $footerOwner = htmlspecialchars($T['footer_owner'] ?? 'Hispantic - Elliot Fernandez Hernandez');
  $pageLabel   = htmlspecialchars($T['page'] ?? 'Pàgina');

  // Filas de productos
  $liniesHtml = '';
  foreach ($arr2 as $obj2) {
    if (!$obj2) continue;
    $prod  = $obj2['producte']   ?? '';
    $notes = $obj2['descripcio'] ?? '';
    $preu  = isset($obj2['preu']) ? (float)$obj2['preu'] : 0.0;
    $line  = htmlspecialchars($prod);
    if (!empty($notes)) $line .= ' (' . htmlspecialchars($notes) . ')';
    $liniesHtml .= '
            <tr>
                <td>' . $line . '</td>
                <td class="num">' . number_format($preu, 2, ',', '.') . ' €</td>
            </tr>';
  }

  return '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang ?? 'ca') . '">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 20mm 15mm 20mm 15mm;
    }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #000;
        margin: 0;
        padding: 0;
    }
    .logo {
        margin-bottom: 16px;
    }
    .logo img {
        height: 50px;
        width: auto;
    }
    .meta {
        margin-bottom: 14px;
        font-size: 11px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
        font-size: 11px;
    }
    th, td {
        border: 1px solid #000;
        padding: 5px 7px;
        vertical-align: top;
    }
    thead tr {
        background-color: #000;
        color: #fff;
    }
    .num { text-align: right; }
    .totals td { border: 1px solid #000; }
    .totals td:last-child { text-align: right; }
    .legal {
        font-size: 9px;
        text-align: center;
        margin-bottom: 8px;
        color: #333;
    }
    .pagament {
        text-align: center;
        font-size: 11px;
        margin-bottom: 8px;
    }
    .footer-pdf {
        position: fixed;
        bottom: -15mm;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 8px;
        color: #555;
    }
</style>
</head>
<body>

<div class="footer-pdf">
    ' . $footerOwner . '
</div>

<div class="logo">
    <img src="https://media.elliot.cat/img/img-hispantic/hispantic_logo.jpg" alt="HispanTIC">
</div>

<div class="meta">
    <strong>' . htmlspecialchars($T['invoice_number']) . ': ' . htmlspecialchars($id_factura) . '</strong><br>
    ' . htmlspecialchars($T['invoice_date'])    . ': ' . htmlspecialchars($facDate_net)    . '<br>
    ' . htmlspecialchars($T['due_date'])        . ': ' . htmlspecialchars($facDue_net)     . '<br>
    ' . htmlspecialchars($T['payment_method'])  . ': ' . htmlspecialchars($tipusPagament)  . '
</div>

<table>
    <thead>
        <tr>
            <th style="width:50%;">
                <strong>' . htmlspecialchars($T['billed_to']) . '</strong><br>
                ' . htmlspecialchars($empresa) . '<br>
                ' . htmlspecialchars(trim($nomClient . ' ' . $cognoms)) . '<br>
                NIF: ' . htmlspecialchars($nif) . '<br>
                ' . htmlspecialchars($adreca) . '<br>
                ' . htmlspecialchars($ciutat) . ', (' . htmlspecialchars($provincia) . '), ' . htmlspecialchars($cp) . '<br>
                ' . htmlspecialchars($pais) . '
            </th>
            <th style="width:50%;">
                <strong>' . htmlspecialchars($emissorNom) . '</strong><br>
                NIF: '          . htmlspecialchars($emissorNIF)       . '<br>
                Partita Iva: '  . htmlspecialchars($emissorNumeroIVA) . '<br>
                '               . htmlspecialchars($emissorAdreca)    . '<br>
                '               . htmlspecialchars($emissorPais)      . '<br>
                '               . htmlspecialchars($emissorTelefon)   . ' - ' . htmlspecialchars($emissorEmail) . '
            </th>
        </tr>
    </thead>
</table>

<h4 style="text-align:center;">' . htmlspecialchars($T['details']) . '</h4>

<table>
    <thead>
        <tr>
            <th>' . htmlspecialchars($T['description']) . '</th>
            <th class="num">' . htmlspecialchars($T['total']) . '</th>
        </tr>
    </thead>
    <tbody>
        ' . $liniesHtml . '
    </tbody>
</table>

<table class="totals" style="width:55%;margin-left:45%;">
    <tr>
        <td>' . htmlspecialchars($T['subtotal']) . '</td>
        <td class="num">' . number_format($subTotal, 2, ',', '.') . ' €</td>
    </tr>
    <tr>
        <td>' . htmlspecialchars($T['vat']) . '</td>
        <td class="num">' . number_format($facVAT, 2, ',', '.') . ' €</td>
    </tr>
    <tr>
        <td><strong>' . htmlspecialchars($T['total']) . '</strong></td>
        <td class="num"><strong>' . number_format($total, 2, ',', '.') . ' €</strong></td>
    </tr>
</table>

<div class="legal">
    Transacció sense IVA, realitzada d\'acord amb l\'article 1, apartats 54 a 89,
    de la Llei núm. 190 de 2014, modificada per la Llei núm. 208 de 2015 i la
    Llei núm. 145 de 2018 de la República Italiana.
</div>

<div class="pagament">
    <strong>' . htmlspecialchars($T['paid_by_bank_transfer']) . htmlspecialchars($tipusPagament) . '</strong><br>
    ' . htmlspecialchars($notesPagament) . '
</div>

</body>
</html>';
}

function generateInvoicePdfBinary(array $obj, array $arr2, array $T): string
{
  $html = buildInvoiceHtml($obj, $arr2, $T);

  $options = new Options();
  $options->set('defaultFont', 'DejaVu Sans');
  $options->set('isRemoteEnabled', true);
  $options->set('isHtml5ParserEnabled', true);

  $dompdf = new Dompdf($options);
  $dompdf->loadHtml($html, 'UTF-8');
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();

  return $dompdf->output();
}

/* -----------------------------------------------------------
   Endpoints
----------------------------------------------------------- */

// /api/comptabilitat/pdf/invoice-pdf/{id}/{lang}
if ($slug === 'invoice-pdf') {

  try {
    $T = i18nInvoice($lang);
    [$obj, $arr2] = fetchInvoiceAndProducts($idInvoice);
    if (!is_array($arr2)) $arr2 = [];

    $pdf = generateInvoicePdfBinary($obj, $arr2, $T);

    $numeroFactura      = $obj['numero_factura'] ?? $idInvoice;
    $numeroFacturaClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $numeroFactura);
    $filename           = "factura_{$numeroFacturaClean}_{$lang}.pdf";

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
  } catch (Throwable $e) {
    http_response_code(500);
    echo 'Error generant PDF: ' . $e->getMessage();
    exit;
  }
}

// /api/comptabilitat/send/invoice-email/{id}/{lang}
if ($slug === 'invoice-email') {

  try {
    $T = i18nInvoice($lang);
    [$obj, $arr2] = fetchInvoiceAndProducts($idInvoice);
    $pdf = generateInvoicePdfBinary($obj, $arr2, $T);

    $clientEmail = $obj['clientEmail'] ?? '';
    $nomClient   = trim(($obj['clientNom'] ?? '') . ' ' . ($obj['clientCognoms'] ?? ''));
    $empresa     = $obj['clientEmpresa'] ?? '';

    if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
      Response::error(MissatgesAPI::error('validacio'), ['Email del client no vàlid o inexistent'], 400);
    }

    $any        = (int)($obj['yearInvoice'] ?? date('Y'));
    $id_factura = (int)$obj['id'];

    $subject = [
      'ca' => "Factura #{$id_factura}",
      'es' => "Factura #{$id_factura}",
      'en' => "Invoice #{$id_factura}",
      'it' => "Fattura #{$id_factura}",
    ][$lang] ?? "Invoice #{$id_factura}";

    $cta = "https://elliot.cat/api/comptabilitat/pdf/invoice-pdf/{$idInvoice}/{$lang}";

    $emailHtml = '
<!DOCTYPE html><html lang="' . $lang . '"><head>
<meta charset="UTF-8"><title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#222;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;padding:24px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">
        <tr><td style="padding:24px 24px 0 24px;" align="center">
          <img src="https://media.elliot.cat/img/img-hispantic/hispantic_logo.jpg" alt="HispanTIC" style="max-width:120px;height:auto;display:block;">
        </td></tr>
        <tr><td style="padding:24px;">
          <h2 style="margin:0 0 8px 0;font-size:20px;">' . htmlspecialchars(['ca' => 'Hola ', 'es' => 'Hola ', 'en' => 'Hello ', 'it' => 'Salve '][$lang]) . htmlspecialchars($nomClient ?: $empresa) . '</h2>
          <p style="margin:0 0 16px 0;font-size:14px;">' . htmlspecialchars(['ca' => 'Adjunt trobaràs la teva factura en format PDF.', 'es' => 'Adjuntas encontrarás tu factura en formato PDF.', 'en' => 'Please find attached your invoice in PDF format.', 'it' => 'In allegato trovi la tua fattura in formato PDF.'][$lang]) . '</p>
          <p style="margin:0 0 20px 0;font-size:14px;"><strong>' . htmlspecialchars(['ca' => 'Factura núm.', 'es' => 'Factura n.º', 'en' => 'Invoice number', 'it' => 'Numero fattura'][$lang]) . ':</strong> ' . $id_factura . '/' . $any . '</p>
          <p style="margin:20px 0 0 0;font-size:13px;color:#555;">' . htmlspecialchars(['ca' => 'Gràcies per la teva confiança.', 'es' => 'Gracias por tu confianza.', 'en' => 'Thanks for your trust.', 'it' => 'Grazie per la fiducia.'][$lang]) . '</p>
        </td></tr>
        <tr><td style="background:#f0f2f6;padding:16px;text-align:center;font-size:12px;color:#6b7280;">
          Elliot Fernández - HispanTIC
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>';

    $numeroFactura      = $obj['numero_factura'] ?? $idInvoice;
    $numeroFacturaClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $numeroFactura);
    $filename           = "factura_{$numeroFacturaClean}_{$lang}.pdf";

    // Guardar PDF en fichero temporal para adjuntarlo
    $tmpPath = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($tmpPath, $pdf);

    $mailer = new Mailer();
    $sent   = $mailer->send(
      to: $clientEmail,
      toName: $nomClient ?: $empresa,
      subject: $subject,
      htmlBody: $emailHtml,
      plainText: strip_tags($subject . "\n" . $cta),
      bcc: ['elliot@hispantic.com' => 'Arxiu Factures'],
      attachments: [['path' => $tmpPath, 'name' => $filename]],
    );

    // Limpiar temporal
    @unlink($tmpPath);

    if (!$sent) {
      Response::error(MissatgesAPI::error('errorEmail'), ['send() returned false'], 500);
    }

    Response::success(
      message: MissatgesAPI::success('emailEnviat'),
      data: $clientEmail,
      httpCode: 200
    );
  } catch (Throwable $e) {
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
}
