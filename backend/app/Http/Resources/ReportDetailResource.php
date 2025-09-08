<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id'         => (int) $this->id,
            'user_id'    => (int) $this->user_id,
            'routine_id' => (int) $this->routine_id,

            // Resumen de la rutina (usa tu RoutineResource)
            'routine'    => new RoutineResource($this->whenLoaded('routine')),

            // Ítems del reporte (cada uno con su routineExercise anidado)
            'items'      => ReportExerciseResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
