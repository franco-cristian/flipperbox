<?php

namespace FlipperBox\Crm\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class StoreUserAsClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->can('crear clientes');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['nullable', 'string', 'max:20', 'unique:'.User::class],
            'documento_tipo' => ['nullable', 'string', 'max:50'],
            'documento_valor' => ['nullable', 'string', 'max:50', 'unique:'.User::class],
        ];
    }
}