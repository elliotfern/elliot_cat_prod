<?php

namespace App\Utils;

class Url
{
    public static function to(string $key): string
    {
        return AppDomains::MAIN . AppRoutes::get($key);
    }

    public static function intranet(string $key): string
    {
        return AppDomains::MAIN . AppRoutes::intranet($key);
    }

    public static function media(string $path): string
    {
        return AppDomains::MEDIA . '/' . ltrim($path, '/');
    }

    public static function api(string $path): string
    {
        return AppDomains::API . '/' . ltrim($path, '/');
    }
}
