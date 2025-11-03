<?php

namespace FlipperBox\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verificamos si el usuario autenticado tiene el permiso 'crear clientes'.
        // El método 'can()' es proporcionado por el trait HasRoles.
        return Auth::user()->can('crear clientes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clientes,email'],
            'telefono' => ['required', 'string', 'max:20', 'unique:clientes,telefono'],
            'documento_tipo' => ['nullable', 'string', 'max:50'],
            'documento_valor' => ['nullable', 'string', 'max:50', 'unique:clientes,documento_valor'],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.unique' => 'Este número de teléfono ya está registrado.',
            'documento_valor.unique' => 'Este número de documento ya está registrado.',
        ];
    }
}