<?php

namespace App\Enums;

enum ExpenseType: string
{
    case RECURRING = 'recurring_bill';
    case ONE_TIME  = 'one_time_expense';
}
