<?php

namespace App\Http\Requests\Publications;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MediaUploadRule;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // En el MVP: cualquier usuario autenticado puede publicar
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'     => ['nullable','string','max:150'],
            'content'   => ['required','string','max:1000'],
            'sport'     => ['nullable','string','max:50'],
            'media'   => ['required','file', new MediaUploadRule()],
        ];
    }
    public function messages(): array
    {
        return [
            'media.required' => 'Debes adjuntar una imagen o un vídeo.',
            'media.file'     => 'El archivo no es válido.',
        ];
    }
}
