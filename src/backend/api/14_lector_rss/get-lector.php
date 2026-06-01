<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: GET");

function obtenerRSS($url)
{
    $contenido = obtenerContenidoURL($url);
    if (!$contenido) {
        return ["error" => "No se pudo obtener contenido del feed"];
    }

    // Intentamos decodificar como JSON
    $data = json_decode($contenido, true);
    if ($data) {
        return procesarJSON($data);
    }

    // Si no es JSON, intentamos como XML
    $xml = simplexml_load_string($contenido, "SimpleXMLElement", LIBXML_NOCDATA);
    if ($xml) {
        return procesarXML($xml);
    }

    return ["error" => "Formato desconocido"];
}

function procesarJSON($data)
{
    $feedTitle = $data["title"] ?? "Fuente desconocida";
    $items = [];

    foreach (array_slice($data["items"] ?? [], 0, 20) as $item) {
        $items[] = [
            "title" => "[" . $feedTitle . "] " . ($item["title"] ?? "Sin título"),
            "date" => formatearFecha($item["date_published"] ?? ""),
            "link" => $item["url"] ?? "#",
            "description" => $item["content_text"] ?? "Sin descripción"
        ];
    }

    return $items;
}

function procesarXML($xml)
{
    $feedTitle = $xml->channel->title ?? "Fuente desconocida";
    $items = [];

    // Iterar sobre cada item en el canal
    foreach ($xml->channel->item as $item) {
        $items[] = [
            "title" => "[" . $feedTitle . "] " . (string) $item->title,
            "date" => formatearFecha((string) $item->pubDate),
            "link" => (string) $item->link,
            "description" => (string) $item->description,
            // Obtenemos las categorías, que pueden ser varias
            "categories" => array_map('strval', iterator_to_array($item->category ?? [])),
            // Obtenemos el contenido extendido (content:encoded), si existe
            "content" => (string) ($item->children('content', true)->encoded ?? '')
        ];
    }

    // Limitar los items a los primeros 20
    return array_slice($items, 0, 20);
}

function formatearFecha($fecha)
{
    if (empty($fecha)) return "Fecha desconocida";

    try {
        $date = new DateTime($fecha);
        return $date->format("d-m-Y");
    } catch (Exception $e) {
        return "Fecha inválida";
    }
}

function obtenerContenidoURL($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP RSS Reader");
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

$url = $_GET["url"] ?? "";
if (!empty($url)) {
    echo json_encode(obtenerRSS($url));
} else {
    echo json_encode(["error" => "URL no proporcionada"]);
}
