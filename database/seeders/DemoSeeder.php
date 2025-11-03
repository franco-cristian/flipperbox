<?php

namespace Database\Seeders;

use FlipperBox\Crm\Models\Cliente;
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
        // Creamos los permisos para el módulo de Clientes
        Permission::create(['name' => 'ver clientes']);
        Permission::create(['name' => 'crear clientes']);
        Permission::create(['name' => 'editar clientes']);
        Permission::create(['name' => 'eliminar clientes']);

        // (Aquí crearemos más permisos para otros módulos)


        // --- CREACIÓN DE ROLES ---
        $adminRole = Role::create(['name' => 'Admin']);
        $mecanicoRole = Role::create(['name' => 'Mecanico']);
        $clienteRole = Role::create(['name' => 'Cliente']);


        // --- ASIGNACIÓN DE PERMISOS A ROLES ---
        // El rol 'Admin' puede hacer todo
        $adminRole->givePermissionTo(Permission::all());

        // El rol 'Mecanico' solo puede ver clientes (por ahora)
        $mecanicoRole->givePermissionTo('ver clientes');

        // El rol 'Cliente' no tiene permisos de administración


        // --- CREACIÓN DE USUARIOS DE PRUEBA ---
        // Crear un Usuario Administrador
        $adminUser = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@flipperbox.com',
        ]);
        $adminUser->assignRole($adminRole);

        // Crear un Usuario Mecánico de prueba
        $mecanicoUser = User::factory()->create([
            'name' => 'Mecánico de Prueba',
            'email' => 'mecanico@flipperbox.com',
        ]);
        $mecanicoUser->assignRole($mecanicoRole);
        
        // Crear Clientes de Prueba en la tabla 'clientes'
        Cliente::factory(50)->create();
    }
}