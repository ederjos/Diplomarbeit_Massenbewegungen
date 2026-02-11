<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $casts = [
        // Sometimes boolean fields can be stored as integers (0/1) in the database
        'is_visible' => 'boolean',
    ];

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projection()
    {
        return $this->belongsTo(Projection::class);
    }
}
