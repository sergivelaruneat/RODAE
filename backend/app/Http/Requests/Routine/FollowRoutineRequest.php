<?php

namespace App\Http\Requests\Routine;

use Illuminate\Foundation\Http\FormRequest;

class FollowRoutineRequest extends FormRequest
{
    /**
     * Usamos policies en el controlador, aquí devolvemos true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * No esperamos campos en el body. El {routine} viene en la ruta.
     */
    public function rules(): array
    {
        return [];
    }
}
