<?php

namespace App\Enums;

enum ExpenseTypeEnum: string
{
    case RECURRING = 'recurring_bill';
    case ONE_TIME  = 'one_time_expense';
}
