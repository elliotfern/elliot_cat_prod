<?php

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
      'paid_by_bank_transfer' => 'PAGAMENT PER TRANSFERÈNCIA BANCÀRIA',
      'paid_with_stripe' => 'PAGAT AMB STRIPE (Targeta de crèdit/dèbit)',
      'paid_bank_transfer' => 'PAGAT PER TRANSFERÈNCIA BANCÀRIA',
      'bank' => 'BANC',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Elliot Fernandez',
      'footer_tax_ref' => 'Número de referència fiscal: 9323971DA',
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
      'paid_by_bank_transfer' => 'PAGO POR TRANSFERENCIA BANCARIA',
      'paid_with_stripe' => 'PAGADO CON STRIPE (Tarjeta crédito/débito)',
      'paid_bank_transfer' => 'PAGADO POR TRANSFERENCIA BANCARIA',
      'bank' => 'BANCO',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Elliot Fernandez',
      'footer_tax_ref' => 'Número de referencia fiscal: 9323971DA',
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
      'paid_by_bank_transfer' => 'PAYMENT BY BANK TRANSFER',
      'paid_with_stripe' => 'PAID WITH STRIPE (Credit/Debit card)',
      'paid_bank_transfer' => 'PAID BY BANK TRANSFER',
      'bank' => 'BANK',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Elliot Fernandez',
      'footer_tax_ref' => 'Tax reference number: 9323971DA',
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
      'paid_by_bank_transfer' => 'PAGAMENTO TRAMITE BONIFICO BANCARIO',
      'paid_with_stripe' => 'PAGATO CON STRIPE (Carta di credito/debito)',
      'paid_bank_transfer' => 'PAGATO TRAMITE BONIFICO BANCARIO',
      'bank' => 'BANCA',
      'iban' => 'IBAN',
      'bic' => 'BIC-SWIFT',
      'footer_owner' => 'Elliot Fernandez',
      'footer_tax_ref' => 'Numero di riferimento fiscale: 9323971DA',
    ],
  ];
  return $i18n[$lang];
}

function fetchInvoiceAndProducts(int $idInvoice): array
{
  $url  = "https://elliot.cat/api/comptabilitat/get/facturaClientsPDF?id={$idInvoice}";
  $url2 = "https://elliot.cat/api/comptabilitat/get/facturaProductesPDF?id={$idInvoice}";
  $invoiceData = hacerLlamadaAPI($url);
  $productData = hacerLlamadaAPI($url2);

  $obj = $invoiceData ?? null;
  if (!$obj || empty($obj['id'])) {
    throw new RuntimeException('Factura no trobada');
  }

  $arr2 = $productData ?? [];
  // si viene objeto único, conviértelo a array
  if (!is_array($arr2) || (is_array($arr2) && array_values($arr2) !== $arr2)) {
    $arr2 = [$arr2];
  }
  return [$obj, $arr2];
}

function buildInvoiceHtml(array $obj, array $arr2, array $T): string
{
  $id_factura   = (int)$obj['id'];
  $empresa      = $obj['clientEmpresa'] ?? '';
  $nomClient    = $obj['clientNom'] ?? '';
  $cognoms      = $obj['clientCognoms'] ?? '';
  $adreca       = $obj['clientAdreca'] ?? '';
  $ciutat       = $obj['clientCiutat'] ?? '';
  $provincia    = $obj['clientProvincia'] ?? '';
  $pais         = $obj['clientPais'] ?? '';
  $nif          = $obj['clientNIF'] ?? '';
  $cp           = $obj['clientCP'] ?? '';
  $any          = (int)($obj['yearInvoice'] ?? date('Y'));
  $facDate_net  = !empty($obj['facData']) ? date('d/m/Y', strtotime($obj['facData'])) : '';
  $facDue_net   = !empty($obj['facDueDate']) ? date('d/m/Y', strtotime($obj['facDueDate'])) : '';
  $pagament     = $obj['tipusNom'] ?? '';
  $idPayment    = (int)($obj['idPayment'] ?? 0);

  $subTotal     = (float)($obj['facSubtotal'] ?? 0);
  $facVAT       = (float)($obj['facVAT'] ?? 0);
  $total        = (float)($obj['facTotal'] ?? 0);

  $styles = '<style>
    .table-custom thead tr { background-color: black; color: white; }
    .table, .table th, .table td { padding: 5px; border: 1px solid black; }
  </style>';

  $html = '<br><br><br><br><br>
  <div class="container">
      <strong>' . htmlspecialchars($T['invoice_number']) . ': ' . $id_factura . '/' . $any . '</strong><br>
      ' . htmlspecialchars($T['invoice_date']) . ': ' . $facDate_net . '<br>
      ' . htmlspecialchars($T['due_date']) . ': ' . $facDue_net . '<br>
      ' . htmlspecialchars($T['payment_method']) . ': ' . htmlspecialchars($pagament) . '
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
            <strong>HISPANTIC®</strong><br>
            Elliot Fernandez<br>
            NIF: 9323971DA<br>
            4 Meehan Court <br>
            Portlaoise, co. Laois<br>
            R32 F6YC<br>
            Ireland
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
    $notes = $obj2['notes'] ?? '';
    $preu  = isset($obj2['preu']) ? (float)$obj2['preu'] : 0.0;
    $line  = htmlspecialchars($prod);
    if (!empty($notes)) $line .= ' (' . htmlspecialchars($notes) . ')';
    $html .= '<tr>
      <td style="padding:5px;border:1px solid black;">' . $line . '</td>
      <td style="padding:5px;border:1px solid black;">€' . number_format($preu, 2, '.', ',') . '</td>
    </tr>';
  }

  $html .= '</tbody></table></div></div>';

  $html .= '<div class="container">
    <table class="table">
      <thead>
        <tr>
          <th scope="col">' . htmlspecialchars($T['subtotal']) . '</th>
          <th scope="col">€' . number_format($subTotal, 2, '.', ',') . '</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th scope="row">' . htmlspecialchars($T['vat']) . '</th>
          <td>' . ($facVAT == 0 ? '€0.00' : '€' . number_format($facVAT, 2, '.', ',')) . '</td>
        </tr>
        <tr>
          <th scope="row">' . htmlspecialchars($T['total']) . '</th>
          <td><strong>€' . number_format($total, 2, '.', ',') . '</strong></td>
        </tr>
    </table>
  </div>';

  // Mensajes según método de pago
  if ($idPayment === 7) {
    $html .= '
    <div class="container">
      <h5 style="text-align: center;">' . htmlspecialchars($T['paid_by_bank_transfer']) . '</h5>
      <div style="text-align: center;">
        <strong>' . htmlspecialchars($T['bank']) . ': N26</strong><br>
        ' . htmlspecialchars($T['iban']) . ': ES16 1563 2626 3632 6466 4439<br>
        ' . htmlspecialchars($T['bic']) . ': NTSBESM1XXX
      </div>
    </div>';
  } elseif ($idPayment === 5) {
    $html .= '
    <div class="container">
      <h4 style="text-align: center;">' . htmlspecialchars($T['paid_with_stripe']) . '</h4>
    </div>';
  } elseif ($idPayment === 2) {
    $html .= '
    <div class="container">
      <h4 style="text-align: center;">' . htmlspecialchars($T['paid_bank_transfer']) . '</h4>
      <div style="text-align: center;">
        <strong>' . htmlspecialchars($T['bank']) . ': N26 (Germany)</strong><br>
        ' . htmlspecialchars($T['iban']) . ': DE56 1001 1001 2620 4037 54<br>
        ' . htmlspecialchars($T['bic']) . ': NTSBDEB1XXX
      </div>
    </div>';
  }

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
    $this->Cell(0, 10, ($T['footer_owner'] ?? 'Elliot Fernandez') . ' — ' . ($T['footer_tax_ref'] ?? 'Tax reference number: 9323971DA'), 0, false, 'C', 0, '', 0, false, 'T', 'M');
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
  $imagePath = "https://elliot.cat/public/img/hispantic_logo.jpg";
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
    $pdf = generateInvoicePdfBinary($obj, $arr2, $T);

    // Respuesta PDF al navegador
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="factura_' . $idInvoice . '_' . $lang . '.pdf"');
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
      'ca' => "Factura #{$id_factura}/{$any}",
      'es' => "Factura #{$id_factura}/{$any}",
      'en' => "Invoice #{$id_factura}/{$any}",
      'it' => "Fattura #{$id_factura}/{$any}",
    ][$lang] ?? "Invoice #{$id_factura}/{$any}";

    $logo = 'https://elliot.cat/public/img/hispantic_logo.jpg';
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
          <img src="' . $logo . '" alt="HispanTIC" style="max-width:220px;height:auto;display:block;">
        </td></tr>
        <tr><td style="padding:24px;">
          <h2 style="margin:0 0 8px 0;font-size:20px;line-height:1.4;">' . htmlspecialchars($nomClient ?: $empresa) . '</h2>
          <p style="margin:0 0 16px 0;font-size:14px;line-height:1.6;">' . htmlspecialchars([
      'ca' => 'Adjunt trobaràs la teva factura en format PDF.',
      'es' => 'Adjuntas encontrarás tu factura en formato PDF.',
      'en' => 'Please find attached your invoice in PDF format.',
      'it' => 'In allegato trovi la tua fattura in formato PDF.',
    ][$lang]) . '</p>
          <p style="margin:0 0 20px 0;font-size:14px;line-height:1.6;"><strong>Invoice:</strong> #' . $id_factura . '/' . $any . '</p>
          <p style="text-align:center;margin:0 0 8px 0;">
            <a href="' . $cta . '" style="display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:bold;">' .
      htmlspecialchars([
        'ca' => 'Descarregar factura',
        'es' => 'Descargar factura',
        'en' => 'Download invoice',
        'it' => 'Scarica fattura'
      ][$lang])
      . '</a>
          </p>
          <p style="margin:20px 0 0 0;font-size:13px;color:#555;">' . htmlspecialchars([
        'ca' => 'Gràcies per la teva confiança.',
        'es' => 'Gracias por tu confianza.',
        'en' => 'Thanks for your trust.',
        'it' => 'Grazie per la fiducia.',
      ][$lang]) . '</p>
        </td></tr>
        <tr><td style="background:#f0f2f6;padding:16px;text-align:center;font-size:12px;color:#6b7280;">
          HispanTIC · Portlaoise (IE) · VAT: 9323971DA
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>';

    // PHPMailer
    $brevoApi = $_ENV['BREVO_API'];
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com'; // Servidor SMTP de Brevo
    $mail->SMTPAuth   = true;
    $mail->Username   = '7a0605001@smtp-brevo.com'; // Tu dirección de correo de Brevo
    $mail->Password   = $brevoApi; // Tu contraseña de Brevo o API key
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar encriptación TLS
    $mail->Port       = 587; // Puerto SMTP para TLS

    $mail->setFrom('elliot@hispantic.com', 'Elliot Fernandez - HispanTIC');
    //$mail->addAddress($clientEmail, $nomClient ?: $empresa);
    $mail->addAddress('elliot@hispantic.com');
    $mail->addReplyTo('elliot@hispantic.com', 'Elliot Fernandez - HispanTIC');
    $mail->addBCC('elliot@hispantic.com', 'Arxiu Factures');

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $emailHtml;
    $mail->AltBody = strip_tags($subject . "\n" . $cta);

    $filename = "factura_{$idInvoice}_{$lang}.pdf";
    $mail->addStringAttachment($pdf, $filename, 'base64', 'application/pdf');

    $mail->send();

    Response::success(MissatgesAPI::success('emailEnviat'), ['to' => $clientEmail], 200);
  } catch (MailException $e) {
    Response::error(MissatgesAPI::error('errorEmail'), [$e->getMessage()], 500);
  } catch (Throwable $e) {
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
  }
}
