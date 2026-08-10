<?php
// app/Utils/Locales.php
namespace App\Utils;

class Locales
{
    private const ID_TO_UUID = [
        1 => '019e204b-3047-713a-b67e-f75a39578aea', // ca
        2 => '019e204b-3047-713a-b67e-f75a39e4e1e6', // en
        3 => '019e204b-3046-725d-b407-33fee2d9a8f8', // es
        4 => '019e204b-3046-725d-b407-33fee2e5832b', // it
    ];

    public static function toUuid(int $locale): ?string
    {
        return self::ID_TO_UUID[$locale] ?? null;
    }
}
