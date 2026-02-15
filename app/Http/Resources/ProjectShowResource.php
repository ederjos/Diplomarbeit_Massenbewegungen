<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'comment' => $this->comment,
            'lastFileNumber' => $this->last_file_number,
            'isActive' => $this->is_active,
            'period' => $this->period,
            // whenLoaded not needed because of eager loading in controller
            'client' => $this->client->name,
            'clerk' => $this->clerk->name,
            'municipality' => $this->municipality->name,
            'type' => $this->type->name,
            'averageYearlyMovement' => $this->averageYearlyMovement(),
        ];
    }
}
