<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoutineExerciseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => (int) $this->id,
            'name'        => $this->name,
            'description' => $this->description, // alias de 'details'
            'series_reps' => $this->series_reps, // texto libre
            'rest'        => $this->rest,        // texto libre
            'position'    => (int) $this->position,
        ];
    }
}

