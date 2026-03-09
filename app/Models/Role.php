<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_admin',
        'can_manage_projects',
        'can_manage_measurements',
        'can_comment',
    ];

    // ensure that bools are correctly interpreted (some dbs store them as 0/1)
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'can_manage_projects' => 'boolean',
            'can_manage_measurements' => 'boolean',
            'can_comment' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
