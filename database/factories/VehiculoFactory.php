<?php
namespace Database\Factories;

use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VehiculoFactory extends Factory
{
    protected $model = Vehiculo::class;

    public function definition(): array
    {
        return [
            'patente' => strtoupper(Str::random(3) . ' ' . fake()->numerify('###')),
            'marca' => fake()->randomElement(['Toyota', 'Ford', 'Chevrolet', 'Volkswagen', 'Fiat', 'Renault']),
            'modelo' => fake()->randomElement(['Corolla', 'Hilux', 'Ranger', 'Onix', 'Gol', 'Sandero']),
            'anio' => fake()->numberBetween(2010, 2024),
            'kilometraje' => fake()->numberBetween(10000, 150000),
            'vin' => strtoupper(Str::random(17)),
        ];
    }
}