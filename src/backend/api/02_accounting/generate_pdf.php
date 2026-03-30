<?php

ini_set('default_charset', 'UTF-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use App\Utils\Response;
use App\Utils\MissatgesAPI;

$slug =  isset($routeParams[0]) ? (string)$routeParams[0] : '';
$idInvoice = isset($routeParams[1]) && ctype_digit((string)$routeParams[1]) ? (int)$routeParams[1] : 0;
$lang = strtolower($routeParams[2] ?? 'ca');

/* -----------------------------------------------------------
   Helpers: I18N + fetch datos + HTML PDF + render TCPDF
----------------------------------------------------------- */

function i18nInvoice(string $lang): array
{
  $allowed = ['ca', 'es', 'en', 'it'];
  if (!in_array($lang, $allowed, true)) $lang = 'ca';

  $i18n = [
    'ca' => [
      'pdf_title' => 'PDF de factura',
      'page' => 'Pàgina',
      'invoice_number' => 'Número de factura',
      'invoice_date' => 'Data de la factura',
      'due_date' => 'Data de venciment',
      'payment_method' => 'Forma de pagament',
      'billed_to' => 'Facturat a:',
      'details' => 'DETALLS DE LA FACTURA',
      'description' => 'Descripció',
      'total' => 'Total',
      'subtotal' => 'Subtotal',
      'vat' => 'IVA',
      'paid_by_bank_transfer' => 'Pagament: ',
      'bank' => 'BANC',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Hispantic - Elliot Fernandez Hernandez',
    ],
    'es' => [
      'pdf_title' => 'PDF de factura',
      'page' => 'Página',
      'invoice_number' => 'Número de factura',
      'invoice_date' => 'Fecha de la factura',
      'due_date' => 'Fecha de vencimiento',
      'payment_method' => 'Forma de pago',
      'billed_to' => 'Facturado a:',
      'details' => 'DETALLES DE LA FACTURA',
      'description' => 'Descripción',
      'total' => 'Total',
      'subtotal' => 'Subtotal',
      'vat' => 'IVA',
      'paid_by_bank_transfer' => 'Pago: ',
      'bank' => 'BANCO',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Hispantic - Elliot Fernandez Hernandez',
    ],
    'en' => [
      'pdf_title' => 'Invoice PDF',
      'page' => 'Page',
      'invoice_number' => 'Invoice number',
      'invoice_date' => 'Invoice date',
      'due_date' => 'Due date',
      'payment_method' => 'Payment method',
      'billed_to' => 'Billed to:',
      'details' => 'INVOICE DETAILS',
      'description' => 'Description',
      'total' => 'Total',
      'subtotal' => 'Subtotal',
      'vat' => 'VAT',
      'paid_by_bank_transfer' => 'Payment: ',
      'bank' => 'BANK',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Hispantic - Elliot Fernandez Hernandez',
    ],
    'it' => [
      'pdf_title' => 'PDF della fattura',
      'page' => 'Pagina',
      'invoice_number' => 'Numero fattura',
      'invoice_date' => 'Data fattura',
      'due_date' => 'Scadenza',
      'payment_method' => 'Metodo di pagamento',
      'billed_to' => 'Fatturato a:',
      'details' => 'DETTAGLI DELLA FATTURA',
      'description' => 'Descrizione',
      'total' => 'Totale',
      'subtotal' => 'Subtotale',
      'vat' => 'IVA',
      'paid_by_bank_transfer' => 'Pagamento: ',
      'bank' => 'BANCA',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Hispantic - Elliot Fernandez Hernandez',
    ],
  ];
  return $i18n[$lang];
}

function fetchInvoiceAndProducts(int $idInvoice): array
{
  $url  = "https://elliot.cat/api/comptabilitat/get/facturaCompleta?id={$idInvoice}";

  $payload = hacerLlamadaAPI($url);

  // la API ahora devuelve: ['factura'=>..., 'productes'=>...]
  $obj  = $payload['factura'] ?? null;
  $arr2 = $payload['productes'] ?? [];

  if (!$obj || empty($obj['id'])) {
    throw new RuntimeException('Factura no trobada');
  }

  // asegurar que productes sea un array indexado
  if (!is_array($arr2) || (is_array($arr2) && array_values($arr2) !== $arr2)) {
    $arr2 = [$arr2];
  }

  return [$obj, $arr2];
}

function buildInvoiceHtml(array $obj, array $arr2, array $T): string
{
  $id_factura   = $obj['numero_factura'];

  $empresa      = $obj['clientEmpresa'] ?? '';
  $nomClient    = $obj['clientNom'] ?? '';
  $cognoms      = $obj['clientCognoms'] ?? '';
  $adreca       = $obj['clientAdreca'] ?? '';
  $ciutat       = $obj['clientCiutat'] ?? '';
  $provincia    = $obj['clientProvincia'] ?? '';
  $pais         = $obj['clientPais'] ?? '';
  $nif          = $obj['clientNIF'] ?? '';
  $cp           = $obj['clientCP'] ?? '';

  $facDate_net  = !empty($obj['yearInvoice']) ? date('d/m/Y', strtotime($obj['yearInvoice'])) : '';
  $facDue_net   = !empty($obj['data_venciment']) ? date('d/m/Y', strtotime($obj['data_venciment'])) : '';

  $pagament     = $obj['metodePagament'] ?? '';
  $tipusPagament     = $obj['tipusNom'] ?? '';
  $notesPagament     = $obj['metodeNotes'] ?? '';

  $subTotal     = (float)($obj['base_imposable'] ?? 0);
  $facVAT       = (float)($obj['import_iva'] ?? 0);
  $total        = (float)($obj['total_factura'] ?? 0);

  $emissorNom   = $obj['emissorNom'] ?? '';
  $emissorNIF   = $obj['emissorNIF'] ?? '';
  $emissorNumeroIVA = $obj['emissorNumeroIVA'] ?? '';
  $emissorPais  = $obj['emissorPais'] ?? '';
  $emissorAdreca = $obj['emissorAdreca'] ?? '';
  $emissorTelefon = $obj['emissorTelefon'] ?? '';
  $emissorEmail = $obj['emissorEmail'] ?? '';

  $styles = '<style>
    .table-custom thead tr { background-color: black; color: white; }
    .table, .table th, .table td { padding: 5px; border: 1px solid black; }
  </style>';

  $html = '<br><br><br><br>
  <div class="container">
      <strong>' . htmlspecialchars($T['invoice_number']) . ': ' . $id_factura . '</strong><br>
      ' . htmlspecialchars($T['invoice_date']) . ': ' . $facDate_net . '<br>
      ' . htmlspecialchars($T['due_date']) . ': ' . $facDue_net . '<br>
      ' . htmlspecialchars($T['payment_method']) . ': ' . htmlspecialchars($tipusPagament) . '
  </div>';

  $html .= '<div class="container">
    <table class="table">
      <thead>
        <tr>
          <th>
            <strong>' . htmlspecialchars($T['billed_to']) . '</strong><br>
            ' . htmlspecialchars($empresa) . '<br>
            ' . htmlspecialchars(trim($nomClient . ' ' . $cognoms)) . '<br>
            NIF: ' . htmlspecialchars($nif) . '<br>
            ' . htmlspecialchars($adreca) . '<br>
            ' . htmlspecialchars($ciutat) . ', (' . htmlspecialchars($provincia) . '), ' . htmlspecialchars($cp) . '<br>
            ' . htmlspecialchars($pais) . '
          </th>
          <th>

            <strong>' . htmlspecialchars($emissorNom) . '</strong><br>
            NIF: ' . htmlspecialchars($emissorNIF) . '<br>
            Partita Iva: ' . htmlspecialchars($emissorNumeroIVA) . '<br>
            ' . htmlspecialchars($emissorAdreca) . '<br>
            ' . htmlspecialchars($emissorPais) . '<br>
            ' . htmlspecialchars($emissorTelefon) . ' - ' . htmlspecialchars($emissorEmail) . '<br>
          </th>
        </tr>
      </thead>
    </table>
  </div>';

  $html = $styles . $html;

  $html .= '
  <div class="container">
    <h4 style="text-align: center;"><strong>' . htmlspecialchars($T['details']) . '</strong></h4>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr style="background-color: black; color: white;">
            <th style="padding:5px;border:1px solid black;">' . htmlspecialchars($T['description']) . '</th>
            <th style="padding:5px;border:1px solid black;">' . htmlspecialchars($T['total']) . '</th>
          </tr>
        </thead>
        <tbody>';

  foreach ($arr2 as $obj2) {
    if (!$obj2) continue;
    $prod  = $obj2['producte'] ?? '';
    $notes = $obj2['descripcio'] ?? '';
    $preu  = isset($obj2['preu']) ? (float)$obj2['preu'] : 0.0;

    $line  = htmlspecialchars($prod);
    if (!empty($notes)) $line .= ' (' . htmlspecialchars($notes) . ')';

    $html .= '<tr>
        <td style="padding:5px;border:1px solid black;">' . $line . '</td>
        <td style="padding:5px;border:1px solid black;">' . number_format($preu, 2, ',', '.') . ' €</td>
    </tr>';
  }

  $html .= '</tbody></table></div></div>';

  $html .= '<div class="container">
    <table class="table">
      <thead>
        <tr>
          <th scope="col">' . htmlspecialchars($T['subtotal']) . '</th>
          <th scope="col">' . number_format($subTotal, 2, ',', '.') . ' €</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th scope="row">' . htmlspecialchars($T['vat']) . '</th>
          <td>' . number_format((float)$facVAT, 2, ',', '.') . ' € </td>
        </tr>
        <tr>
          <th scope="row">' . htmlspecialchars($T['total']) . '</th>
          <td><strong>' . number_format($total, 2, ',', '.') . ' €</strong></td>
        </tr>
    </table>
  </div>';

  // Mensajes según método de pago
  $html .= '
    <div class="container">
     <div style="text-align: center; font-size: 12px;"> 
        Transacció sense IVA, realitzada d\'acord amb l\'article 1, apartats 54 a 89, de la Llei núm. 190 de 2014, modificada per la Llei núm. 208 de 2015 i la Llei núm. 145 de 2018 de la República Italiana.
     </div>

      <div style="text-align:center;">
          <strong>' . htmlspecialchars($T['paid_by_bank_transfer']) . htmlspecialchars($tipusPagament) . '</strong><br>
          ' . htmlspecialchars($notesPagament) . '
      </div>
    </div>';

  return $html;
}

class MYPDF extends TCPDF
{
  public function Footer()
  {
    $T = $GLOBALS['T'] ?? [];
    $this->SetY(-15);
    $this->SetFont('helvetica', 'I', 8);
    $this->Cell(0, 10, ($T['page'] ?? 'Page') . ' ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    $this->Ln(4);
    $this->Cell(0, 10, ($T['footer_owner'] ?? 'Elliot Fernandez'), 0, false, 'C', 0, '', 0, false, 'T', 'M');
  }
}

function generateInvoicePdfBinary(array $obj, array $arr2, array $T): string
{
  $GLOBALS['T'] = $T;

  $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8');
  $pdf->SetCreator('Elliot Fernandez - HispanTIC');
  $pdf->SetAuthor('Elliot Fernandez');
  $pdf->SetTitle($T['pdf_title'] ?? 'Invoice PDF');
  $pdf->AddPage('P', 'A4');

  // Logo
  $imagePath = "https://media.elliot.cat/img/img-hispantic/hispantic_logo.jpg";
  $pdf->Image($imagePath, 17, 10, 70, 0, '', '', '', false, 150);

  // Márgenes y fuentes
  $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
  $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
  $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
  $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
  $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
  $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

  $html = buildInvoiceHtml($obj, $arr2, $T);
  $pdf->SetHtmlVSpace([0, 0, 0, 0]);
  $pdf->writeHTML($html, true, false, true, false, '');

  // ⚠️ No ob_clean aquí: devolvemos binario en memoria para adjuntar o enviar
  return $pdf->Output('', 'S');
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

    // Número real de la factura
    $numeroFactura = $obj['numero_factura'] ?? $idInvoice;

    // Respuesta PDF al navegador con nombre bonito
    $numeroFactura = $obj['numero_factura'] ?? $idInvoice;
    // Limpiar nombre: quitar espacios, acentos y caracteres raros
    $numeroFacturaClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $numeroFactura);
    $filename = "factura_{$numeroFacturaClean}_{$lang}.pdf";
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

    $any = (int)($obj['yearInvoice'] ?? date('Y'));
    $id_factura = (int)$obj['id'];

    // Asunto + HTML del email (simple y con logo)
    $subject = [
      'ca' => "Factura #{$id_factura}",
      'es' => "Factura #{$id_factura}",
      'en' => "Invoice #{$id_factura}",
      'it' => "Fattura #{$id_factura}",
    ][$lang] ?? "Invoice #{$id_factura}";

    $logo = 'https://media.elliot.cat/img/img-hispantic/hispantic_logo.jpg';
    $cta  = "https://elliot.cat/api/comptabilitat/pdf/invoice-pdf/{$idInvoice}/{$lang}";

    $emailHtml = '
<!DOCTYPE html><html lang="' . $lang . '"><head>
<meta charset="UTF-8"><title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#222;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f6f7fb;padding:24px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">
        <tr><td style="padding:24px 24px 0 24px;" align="center">
          <img src="' . $logo . '" alt="HispanTIC" style="max-width:120px;height:auto;display:block;">
        </td></tr>
        <tr><td style="padding:24px;">
          <h2 style="margin:0 0 8px 0;font-size:20px;line-height:1.4;">' . htmlspecialchars([
      'ca' => 'Hola ',
      'es' => 'Hola ',
      'en' => 'Hello ',
      'it' => 'Salve ',
    ][$lang]) . htmlspecialchars($nomClient ?: $empresa) . '</h2>
          <p style="margin:0 0 16px 0;font-size:14px;line-height:1.6;">' . htmlspecialchars([
      'ca' => 'Adjunt trobaràs la teva factura en format PDF.',
      'es' => 'Adjuntas encontrarás tu factura en formato PDF.',
      'en' => 'Please find attached your invoice in PDF format.',
      'it' => 'In allegato trovi la tua fattura in formato PDF.',
    ][$lang]) . '</p>
          <p style="margin:0 0 20px 0;font-size:14px;line-height:1.6;"><strong>' . htmlspecialchars([
      'ca' => 'Factura núm.',
      'es' => 'Factura n.º',
      'en' => 'Invoice number',
      'it' => 'Numero fattura',
    ][$lang]) . ':</strong> ' . $id_factura . '/' . $any . '</p>
          </p>
          <p style="margin:20px 0 0 0;font-size:13px;color:#555;">' . htmlspecialchars([
      'ca' => 'Gràcies per la teva confiança.',
      'es' => 'Gracias por tu confianza.',
      'en' => 'Thanks for your trust.',
      'it' => 'Grazie per la fiducia.',
    ][$lang]) . '</p>
        </td></tr>
        <tr><td style="background:#f0f2f6;padding:16px;text-align:center;font-size:12px;color:#6b7280;">
          Elliot Fernández - HispanTIC 
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>';

    // PHPMailer
    $brevoApi = $_ENV['BREVO_API'];
    $mail = new PHPMailer(true);
    $mail->CharSet  = 'UTF-8';                        // <— clave
    $mail->Encoding = PHPMailer::ENCODING_BASE64;     // o 'quoted-printable'
    $mail->isHTML(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com'; // Servidor SMTP de Brevo
    $mail->SMTPAuth   = true;
    $mail->Username   = '7a0605001@smtp-brevo.com'; // Tu dirección de correo de Brevo
    $mail->Password   = $brevoApi; // Tu contraseña de Brevo o API key
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar encriptación TLS
    $mail->Port       = 587; // Puerto SMTP para TLS

    $mail->setFrom('elliot@hispantic.com', 'Elliot Fernandez - HispanTIC');
    $mail->addAddress($clientEmail, $nomClient ?: $empresa);
    $mail->addReplyTo('elliot@hispantic.com', 'Elliot Fernandez - HispanTIC');
    $mail->addBCC('elliot@hispantic.com', 'Arxiu Factures');

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $emailHtml;
    $mail->AltBody = strip_tags($subject . "\n" . $cta);

    $numeroFactura = $obj['numero_factura'] ?? $idInvoice; // fallback por si no existe
    // Limpiar nombre: quitar espacios, acentos y caracteres raros
    $numeroFacturaClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $numeroFactura);
    $filename = "factura_{$numeroFacturaClean}_{$lang}.pdf";

    $mail->addStringAttachment($pdf, $filename, 'base64', 'application/pdf');

    $mail->send();

    Response::success(MissatgesAPI::success('emailEnviat'), ['to' => $clientEmail], 200);
  } catch (MailException $e) {
    Response::error(MissatgesAPI::error('errorEmail'), [$e->getMessage()], 500);
  } catch (Throwable $e) {
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
}
