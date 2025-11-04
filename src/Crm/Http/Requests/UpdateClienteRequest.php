<?php

namespace FlipperBox\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verificamos si el usuario tiene el permiso para editar clientes
        return Auth::user()->can('editar clientes');
    }

    public function rules(): array
    {
        // Obtenemos el ID del cliente que se está editando desde la ruta
        $clienteId = $this->route('cliente')->id;

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            // La regla 'unique' debe ignorar el registro del cliente actual
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clientes', 'email')->ignore($clienteId)],
            'telefono' => ['required', 'string', 'max:20', Rule::unique('clientes', 'telefono')->ignore($clienteId)],
            'documento_tipo' => ['nullable', 'string', 'max:50'],
            'documento_valor' => ['nullable', 'string', 'max:50', Rule::unique('clientes', 'documento_valor')->ignore($clienteId)],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado en otro cliente.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.unique' => 'Este número de teléfono ya está registrado en otro cliente.',
            'documento_valor.unique' => 'Este número de documento ya está registrado en otro cliente.',
        ];
    }
}