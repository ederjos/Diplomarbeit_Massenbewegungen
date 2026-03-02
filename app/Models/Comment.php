<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'content',
        'measurement_id',
        'user_id',
    ];

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
