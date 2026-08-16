<?php

use App\Config\Database;
use App\Utils\Mailer;
use App\Utils\Response;
use App\Utils\MissatgesAPI;


/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

/*
 * BACKEND SALUT
 * POST SALUT
 */

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * POST : Demanar recepta de medicaments
 * URL: https://elliot.cat/api/salut/post/receptaMedicament?id=1
 */
if ($slug === "receptaMedicament") {
    try {

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            Response::error(
                MissatgesAPI::error('bad_request'),
                ['id' => 'required'],
                400
            );
            return;
        }

        // Mateixa font de dades que llistatMedicaments (placeholder fins que hi hagi taula a la BD).
        // Cada entrada porta ja tot el necessari per construir l'email d'aquest metge/medicament.
        $receptes = [
            '1' => [
                'metgeNom'   => 'Dott. Favara',
                'subject'    => 'Richiesta rinnovo ricetta',
                'patologia'  => 'ipercolesterolemia',
                'genere'     => 'f', // 'f' o 'm', per la concordança gramatical italiana
                'quantitat'  => '2 confezioni',
                'medicament' => 'EZETIMIBE e ATORVASTATINA DOC 10 mg/20 mg capsule rigide.',
                'paziente'   => 'Elliot Fernandez Hernandez',
            ],
        ];

        if (!isset($receptes[$id])) {
            Response::error(
                MissatgesAPI::error('notFound'),
                ['id' => 'not_found'],
                404
            );
            return;
        }

        $r = $receptes[$id];
        $connettore = ($r['genere'] ?? 'f') === 'm' ? 'del mio' : 'della mia';
        $telefon = '377 813 0921';

        $bodyText = "All'attenzione del {$r['metgeNom']},\n\n"
            . "Buongiorno,\n"
            . "Avrei bisogno di una nuova ricetta per il trattamento {$connettore} {$r['patologia']}.\n"
            . "Il farmaco è il seguente:\n\n"
            . "* {$r['quantitat']} di {$r['medicament']}\n\n"
            . "La ringrazio anticipatamente per la disponibilità.\n\n"
            . "Cordiali saluti,\n"
            . "Paziente assistito: {$r['paziente']}\n"
            . "Telefono: {$telefon}";

        $bodyHtml = nl2br(htmlspecialchars($bodyText));

        $mailer = new Mailer();

        $sent = $mailer->send(
            to: 'AMBULATORIOZIPPEL@gmail.com',
            //to: 'elliot@hispantic.com',
            toName: $r['metgeNom'],
            subject: $r['subject'],
            htmlBody: $bodyHtml,
            plainText: $bodyText,
            bcc: ['elliot@hispantic.com' => 'Còpia sol·licitud recepta'],
            replyTo: 'elliot@hispantic.com',
        );

        if (!$sent) {
            Response::error(
                MissatgesAPI::error('errorEnviament'),
                ['mail' => 'send_failed'],
                500
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('create'),
            data: ['sent' => true],
            httpCode: 200
        );
    } catch (Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else {
    // Slug no reconocido
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
