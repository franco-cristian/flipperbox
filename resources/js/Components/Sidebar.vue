<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { computed } from 'vue';

const page = usePage();
const user = page.props.auth.user;

// Determinamos la ruta del dashboard según los permisos
const dashboardRoute = computed(() => {
    if (user.permissions.includes('ver mis vehiculos')) {
        return 'cliente.dashboard'; // Es Cliente
    }
    if (user.permissions.includes('gestionar ordenes de trabajo') && !user.permissions.includes('crear usuarios')) {
        return 'mecanico.dashboard'; // Es Mecánico (asumiendo permisos estándar)
    }
    // Por defecto Admin o si falla la lógica anterior
    return 'dashboard';
});

const navigationLinks = computed(() => [
    // Usamos la ruta dinámica aquí
    { name: 'Dashboard', href: route(dashboardRoute.value), permission: null },
    { name: 'Clientes', href: route('clientes.index'), permission: 'ver clientes' },
    { name: 'Productos', href: route('inventario.products.index'), permission: 'ver inventario' },
    { name: 'Proveedores', href: route('inventario.suppliers.index'), permission: 'ver proveedores' },
    { name: 'Órdenes de Trabajo', href: route('work-orders.index'), permission: 'ver ordenes de trabajo' },
    { name: 'Gestión de Cupos', href: route('admin.scheduling.capacities.index'), permission: 'gestionar cupos' },
    { name: 'Mis Vehículos', href: route('cliente.vehiculos.index'), permission: 'ver mis vehiculos' },
    { name: 'Solicitar Reserva', href: route('cliente.reservations.index'), permission: 'solicitar reserva' },
    {
        name: 'Gestión de Reservas',
        href: route('admin.scheduling.reservations.index'),
        permission: 'gestionar reservas',
    },
]);

const can = (permission) => {
    if (!permission) return true;
    return user.permissions.includes(permission);
};
</script>

<template>
    <div
        class="flex flex-col h-full py-6 px-4 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-colors duration-300"
    >
        <!-- Logo -->
        <div class="flex items-center justify-center mb-8">
            <Link :href="route(dashboardRoute)">
                <ApplicationLogo class="block h-12 w-auto" />
            </Link>
        </div>

        <!-- Links de Navegación -->
        <nav class="flex-1 space-y-1">
            <template v-for="link in navigationLinks" :key="link.name">
                <Link
                    v-if="can(link.permission)"
                    :href="link.href"
                    :class="{
                        'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 border-l-4 border-blue-600':
                            $page.url.startsWith(link.href.substring(link.href.lastIndexOf('/'))),
                        'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-200':
                            !$page.url.startsWith(link.href.substring(link.href.lastIndexOf('/'))),
                    }"
                    class="flex items-center px-4 py-3 text-sm font-medium rounded-md transition-colors duration-200 group"
                >
                    <span class="truncate">{{ link.name }}</span>
                </Link>
            </template>
        </nav>
    </div>
</template>
