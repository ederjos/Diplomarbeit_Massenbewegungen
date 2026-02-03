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
    use HasFactory; // Enough bc Factories keep to naming conventions

    protected $casts = [
        'is_active' => 'boolean', // always boolean (not 0/1)
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

    // Prompt: What is the code for the models to use a pivot table
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class) // n:m relationship with User
            ->withPivot('is_contact_person') // all values stored in pivot table
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function clerk(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
