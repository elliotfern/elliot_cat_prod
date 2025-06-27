<?php

namespace App\Utils;

class Response
{
    public static function success(string $message = '', $data = null): void
    {
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

    private static function send(array $payload): void
    {
        header('Content-Type: application/json');
        // Garantim sempre aquestes claus, per coherència
        $response = array_merge([
            'status' => 'success',
            'message' => '',
            'errors' => [],
            'data' => null,
        ], $payload);

        echo json_encode($response);
        exit;
    }
}
