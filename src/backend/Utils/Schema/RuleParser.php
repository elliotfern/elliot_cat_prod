<?php

namespace App\Utils\Schema;

class RuleParser
{
    public static function parse(string|array $rules): array
    {
        $rules = is_string($rules)
            ? explode('|', $rules)
            : $rules;

        $parsed = [
            'rules' => [],
            'label' => null,
        ];

        foreach ($rules as $rule) {

            $rule = trim($rule);

            /**
             * LABEL (metadata)
             */
            if (str_starts_with($rule, 'label:')) {
                $parsed['label'] = substr($rule, 6);
                continue;
            }

            /**
             * PARAMETRIC RULES (key:value)
             */
            if (str_contains($rule, ':')) {

                [$name, $value] = explode(':', $rule, 2);

                $parsed['rules'][] = [
                    'name'  => $name,
                    'value' => is_numeric($value) ? (int)$value : $value,
                ];

                continue;
            }

            /**
             * SIMPLE RULES
             */
            $parsed['rules'][] = [
                'name' => $rule,
            ];
        }

        return $parsed;
    }
}
