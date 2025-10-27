<?php

namespace App\Exceptions;

use Exception;

class JitAccountException extends Exception
{
    public function __construct(string $message = 'JIT account operation failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
