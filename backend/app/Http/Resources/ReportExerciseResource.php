<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportExerciseResource extends JsonResource
{
    public function toArray($request)
    {
        $ex = $this->whenLoaded('routineExercise');

        return [
            'id'                  => (int) $this->id,
            'routine_exercise_id' => (int) $this->routine_exercise_id,
            'difficulty'          => $this->difficulty !== null ? (int) $this->difficulty : null,
            'metric'              => $this->metric,
            'completed'           => (bool) $this->completed,

            // Info útil del ejercicio base (si está cargado)
            'exercise' => $this->when($ex, [
                'id'         => (int) $ex->id,
                'name'       => $ex->name,
                'description'=> $ex->description ?? $ex->details ?? null,
                'position'   => (int) $ex->position,
            ]),
        ];
    }
}
