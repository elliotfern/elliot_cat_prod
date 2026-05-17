<?php

namespace App\Utils;

class Validator
{
    public static function required(array &$errors, string $field, $value): void
    {
        if ($value === null || $value === '') {
            $errors[$field][] = ValidacioErrors::requerit($field);
        }
    }

    public static function email(array &$errors, string $field, $value): void
    {
        if ($value === null || $value === '') {
            $errors[$field][] = ValidacioErrors::requerit($field);
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field][] = ValidacioErrors::invalid($field);
        }
    }

    public static function maxLength(array &$errors, string $field, $value, int $max): void
    {
        if ($value !== null && mb_strlen($value) > $max) {
            $errors[$field][] = ValidacioErrors::massaLlarg($field, $max);
        }
    }
}
