<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point as MagellanPoint;
use Illuminate\Database\Eloquent\Model;

class Projection extends Model
{
    protected $fillable = ['px', 'py', 'ax', 'ay'];

    protected $casts = [
        'start_point' => MagellanPoint::class,
    ];

    protected static function booted()
    {
        // Like in MeasurementValue
        static::saving(function (Projection $projection) {
            if (
                isset($projection->px, $projection->py) &&
                (! $projection->start_point || $projection->isDirty(['px', 'py']))
            ) {
                $projection->start_point = MagellanPoint::make(
                    $projection->px,
                    $projection->py,
                    null,
                    null,
                    config('spatial.srids.default')
                );
            }
        });
    }

    public function point()
    {
        return $this->hasOne(Point::class);
    }
}
