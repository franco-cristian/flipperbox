<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { ref } from 'vue';

// Definimos la estructura de nuestra navegación en un solo lugar
const navigationLinks = ref([
    { name: 'Dashboard', href: route('dashboard'), permission: null }, // Sin permiso, visible para todos
    { name: 'Clientes', href: route('clientes.index'), permission: 'ver clientes' },
    // Aquí añadiremos más enlaces en el futuro (Inventario, Turnos, etc.)
]);

// Creamos una función helper para verificar permisos
const can = (permission) => {
    if (!permission) return true; // Si el enlace no requiere permiso, siempre se muestra
    return usePage().props.auth.user.permissions.includes(permission);
};
</script>

<template>
    <div class="flex flex-col w-64 py-4 px-4 bg-white border-r">
        <!-- Logo -->
        <div class="flex items-center justify-center mb-6">
            <Link :href="route('dashboard')">
                <ApplicationLogo class="block h-12 w-auto" />
            </Link>
        </div>
        
        <!-- Links de Navegación (Ahora generados dinámicamente) -->
        <nav class="flex-1">
            <template v-for="link in navigationLinks" :key="link.name">
                <!-- Usamos v-if con nuestra función 'can' para mostrar/ocultar el enlace -->
                <Link
                    v-if="can(link.permission)"
                    :href="link.href"
                    :class="{
                        'bg-gray-100 text-gray-900': $page.url.startsWith(link.href.substring(link.href.lastIndexOf('/'))),
                        'text-gray-600 hover:bg-gray-100 hover:text-gray-900': !$page.url.startsWith(link.href.substring(link.href.lastIndexOf('/')))
                    }"
                    class="flex items-center px-4 py-2 mt-2 transition-colors duration-300 transform rounded-md"
                >
                    <span class="mx-4 font-medium">{{ link.name }}</span>
                </Link>
            </template>
        </nav>
    </div>
</template>
