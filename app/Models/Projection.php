<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A Projection defines a normalized axis (ax, ay) for projecting
 * displacement vectors onto a specific direction (e.g., slope direction).
 *
 * Only the axis components are needed for the scalar product calculation.
 * The start_point (px, py) would only be relevant for visualizing the
 * axis line itself on a map, not for displacement calculations.
 */
class Projection extends Model
{
    use HasFactory;

    protected $fillable = [
        'ax',
        'ay',
    ];

    public function point(): HasOne
    {
        return $this->hasOne(Point::class);
    }

    public function projectDisplacement(float $dX, float $dY): float
    {
        // Don't duplicate the dot product calculation
        return $dX * $this->ax + $dY * $this->ay;
    }
}
