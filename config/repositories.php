<?php
return [
    'mappings' => [
        \App\Repositories\SavingsGoal\SavingsGoalRepositoryInterface::class => [
            'authenticated' => \App\Repositories\SavingsGoal\DBSavingsGoalRepository::class,
            'guest' => \App\Repositories\SavingsGoal\SessionSavingsGoalRepository::class,
        ],
        \App\Repositories\Bill\BillRepositoryInterface::class => [
            'authenticated' => \App\Repositories\Bill\DBBillRepository::class,
            'guest' => \App\Repositories\Bill\SessionBillRepository::class,
        ],
        \App\Repositories\Expense\ExpenseRepositoryInterface::class => [
            'authenticated' => \App\Repositories\Expense\DBExpenseRepository::class,
            'guest' => \App\Repositories\Expense\SessionExpenseRepository::class,
        ],
        // BudgetBean future repositories...
    ],

    'default_strategy' => 'authenticated',
    'guest_strategy' => 'guest',
];
