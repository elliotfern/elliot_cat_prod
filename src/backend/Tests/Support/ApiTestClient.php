<?php

declare(strict_types=1);

namespace App\Tests\Support;

final class ApiTestClient
{
    private string $baseUrl;

    public function __construct(string $baseUrl = 'http://localhost')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl . $path;

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->request('GET', $url);
    }

    public function post(string $path, array $data = []): array
    {
        $url = $this->baseUrl . $path;
        return $this->request('POST', $url, $data);
    }

    public function put(string $path, array $data = []): array
    {
        $url = $this->baseUrl . $path;
        return $this->request('PUT', $url, $data);
    }

    private function request(string $method, string $url, array $data = []): array
    {
        $ch = curl_init();

        $payload = json_encode($data);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        return [
            'status' => $httpCode,
            'body' => json_decode($response ?: '', true),
            'raw' => $response,
            'error' => $error,
        ];
    }
}
