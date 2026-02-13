<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    // Enough bc Factories keep to naming conventions
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'period',
        'is_active',
        'municipality_id',
        'client_id',
        'type_id',
        'clerk_id',
        'reference_measurement_id',
    ];

    protected $casts = [
        // always boolean (not 0/1)
        'is_active' => 'boolean',
    ];

    public function scopeWithLastAndNextMeasurementDate(Builder $query): void
    {
        // Can be queried in the Controller
        // Using raw sql allows us to calculate the 'next_measurement' date directly on the database part.
        $query->addSelect([
            'last_measurement' => Measurement::select('measurement_datetime')
                ->whereColumn('project_id', 'projects.id')
                ->latest('measurement_datetime')
                ->limit(1),
            'next_measurement' => Measurement::selectRaw('measurement_datetime + projects.period')
                ->whereColumn('project_id', 'projects.id')
                ->latest('measurement_datetime')
                ->limit(1),
        ]);
    }

    /**
     * Gemini 3 Pro, 2025-12-30
     * "What is the code for the models to use a pivot table"
     */
    public function users(): BelongsToMany
    {
        // n:m relationship with User
        return $this->belongsToMany(User::class)
            // all values stored in pivot table
            ->withPivot('is_contact_person')
            ->withTimestamps();
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function clerk(): BelongsTo
    {
        return $this->belongsTo(Clerk::class);
    }

    public function referenceMeasurement(): BelongsTo
    {
        // Set by admin
        return $this->belongsTo(Measurement::class, 'reference_measurement_id');
    }

    public function averageYearlyMovement()
    {
        $points = $this->points()->with('measurementValues.measurement')->get();

        $valuesSum = 0;
        $valuesCount = 0;
        foreach ($points as $point) {
            $yearlyMovement = $point->yearlyMovementInCm();
            if ($yearlyMovement !== null) {
                $valuesSum += $yearlyMovement;
                $valuesCount++;
            }
        }

        return $valuesCount > 0 ? $valuesSum / $valuesCount : null;
    }
}
