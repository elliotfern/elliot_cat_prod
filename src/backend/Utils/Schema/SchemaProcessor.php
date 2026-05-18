<?php

namespace App\Utils\Schema;

use App\Utils\Schema\SchemaValidationException;
use App\Utils\Schema\RuleParser;

class SchemaProcessor
{
    public static function process(
        array $input,
        array $schema
    ): array {

        $result = [];
        $errors = [];

        foreach ($schema as $field => $rulesRaw) {

            $rules = RuleParser::parse($rulesRaw);

            $value = $input[$field] ?? null;

            /**
             * 🔥 NORMALIZACIÓN HARD (CRÍTICA)
             * - convierte strings vacíos y whitespace a null SIEMPRE
             */
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }

            /**
             * default
             */
            if ($value === null && array_key_exists('default', $rules)) {
                $value = $rules['default'];
            }

            /**
             * normalize
             */
            $value = Normalizer::normalize($value, $rules);

            /**
             * validate
             */
            $fieldErrors = Validator::validate($value, $rules);

            if (!empty($fieldErrors)) {
                $errors[$field] = [
                    'label' => $rules['label'] ?? $field,
                    'messages' => $fieldErrors
                ];
                continue;
            }

            $result[$field] = $value;
        }

        if (!empty($errors)) {
            throw new SchemaValidationException($errors);
        }

        return $result;
    }
}
