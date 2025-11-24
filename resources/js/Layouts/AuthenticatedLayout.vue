<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import ChatbotWidget from '@/Components/ChatbotWidget.vue';
import { useDarkMode } from '@/Composables/useDarkMode';

const { isDark, toggleDarkMode } = useDarkMode(); // <-- USAR COMPOSABLE

const showingNavigationDropdown = ref(false);

const navigationLinks = ref([
    { name: 'Dashboard', routeName: 'dashboard', permission: null },
    { name: 'Clientes', routeName: 'clientes.index', permission: 'ver clientes' },
    { name: 'Productos', routeName: 'inventario.products.index', permission: 'ver inventario' },
    { name: 'Servicios', routeName: 'services.index', permission: 'ver servicios' },
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
    <!-- Fondo base adaptable: gris claro o gris muy oscuro -->
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
        <!-- Sidebar para Escritorio -->
        <aside
            class="z-20 h-full w-64 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex-shrink-0 hidden md:block transition-colors duration-300"
        >
            <Sidebar />
        </aside>

        <!-- Menú Hamburguesa para Móvil -->
        <div
            v-show="showingNavigationDropdown"
            class="fixed inset-0 z-20 bg-black/50 backdrop-blur-sm md:hidden"
            @click="showingNavigationDropdown = false"
        ></div>

        <aside
            v-show="showingNavigationDropdown"
            class="fixed inset-y-0 z-30 flex-shrink-0 w-64 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 md:hidden transition-colors duration-300"
        >
            <div class="py-4 flex justify-center border-b border-gray-200 dark:border-gray-700">
                <Link href="/">
                    <ApplicationLogo class="block h-12 w-auto" />
                </Link>
            </div>

            <div class="space-y-1 pb-3 pt-2 px-2">
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

            <div class="border-t border-gray-200 dark:border-gray-700 pb-1 pt-4 px-4">
                <div class="text-base font-medium text-gray-800 dark:text-gray-200">
                    {{ $page.props.auth.user.name }}
                </div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $page.props.auth.user.email }}
                </div>

                <div class="mt-4 space-y-2">
                    <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                        Cerrar Sesión
                    </ResponsiveNavLink>
                </div>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <div class="flex flex-col flex-1 w-full overflow-hidden">
            <!-- Barra de Navegación Superior -->
            <header
                class="z-10 py-3 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-300"
            >
                <div class="container flex items-center justify-between h-full px-6 mx-auto">
                    <!-- Botón Hamburguesa -->
                    <button
                        class="p-2 mr-5 -ml-1 rounded-md md:hidden text-gray-500 dark:text-gray-200 focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-700"
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                    >
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                    </button>

                    <div class="flex-1"></div>

                    <!-- Iconos de la derecha -->
                    <div class="flex items-center space-x-4">
                        <!-- BOTÓN TOGGLE DARK MODE -->
                        <button
                            class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300"
                            :title="isDark ? 'Cambiar a modo Claro' : 'Cambiar a modo Oscuro'"
                            @click="toggleDarkMode"
                        >
                            <svg
                                v-if="isDark"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                                />
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                        </button>

                        <NotificationBell :initial-notifications="$page.props.notifications || []" />

                        <div class="hidden sm:flex sm:items-center">
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white focus:outline-none transition duration-150 ease-in-out"
                                        >
                                            <div>{{ $page.props.auth.user.name }}</div>
                                            <div class="ml-1">
                                                <svg
                                                    class="fill-current h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </div>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Cerrar Sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <FlashMessage />

            <!-- Contenido con scroll -->
            <main class="h-full overflow-y-auto bg-gray-100 dark:bg-gray-900 p-6 transition-colors duration-300">
                <!-- Header de Página (Título) -->
                <div v-if="$slots.header" class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                        <slot name="header" />
                    </h2>
                </div>

                <slot />
            </main>
        </div>
        <ChatbotWidget />
    </div>
</template>
