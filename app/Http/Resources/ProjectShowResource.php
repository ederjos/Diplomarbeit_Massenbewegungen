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
            'last_file_number' => $this->last_file_number,
            'is_active' => $this->is_active,
            'period' => $this->period,
            'client' => $this->whenLoaded('client', fn () => $this->client->name),
            'clerk' => $this->whenLoaded('clerk', fn () => $this->clerk->name),
            'municipality' => $this->whenLoaded('municipality', fn () => $this->municipality->name),
            'type' => $this->whenLoaded('type', fn () => $this->type->name),
        ];
    }
}
