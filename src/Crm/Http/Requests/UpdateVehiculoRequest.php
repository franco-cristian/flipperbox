<?php

namespace FlipperBox\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->can('editar vehiculos');
    }

    public function rules(): array
    {
        // Obtenemos el ID del vehículo que se está editando desde la ruta
        $vehiculoId = $this->route('vehiculo')->id;

        return [
            'patente' => ['required', 'string', 'max:255', Rule::unique('vehiculos', 'patente')->ignore($vehiculoId)],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'anio' => ['required', 'integer', 'min:1950', 'max:'.date('Y')],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
            'vin' => ['nullable', 'string', 'max:255', Rule::unique('vehiculos', 'vin')->ignore($vehiculoId)],
            'numero_motor' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
        ];
    }
}
