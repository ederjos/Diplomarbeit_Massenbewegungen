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

    /**
     * Get the model attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'measurement_datetime' => 'datetime',
        ];
    }

    /**
     * Get the project that owns the measurement.
     */
    public function project(): BelongsTo
    {
        // Never used, but we might want to use it in the future
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the measurement values linked to this measurement.
     */
    public function measurementValues(): HasMany
    {
        // Never used, but we might want to use it in the future
        return $this->hasMany(MeasurementValue::class);
    }

    /**
     * Get the comments for this measurement.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
