<?php

namespace App\Http\Resources;

use App\Services\RoundService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class RoundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'id' => "mixed",
        'name' => "mixed",
        'start_date' => "mixed",
        'end_date' => "mixed",
        'is_completed' => "mixed",
        'lockout_time' => "mixed",
        'is_locked' => "mixed",
        'time_until_lockout' => "null|string",
        'games' => "\Illuminate\Http\Resources\Json\AnonymousResourceCollection"
    ])] public function toArray(Request $request): array
    {
        $roundService = app(RoundService::class);
        $lockoutTime = $roundService->calculateLockoutTime($this->resource);
        $isLocked = $roundService->isRoundLocked($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
            'is_completed' => $this->is_completed,
            'lockout_time' => $lockoutTime?->format('Y-m-d H:i:s'),
            'is_locked' => $isLocked,
            'time_until_lockout' => $lockoutTime ? now()->diffForHumans($lockoutTime, ['parts' => 4]) : null,
            'games' => GameResource::collection($this->games),
        ];
    }
}
