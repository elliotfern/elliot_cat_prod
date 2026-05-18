<?php

namespace App\Utils\Schema;

use Exception;

class SchemaValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation error');
        $this->errors = $errors;
    }

    public function getFormattedErrors(): array
    {
        return $this->errors;
    }
}
