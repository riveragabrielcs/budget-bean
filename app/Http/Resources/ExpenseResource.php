<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'formatted_expense_date' => $this->formatted_expense_date,
            'description' => $this->description,
            'type' => 'one_time_expense',
            'created_at' => $this->created_at,
        ];
    }
}
