<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Builder;
use Clickbar\Magellan\Database\PostgisFunctions\ST;

class MeasurementValue extends Model
{
    use HasFactory;

    public const SRID_MGI_AUSTRIA_GK_WEST = 31254;
    public const SRID_WGS84 = 4326;

    protected $fillable = ['x', 'y', 'z', 'point_id', 'measurement_id', 'addition_id'];

    protected $casts = [
        'geom' => MagellanPoint::class,
    ];

    public function scopeWithLatLonAndOrderedByDate(Builder $query): void
    {
        // Same as in Project: For the Controller, but reusable
        $query->select('measurement_values.*')
            ->join('measurements', 'measurement_values.measurement_id', '=', 'measurements.id')
            ->orderBy('measurements.measurement_datetime')
            ->addSelect(ST::y(ST::transform('measurement_values.geom', self::SRID_WGS84))->as('lat'))
            ->addSelect(ST::x(ST::transform('measurement_values.geom', self::SRID_WGS84))->as('lon'));
    }

    /* Prompt (Gemini 3 Pro)
     * "When removing the triggers by magellan, what code will make the geom field?"
     */
    protected static function booted()
    {
        // Listens to saving event
        static::saving(function ($measurementValue) {
            // Automatically sync geom from x,y,z if geom is missing
            if (!$measurementValue->geom && isset($measurementValue->x, $measurementValue->y, $measurementValue->z)) {
                $measurementValue->geom = MagellanPoint::make($measurementValue->x, $measurementValue->y, $measurementValue->z, null, self::SRID_MGI_AUSTRIA_GK_WEST);
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
