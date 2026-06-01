<?php

namespace App\Utils;

use Ramsey\Uuid\Uuid as RamseyUuid;

final class Uuid
{
    public static function generate(): string
    {
        return RamseyUuid::uuid7()->toString();
    }

    public static function toBinary(string $uuid): string
    {
        return RamseyUuid::fromString($uuid)->getBytes();
    }

    public static function toString(string $binary): string
    {
        return RamseyUuid::fromBytes($binary)->toString();
    }
}
