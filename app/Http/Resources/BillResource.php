<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'formatted_bill_date' => $this->date,
            'description' => $this->description,
            'type' => 'recurring_bill',
            'created_at' => $this->created_at,
        ];
    }
}
