<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'id' => "mixed",
        'home_team' => "array",
        'away_team' => "array",
        'game_date' => "mixed",
        'game_time' => "mixed",
        'lockout_time' => "mixed",
        'is_locked' => "mixed",
        'user_prediction' => "array|null"
    ])] public function toArray(Request $request): array
    {
        $userPrediction = null;

        // Get user's prediction if it exists
        if ($this->predictions && $this->predictions->isNotEmpty()) {
            $userPrediction = $this->predictions->first();
        }

        return [
            'id' => $this->id,
            'home_team' => [
                'id' => $this->homeTeam->id,
                'name' => $this->homeTeam->name,
                'short_name' => $this->homeTeam->short_name ?? null,
            ],
            'away_team' => [
                'id' => $this->awayTeam->id,
                'name' => $this->awayTeam->name,
                'short_name' => $this->awayTeam->short_name ?? null,
            ],
            'game_date' => $this->game_date?->format('Y-m-d'),
            'game_time' => $this->game_time?->format('Y-m-d H:i:s'),
            'lockout_time' => $this->lockout_time?->format('Y-m-d H:i:s'),
            'is_locked' => $this->isLocked(),
            'user_prediction' => $userPrediction ? [
                'id' => $userPrediction->id,
                'predicted_home_score' => $userPrediction->predicted_home_score,
                'predicted_away_score' => $userPrediction->predicted_away_score,
                'created_at' => $userPrediction->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $userPrediction->updated_at?->format('Y-m-d H:i:s'),
            ] : null,
        ];
    }
}
