<?php

namespace App\Http\Resources;

use App\Services\PlantGrowthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
{
    public function toArray($request): array
    {
        $plantGrowthService = app(PlantGrowthService::class);
        $progressPercentage = $this->getProgressPercentage();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'target_amount' => $this->target_amount,
            'current_amount' => $this->current_amount,
            'remaining_amount' => $this->getRemainingAmount(),
            'progress_percentage' => $progressPercentage,
            'plant_emoji' => $plantGrowthService->getPlantEmoji($progressPercentage),
            'growth_stage' => $plantGrowthService->getGrowthStage($progressPercentage),
            'is_completed' => $this->is_completed,
            'is_reached' => $this->current_amount >= $this->target_amount,
            'type' => 'savings_goal',
            'created_at' => $this->created_at,
        ];
    }
}
