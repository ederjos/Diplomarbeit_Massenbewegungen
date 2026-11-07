<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'createdDatetime' => $this->created_at,
            'updatedDatetime' => $this->updated_at,
            'lastEditedDatetime' => $this->last_edited_by_user_id ? $this->updated_at : null,
            'lastEditor' => $this->whenLoaded(
                'lastEditor',
                fn () => $this->lastEditor ? (new UserResource($this->lastEditor))->resolve() : null,
            ),
            'canEdit' => $request->user()?->can('update', $this->resource) ?? false,
            'canDelete' => $request->user()?->can('delete', $this->resource) ?? false,
            // resolve needed to get rid of the "data" wrapper
            'user' => $this->whenLoaded('user', fn () => (new UserResource($this->user))->resolve()),
        ];
    }
}
