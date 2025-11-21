<?php

namespace Database\Seeders;

use App\Models\User;
use FlipperBox\Crm\Models\Vehiculo;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\Inventory\Models\Supplier;
use FlipperBox\WorkManagement\Models\Service;
use FlipperBox\WorkManagement\Models\WorkOrder;
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
        // Resetear roles y permisos cacheados para evitar problemas de caché
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Definimos el guard explícitamente para asegurar la consistencia
        $guard = 'web';

        // --- CREACIÓN DE PERMISOS ---
        $permissions = [
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',
            'crear vehiculos',
            'editar vehiculos',
            'eliminar vehiculos',
            'ver inventario',
            'gestionar inventario',
            'ver proveedores',
            'gestionar proveedores',
            'ver ordenes de trabajo',
            'gestionar ordenes de trabajo',
            'ver cupos',
            'gestionar cupos',
            'ver reservas',
            'gestionar reservas',
            'ver mis vehiculos',
            'solicitar reserva',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => $guard]);
        }

        // --- CREACIÓN DE ROLES ---
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => $guard]);
        $mecanicoRole = Role::create(['name' => 'Mecanico', 'guard_name' => $guard]);
        $clienteRole = Role::create(['name' => 'Cliente', 'guard_name' => $guard]);

        // --- ASIGNACIÓN DE PERMISOS A ROLES ---

        // Usamos Permission::all() y luego removemos los permisos del cliente
        $todosLosPermisos = Permission::all();

        // Admin: todos los permisos EXCEPTO los del cliente
        $permisosAdmin = $todosLosPermisos->reject(function ($permiso) {
            return in_array($permiso->name, ['ver mis vehiculos', 'solicitar reserva']);
        });
        $adminRole->givePermissionTo($permisosAdmin);

        // Mecánico: permisos específicos
        $mecanicoRole->givePermissionTo(['ver clientes', 'ver inventario', 'ver ordenes de trabajo']);

        // Cliente: solo sus permisos específicos
        $clienteRole->givePermissionTo(['ver mis vehiculos', 'solicitar reserva']);

        // --- CREACIÓN DE USUARIOS DE PRUEBA ---
        $adminUser = User::factory()->create([
            'name' => 'Administrador',
            'apellido' => 'Flipper',
            'email' => 'admin@flipperbox.com',
        ]);
        $adminUser->assignRole($adminRole);

        $mecanicoUser = User::factory()->create([
            'name' => 'Mecánico',
            'apellido' => 'de Prueba',
            'email' => 'mecanico@flipperbox.com',
        ]);
        $mecanicoUser->assignRole($mecanicoRole);

        // --- REFACTORIZACIÓN: CREACIÓN DE CLIENTES (COMO USUARIOS) Y VEHÍCULOS ---
        User::factory(50)
            ->create()
            ->each(function ($user) use ($clienteRole) {
                $user->assignRole($clienteRole);
                $user->vehiculos()->saveMany(
                    Vehiculo::factory(fake()->numberBetween(1, 3))->make()
                );
            });

        // --- INVENTARIO REAL ---
        // 1. Creamos los Proveedores
        $hasting = Supplier::create(['name' => 'HASTING', 'contact_person' => 'Ventas Resistencia']);
        $wurth = Supplier::create(['name' => 'WURTH', 'contact_person' => 'Vendedor Juan Perez']);
        $indeser = Supplier::create(['name' => 'INDESER', 'contact_person' => 'Oficina Santa Fe']);

        // 2. Creamos los Productos
        $productosData = [
            ['name' => 'Filtro Aceite VW Gol/Trend/Fox', 'sku' => 'J3393PA', 'cost' => 85.00, 'iva_percentage' => 21, 'profit_margin' => 40, 'current_stock' => 20, 'min_threshold' => 5, 'supplier' => $hasting, 'supplier_cost' => 80.00],
            ['name' => 'Filtro Aire Renault Logan 1.6 8v', 'sku' => 'J3605PA', 'cost' => 100.00, 'iva_percentage' => 21, 'profit_margin' => 40, 'current_stock' => 15, 'min_threshold' => 5, 'supplier' => $hasting, 'supplier_cost' => 95.00],
            ['name' => 'Abrazadera Zincada 8x12x9', 'sku' => 'WURTH-ABZ-01', 'cost' => 5.50, 'iva_percentage' => 21, 'profit_margin' => 50, 'current_stock' => 100, 'min_threshold' => 20, 'supplier' => $wurth, 'supplier_cost' => 5.00],
            ['name' => 'Desengrasante 5Lts', 'sku' => 'IND-DEG-05', 'cost' => 350.00, 'iva_percentage' => 21, 'profit_margin' => 35, 'current_stock' => 10, 'min_threshold' => 2, 'supplier' => $indeser, 'supplier_cost' => 350.00],
        ];

        foreach ($productosData as $data) {
            $costWithIva = $data['cost'] * (1 + ($data['iva_percentage'] / 100));
            $price = $costWithIva * (1 + ($data['profit_margin'] / 100));

            $product = Product::create([
                'name' => $data['name'], 'sku' => $data['sku'], 'cost' => $data['cost'], 'iva_percentage' => $data['iva_percentage'],
                'profit_margin' => $data['profit_margin'], 'price' => round($price, 2), 'current_stock' => $data['current_stock'], 'min_threshold' => $data['min_threshold'],
            ]);

            if (isset($data['supplier'])) {
                $product->suppliers()->attach($data['supplier']->id, ['cost' => $data['supplier_cost']]);
            }
        }

        // --- SERVICIOS ---
        $servicioCambioAceite = Service::create(['name' => 'Cambio de Aceite y Filtros', 'price' => 50.00]);
        Service::create(['name' => 'Diagnóstico General', 'price' => 30.00]);
        Service::create(['name' => 'Reparación de Tren Delantero', 'price' => 150.00]);

        // --- ÓRDENES DE TRABAJO DE PRUEBA ---
        $vehiculos = Vehiculo::all();
        $mecanico = User::where('email', 'mecanico@flipperbox.com')->first();
        $filtroAceite = Product::where('sku', 'J3393PA')->first();

        if ($vehiculos->count() > 0 && $mecanico && $filtroAceite && $servicioCambioAceite) {
            // --- Orden Completada (con ítems) ---
            $ordenCompletada = WorkOrder::create([
                'vehicle_id' => $vehiculos->random()->id,
                'mechanic_id' => $mecanico->id,
                'status' => 'Completada',
                'description' => 'Servicio completo de 50.000km.',
                'completion_date' => now(),
            ]);

            $ordenCompletada->products()->attach($filtroAceite->id, ['quantity' => 1, 'unit_price' => $filtroAceite->price]);
            $ordenCompletada->services()->attach($servicioCambioAceite->id, ['price' => $servicioCambioAceite->price]);

            $total = ($filtroAceite->price * 1) + $servicioCambioAceite->price;
            $ordenCompletada->update(['total' => $total]);

            // --- Orden en Progreso ---
            WorkOrder::create(['vehicle_id' => $vehiculos->random()->id, 'mechanic_id' => $mecanico->id, 'status' => 'En Progreso', 'description' => 'Revisión de tren delantero, ruido al girar.']);
            // --- Orden Pendiente ---
            WorkOrder::create(['vehicle_id' => $vehiculos->random()->id, 'status' => 'Pendiente', 'description' => 'Falla en el arranque, posible problema eléctrico.']);
        }
    }
}
