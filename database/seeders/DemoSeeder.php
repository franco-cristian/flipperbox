<?php
namespace Database\Seeders;

use FlipperBox\Crm\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Roles
        $adminRole = Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Mecanico']);
        Role::create(['name' => 'Cliente']);

        // Crear un Usuario Administrador
        $adminUser = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@flipperbox.com',
        ]);
        $adminUser->assignRole($adminRole);
        
        // Crear Clientes de Prueba
        Cliente::factory(50)->create();
    }
}