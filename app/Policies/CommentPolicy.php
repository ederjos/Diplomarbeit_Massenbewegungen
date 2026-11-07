<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user may create a comment.
     */
    public function create(User $user): bool
    {
        return $user->role?->is_admin || $user->role?->can_comment;
    }

    /**
     * Determine whether the user may update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->role?->is_admin || $comment->user_id === $user->id;
    }

    /**
     * Determine whether the user may delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }
}
