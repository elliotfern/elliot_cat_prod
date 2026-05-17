<?php

namespace App\Utils;

class Validator
{
    public static function required(array &$errors, string $field, $value, ?string $label = null): void
    {
        if (
            $value === null ||
            $value === '' ||
            $value === 0 ||
            $value === '0'
        ) {
            $errors[$field][] = ValidacioErrors::requerit($label ?? $field);
        }
    }

    public static function requiredInt(array &$errors, string $field, $value, ?string $label = null): void
    {
        if (
            $value === null ||
            $value === '' ||
            $value === 0 ||
            $value === '0'
        ) {
            $errors[$field][] = ValidacioErrors::requerit($label ?? $field);
        }
    }

    public static function requiredUuid(array &$errors, string $field, $value, ?string $label = null): void
    {
        if ($value === null || $value === '') {
            $errors[$field][] = ValidacioErrors::requerit($label ?? $field);
            return;
        }

        if (!is_string($value)) {
            $errors[$field][] = ValidacioErrors::invalid($label ?? $field);
            return;
        }

        $value = strtolower(trim($value));

        if ($value === '') {
            $errors[$field][] = ValidacioErrors::requerit($label ?? $field);
            return;
        }

        // UUID v7 compatible (tu regex actual)
        if (!preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        )) {
            $errors[$field][] = ValidacioErrors::invalid($label ?? $field);
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
            $errors[$field][] = ValidacioErrors::dataNoValida($field);
        }
    }

    public static function schema(array $data, array &$errors, array $rules): void
    {
        foreach ($rules as $field => $fieldRules) {

            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {

                // required
                if ($rule === 'required') {
                    self::required($errors, $field, $value);
                    continue;
                }

                if ($rule === 'required_int') {
                    self::requiredInt($errors, $field, $value);
                    continue;
                }

                if ($rule === 'required_uuid') {
                    self::requiredUuid($errors, $field, $value);
                    continue;
                }

                // string
                if ($rule === 'string') {
                    if ($value !== null && !is_string($value)) {
                        $errors[$field][] = ValidacioErrors::invalid($field);
                    }
                    continue;
                }

                // int
                if ($rule === 'int') {
                    if ($value === null || $value === '') {
                        continue; // required se encarga
                    }
                    if (!is_int($value) && !ctype_digit((string)$value)) {
                        $errors[$field][] = ValidacioErrors::invalid($field);
                    }
                    continue;
                }

                // email
                if ($rule === 'email') {
                    self::email($errors, $field, $value);
                    continue;
                }

                // date
                if ($rule === 'date') {
                    self::date($errors, $field, $value);
                    continue;
                }

                // max:255
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) explode(':', $rule)[1];
                    self::maxLength($errors, $field, $value, $max);
                    continue;
                }
            }
        }
    }
}
