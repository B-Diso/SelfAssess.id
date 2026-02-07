<?php

namespace App\Exceptions\Domain;

use Exception;

abstract class DomainException extends Exception
{
    protected $code = 400;

    public function getErrorCode(): string
    {
        return 'domainError';
    }

    public function getDetails(): ?array
    {
        return null;
    }
}
