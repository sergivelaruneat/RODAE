<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    // La policy se aplica en el controller -> $this->authorize('create', Report::class)
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normaliza items (casts básicos)
        $items = (array) $this->input('items', []);
        $norm  = array_map(function ($i) {
            if (isset($i['routine_exercise_id'])) {
                $i['routine_exercise_id'] = (int) $i['routine_exercise_id'];
            }

            if (array_key_exists('difficulty', $i) && $i['difficulty'] !== null && $i['difficulty'] !== '') {
                $i['difficulty'] = (int) $i['difficulty'];
            } else {
                $i['difficulty'] = null;
            }

            if (isset($i['completed'])) {
                $i['completed'] = filter_var($i['completed'], FILTER_VALIDATE_BOOL);
            }

            // No tocamos 'metric' (ya llega como string/null)
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

            'items' => ['required','array','min:1'],

            'items.*.routine_exercise_id' => [
                'required','integer',
                // Debe existir y pertenecer a la rutina seleccionada
                Rule::exists('routine_exercises','id')
                    ->where(fn ($q) => $q->where('routine_id', (int) $this->input('routine_id')))
            ],

            'items.*.difficulty' => ['nullable','integer','min:1','max:10'], // ajusta si permites 0
            'items.*.metric'     => ['nullable','string','max:255'],
            'items.*.completed'  => ['sometimes','boolean'],
        ];
    }
}

