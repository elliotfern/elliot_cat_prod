<?php

declare(strict_types=1);

/**
 * Sitemap XML de elliot.cat.
 *
 * Obtiene los contenidos dinámicos exclusivamente a través de la API.
 */

$baseUrl = $_ENV['DOMAIN_WEB'] ?? null;
$apiBaseUrl = rtrim($_ENV['API_BASE'] ?? '', '/') . '/blog/get/';


const SITEMAP_API_ENDPOINTS = [
    'articles' => 'llistatArticlesSitemap',
    'courses' => 'llistatCursosSitemap',
    'history_articles' => 'llistatHistoriaSitemap',
];

/**
 * Consulta un endpoint de la API y devuelve sus registros.
 *
 * @return list<array{slug: string, lastmod?: string|null}>
 *
 * @throws RuntimeException Si la consulta o la respuesta fallan.
 */
function obtenirDadesSitemap(string $endpoint): array
{

    $baseUrl = $_ENV['DOMAIN_WEB'] ?? null;
    $apiBaseUrl = rtrim($_ENV['API_BASE'] ?? '', '/') . '/blog/get/';

    $url = $apiBaseUrl . $endpoint;

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'ignore_errors' => true,
            'header' => [
                'Accept: application/json',
            ],
        ],
    ]);

    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        throw new RuntimeException(
            sprintf('No s’ha pogut consultar l’endpoint: %s', $endpoint)
        );
    }

    $httpStatus = 0;

    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
            $httpStatus = (int) $matches[1];
        }
    }

    if ($httpStatus < 200 || $httpStatus >= 300) {
        throw new RuntimeException(
            sprintf(
                'L’endpoint %s ha retornat HTTP %d.',
                $endpoint,
                $httpStatus
            )
        );
    }

    try {
        $payload = json_decode(
            $response,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    } catch (JsonException $e) {
        throw new RuntimeException(
            sprintf('La resposta de %s no és JSON vàlid.', $endpoint),
            0,
            $e
        );
    }

    if (
        !is_array($payload)
        || ($payload['success'] ?? false) !== true
        || !isset($payload['data'])
        || !is_array($payload['data'])
    ) {
        throw new RuntimeException(
            sprintf('La resposta de %s no té el format esperat.', $endpoint)
        );
    }

    $records = [];

    foreach ($payload['data'] as $item) {
        if (
            !is_array($item)
            || !isset($item['slug'])
            || !is_string($item['slug'])
        ) {
            throw new RuntimeException(
                sprintf('Hi ha un registre invàlid a %s.', $endpoint)
            );
        }

        $slug = trim($item['slug']);

        if ($slug === '') {
            continue;
        }

        $lastmod = $item['lastmod'] ?? null;

        if ($lastmod !== null && !is_string($lastmod)) {
            throw new RuntimeException(
                sprintf('La data lastmod no és vàlida a %s.', $endpoint)
            );
        }

        $records[] = [
            'slug' => $slug,
            'lastmod' => $lastmod,
        ];
    }

    return $records;
}

/**
 * Converteix una data de la base de dades a format W3C.
 */
function formatarLastmod(?string $date): ?string
{
    if ($date === null || trim($date) === '') {
        return null;
    }

    try {
        $dateTime = new DateTimeImmutable($date);
    } catch (Exception) {
        return null;
    }

    return $dateTime->format('c');
}

/**
 * Afegeix una URL al sitemap.
 */
function afegirUrl(
    XMLWriter $xml,
    string $path,
    ?string $lastmod = null
): void {

    $baseUrl = $_ENV['DOMAIN_WEB'] ?? null;

    $xml->startElement('url');

    $xml->writeElement(
        'loc',
        $baseUrl . $path
    );

    $formattedLastmod = formatarLastmod($lastmod);

    if ($formattedLastmod !== null) {
        $xml->writeElement('lastmod', $formattedLastmod);
    }

    $xml->endElement();
}

try {
    // Obtenim les tres llistes abans de generar cap resposta.
    $articles = obtenirDadesSitemap(
        SITEMAP_API_ENDPOINTS['articles']
    );

    $courses = obtenirDadesSitemap(
        SITEMAP_API_ENDPOINTS['courses']
    );

    $historyArticles = obtenirDadesSitemap(
        SITEMAP_API_ENDPOINTS['history_articles']
    );

    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->setIndent(true);

    $xml->startElement('urlset');
    $xml->writeAttribute(
        'xmlns',
        'http://www.sitemaps.org/schemas/sitemap/0.9'
    );

    // Pàgines estàtiques.
    foreach (
        [
            '/',
            '/autor',
            '/biblioteca',
            '/blog',
            '/historia',
        ] as $path
    ) {
        afegirUrl($xml, $path);
    }

    // Articles del blog.
    foreach ($articles as $article) {
        afegirUrl(
            $xml,
            '/blog/' . rawurlencode($article['slug']),
            $article['lastmod']
        );
    }

    // Cursos d'Història Oberta.
    foreach ($courses as $course) {
        afegirUrl(
            $xml,
            '/historia/curs/' . rawurlencode($course['slug']),
            $course['lastmod']
        );
    }

    // Articles d'Història Oberta.
    foreach ($historyArticles as $article) {
        afegirUrl(
            $xml,
            '/historia/article/' . rawurlencode($article['slug']),
            $article['lastmod']
        );
    }

    $xml->endElement(); // urlset
    $xml->endDocument();

    $output = $xml->outputMemory();

    header('Content-Type: application/xml; charset=UTF-8');
    header('Cache-Control: public, max-age=3600');

    echo $output;
} catch (Throwable $e) {
    error_log('[SITEMAP] ' . $e->getMessage());

    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');

    echo 'No s’ha pogut generar el sitemap.';
}
