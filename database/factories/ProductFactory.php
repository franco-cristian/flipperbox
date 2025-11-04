<?php

namespace Database\Factories;

use FlipperBox\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FlipperBox\Inventory\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productName = 'Filtro de ' . fake()->randomElement(['Aceite', 'Aire', 'Combustible']) . ' ' . fake()->word();
        
        return [
            'name' => $productName,
            'sku' => 'SKU-' . strtoupper(Str::random(8)),
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 10, 500),
            'current_stock' => fake()->numberBetween(0, 100),
            'min_threshold' => fake()->numberBetween(5, 20),
        ];
    }
}