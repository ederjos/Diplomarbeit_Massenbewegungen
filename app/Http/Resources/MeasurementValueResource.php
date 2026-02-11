<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeasurementValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'height' => (float) $this->height,
            'lat' => (float) $this->lat,
            'lon' => (float) $this->lon,
            'measurementId' => $this->measurement_id,
        ];
    }
}
