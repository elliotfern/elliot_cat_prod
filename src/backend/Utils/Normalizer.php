<?php

namespace App\Utils;

class Normalizer
{
    public static function string($v): ?string
    {
        if ($v === null) return null;

        $v = trim((string)$v);
        return $v === '' ? null : $v;
    }

    public static function int($v): ?int
    {
        if ($v === null) return null;

        $v = trim((string)$v);

        return ($v === '' || !is_numeric($v))
            ? null
            : (int)$v;
    }

    public static function bool($v): bool
    {
        return filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    public static function decimal($v): ?string
    {
        if ($v === null) return null;

        $v = trim((string)$v);

        if ($v === '') return null;

        $v = str_replace([" ", "\u{00A0}"], '', $v);
        $v = str_replace(',', '.', $v);

        return is_numeric($v) ? (string)$v : null;
    }

    public static function date($v): ?string
    {
        if ($v === null) return null;

        $v = trim((string)$v);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return null;
        }

        [$y, $m, $d] = explode('-', $v);

        return checkdate((int)$m, (int)$d, (int)$y)
            ? $v
            : null;
    }

    public static function email($v): ?string
    {
        $v = self::string($v);

        if ($v === null) return null;

        return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : null;
    }

    public static function uuid($v): ?string
    {
        if ($v === null) return null;

        $v = strtolower(trim((string)$v));

        if ($v === '') return null;

        // UUID v4/v7 compatible (8-4-4-4-12)
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $v)) {
            return null;
        }

        return $v;
    }
}
