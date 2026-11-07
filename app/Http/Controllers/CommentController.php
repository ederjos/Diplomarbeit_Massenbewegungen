<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Measurement;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Store a comment for a measurement.
     */
    public function store(StoreCommentRequest $request, Project $project, Measurement $measurement): RedirectResponse
    {
        $this->authorize('create', Comment::class);

        $measurement->comments()->create([
            'content' => $request->validated('content'),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('project', $project);
    }

    /**
     * Update an existing comment.
     */
    public function update(UpdateCommentRequest $request, Project $project, Measurement $measurement, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $comment->update([
            ...$request->validated(),
            'last_edited_by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('project', $project);
    }

    /**
     * Delete an existing comment.
     */
    public function delete(Project $project, Measurement $measurement, Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        Comment::query()
            ->whereKey($comment->getKey())
            ->delete();

        return redirect()->route('project', $project);
    }
}
