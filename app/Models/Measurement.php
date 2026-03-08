<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'measurement_datetime',
        'project_id',
    ];

    protected $casts = [
        'measurement_datetime' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function measurementValues(): HasMany
    {
        return $this->hasMany(MeasurementValue::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
