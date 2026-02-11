<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $casts = [
        'measurement_datetime' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function measurementValues()
    {
        return $this->hasMany(MeasurementValue::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
