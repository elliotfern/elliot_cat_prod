<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtService implements JwtDecoderInterface

{
    public function __construct(
        private string $secret,
        private string $algo = 'HS256'
    ) {}

    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function decode(string $token): array
    {
        $decoded = JWT::decode(
            $token,
            new Key($this->secret, 'HS256')
        );

        return (array) $decoded;
    }
}
