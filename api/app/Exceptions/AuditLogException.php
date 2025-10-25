<?php

namespace App\Exceptions;

use Exception;

class AuditLogException extends Exception
{
    public function __construct(string $message = 'Audit log operation failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
