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
    public function run(): void
    {
        // Resetear roles y permisos cacheados
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Definimos el guard explícitamente
        $guard = 'web';

        // --- CREACIÓN DE PERMISOS ---
        $permissions = [
            'ver clientes', 'crear clientes', 'editar clientes', 'eliminar clientes',
            'crear vehiculos', 'editar vehiculos', 'eliminar vehiculos',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => $guard]);
        }

        // --- CREACIÓN DE ROLES ---
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => $guard]);
        $mecanicoRole = Role::create(['name' => 'Mecanico', 'guard_name' => $guard]);
        $clienteRole = Role::create(['name' => 'Cliente', 'guard_name' => $guard]);

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