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

        return $v === '' ? null : (int)$v;
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

        return (string)$v;
    }

    public static function date($v): ?string
    {
        if ($v === null) return null;

        $v = trim((string)$v);

        return $v === '' ? null : $v;
    }

    public static function email($v): ?string
    {
        if ($v === null) return null;

        $v = trim((string)$v);

        return $v === '' ? null : $v;
    }

    public static function uuid($v): ?string
    {
        if ($v === null) return null;

        $v = strtolower(trim((string)$v));

        return $v === '' ? null : $v;
    }
}
