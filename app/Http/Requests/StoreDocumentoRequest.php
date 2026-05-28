<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso lo controla Sanctum en la ruta
    }

    public function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'archivo'     => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // max 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'  => 'El nombre del documento es obligatorio.',
            'archivo.mimes'    => 'El archivo debe ser un PDF.',
            'archivo.max'      => 'El archivo no puede superar los 10MB.',
        ];
    }
}
