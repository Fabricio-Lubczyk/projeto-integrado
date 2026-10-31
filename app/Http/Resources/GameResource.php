<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'developer' => $this->developer,
            'released_on' => $this->released_on?->format('Y-m-d'),
            'description' => $this->description,
            'genres' => $this->genres->map->only(['id', 'name']),
            'platforms' => $this->platforms->map->only(['id', 'name']),
        ];
    }
}
