<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'measurement_id',
        'user_id',
    ];

    /**
     * Get the measurement that owns the comment.
     */
    public function measurement(): BelongsTo
    {
        // Never used, but we might want to use it in the future
        return $this->belongsTo(Measurement::class);
    }

    /**
     * Get the user that authored the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
