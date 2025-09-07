<?php

namespace App\Http\Requests\Routine;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoutineRequest extends FormRequest
{
    /**
     * Con policies activas, no dupliques aquí la autorización.
     * El controlador hará: $this->authorize('assignToUser', $routine);
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('user_id')) {
            $this->merge([
                'user_id' => (int) $this->input('user_id'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // Debe existir y ser un atleta
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'athlete')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'El usuario no existe o no es un atleta.',
        ];
    }
}
