<?php

namespace App\Http\Requests\Routine;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Sport;

class StoreRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // policy en controller
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('exercises'))) {
            $ex = array_map(function ($e) {
                // compatibilidad con claves antiguas del front
                if (isset($e['details']) && !isset($e['description']))   $e['description']  = $e['details'];
                if (isset($e['seriesReps']) && !isset($e['series_reps'])) $e['series_reps'] = $e['seriesReps'];
                if (isset($e['descanso']) && !isset($e['rest']))          $e['rest']        = $e['descanso'];
                if (isset($e['position']) && $e['position'] !== '')       $e['position']    = (int) $e['position'];
                return $e;
            }, $this->input('exercises'));
            $this->merge(['exercises' => $ex]);
        }
    }

    public function rules(): array
    {
        return [
            'name'  => ['required','string','max:150'],
            'sport' => ['required', Rule::in(Sport::values())],

            'exercises'                 => ['required','array','min:1'],
            'exercises.*.name'          => ['required','string','max:150'],
            'exercises.*.description'   => ['required','string','max:10000'],
            'exercises.*.series_reps'   => ['nullable','string','max:1000'],
            'exercises.*.rest'          => ['nullable','string','max:80'],
            'exercises.*.position'      => ['required','integer','min:1'],
        ];
    }
}
