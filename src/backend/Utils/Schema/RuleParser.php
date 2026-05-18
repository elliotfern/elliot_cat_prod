<?php

namespace App\Utils\Schema;

class RuleParser
{
    public static function parse(array $rules): array
    {
        $parsed = [
            'rules' => [],
            'label' => null,
            'type' => null,
        ];

        foreach ($rules as $rule) {

            /**
             * LABEL (metadata)
             */
            if (str_starts_with($rule, 'label:')) {
                $parsed['label'] = substr($rule, 6);
                continue;
            }

            /**
             * MAX (rule with parameter)
             */
            if (str_starts_with($rule, 'max:')) {
                $parsed['rules'][] = [
                    'name' => 'max',
                    'value' => (int) substr($rule, 4),
                ];
                continue;
            }

            /**
             * SIMPLE RULES
             */
            if ($rule === 'required') {
                $parsed['rules'][] = ['name' => 'required'];
                continue;
            }

            if ($rule === 'nullable') {
                $parsed['rules'][] = ['name' => 'nullable'];
                continue;
            }

            if ($rule === 'email') {
                $parsed['rules'][] = ['name' => 'email'];
                continue;
            }

            if ($rule === 'date') {
                $parsed['rules'][] = ['name' => 'date'];
                continue;
            }

            /**
             * TYPE RULES (solo uno por campo)
             */
            if ($rule === 'string') {
                $parsed['type'] = 'string';
                continue;
            }

            if ($rule === 'int') {
                $parsed['type'] = 'int';
                continue;
            }

            if ($rule === 'uuid') {
                $parsed['type'] = 'uuid';
                continue;
            }

            if ($rule === 'bool') {
                $parsed['type'] = 'bool';
                continue;
            }
        }

        return $parsed;
    }
}
