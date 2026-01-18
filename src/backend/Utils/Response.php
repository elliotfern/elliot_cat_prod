<?php

namespace App\Utils;

class Response
{
    public static function success(string $message = '', $data = null, int $httpCode = 200, array $errors = []): void
    {
        http_response_code($httpCode);

        self::send([
            'status' => 'success',
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public static function error(string $message = '', array $errors = [], int $httpCode = 400, $data = null): void
    {
        http_response_code($httpCode);

        self::send([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    private static function send(array $payload): void
    {
        // Evitar "headers already sent" silenciosos en algunos setups
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        // Garantim sempre aquestes claus, per coherència
        $response = array_merge([
            'status' => 'success',
            'message' => '',
            'errors' => [],
            'data' => null,
        ], $payload);

        $json = json_encode(
            $response,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        // Si json_encode falla (UTF-8 roto, etc.), devolvemos error controlado
        if ($json === false) {
            http_response_code(500);
            $json = json_encode([
                'status' => 'error',
                'message' => 'JSON encode error',
                'errors' => [
                    'json_last_error' => json_last_error(),
                    'json_last_error_msg' => json_last_error_msg(),
                ],
                'data' => null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        echo $json;
        exit;
    }
}
