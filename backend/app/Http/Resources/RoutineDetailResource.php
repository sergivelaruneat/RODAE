<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoutineDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $request->user();

        // OJO: son 1–2 queries, pero es el show (no listado)
        $isFollowing = false;
        $userRating  = null;
        if ($user) {
            $pivot = $this->followers()->where('user_id', $user->id)->first();
            $isFollowing = (bool) $pivot;
            $userRating  = $pivot?->pivot?->rating;
        }

        return [
            'id'              => (int) $this->id,
            'name'            => $this->name,
            'sport'           => $this->sport,
            'sport_label'     => method_exists($this->sport, 'label') ? $this->sport->label() : $this->sport,
            'owner_user_id'   => (int) $this->owner_user_id,
            'owner'           => $this->whenLoaded('owner', fn() => [
                                    'id' => (int) $this->owner->id,
                                    'name' => $this->owner->name,
                                ]),
            'rating_avg'      => (float) $this->rating_avg,
            'exercises_count' => (int) $this->exercises_count,
            'exercises'       => RoutineExerciseResource::collection($this->whenLoaded('exercises')),

            // ✅ claves que usa el front para el botón y la valoración del usuario
            'is_following'    => $isFollowing,
            'user_rating'     => $userRating,
        ];
    }
}
