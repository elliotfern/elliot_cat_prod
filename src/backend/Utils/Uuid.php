<?php

namespace App\Utils;

class Uuid
{
    public static function toString(?string $bin): ?string
    {

        if ($bin === null) {
            return null;
        }

        if (strlen($bin) !== 16) {
            throw new \InvalidArgumentException('UUID binary must be 16 bytes');
        }

        $hex = bin2hex($bin);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    public static function toBinary(?string $uuid): ?string
    {

        if ($uuid === null || $uuid === '') {
            return null;
        }

        $uuid = str_replace('-', '', strtolower($uuid));

        if (strlen($uuid) !== 32 || !ctype_xdigit($uuid)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }

        $bin = hex2bin($uuid);

        if ($bin === false) {
            throw new \RuntimeException('UUID conversion failed');
        }

        return $bin;
    }

    public static function fromBytes(string $bytes): string
    {
        return \Ramsey\Uuid\Uuid::fromBytes($bytes)->toString();
    }
}
