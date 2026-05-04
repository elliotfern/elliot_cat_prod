<?php

namespace App\Utils;

use App\Utils\Uuid;

class Response
{

    private static array $uuidFields = [
        'id',
        'img_id',
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
    ];

    public static function success(string $message = '', $data = null, int $httpCode = 200): void
    {
        http_response_code($httpCode);

        $data = self::mapUuid($data);

        self::send([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message = '', array $errors = [], int $httpCode = 400): void
    {
        http_response_code($httpCode);

        self::send([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ]);
    }

    /**
     * Convierte automáticamente UUID binarios a string UUID
     */
    private static function mapUuid($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => &$value) {

            // SOLO si el campo es UUID real
            if (in_array($key, self::$uuidFields, true) && is_string($value) && strlen($value) === 16) {
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
        header('Content-Type: application/json');

        $response = array_merge([
            'status' => 'success',
            'message' => '',
            'errors' => [],
            'data' => null,
        ], $payload);

        $response = self::sanitizeUtf8($response);

        echo json_encode($response);
        exit;
    }

    private static function sanitizeUtf8($data)
    {
        if (!is_array($data)) {
            return self::cleanString($data);
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

        // si ya es UTF-8 válido
        if (mb_check_encoding($value, 'UTF-8')) {
            return @iconv('UTF-8', 'UTF-8//IGNORE', $value) ?: $value;
        }

        // intentar latin1 → UTF-8
        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);
        if ($converted !== false) {
            return $converted;
        }

        // fallback final
        return @iconv('UTF-8', 'UTF-8//IGNORE', $value) ?: $value;
    }

    private static function isBinaryUuid($value): bool
    {
        return is_string($value) && strlen($value) === 16;
    }
}
