<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class InsufficientWaterException extends Exception
{
    public function __construct(string $message = "Not enough water in your Water Bank! 💧", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
