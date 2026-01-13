<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipality extends Model
{
    use HasFactory;
    
    // That tells Laravel: "It's okay to insert or update the _name_ field via create() or update()."
    // protected $fillable = [
    //     'name', // add all fields we plan to mass assign
    // ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
