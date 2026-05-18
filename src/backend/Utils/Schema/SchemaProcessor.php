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

            if ($value === null && isset($rules['default'])) {
                $value = $rules['default'];
            }

            $value = Normalizer::normalize($value, $rules);

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

        /*
         * Validation failed
         */
        if (!empty($errors)) {

            throw new SchemaValidationException($errors);
        }

        return $result;
    }
}
