<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementValue extends Model
{
    // Adds the trait for model factories -> factory-related features
    use HasFactory;

    // What attributes are mass-aissgnable
    protected $fillable = ['x', 'y', 'z', 'point_id', 'measurement_id', 'addition_id'];

    // How to convert attributes when reading/writing
    protected $casts = [
        'geom' => MagellanPoint::class,
    ];

    // Usable as query scope
    public function scopeWithLatLonAndOrderedByDate(Builder $query): void
    {
        // Same as in Project: For the Controller, but reusable
        // Select all columns from measurement_values
        $query->select('measurement_values.*')
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            // Adds calculated columns to selected columns
            ->addSelect(ST::y(ST::transform('measurement_values.geom', config('spatial.srids.wgs84')))->as('lat'))
            ->addSelect(ST::x(ST::transform('measurement_values.geom', config('spatial.srids.wgs84')))->as('lon'))
            // always in meter, so no need to transform
            ->addSelect(ST::z('measurement_values.geom')->as('height'));
    }

    /**
     * Gemini 3 Pro, 2026-01-11
     * "When removing the triggers by magellan, what code will make the geom field?"
     */
    // Runs when the Model is booted
    protected static function booted()
    {
        // Listens to saving event (on class-level -> static)
        static::saving(function (MeasurementValue $measurementValue) {
            // Automatically sync geom from x,y,z if geom is missing or x,y,z changed (and x,y,z are set)
            if (isset($measurementValue->x, $measurementValue->y, $measurementValue->z) &&
                (! $measurementValue->geom || $measurementValue->isDirty(['x', 'y', 'z']))
            ) {
                $measurementValue->geom = MagellanPoint::make(
                    $measurementValue->x,
                    $measurementValue->y,
                    $measurementValue->z,
                    null,
                    config('spatial.srids.default')
                );
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
