<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
{
    // La policy se aplica en el controller -> $this->authorize('update', $report)
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = (array) $this->input('items', []);
        $norm  = array_map(function ($i) {
            if (isset($i['id'])) {
                $i['id'] = (int) $i['id'];
            }
            if (isset($i['routine_exercise_id'])) {
                $i['routine_exercise_id'] = (int) $i['routine_exercise_id'];
            }

            // Casts
            if (array_key_exists('difficulty', $i) && $i['difficulty'] !== null && $i['difficulty'] !== '') {
                $i['difficulty'] = (int) $i['difficulty'];
            } else {
                $i['difficulty'] = null; // permite null
            }

            if (isset($i['completed'])) {
                $i['completed'] = filter_var($i['completed'], FILTER_VALIDATE_BOOL);
            }

            // No tocamos 'metric' (llega tal cual)
            return $i;
        }, $items);

        $deleteIds = array_map('intval', (array) $this->input('delete_item_ids', []));

        $this->merge([
            'items'           => $norm,
            'delete_item_ids' => $deleteIds,
        ]);
    }

    public function rules(): array
    {
        $report    = $this->route('report'); // model bind
        $reportId  = $report?->id ?? 0;
        $routineId = $report?->routine_id ?? 0;

        return [
            // Upsert de items (opcional)
            'items' => ['sometimes','array','min:1'],

            // Si viene id => actualizar; si NO viene id => crear
            'items.*.id' => [
                'sometimes','integer',
                Rule::exists('report_exercises','id')
                    ->where(fn ($q) => $q->where('report_id', $reportId)),
            ],

            // Para items nuevos (sin id) es requerido; para existentes puede venir si cambias el vínculo
            'items.*.routine_exercise_id' => [
                'required_without:items.*.id',
                'sometimes','integer',
                Rule::exists('routine_exercises','id')
                    ->where(fn ($q) => $q->where('routine_id', $routineId)),
            ],

            'items.*.difficulty' => ['nullable','integer','min:1','max:10'], // ajusta si permites 0
            'items.*.metric'     => ['nullable','string','max:255'],
            'items.*.completed'  => ['sometimes','boolean'],

            // Borrado de items por id
            'delete_item_ids'   => ['sometimes','array'],
            'delete_item_ids.*' => [
                'integer',
                Rule::exists('report_exercises','id')
                    ->where(fn ($q) => $q->where('report_id', $reportId)),
            ],
        ];
    }
}
