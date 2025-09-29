<?php
$uri  = $_SERVER['REQUEST_URI'];                       // /gestio/comptabilitat/fitxa-factura-client/106
$path = parse_url($uri, PHP_URL_PATH);                 // limpia querystrings
$parts = array_values(array_filter(explode('/', $path), 'strlen')); // trocea y quita vacíos

$id = null;
for ($i = count($parts) - 1; $i >= 0; $i--) {
	if (ctype_digit($parts[$i])) {                     // toma el último segmento numérico
		$id = (int) $parts[$i];
		break;
	}
}

if ($id === null) {
	http_response_code(400);
	exit('ID no válido en la URL');
}
?>

<div class="barraNavegacio"></div>

<div id="invoiceRoot" class="container-fluid form" data-invoice-id="<?php echo $id; ?>">

	<h2>Gestió comptabilitat i clients</h2>
	<h4>Detalls factura</h4>

	<div id="invoiceHeader">
	</div>
	<div id="invoiceAmounts" class="mt-3"></div>
	<div id="invoiceProducts" class="mt-4"></div>

</div>