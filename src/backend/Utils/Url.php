<?php

namespace App\Utils;

class Url
{
    public static function to(string $key): string
    {
        return AppDomains::main() . AppRoutes::get($key);
    }

    public static function intranet(string $key): string
    {
        return AppDomains::main() . AppRoutes::intranet($key);
    }

    public static function media(string $path): string
    {
        return AppDomains::media() . '/' . ltrim($path, '/');
    }

    public static function api(string $path): string
    {
        return AppDomains::api() . '/' . ltrim($path, '/');
    }
}
