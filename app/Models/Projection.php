<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;

class Projection extends Model
{
    protected $fillable = ['px', 'py', 'ax', 'ay'];

    protected $casts = [
        'start_point' => MagellanPoint::class,
    ];

    public function point()
    {
        return $this->hasOne(Point::class);
    }

    protected static function booted()
    {
        // Like in MeasurementValue
        static::saving(function ($projection) {
            if (!$projection->start_point && isset($projection->px, $projection->py)) {
                $projection->start_point = MagellanPoint::make($projection->px, $projection->py, null, null, 31254);
            }
        });
    }
}
