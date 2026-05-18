<?php

namespace App\Utils\Schema;

use Exception;

class SchemaProcessor
{
    public static function process(
        array $input,
        array $schema
    ): array {

        $result = [];

        $errors = [];

        foreach ($schema as $field => $rules) {

            /*
             * Input value
             */
            $value = $input[$field] ?? null;

            /*
             * Default value
             */
            if (
                $value === null &&
                array_key_exists('default', $rules)
            ) {
                $value = $rules['default'];
            }

            /*
             * Normalize
             */
            $value = Normalizer::normalize(
                $value,
                $rules
            );

            /*
             * Validate
             */
            $fieldErrors = Validator::validate(
                $value,
                $rules
            );

            /*
             * Collect errors
             */
            if (!empty($fieldErrors)) {

                $errors[$field] = [
                    'label' => $rules['label'] ?? $field,
                    'messages' => $fieldErrors
                ];

                continue;
            }

            /*
             * Store normalized value
             */
            $result[$field] = $value;
        }

        /*
         * Validation failed
         */
        if (!empty($errors)) {

            throw new Exception(
                json_encode(
                    $errors,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }

        return $result;
    }
}
