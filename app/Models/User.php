<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the model attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Determine whether the user belongs to the given project.
     */
    public function isMemberOfProject(int $projectId): bool
    {
        return $this->projects()->where('projects.id', $projectId)->exists();
    }

    // Same as project
    /**
     * Get the projects the user belongs to.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('is_contact_person', 'is_favorite')
            ->withTimestamps();
    }

    /**
     * Get the role assigned to the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the comments authored by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
