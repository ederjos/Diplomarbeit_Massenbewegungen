<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeasurementValue extends Model
{
    // Adds the trait for model factories -> factory-related features
    use HasFactory;

    // What attributes are mass-assignable
    protected $fillable = [
        'x',
        'y',
        'z',
        'geom',
        'point_id',
        'measurement_id',
        'addition_id',
    ];

    // How to convert attributes when reading/writing
    protected function casts(): array
    {
        return [
            'geom' => MagellanPoint::class,
        ];
    }

    // Usable as query scope
    public function scopeWithLatLonAndOrderedByDate(Builder $query): void
    {
        // Same as in Project: For the Controller, but reusable
        // Select only foreign keys (measurement and point) instead of everything
        $query->select('measurement_values.measurement_id', 'measurement_values.point_id')
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            // Adds calculated columns to selected columns
            ->addSelect(ST::y(ST::transform('measurement_values.geom', config('spatial.srids.wgs84')))->as('lat'))
            ->addSelect(ST::x(ST::transform('measurement_values.geom', config('spatial.srids.wgs84')))->as('lon'))
            // basically always in meters, so no need to transform
            ->addSelect(ST::z('measurement_values.geom')->as('height'));
    }

    /**
     * Gemini 3 Pro, 2026-01-11
     * "When removing the triggers by magellan, what code will make the geom field?"
     */
    // Runs when the Model is booted
    protected static function booted(): void
    {
        // Listens to saving event (on class-level -> static)
        static::saving(function (MeasurementValue $mv) {
            // Automatically sync geom from x,y,z if geom is missing or x,y,z or addition changed
            if (isset($mv->x, $mv->y, $mv->z) &&
                (! $mv->geom || $mv->isDirty(['x', 'y', 'z', 'addition_id']))) {
                $addition = $mv->addition_id ? $mv->addition : null;
                $mv->geom = $mv->computeGeom($mv->x, $mv->y, $mv->z, $addition);
            }
        });
    }

    public static function computeGeom(?float $x = null, ?float $y = null, ?float $z = null, ?Addition $addition = null): MagellanPoint
    {
        $geomX = $x + ($addition?->dx ?? 0);
        $geomY = $y + ($addition?->dy ?? 0);
        $geomZ = $z + ($addition?->dz ?? 0);

        return MagellanPoint::make($geomX, $geomY, $geomZ, srid: config('spatial.srids.default'));
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }

    public function addition(): BelongsTo
    {
        return $this->belongsTo(Addition::class);
    }
}
