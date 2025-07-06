<?php

namespace App\Http\Resources;

use App\DTOs\WaterBankDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaterBankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var WaterBankDTO $waterBank */
        $waterBank = $this->resource;

        return [
            'id' => $waterBank->id,
            'user_id' => $waterBank->user_id,
            'balance' => $waterBank->balance,
            'formatted_balance' => $waterBank->getFormattedBalance(),
            'created_at' => $waterBank->created_at,
            'updated_at' => $waterBank->updated_at,
            'recent_transactions' => WaterBankTransactionResource::collection($waterBank->recent_transactions ?? collect()),
        ];
    }
}
