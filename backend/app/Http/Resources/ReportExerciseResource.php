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
            'report_id'           => (int) $this->report_id,
            'routine_exercise_id' => (int) $this->routine_exercise_id,

            // ---- Snapshot guardado en el reporte ----
            'exercise_name' => $this->exercise_name,                 // string|null
            'rest'          => $this->rest,                          // string|null
            'series_reps'   => $this->series_reps,                   // string|null
            'position'      => $this->position !== null ? (int) $this->position : null,

            // ---- Datos introducidos por el usuario ----
            'difficulty' => $this->difficulty !== null ? (int) $this->difficulty : null,
            'metric'     => $this->metric,
            'completed'  => (bool) $this->completed,

            // Relación opcional (solo informativa / fallback)
            'routine_exercise' => $this->when($ex, [
                'id'          => (int) $ex->id,
                'name'        => $ex->name,
                'rest'        => $ex->rest ?? null,
                'series_reps' => $ex->series_reps ?? null,
                'position'    => (int) $ex->position,
            ]),
        ];
    }
}
