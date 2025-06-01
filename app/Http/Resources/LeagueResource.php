<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class LeagueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'id' => "mixed",
        'name' => "mixed",
        'description' => "mixed",
        'created_by' => "mixed",
        'created_at' => "mixed",
        'updated_at' => "mixed"
    ])] public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,
        ];
    }
}
