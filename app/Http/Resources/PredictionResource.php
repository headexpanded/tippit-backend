<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class PredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'id' => "mixed",
        'user_id' => "mixed",
        'game_id' => "mixed",
        'predicted_home_score' => "mixed",
        'predicted_away_score' => "mixed",
        'points_awarded' => "mixed",
        'created_at' => "mixed",
        'updated_at' => "mixed",
        'game' => "\Illuminate\Http\Resources\MissingValue|mixed"
    ])] public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'game_id' => $this->game_id,
            'predicted_home_score' => $this->predicted_home_score,
            'predicted_away_score' => $this->predicted_away_score,
            'points_awarded' => $this->points_awarded,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'game' => $this->whenLoaded('game', function () {
                return [
                    'id' => $this->game->id,
                    'home_team' => [
                        'id' => $this->game->homeTeam->id,
                        'name' => $this->game->homeTeam->name,
                        'short_name' => $this->game->homeTeam->short_name ?? null,
                    ],
                    'away_team' => [
                        'id' => $this->game->awayTeam->id,
                        'name' => $this->game->awayTeam->name,
                        'short_name' => $this->game->awayTeam->short_name ?? null,
                    ],
                    'game_date' => $this->game->game_date?->format('Y-m-d'),
                    'game_time' => $this->game->game_time?->format('Y-m-d H:i:s'),
                    'is_locked' => $this->game->isLocked(),
                ];
            }),
        ];
    }
}
