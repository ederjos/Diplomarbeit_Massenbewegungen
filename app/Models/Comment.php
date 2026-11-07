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
        'last_edited_by_user_id',
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

    /**
     * Get the user who last edited the comment.
     */
    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by_user_id');
    }
}
