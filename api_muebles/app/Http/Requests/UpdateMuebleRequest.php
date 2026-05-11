<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMuebleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo permite usuarios que tengan permisos de edición de muebles
        $usuario = $this->user();
        return $usuario != null && $usuario->tokenCan('muebles.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 'sometimes' permite actualizar solo los campos enviados (como en lección 2)
        return [
            'nombre'       => ['sometimes', 'required', 'string', 'max:255'],
            'descripcion'  => ['sometimes', 'required', 'string'],
            'precio'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock'        => ['sometimes', 'required', 'integer', 'min:0'],
            'color'        => ['sometimes', 'nullable', 'string', 'max:100'],
            'material'     => ['sometimes', 'nullable', 'string', 'max:100'],
            'categoria_id' => ['sometimes', 'required', 'exists:categorias,id'],
            'imagen_url'   => ['sometimes', 'nullable', 'string'],
        ];
    }
}
