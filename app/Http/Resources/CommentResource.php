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
            // resolve needed to get rid of the "data" wrapper
            'user' => (new UserResource($this->user))->resolve(),
        ];
    }
}
