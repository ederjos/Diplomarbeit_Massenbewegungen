<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }
}
