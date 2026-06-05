<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Jwt;

interface JwtDecoderInterface
{
    public function decode(string $token): array;
}
