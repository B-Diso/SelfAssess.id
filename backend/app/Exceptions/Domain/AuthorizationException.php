<?php

namespace App\Exceptions\Domain;

class AuthorizationException extends DomainException
{
    protected $code = 403;
    protected $message = 'Unauthorized access';

    public function getErrorCode(): string
    {
        return 'authorizationError';
    }
}
