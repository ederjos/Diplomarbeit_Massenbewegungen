<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeasurementValue extends Model
{

    protected $fillable = ['x', 'y', 'z', 'point_id', 'measurement_id', 'addition_id'];

    protected $casts = [
        'geom' => Point::class, // Magellan will cast geom to a Point object
    ];

    // Optional: automatically append lat/lon
    protected $appends = ['lat', 'lon'];

    public function point()
    {
        return $this->belongsTo(Point::class);
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }

    // Accessors -> could maybe be replaced by magellan?
    // Accessor for latitude
    public function getLatAttribute(): ?float
    {
        $lat = DB::table('measurement_values')
            ->where('id', $this->id)
            ->value(DB::raw('ST_Y(ST_Transform(geom, 4326))'));

        return $lat !== null ? (float) $lat : null;
    }

    // Accessor for longitude
    public function getLonAttribute(): ?float
    {
        $lon = DB::table('measurement_values')
            ->where('id', $this->id)
            ->value(DB::raw('ST_X(ST_Transform(geom, 4326))'));

        return $lon !== null ? (float) $lon : null;
    }
}
