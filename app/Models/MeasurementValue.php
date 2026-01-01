<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeasurementValue extends Model
{

    protected $fillable = ['x', 'y', 'z', 'name', 'point_id', 'measurement_id', 'addition_id'];

    public function point()
    {
        return $this->belongsTo(Point::class);
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
}
