<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Role::create(['name' => 'Cliente', 'guard_name' => 'web']);

        $role = Role::findByName('Cliente');
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::create(['name' => 'ver mis vehiculos', 'guard_name' => 'web']));

        $response = $this->post('/register', [
            'name' => 'Test User',
            'apellido' => 'Test Apellido',
            'telefono' => '1122334455',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('cliente.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'apellido' => 'Test Apellido',
            'telefono' => '1122334455',
        ]);
    }
}
