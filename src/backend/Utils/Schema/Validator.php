<?php

namespace App\Utils\Schema;

class Validator
{
    public static function validate(
        mixed $value,
        array $rules
    ): array {

        $errors = [];

        $type = $rules['type'] ?? null;

        /*
         * Required
         */
        if (($rules['required'] ?? false) === true) {

            if ($value === null) {

                $errors[] = 'Camp obligatori';

                return $errors;
            }
        }

        /*
         * Null allowed
         */
        if ($value === null) {
            return $errors;
        }

        /*
         * Type validation
         */
        switch ($type) {

            case FieldType::STRING:

                $errors = array_merge(
                    $errors,
                    self::validateString($value, $rules)
                );

                break;

            case FieldType::INT:

                $errors = array_merge(
                    $errors,
                    self::validateInt($value)
                );

                break;

            case FieldType::FLOAT:

                $errors = array_merge(
                    $errors,
                    self::validateFloat($value)
                );

                break;

            case FieldType::BOOL:

                $errors = array_merge(
                    $errors,
                    self::validateBool($value)
                );

                break;

            case FieldType::UUID:

                $errors = array_merge(
                    $errors,
                    self::validateUuid($value)
                );

                break;
        }

        return $errors;
    }

    private static function validateString(
        mixed $value,
        array $rules
    ): array {

        $errors = [];

        if (!is_string($value)) {
            $errors[] = 'Ha de ser un string';
            return $errors;
        }

        if (isset($rules['max'])) {

            if (mb_strlen($value) > $rules['max']) {

                $errors[] =
                    'Longitud màxima: ' .
                    $rules['max'];
            }
        }

        return $errors;
    }

    private static function validateInt(
        mixed $value
    ): array {

        if (!is_int($value)) {
            return ['Ha de ser un enter'];
        }

        return [];
    }

    private static function validateFloat(
        mixed $value
    ): array {

        if (!is_float($value)) {
            return ['Ha de ser un número'];
        }

        return [];
    }

    private static function validateBool(
        mixed $value
    ): array {

        if (!is_bool($value)) {
            return ['Ha de ser un boolean'];
        }

        return [];
    }

    private static function validateUuid(
        mixed $value
    ): array {

        if (!is_string($value)) {
            return ['UUID invàlid'];
        }

        if (!preg_match(
            '/^[0-9a-f-]{36}$/',
            $value
        )) {
            return ['UUID invàlid'];
        }

        return [];
    }
}
