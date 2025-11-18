<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    // Prompt: What is the code for the models to use a pivot table
    public function users()
    {
        return $this->belongsToMany(User::class) // n:m relationship with User
            ->withPivot('is_contact_person') // all values stored in pivot table
            ->withTimestamps();
    }

    // Example usage
    /*
    Add user as contact person
        $project->users()->attach($user->id, ['is_contact_person' => true]);
    Remove user
        $project->users()->detach($user->id);


    $project = Project::find(1); // get project with id 1
    foreach ($project->users as $user) {
        if ($user->pivot->is_contact_person) {
            echo $user->name . ' is contact person\n';
        }
    }
     */
}
