<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray($request)
    {
        $routine = $this->whenLoaded('routine');
        $owner   = $routine?->owner;

        // sport puede venir casteado al Enum o como string
        $sport       = $routine?->sport;
        $sportValue  = is_string($sport) ? $sport : ($sport?->value ?? null);
        $sportLabel  = is_object($sport) && method_exists($sport, 'label') ? $sport->label() : null;

        return [
            'id'         => (int) $this->id,
            'user_id'    => (int) $this->user_id,
            'routine_id' => (int) $this->routine_id,

            'created_at' => optional($this->created_at)->toIso8601String(),
            'date'       => optional($this->created_at)->toDateString(),

            'items_count' => $this->when(isset($this->items_count),
                (int) $this->items_count,
                fn () => (int) ($this->relationLoaded('items') ? $this->items->count() : $this->items()->count())
            ),

            'routine' => $this->when($routine, [
                'id'           => (int) $routine->id,
                'name'         => $routine->name,
                'sport'        => $sportValue,
                'sport_label'  => $sportLabel,
                'owner'        => $owner ? [
                    'id'   => (int) $owner->id,
                    'name' => $owner->name,
                ] : null,
            ]),
        ];
    }
}
