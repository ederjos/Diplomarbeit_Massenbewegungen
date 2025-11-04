<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Same as project
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('is_contact_person')
            ->withTimestamps();
    }
}
