<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manage_users',
        'manage_projects',
        'manage_measurements',
        'manage_comments',
    ];

    protected function casts(): array
    {
        // ensure that bools are correctly interpreted (some dbs store them as 0/1)
        return [
            'manage_users' => 'boolean',
            'manage_projects' => 'boolean',
            'manage_measurements' => 'boolean',
            'manage_comments' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
