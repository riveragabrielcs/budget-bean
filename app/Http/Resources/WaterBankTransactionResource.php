<?php

namespace App\Http\Resources;

use App\DTOs\WaterBankTransactionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaterBankTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var WaterBankTransactionDTO $transaction */
        $transaction = $this->resource;

        return [
            'id' => $transaction->id,
            'water_bank_id' => $transaction->water_bank_id,
            'type' => $transaction->type->value,
            'amount' => $transaction->amount,
            'formatted_amount' => $transaction->getFormattedAmount(),
            'source' => $transaction->source->value,
            'source_label' => $transaction->source->label(),
            'description' => $transaction->description,
            'display_description' => $transaction->getDisplayDescription(),
            'savings_goal_id' => $transaction->savings_goal_id,
            'savings_goal_name' => $transaction->savings_goal_name,
            'balance_after' => $transaction->balance_after,
            'icon' => $transaction->getIcon(),
            'created_at' => $transaction->created_at,
            'formatted_date' => $transaction->getFormattedDate(),
        ];
    }
}
