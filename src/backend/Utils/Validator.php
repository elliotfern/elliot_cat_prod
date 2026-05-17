<?php

namespace App\Utils;

class Validator
{
    public static function required(array &$errors, string $field, $value, ?string $label = null): void
    {
        if ($value === null || $value === '') {
            $errors[$field][] = ValidacioErrors::requerit($label ?? $field);
        }
    }

    public static function email(array &$errors, string $field, $value, ?string $label = null): void
    {
        if ($value === null || $value === '') {
            $errors[$field][] = ValidacioErrors::requerit($label ?? $field);
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field][] = ValidacioErrors::invalid($label ?? $field);
        }
    }

    public static function maxLength(array &$errors, string $field, $value, int $max, ?string $label = null): void
    {
        if ($value !== null && mb_strlen($value) > $max) {
            $errors[$field][] = ValidacioErrors::massaLlarg($label ?? $field, $max);
        }
    }

    public static function date(array &$errors, string $field, $value): void
    {
        if ($value === null || $value === '') {
            $errors[$field][] = ValidacioErrors::requerit($field);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $errors[$field][] = ValidacioErrors::invalid($field);
        }
    }
}
