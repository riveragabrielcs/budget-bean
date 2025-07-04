<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class BillNotFoundException extends Exception
{
    public function __construct(string $message = "Bill not found", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
