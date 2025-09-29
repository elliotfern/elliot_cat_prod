<?php
require 'vendor/autoload.php';

// --- Entrada: /api/comptabilitat/pdf/invoice-pdf/{id}/{lang}
$idInvoice = isset($routeParams[0]) ? (int)$routeParams[0] : 0;
$lang = strtolower($routeParams[1] ?? 'ca');
$allowedLangs = ['ca', 'es', 'en', 'it'];
if (!in_array($lang, $allowedLangs, true)) {
  $lang = 'ca';
}

// --- I18N bàsic
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
$T = $i18n[$lang];
$GLOBALS['T'] = $T; // per a Footer()

$url = "https://elliot.cat/api/comptabilitat/get/facturaClientsPDF?id={$idInvoice}";

// segona crida a l'API
$url2 = "https://elliot.cat/api/comptabilitat/get/facturaProductesPDF?id={$idInvoice}";

// Crida a l'API passant el token i l'ID de la factura
$invoiceData = hacerLlamadaAPI($url);

// Crida a l'API passant el token i l'ID de la factura
$productData = hacerLlamadaAPI($url2);

// Accedir al primer element si existeix
$obj = $invoiceData ?? null; // L'operador de coalescència nul·la assegura que no falli si no existeix l'índex

$id_factura = $obj['id'];
$empresa = $obj['clientEmpresa'];
$nomClient = $obj['clientNom'];
$cognomsClient = $obj['clientCognoms'];
$clientAdreca = $obj['clientAdreca'];
$ciutat = $obj['clientCiutat'];
$provincia = $obj['clientProvincia'];
$pais = $obj['clientPais'];
$nif = $obj['clientNIF'];
$clientEmail = $obj['clientEmail'];
$clientWeb = $obj['clientWeb'];
$clientCP = $obj['clientCP'];
$any = $obj['yearInvoice'];
$facDate2 = $obj['facData'];
$facDate_net = date('d/m/Y', strtotime($facDate2));
$facDueDate2 = $obj['facDueDate'];
$facDueDate_net = date('d/m/Y', strtotime($facDueDate2));
$pagament = $obj['tipusNom'];
$idPayment = $obj['idPayment'];

$total = $obj['facTotal'];
$subTotal = $obj['facSubtotal'];
$facVAT = $obj['facVAT'];
$malt = $obj['facFees'];



// Accedir al primer element si existeix
$arr2 = $productData ?? null; // L'operador de coalescència nul·la assegura que no falli si no existeix l'índex

// comença la generació del PDF

// Estendre la classe TCPDF per crear capçalera i peu personalitzats
class MYPDF extends TCPDF
{

  // Peu de pàgina
  public function Footer()
  {
    // Posició a 15 mm del final
    $this->SetY(-15);
    // Tipus de lletra
    $this->SetFont('helvetica', 'I', 8);
    // Número de pàgina
    $this->Cell(0, 10, 'Pàgina ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    // Text personalitzat del peu
    $this->Cell(0, 10, '<strong>Elliot Fernandez<br>Número de referència fiscal: 9323971DA</strong>', 0, false, 'C', 0, '', 0, false, 'T', 'M');
  }
}

// Crear una nova instància de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

// Informació del document
$pdf->SetCreator('Elliot Fernandez - HispanTIC');
$pdf->SetAuthor('Elliot Fernandez');
$pdf->SetTitle('PDF de factura');

// Afegir una pàgina
$pdf->AddPage('P', 'A4');

// Afegir la imatge al PDF
$imagePath = "https://elliot.cat/public/img/hispantic_logo.jpg";
// Especifica els valors sense unitats, per exemple, en mil·límetres (mm).
$pdf->Image($imagePath, $x = 17, $y = 10, $w = 70, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 150, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = '');

// tipus de lletra per a capçalera i peu
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// marges
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// salts de pàgina automàtics
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Escriure contingut HTML al PDF

// CSS
$styles = '<style>
  .table-custom thead tr { background-color: black; color: white; }
  .table, .table th, .table td { padding: 5px; border: 1px solid black; }
</style>';

$html = '<br><br><br><br><br>
<div class="container">
    <strong>' . htmlspecialchars($T['invoice_number']) . ': ' . $id_factura . '/' . $any . '</strong><br>
    ' . htmlspecialchars($T['invoice_date']) . ': ' . $facDate_net . '<br>
    ' . htmlspecialchars($T['due_date']) . ': ' . $facDueDate_net . '<br>
    ' . htmlspecialchars($T['payment_method']) . ': ' . htmlspecialchars($pagament) . '
</div>';

$html .= '<div class="container">
  <table class="table">
    <thead>
      <tr>
        <th>
          <strong>' . htmlspecialchars($T['billed_to']) . '</strong><br>
          ' . htmlspecialchars($empresa) . '<br>
          ' . htmlspecialchars($nomClient . ' ' . $cognomsClient) . '<br>
          NIF: ' . htmlspecialchars($nif) . '<br>
          ' . htmlspecialchars($clientAdreca) . '<br>
          ' . htmlspecialchars($ciutat) . ', (' . htmlspecialchars($provincia) . '), ' . htmlspecialchars($clientCP) . '<br>
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
  $prod = $obj2['producte'] ?? '';
  $notes = $obj2['notes'] ?? '';
  $preu = isset($obj2['preu']) ? (float)$obj2['preu'] : 0.0;
  $line = htmlspecialchars($prod);
  if (!empty($notes)) {
    $line .= ' (' . htmlspecialchars($notes) . ')';
  }
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

// Missatges segons mètode de pagament
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

$pdf->SetHtmlVSpace([0, 0, 0, 0]);
$pdf->writeHTML($html, true, false, true, false, '');

@ob_clean();
@flush();

$pdf->Output('factura_' . $idInvoice . '_' . $lang . '.pdf', 'D');
