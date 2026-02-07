<?php

namespace App\Exceptions\Domain;

class InvalidCredentialsException extends DomainException
{
    protected $code = 401;
    protected $message = 'Invalid credentials';

    public function getErrorCode(): string
    {
        return 'invalidCredentials';
    }
}
