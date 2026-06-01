<?php

namespace App\Domain\Shared\Exception;

abstract class DomainException extends \RuntimeException {}

class ValidationException extends DomainException
{
    public function __construct(
        string $message = 'Validation error',
        private array $errors = []
    ) {
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
