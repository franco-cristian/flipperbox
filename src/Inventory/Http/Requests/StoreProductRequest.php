<?php
namespace FlipperBox\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->can('gestionar inventario');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'min_threshold' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'Este SKU ya está en uso.',
            'price.required' => 'El precio de venta es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'current_stock.required' => 'El stock inicial es obligatorio.',
            'current_stock.integer' => 'El stock debe ser un número entero.',
            'min_threshold.required' => 'El umbral mínimo es obligatorio.',
            'min_threshold.integer' => 'El umbral debe ser un número entero.',
        ];
    }
}