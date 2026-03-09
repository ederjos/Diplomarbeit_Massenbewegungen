<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Point extends Model
{
    use HasFactory;
    // data inserted before being passed to Resource.
    // https://www.php.net/releases/8.2/en.php#deprecate_dynamic_properties
    public ?object $preloadedFirstMv = null;
    public ?object $preloadedLastMv = null;
    public ?array $preloadedAxis = null;

    protected $fillable = [
        'name',
        'is_visible',
        'project_id',
        'projection_id',
    ];

    protected function casts(): array
    {
        return [
            // Sometimes boolean fields can be stored as integers (0/1) in the database
            'is_visible' => 'boolean',
        ];
    }

    public function measurementValues(): HasMany
    {
        return $this->hasMany(MeasurementValue::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projection(): BelongsTo
    {
        return $this->belongsTo(Projection::class);
    }
}
