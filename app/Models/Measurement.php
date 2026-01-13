<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Measurement extends Model
{
    use HasFactory;
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }
}
