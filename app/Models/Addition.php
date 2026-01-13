<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addition extends Model
{
    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }
}
