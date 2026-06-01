<?php

namespace App\Utils;

class AppDomains
{
    public static function main(): string
    {
        return $_ENV['DOMAIN_WEB'] ?? 'https://elliot.cat';
    }

    public static function media(): string
    {
        return $_ENV['DOMAIN_IMG'] ?? 'https://media.elliot.cat';
    }

    public static function api(): string
    {
        return $_ENV['API_BASE'] ?? 'https://api.elliot.cat';
    }
}
