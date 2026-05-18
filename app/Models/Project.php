<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    // Enough bc Factories keep to naming conventions
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
        'comment',
        'last_file_number',
        'measurement_interval',
        'movement_magnitude',
        'image',
        'image_mime_type',
        'client',
        'clerk',
        'type',
        'municipality',
        'reference_measurement_id',
    ];

    /**
     * Get the model attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // always boolean (not 0/1)
            'is_active' => 'boolean',
        ];
    }

    /**
     * Add the most recent measurement timestamp to the select list.
     */
    public function scopeWithLastMeasurementDate(Builder $query): void
    {
        // Can be queried in the Controller
        $query->addSelect([
            'last_measurement' => Measurement::select('measurement_datetime')
                ->whereColumn('project_id', 'projects.id')
                ->latest('measurement_datetime')
                ->limit(1),
        ]);
    }

    /**
     * Add the first and most recent measurement timestamps to the select list.
     */
    public function scopeWithFirstAndLastMeasurementDate(Builder $query): void
    {
        $query->addSelect([
            'first_measurement' => Measurement::select('measurement_datetime')
                ->whereColumn('project_id', 'projects.id')
                ->oldest('measurement_datetime')
                ->limit(1),
            'last_measurement' => Measurement::select('measurement_datetime')
                ->whereColumn('project_id', 'projects.id')
                ->latest('measurement_datetime')
                ->limit(1),
        ]);
    }

    /**
     * Gemini 3 Pro, 2025-12-30
     * "What is the code for the models to use a pivot table"
     */
    /**
     * Get the users assigned to the project.
     */
    public function users(): BelongsToMany
    {
        // n:m relationship with User
        return $this->belongsToMany(User::class)
            // all values stored in pivot table
            ->withPivot('is_contact_person', 'is_favorite')
            ->withTimestamps();
    }

    /**
     * Get the points associated with the project.
     */
    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    /**
     * Get the measurements associated with the project.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    /**
     * Get the reference measurement for the project.
     */
    public function referenceMeasurement(): BelongsTo
    {
        // Set by admin
        return $this->belongsTo(Measurement::class, 'reference_measurement_id');
    }
}
