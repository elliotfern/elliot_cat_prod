<?php

namespace App\Utils;

class Uuid
{
    public static function toString(string $bin): string
    {
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

    public static function toBinary(string $uuid): string
    {
        return hex2bin(str_replace('-', '', $uuid));
    }
}
