<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An Addition represents a point relocation offset (dx, dy, dz).
 *
 * When an Addition is created or updated, all linked MeasurementValues
 * automatically recalculate their geom to include the offset.
 * The raw x, y, z on MeasurementValue stay untouched.
 */
class Addition extends Model
{
    protected $fillable = ['dx', 'dy', 'dz'];

    protected static function booted()
    {
        // Listens to saved event -> after addition is written
        static::saved(function (Addition $addition) {
            // isDirty cannot be used in saved event (compare Eloquent ORM docs)
            if ($addition->wasChanged(['dx', 'dy', 'dz'])) {
                foreach ($addition->measurementValues as $mv) {
                    // Clear geom so the saving event recalculates it with the new offsets
                    $mv->geom = null;
                    $mv->save();
                }
            }
        });
    }

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }
}
