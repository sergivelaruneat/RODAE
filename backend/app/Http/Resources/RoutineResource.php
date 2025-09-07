<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoutineResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $request->user();

        // rating del usuario autenticado (si sigue la rutina)
        $userRating = null;
        if ($user) {
            $pivot = $this->followers()->where('user_id', $user->id)->first();
            if ($pivot) {
                $userRating = $pivot->pivot->rating;
            }
        }

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'sport'            => $this->sport instanceof \BackedEnum ? $this->sport->value : (string) $this->sport,
            'sport_label'      => method_exists($this->sport, 'label') ? $this->sport->label() : null,
            'rating_avg'       => (float) $this->rating_avg,
            'exercises_count'  => (int) $this->exercises_count,

            // útil para el front (lo usas en RoutineCard)
            'owner_user_id'    => (int) $this->owner_user_id,
            'owner' => [
                'id'         => $this->owner->id ?? $this->owner_user_id,
                'name'       => $this->owner->name ?? null,
                'avatar_url' => ($this->owner && method_exists($this->owner, 'avatarUrl'))
                    ? $this->owner->avatarUrl()
                    : null,
            ],

            'is_owner'    => $user ? ((int) $user->id === (int) $this->owner_user_id) : false,
            'user_rating' => $user ? ($userRating !== null ? (int) $userRating : null) : null,

            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
