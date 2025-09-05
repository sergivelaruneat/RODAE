<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Con JWT + middleware, con que haya user autenticado vale.
     * Si prefieres algo más estricto, aquí podrías chequear roles.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            // USER
            'name' => ['sometimes','string','max:120'],
            'role' => ['prohibited'],

            // PROFILE
            'bio'       => ['sometimes','nullable','string','max:2000'],
            'sport'     => ['sometimes','nullable','string','max:80'],
            'birthdate' => ['sometimes','nullable','date','before:today','after:1900-01-01'],
            'avatar'    => ['sometimes','file','image','mimes:jpg,jpeg,png,webp','max:4096'],

            // No editable
            'email'     => ['prohibited'],
        ];
    }

    /**
     * Normalizo entrada antes de validar:
     * - role en minúsculas
     * - birthdate: acepto "dd/mm/yyyy" y lo paso a "YYYY-mm-dd"
     * - strings vacíos -> null (para que pasen 'nullable')
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Normalizar role
        if (isset($data['role']) && is_string($data['role'])) {
            $data['role'] = Str::lower(trim($data['role']));
        }

        // Normalizar birthdate (aceptar dd/mm/yyyy y convertir a Y-m-d)
        if (isset($data['birthdate']) && is_string($data['birthdate'])) {
            $raw = trim($data['birthdate']);
            if ($raw === '') {
                $data['birthdate'] = null;
            } elseif (preg_match('~^\d{2}/\d{2}/\d{4}$~', $raw)) {
                try {
                    $data['birthdate'] = Carbon::createFromFormat('d/m/Y', $raw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // si falla, lo dejo tal cual para que la regla 'date_format' dispare error
                }
            }
        }

        // Vacíos -> null (para campos opcionales)
        foreach (['name','bio','sport'] as $k) {
            if (isset($data[$k]) && is_string($data[$k]) && trim($data[$k]) === '') {
                $data[$k] = null;
            }
        }

        $this->replace($data);
    }

    /**
     * Mensajes y alias bonitos en errores (opcional pero recomendable).
     */
    public function messages(): array
    {
        return [
            'role.in'                   => 'El rol debe ser "athlete" o "trainer".',
            'birthdate.date_format'     => 'La fecha debe tener formato AAAA-MM-DD (también acepto dd/mm/aaaa).',
            'birthdate.before'          => 'La fecha de nacimiento debe ser anterior a hoy.',
            'birthdate.after'           => 'La fecha de nacimiento debe ser posterior a 1900-01-01.',
            'avatar.image'              => 'El avatar debe ser una imagen válida.',
            'avatar.mimes'              => 'Formatos permitidos: jpg, jpeg, png o webp.',
            'avatar.max'                => 'El avatar no puede superar los 4 MB.',
            'email.prohibited'          => 'El email no es editable.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'nombre',
            'role'      => 'rol',
            'bio'       => 'descripción',
            'sport'     => 'deporte principal',
            'birthdate' => 'fecha de nacimiento',
            'avatar'    => 'avatar',
        ];
    }
}