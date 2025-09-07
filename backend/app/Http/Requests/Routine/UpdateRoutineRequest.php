<?php

namespace App\Http\Requests\Routine;

use App\Enums\Sport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoutineRequest extends FormRequest
{
    // Autorización vía policy en el controller
    public function authorize(): bool
    {
        return true;
    }

    // Normaliza y mapea legacy: details→description, seriesReps→series_reps, descanso→rest
    protected function prepareForValidation(): void
    {
        if (is_array($this->input('exercises'))) {
            $ex = array_map(function ($e) {
                if (isset($e['details']) && !isset($e['description']))   $e['description']  = $e['details'];
                if (isset($e['seriesReps']) && !isset($e['series_reps'])) $e['series_reps'] = $e['seriesReps'];
                if (isset($e['descanso']) && !isset($e['rest']))          $e['rest']        = $e['descanso'];
                foreach (['id','position'] as $k) {
                    if (isset($e[$k]) && $e[$k] !== '') $e[$k] = (int) $e[$k];
                }
                return $e;
            }, $this->input('exercises'));
            $this->merge(['exercises' => $ex]);
        }

        if ($this->has('delete_exercise_ids') && is_array($this->input('delete_exercise_ids'))) {
            $this->merge(['delete_exercise_ids' => array_map('intval', $this->input('delete_exercise_ids'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name'  => ['sometimes','string','max:150'],
            'sport' => ['sometimes', Rule::in(Sport::values())],

            // Upsert de ejercicios
            'exercises' => ['sometimes','array','min:1'],

            'exercises.*.id'          => ['sometimes','integer','exists:routine_exercises,id'],
            'exercises.*.name'        => ['required_without:exercises.*.id','string','max:150'],
            'exercises.*.description' => ['required_without:exercises.*.id','string','max:10000'],
            'exercises.*.series_reps' => ['nullable','string','max:1000'],
            'exercises.*.rest'        => ['nullable','string','max:80'],
            'exercises.*.position'    => ['required_without:exercises.*.id','integer','min:1'],

            // Borrados
            'delete_exercise_ids'   => ['sometimes','array'],
            'delete_exercise_ids.*' => ['integer','exists:routine_exercises,id'],
        ];
    }
}
