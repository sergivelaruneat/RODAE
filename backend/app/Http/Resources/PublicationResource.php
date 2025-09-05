<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'sport'          => $this->sport,
            'content'        => $this->content,
            'media_url'      => $this->media_url,
            'created_at'     => $this->created_at?->toIso8601String(),
            'comments_count' => $this->comments_count ?? 0, 

            'user' => [
                'id'       => $this->user->id,
                'name'     => $this->user->name,
                'username' => $this->user->username,
                'avatar_url' => optional($this->user->profile)->avatar_url,
            ],
        ];
    }
}
