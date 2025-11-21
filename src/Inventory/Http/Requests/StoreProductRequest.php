<?php

namespace FlipperBox\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('gestionar inventario');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
            'iva_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'profit_margin' => ['required', 'numeric', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'min_threshold' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
        ];
    }

    /**
     * Get the custom validation messages for the defined rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'Este SKU ya está registrado. Por favor, elige otro.',
            'cost.required' => 'El precio de costo es obligatorio.',
            'cost.numeric' => 'El costo debe ser un valor numérico.',
            'cost.min' => 'El costo no puede ser negativo.',
            'iva_percentage.required' => 'El porcentaje de IVA es obligatorio.',
            'iva_percentage.numeric' => 'El IVA debe ser un valor numérico.',
            'profit_margin.required' => 'El margen de ganancia es obligatorio.',
            'profit_margin.numeric' => 'El margen de ganancia debe ser un valor numérico.',
            'current_stock.required' => 'El stock inicial es obligatorio.',
            'current_stock.integer' => 'El stock debe ser un número entero.',
            'current_stock.min' => 'El stock no puede ser negativo.',
            'min_threshold.required' => 'El umbral de stock bajo es obligatorio.',
            'min_threshold.integer' => 'El umbral debe ser un número entero.',
            'min_threshold.min' => 'El umbral no puede ser negativo.',
            'supplier_id.required' => 'Debes seleccionar un proveedor.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
        ];
    }
}
