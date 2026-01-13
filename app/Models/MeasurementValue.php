<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeasurementValue extends Model
{
    use HasFactory;

    protected $fillable = ['x', 'y', 'z', 'point_id', 'measurement_id', 'addition_id'];

    protected $casts = [
        'geom' => MagellanPoint::class,
    ];

    /* Prompt (Gemini 3 Pro)
     * "When removing the triggers by magellan, what code will make the geom field?"
     */
    protected static function booted()
    {
        // Listens to saving event
        static::saving(function ($measurementValue) {
            // Automatically sync geom from x,y,z if geom is missing
            if (!$measurementValue->geom && isset($measurementValue->x, $measurementValue->y, $measurementValue->z)) {
                $measurementValue->geom = MagellanPoint::make($measurementValue->x, $measurementValue->y, $measurementValue->z, null, 31254);
            }
        });
    }

    public function point()
    {
        return $this->belongsTo(Point::class);
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }

    public function addition()
    {
        return $this->belongsTo(Addition::class);
    }
}
