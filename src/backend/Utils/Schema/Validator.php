<?php

namespace App\Utils\Schema;

class Validator
{
    public static function validate(
        mixed $value,
        array $rules
    ): array {

        $errors = [];

        $ruleList = $rules['rules'] ?? [];
        $type = $rules['type'] ?? null;

        $isNullable = self::hasRule($ruleList, 'nullable');

        /*
         * REQUIRED
         */
        if (self::hasRule($ruleList, 'required')) {

            if ($value === null || $value === '') {
                return ['Camp obligatori'];
            }
        }

        /*
         * NULL handling
         */
        if ($value === null) {
            return $isNullable ? [] : [];
        }

        /*
         * TYPE VALIDATION (delegated)
         */
        $errors = array_merge(
            $errors,
            self::validateType($value, $type, $ruleList)
        );

        /*
         * RULE VALIDATION (engine)
         */
        foreach ($ruleList as $rule) {

            $name = $rule['name'];

            switch ($name) {

                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Email invàlid';
                    }
                    break;

                case 'max':
                    if (is_string($value)) {
                        if (mb_strlen($value) > $rule['value']) {
                            $errors[] = 'Longitud màxima: ' . $rule['value'];
                        }
                    }
                    break;

                case 'date':
                    if (!self::isValidDate($value)) {
                        $errors[] = 'Data invàlida';
                    }
                    break;
            }
        }

        return $errors;
    }

    private static function validateType(
        mixed $value,
        ?string $type,
        array $rules
    ): array {

        return match ($type) {

            'string' => self::validateString($value),
            'int'    => self::validateInt($value),
            'float'  => self::validateFloat($value),
            'bool'   => self::validateBool($value),
            'uuid'   => self::validateUuid($value),

            default => []
        };
    }

    private static function validateString(mixed $value): array
    {
        return is_string($value)
            ? []
            : ['Ha de ser un string'];
    }

    private static function validateInt(mixed $value): array
    {
        return is_int($value)
            ? []
            : ['Ha de ser un enter'];
    }

    private static function validateFloat(mixed $value): array
    {
        return is_float($value)
            ? []
            : ['Ha de ser un número'];
    }

    private static function validateBool(mixed $value): array
    {
        return is_bool($value)
            ? []
            : ['Ha de ser un boolean'];
    }

    private static function validateUuid(mixed $value): array
    {
        if (!is_string($value)) {
            return ['UUID invàlid'];
        }

        if (!preg_match('/^[0-9a-f-]{36}$/', $value)) {
            return ['UUID invàlid'];
        }

        return [];
    }

    private static function isValidDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false;
    }

    private static function hasRule(array $rules, string $name): bool
    {
        foreach ($rules as $rule) {
            if (($rule['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }
}
