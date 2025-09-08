<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La policy se aplica en el controller (create)
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = (array) $this->input('items', []);

        $norm = array_map(function ($i) {
            // Casts básicos y normalización
            if (isset($i['routine_exercise_id'])) {
                $i['routine_exercise_id'] = (int) $i['routine_exercise_id'];
            }

            // Solo castear si VIENE presente
            if (array_key_exists('difficulty', $i)) {
                $i['difficulty'] = ($i['difficulty'] === '' || $i['difficulty'] === null)
                    ? null
                    : (int) $i['difficulty'];
            }

            if (array_key_exists('completed', $i)) {
                $i['completed'] = filter_var($i['completed'], FILTER_VALIDATE_BOOL);
            }

            if (array_key_exists('metric', $i) && is_string($i['metric'])) {
                $i['metric'] = trim($i['metric']);
            }

            return $i;
        }, $items);

        $this->merge([
            'routine_id' => $this->input('routine_id') ? (int) $this->input('routine_id') : null,
            'items'      => $norm,
        ]);
    }

    public function rules(): array
    {
        return [
            'routine_id' => ['required','integer','exists:routines,id'],

            'items'   => ['required','array','min:1'],
            'items.*' => ['required','array'],

            'items.*.routine_exercise_id' => [
                'required','integer','distinct',
                // Debe existir y pertenecer a la rutina indicada
                Rule::exists('routine_exercises','id')
                    ->where(fn ($q) => $q->where('routine_id', (int) $this->input('routine_id')))
            ],

            'items.*.difficulty' => ['nullable','integer','min:1','max:10'],
            'items.*.metric'     => ['nullable','string','max:255'],
            'items.*.completed'  => ['sometimes','boolean'],
        ];
    }
}
