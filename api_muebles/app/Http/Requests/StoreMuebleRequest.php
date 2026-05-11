<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMuebleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo permite usuarios que tengan permisos de creación de muebles
        $usuario = $this->user();
        return $usuario != null && $usuario->tokenCan('muebles.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre'       => ['required', 'string', 'max:255'],
            'descripcion'  => ['required', 'string'],
            'precio'       => ['required', 'numeric', 'min:0'],
            'stock'        => ['required', 'integer', 'min:0'],
            'color'        => ['nullable', 'string', 'max:100'],
            'material'     => ['nullable', 'string', 'max:100'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'imagen_url'   => ['nullable', 'string'],
        ];
    }
}
