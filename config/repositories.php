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
        // BudgetBean future repositories...
    ],

    'default_strategy' => 'authenticated',
    'guest_strategy' => 'guest',
];
