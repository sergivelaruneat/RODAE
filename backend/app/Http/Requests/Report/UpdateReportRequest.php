<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La policy se aplica en el controller (update)
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = (array) $this->input('items', []);

        $norm = array_map(function ($i) {
            if (isset($i['id'])) {
                $i['id'] = (int) $i['id'];
            }
            if (isset($i['routine_exercise_id'])) {
                $i['routine_exercise_id'] = (int) $i['routine_exercise_id'];
            }

            // ⚠️ Solo castear 'difficulty' si el campo viene presente
            if (array_key_exists('difficulty', $i)) {
                $i['difficulty'] = ($i['difficulty'] === '' || $i['difficulty'] === null)
                    ? null
                    : (int) $i['difficulty'];
            }
            // ⚠️ Solo castear 'completed' si el campo viene presente
            if (array_key_exists('completed', $i)) {
                // BOOL y BOOLEAN son equivalentes; uso BOOL por brevedad
                $i['completed'] = filter_var($i['completed'], FILTER_VALIDATE_BOOL);
            }

            // 'metric' no se toca: puede venir string o null
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
        $report    = $this->route('report'); // model binding
        $reportId  = $report?->id ?? 0;
        $routineId = $report?->routine_id ?? 0;

        return [
            // Upsert opcional
            'items'   => ['sometimes','array','min:1'],
            'items.*' => ['sometimes','array'],

            // Si viene id → actualizar; si no → crear
            'items.*.id' => [
                'sometimes','integer',
                Rule::exists('report_exercises', 'id')
                    ->where(fn ($q) => $q->where('report_id', $reportId)),
            ],

            // Para items nuevos es requerido; para existentes puede venir para mover el vínculo
            'items.*.routine_exercise_id' => [
                'required_without:items.*.id',   // ← ruta completa del hermano
                'sometimes','integer',
                Rule::exists('routine_exercises', 'id')
                    ->where(fn ($q) => $q->where('routine_id', $routineId)),
            ],

            'items.*.difficulty' => ['nullable','integer','min:1','max:10'],
            'items.*.metric'     => ['nullable','string','max:255'],
            'items.*.completed'  => ['sometimes','boolean'],

            // Borrado de items por id
            'delete_item_ids'   => ['sometimes','array'],
            'delete_item_ids.*' => [
                'integer',
                Rule::exists('report_exercises', 'id')
                    ->where(fn ($q) => $q->where('report_id', $reportId)),
            ],
        ];
    }
}
