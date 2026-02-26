<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected function casts(): array
    {
        // ensure that bools are correctly interpreted (some dbs store them as 0/1)
        return [
            'is_admin' => 'boolean',
            'can_manage_projects' => 'boolean',
            'can_manage_measurements' => 'boolean',
            'can_comment' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
