<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user = $this->user;

        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'bio'       => $this->bio,
            'sport'     => $this->sport,
            'birthdate' => $this->birthdate,
            'age'       => $this->birthdate ? \Carbon\Carbon::parse($this->birthdate)->age : null,
            'avatarUrl' => $this->avatar_url,
            'isOwner'   => \Illuminate\Support\Facades\Auth::id() === $this->user_id,
        ];
    }
}
