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

    protected function casts(): array
    {
        return [
            'measurement_datetime' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        // Never used, but we might want to use it in the future
        return $this->belongsTo(Project::class);
    }

    public function measurementValues(): HasMany
    {
        // Never used, but we might want to use it in the future
        return $this->hasMany(MeasurementValue::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
