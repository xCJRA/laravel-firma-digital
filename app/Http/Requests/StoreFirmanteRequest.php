<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFirmanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firmantes'         => ['required', 'array', 'min:1'],
            'firmantes.*.nombre' => ['required', 'string', 'max:255'],
            'firmantes.*.email'  => ['required', 'email'],
            'firmantes.*.orden'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'firmantes.required'          => 'Debes enviar al menos un firmante.',
            'firmantes.*.nombre.required' => 'El nombre de cada firmante es obligatorio.',
            'firmantes.*.email.required'  => 'El email de cada firmante es obligatorio.',
            'firmantes.*.email.email'     => 'El email de cada firmante debe ser válido.',
            'firmantes.*.orden.required'  => 'El orden de firma es obligatorio.',
        ];
    }
}
