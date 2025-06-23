<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class UserResource extends JsonResource
{
    protected ?array $statsAsAtRound = null;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param array|null $statsAsAtRound
     * @return void
     */
    public function __construct($resource, ?array $statsAsAtRound = null)
    {
        parent::__construct($resource);
        $this->statsAsAtRound = $statsAsAtRound;
    }

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
        $stats = $this->statsAsAtRound;
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'supportedTeam' => $this->supportedTeam ?? '',
            'totalPoints' => $stats['totalPoints'] ?? $this->statistics->total_points ?? 0,
            'latestPoints' => $stats['latestPoints'] ?? $this->statistics->latest_points ?? 0,
            'roundsPlayed' => $stats['roundsPlayed'] ?? $this->statistics->rounds_played ?? 0,
            'averagePoints' => $stats['averagePoints'] ?? $this->statistics->average_points ?? 0.0,
            'currentRank' => $stats['currentRank'] ?? $this->statistics->current_rank ?? 0,
            'leagues' => BasicLeagueResource::collection($this->whenLoaded('leagues')),
        ];
    }
}
