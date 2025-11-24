<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    public function test_admin_can_create_a_client(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('clientes.store'), [
            'name' => 'Nuevo Cliente',
            'apellido' => 'Test',
            'email' => 'cliente@test.com',
            'telefono' => '123456789',
            'documento_tipo' => 'DNI',
            'documento_valor' => '99887766',
        ]);

        $response->assertRedirect(route('clientes.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'cliente@test.com',
            'name' => 'Nuevo Cliente',
        ]);
    }

    public function test_mechanic_cannot_create_a_client(): void
    {
        $mechanic = User::factory()->create();
        $mechanic->assignRole('Mecanico');

        $response = $this->actingAs($mechanic)->post(route('clientes.store'), [
            'name' => 'Cliente Hacker',
            'apellido' => 'Test',
            'email' => 'hacker@test.com',
            'telefono' => '123456789',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'email' => 'hacker@test.com',
        ]);
    }
}
