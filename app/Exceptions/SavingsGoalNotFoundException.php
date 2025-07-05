<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class SavingsGoalNotFoundException extends Exception
{
    public function __construct(string $message = "Savings goal not found", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
