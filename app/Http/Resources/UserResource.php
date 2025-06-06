<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'id' => "mixed",
        'username' => "string",
        'email' => "string",
        'supportedTeam' => "string",
        'totalPoints' => "int",
        'latestPoints' => "int",
        'roundsPlayed' => "int",
        'averagePoints' => "float",
        'currentRank' => "int",
        'leagues' => "mixed",
    ])] public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'supportedTeam' => $this->supportedTeam ?? '',
            'totalPoints' => $this->statistics->total_points ?? 0,
            'latestPoints' => $this->statistics->latest_points ?? 0,
            'roundsPlayed' => $this->statistics->rounds_played ?? 0,
            'averagePoints' => $this->statistics->average_points ?? 0.0,
            'currentRank' => $this->statistics->current_rank ?? 0,
            'leagues' => BasicLeagueResource::collection($this->whenLoaded('leagues')),
        ];
    }
}
