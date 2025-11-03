<?php
namespace Database\Factories;

use FlipperBox\Crm\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->unique()->e164PhoneNumber(),
            'documento_tipo' => 'DNI',
            'documento_valor' => fake()->unique()->numerify('########'),
        ];
    }
}