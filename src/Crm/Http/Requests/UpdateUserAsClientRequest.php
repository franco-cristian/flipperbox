<?php

namespace FlipperBox\Crm\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserAsClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->can('editar clientes');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($userId)],
            'telefono' => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($userId)],
            'documento_tipo' => ['nullable', 'string', 'max:50'],
            'documento_valor' => ['nullable', 'string', 'max:50', Rule::unique(User::class)->ignore($userId)],
        ];
    }
}