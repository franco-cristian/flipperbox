<script setup>
import NotificationBell from '@/Components/NotificationBell.vue';
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const showingNavigationDropdown = ref(false);

const navigationLinks = ref([
    { name: 'Dashboard', routeName: 'dashboard', permission: null },
    { name: 'Clientes', routeName: 'clientes.index', permission: 'ver clientes' },
    { name: 'Productos', routeName: 'inventario.products.index', permission: 'ver inventario' },
    { name: 'Proveedores', routeName: 'inventario.suppliers.index', permission: 'ver proveedores' },
    { name: 'Órdenes de Trabajo', routeName: 'work-orders.index', permission: 'ver ordenes de trabajo' },
    { name: 'Gestión de Cupos', routeName: 'admin.scheduling.capacities.index', permission: 'gestionar cupos' },
    { name: 'Mis Vehículos', routeName: 'cliente.vehiculos.index', permission: 'ver mis vehiculos' },
    { name: 'Solicitar Reserva', routeName: 'cliente.reservations.index', permission: 'solicitar reserva' },
    { name: 'Gestión de Reservas', routeName: 'admin.scheduling.reservations.index', permission: 'gestionar reservas' },
]);

const can = (permission) => {
    if (!permission) return true;
    return usePage().props.auth.user.permissions.includes(permission);
};
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar para Escritorio -->
        <aside class="z-20 h-full w-64 overflow-y-auto bg-white flex-shrink-0 hidden md:block">
            <Sidebar />
        </aside>

        <!-- Menú Hamburguesa para Móvil -->
        <div v-show="showingNavigationDropdown" @click="showingNavigationDropdown = false" class="fixed inset-0 z-20 bg-black opacity-50 md:hidden"></div>
        <aside 
            class="fixed inset-y-0 z-30 flex-shrink-0 w-64 overflow-y-auto bg-white md:hidden" 
            v-show="showingNavigationDropdown"
            @click.away="showingNavigationDropdown = false"
        >
            <div class="py-4 text-gray-500">
                <Link class="ml-6" href="/">
                    <ApplicationLogo class="block h-10 w-auto" />
                </Link>
            </div>
            <div class="space-y-1 pb-3 pt-2">
                <template v-for="link in navigationLinks" :key="link.name">
                    <ResponsiveNavLink 
                        v-if="can(link.permission)"
                        :href="route(link.routeName)" 
                        :active="route().current(link.routeName)"
                    >
                        {{ link.name }}
                    </ResponsiveNavLink>
                </template>
            </div>
            <div class="border-t border-gray-200 pb-1 pt-4">
                <div class="px-4">
                    <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button"> Cerrar Sesión </ResponsiveNavLink>
                </div>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <div class="flex flex-col flex-1 w-full">
            <!-- Barra de Navegación Superior -->
            <header class="z-10 py-4 bg-white shadow-md">
                <div class="container flex items-center justify-between h-full px-6 mx-auto">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="p-1 -ml-1 mr-5 rounded-md md:hidden focus:outline-none">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                    </button>
                    <div class="flex-1"></div>
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <NotificationBell class="mr-4" />
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            {{ $page.props.auth.user.name }}
                                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button"> Cerrar Sesión </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </header>
            
            <FlashMessage />

            <main class="h-full overflow-y-auto">
                <header class="bg-white shadow" v-if="$slots.header">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>
                <slot />
            </main>
        </div>
    </div>
</template>

