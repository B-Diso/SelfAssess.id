<?php

namespace App\Exceptions\Domain;

class ValidationDomainException extends DomainException
{
    protected $code = 422;
    protected $message = 'Validation failed';

    private ?array $errors = null;

    public function __construct(string $message = '', ?array $errors = null)
    {
        parent::__construct($message ?: $this->message);
        $this->errors = $errors;
    }

    public function getErrorCode(): string
    {
        return 'validationError';
    }

    public function getDetails(): ?array
    {
        return $this->errors;
    }
}
