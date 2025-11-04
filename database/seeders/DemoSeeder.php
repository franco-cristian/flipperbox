<?php
namespace Database\Seeders;

use FlipperBox\Crm\Models\Cliente;
use FlipperBox\Crm\Models\Vehiculo;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\Inventory\Models\Supplier;
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

        $guard = 'web';

        // --- PERMISOS ---
        $permissions = [
            'ver clientes', 'crear clientes', 'editar clientes', 'eliminar clientes',
            'crear vehiculos', 'editar vehiculos', 'eliminar vehiculos',
            'ver inventario', 'gestionar inventario', 'ver proveedores', 'gestionar proveedores',
        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => $guard]);
        }

        // --- ROLES ---
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => $guard]);
        $mecanicoRole = Role::create(['name' => 'Mecanico', 'guard_name' => $guard]);
        $clienteRole = Role::create(['name' => 'Cliente', 'guard_name' => $guard]);
        
        // --- ASIGNACIÓN DE PERMISOS A ROLES ---
        $adminRole->givePermissionTo(Permission::all());
        $mecanicoRole->givePermissionTo(['ver clientes', 'ver inventario']);

        // --- USUARIOS ---
        $adminUser = User::factory()->create(['name' => 'Administrador', 'email' => 'admin@flipperbox.com'])->assignRole($adminRole);
        $mecanicoUser = User::factory()->create(['name' => 'Mecánico de Prueba', 'email' => 'mecanico@flipperbox.com'])->assignRole($mecanicoRole);
        
        // --- CLIENTES Y VEHÍCULOS ---
        Cliente::factory(50)->has(Vehiculo::factory()->count(fake()->numberBetween(1, 3)))->create();

        // --- INVENTARIO REAL ---
        // 1. Creamos los Proveedores
        $hasting = Supplier::create(['name' => 'HASTING', 'contact_person' => 'Ventas Resistencia']);
        $wurth = Supplier::create(['name' => 'WURTH', 'contact_person' => 'Vendedor Juan Perez']);
        $indeser = Supplier::create(['name' => 'INDESER', 'contact_person' => 'Oficina Santa Fe']);

        // 2. Creamos los Productos
        $filtroAceite = Product::create([
            'name' => 'Filtro Aceite VW Gol/Trend/Fox',
            'sku' => 'J3393PA',
            'price' => 125.00,
            'current_stock' => 20,
            'min_threshold' => 5,
        ]);

        $filtroAire = Product::create([
            'name' => 'Filtro Aire Renault Logan 1.6 8v',
            'sku' => 'J3605PA',
            'price' => 150.00,
            'current_stock' => 15,
            'min_threshold' => 5,
        ]);

        $abrazadera = Product::create([
            'name' => 'Abrazadera Zincada 8x12x9',
            'sku' => 'WURTH-ABZ-01',
            'price' => 10.50,
            'current_stock' => 100,
            'min_threshold' => 20,
        ]);

        $desengrasante = Product::create([
            'name' => 'Desengrasante 5Lts',
            'sku' => 'IND-DEG-05',
            'price' => 550.00,
            'current_stock' => 10,
            'min_threshold' => 2,
        ]);

        // 3. Asociamos productos a proveedores (usando la tabla pivot)
        $filtroAceite->suppliers()->attach($hasting->id, ['cost' => 80.00]);
        $filtroAire->suppliers()->attach($hasting->id, ['cost' => 95.00]);
        $abrazadera->suppliers()->attach($wurth->id, ['cost' => 5.00]);
        $desengrasante->suppliers()->attach($indeser->id, ['cost' => 350.00]);
    }
}