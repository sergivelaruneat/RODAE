<?php

namespace App\Http\Requests\Routine;

use Illuminate\Foundation\Http\FormRequest;

class RateRoutineRequest extends FormRequest
{
    /**
     * La autorización se hace en el controlador con policies:
     *   $this->authorize('rate', $userRoutine);
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza rating antes de validar.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('rating')) {
            $this->merge([
                'rating' => is_numeric($this->input('rating'))
                    ? (int) $this->input('rating')
                    : $this->input('rating'),
            ]);
        }
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
        ];
    }

    /**
     * Mensajes (opcional).
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'La valoración es obligatoria.',
            'rating.integer'  => 'La valoración debe ser un número entero.',
            'rating.between'  => 'La valoración debe estar entre 1 y 5.',
        ];
    }
}

