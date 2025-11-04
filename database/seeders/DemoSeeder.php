<?php

namespace Database\Seeders;

use FlipperBox\Crm\Models\Cliente;
use FlipperBox\Crm\Models\Vehiculo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetear roles y permisos cacheados
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- CREACIÓN DE PERMISOS ---
        Permission::create(['name' => 'ver clientes']);
        Permission::create(['name' => 'crear clientes']);
        Permission::create(['name' => 'editar clientes']);
        Permission::create(['name' => 'eliminar clientes']);
        Permission::create(['name' => 'crear vehiculos']);
        Permission::create(['name' => 'editar vehiculos']);
        Permission::create(['name' => 'eliminar vehiculos']);

        // --- CREACIÓN DE ROLES ---
        $adminRole = Role::create(['name' => 'Admin']);
        $mecanicoRole = Role::create(['name' => 'Mecanico']);
        $clienteRole = Role::create(['name' => 'Cliente']);

        // --- ASIGNACIÓN DE PERMISOS A ROLES ---
        $adminRole->givePermissionTo(Permission::all());
        $mecanicoRole->givePermissionTo('ver clientes');

        // --- CREACIÓN DE USUARIOS DE PRUEBA ---
        $adminUser = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@flipperbox.com',
        ]);
        $adminUser->assignRole($adminRole);

        $mecanicoUser = User::factory()->create([
            'name' => 'Mecánico de Prueba',
            'email' => 'mecanico@flipperbox.com',
        ]);
        $mecanicoUser->assignRole($mecanicoRole);
        
        // --- CREACIÓN DE CLIENTES Y VEHÍCULOS DE PRUEBA ---
        Cliente::factory(50)
            ->has(Vehiculo::factory()->count(fake()->numberBetween(1, 3)))
            ->create();
    }
}