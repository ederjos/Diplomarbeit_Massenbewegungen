<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // This resource is used for project listing

        $favoriteIds = $this->additional['favoriteProjectIds'] ?? [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'isActive' => (bool) $this->is_active,
            'lastMeasurement' => $this->last_measurement,
            'nextMeasurement' => $this->is_active ? $this->next_measurement : null,
            // check if the project id is in the list of favorite projects
            'isFavorite' => in_array($this->id, $favoriteIds),
        ];
    }
}
