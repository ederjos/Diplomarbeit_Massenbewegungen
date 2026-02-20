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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isActive' => (bool) $this->is_active,
            'lastMeasurement' => $this->last_measurement,
            'nextMeasurement' => $this->is_active ? $this->next_measurement : null,
            // This line was completed with Copilot autocomplete
            // check if the user has the project marked as favorite in the pivot table
            'isFavorite' => $request->user() ? $request->user()->projects()->wherePivot('is_favorite', true)->where('projects.id', $this->id)->exists() : false,
        ];
    }
}
