<?php

namespace App\Exceptions;

use Exception;

class CredentialNotFoundException extends Exception
{
    public function __construct(string $message = 'Credentials not found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
