<?php
require 'vendor/autoload.php';

/*
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="factura_' . $idInvoice . '.pdf"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
*/
// Dades d'entrada
$idInvoice = $routeParams[0];

$url = "https://elliot.cat/api/accounting/get/?type=customers-invoices&id={$idInvoice}";

// segona crida a l'API
$url2 = "https://elliot.cat/api/accounting/get/?type=invoice-products&id={$idInvoice}";

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

// Afegir estils CSS per a la taula
$styles = '<style>
            .table-custom thead tr {
                background-color: black;
                color: white;
            }

            .table,
            .table th,
            .table td {
                padding: 5px;
                border: 1px solid black;
            }
          </style>';

$html = '<br><br><br><br><br>
<div class="container">
    <strong>Número de factura: ' . $id_factura . '/' . $any . '</strong><br>
    Data de la factura: ' . $facDate_net . '<br>
    Data de venciment: ' . $facDueDate_net . '<br>
    Forma de pagament: ' . $pagament . '
</div>';

$html .= '<div class="container">
  <table class="table">
          <thead>
          <tr>
            <th>
                <strong>Facturat a:</strong><br>
                ' . $empresa . '<br>
                A l’atenció de: ' . $nomClient . ' ' . $cognomsClient . '<br>
                NIF: ' . $nif . '<br>
                ' . $clientAdreca . '<br>
                ' . $ciutat . ', (' . $provincia . '), ' . $clientCP . '<br>
                ' . $pais . '
            </th>
            <th>
            <strong>HISPANTIC®</strong><br>
            Elliot Fernandez<br>
            NIF: 9323971DA<br>
        
            4 Meehan Court <br>
            Portlaoise, co. Laois<br>
            R32 F6YC<br>
            Irlanda
            </th>
          </tr>
          </thead>
  </table>
</div>';

$html = $styles . $html;
$html .= '
<div class="container">
<h4 style="text-align: center;"><strong>DETALLS DE LA FACTURA</strong></h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr style="background-color: black; color: white;">
                    <th style="padding: 5px; border: 1px solid black;">Descripció</th>
                    <th style="padding: 5px; border: 1px solid black;">Total</th>
                </tr>
            </thead>
            <tbody>';

// Verificar si $arr2 és un array; si no, convertir-lo en un array.
$arr2 = is_array($arr2) ? $arr2 : [$arr2]; // Si no és un array, es converteix en un array amb un sol producte

foreach ($arr2 as $obj2) {
  $html .= '<tr>
                    <td style="padding: 5px; border: 1px solid black;">' . $obj2['product'] . ' ';
  if (!empty($obj2['notes'])) {
    $html .= '(' . $obj2['notes'] . ')';
  }
  $html .= '</td>
                    <td style="padding: 5px; border: 1px solid black;">€' . number_format($obj2['price'], 2, '.', ',') . '</td>
               </tr>';
}

$html .= '</tbody>                       
        </table>
    </div>
</div>';

$html .= '<div class="container">
  <table class="table">
          <thead>
          <tr>
            <th scope="col">Subtotal</th>
            <th scope="col">€' . number_format($subTotal, 2, '.', ',') . '</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <th scope="row">IVA</th>
            <td>';
if ($facVAT == 0) {
  $html .= '€0.00';
} else {
  $html .= number_format($facVAT, 2, '.', ',');
}
$html .= '</td>
          </tr>

          <tr>
            <th scope="row">Total</th>
            <td><strong>€' . number_format($total, 2, '.', ',') . '</strong></td>
          </tr>
  </table>
</div>';

if ($idPayment == 7) {
  $html .= '
  <div class="container">
  <h5 style="text-align: center;">PAGAMENT PER TRANSFERÈNCIA BANCÀRIA</h5>
  <span style="text-align: center;"><strong>BANC: N26</strong><br>
  IBAN: ES16 1563 2626 3632 6466 4439<br>
  BIC-SWIFT: NTSBESM1XXX</span>
  </div>';
} elseif ($idPayment == 5) {
  $html .= '
  <div class="container">
  <h4 style="text-align: center;">PAGAT AMB STRIPE (Targeta de crèdit/dèbit)</h4>
  </div>';
} elseif ($idPayment == 2) {
  $html .= '
  <div class="container">
  <h4 style="text-align: center;">PAGAT PER TRANSFERÈNCIA BANCÀRIA</h4>
  <span style="text-align: center;"><strong>BANC: N26 (Germany)</strong><br>
  IBAN: DE56100110012620403754<br>
  BIC-SWIFT: NTSBDEB1XXX</span>
  </div>';
}

// Establir l'espaiat vertical entre les cel·les
$pdf->SetHtmlVSpace(array(0, 0, 0, 0));

// Afegir el contingut HTML a través de la funció writeHTML()
$pdf->writeHTML($html, true, false, true, false, '');

// Netejar el buffer de sortida
ob_clean();
flush();

// Sortida del PDF com a fitxer descarregable
$pdf->Output('factura_' . $idInvoice . '.pdf', 'D');
