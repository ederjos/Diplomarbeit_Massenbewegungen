<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['ax', 'ay'];

    public function point()
    {
        return $this->hasOne(Point::class);
    }
}
