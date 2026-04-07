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
            'measurementInterval' => $this->measurement_interval,
            'client' => $this->client,
            'clerk' => $this->clerk,
            'municipality' => $this->municipality,
            'type' => $this->type,
            'movementMagnitude' => $this->movement_magnitude,
            'firstMeasurement' => $this->first_measurement,
            'lastMeasurement' => $this->last_measurement,
        ];
    }
}
