<?php

namespace App\Exceptions\Domain;

class InvariantViolationException extends DomainException
{
    protected $code = 422;
    protected $message = 'Business rule violation';

    public function getErrorCode(): string
    {
        return 'invariantViolation';
    }
}
