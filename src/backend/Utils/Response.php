<?php

namespace App\Utils;

use App\Utils\Uuid;

class Response
{
    private static array $uuidFields = [
        'id',
        'id2',
        'img_id',
        'imatge_id',
        'pais_autor_id',
        'ciutat_naixement_id',
        'ciutat_defuncio_id',
        'user_id',
        'event_id',
        'ciutat_id',
        'provincia_id',
        'pais_id',
        'tema_id',
        'grup_ids',
        'grup_id',
        'grup',
        'tipus_id',
        'sub_tema_id',
        'idGrup',
        'estat_id',
        'editorial_id',
        'espai_id',
        'viatge_id',
        'director_id',
        'genere_id',
        'lang_id',
        'idioma_id',
        'actor_id',
        'client_id',
        'servei_id',
        'categoria_id',
        'proveidor_id',
        'emissor_id',
        'receptor_id',
        'subcategoria_id',
    ];

    public static function success(
        string $message = '',
        mixed $data = null,
        array $meta = [],
        int $httpCode = 200
    ): void {
        http_response_code($httpCode);

        $data = self::mapUuid($data);

        self::send([
            'success' => true,
            'message' => $message,
            'errors' => [],
            'meta' => $meta,
            'data' => $data,
        ]);
    }

    public static function error(
        string $message = '',
        array $errors = [],
        int $httpCode = 400
    ): void {
        http_response_code($httpCode);

        self::send([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [],
            'data' => null,
        ]);
    }

    /**
     * Convierte automáticamente UUID binarios a string UUID
     */
    private static function mapUuid(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => &$value) {

            if (
                in_array($key, self::$uuidFields, true)
                && self::isBinaryUuid($value)
            ) {
                $value = Uuid::toString($value);
                continue;
            }

            if (is_array($value)) {
                $value = self::mapUuid($value);
            }
        }

        return $data;
    }

    private static function send(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $response = array_merge([
            'success' => true,
            'message' => '',
            'errors' => [],
            'meta' => [],
            'data' => null,
        ], $payload);

        $response = self::sanitizeUtf8($response);

        $json = json_encode(
            $response,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'JSON_ENCODE_ERROR',
                'errors' => [
                    json_last_error_msg()
                ],
                'meta' => [],
                'data' => null,
            ]);

            exit;
        }

        echo $json;
    }

    private static function sanitizeUtf8(mixed $data): mixed
    {
        if (!is_array($data)) {
            return is_string($data)
                ? self::cleanString($data)
                : $data;
        }

        array_walk_recursive($data, function (&$v) {

            if (is_string($v)) {
                $v = self::cleanString($v);
            }
        });

        return $data;
    }

    private static function cleanString(string $value): string
    {
        // quitar NUL bytes
        $value = str_replace("\0", '', $value);

        // ya es UTF-8 válido
        if (mb_check_encoding($value, 'UTF-8')) {

            return @iconv(
                'UTF-8',
                'UTF-8//IGNORE',
                $value
            ) ?: $value;
        }

        // intentar latin1 -> UTF8
        $converted = @iconv(
            'ISO-8859-1',
            'UTF-8//IGNORE',
            $value
        );

        if ($converted !== false) {
            return $converted;
        }

        // fallback final
        return @iconv(
            'UTF-8',
            'UTF-8//IGNORE',
            $value
        ) ?: $value;
    }

    private static function isBinaryUuid(mixed $value): bool
    {
        return is_string($value)
            && strlen($value) === 16;
    }
}
