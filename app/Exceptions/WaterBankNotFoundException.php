<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class WaterBankNotFoundException extends Exception
{
    public function __construct(string $message = "Water Bank not found", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
