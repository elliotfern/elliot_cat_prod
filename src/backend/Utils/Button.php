<?php

namespace App\Utils;

class Button
{
    public static function create(string $text, string $url): string
    {
        return self::base($text, $url, 'secondary');
    }

    public static function edit(string $text, string $url): string
    {
        return self::base($text, $url, 'warning');
    }

    public static function delete(string $text, string $url): string
    {
        return self::base($text, $url, 'danger');
    }

    private static function base(string $text, string $url, string $type): string
    {
        return sprintf(
            '<a href="%s" class="btn btn-%s btn-sm">%s</a>',
            htmlspecialchars($url),
            $type,
            htmlspecialchars($text)
        );
    }
}
