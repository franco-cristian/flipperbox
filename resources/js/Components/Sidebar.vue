<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <!-- Cambiamos el fondo a blanco y añadimos un borde a la derecha -->
    <div class="flex flex-col w-64 py-4 px-4 bg-white border-r">
        <!-- Logo -->
        <div class="flex items-center justify-center mb-6">
            <Link :href="route('dashboard')">
                <ApplicationLogo class="block h-12 w-auto" />
            </Link>
        </div>
        
        <!-- Links de Navegación -->
        <nav class="flex-1">
            <Link
                :href="route('dashboard')"
                :class="{
                    'bg-gray-100 text-gray-900': route().current('dashboard'), // Clase para estado activo
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900': !route().current('dashboard') // Clases para estado normal y hover
                }"
                class="flex items-center px-4 py-2 mt-2 transition-colors duration-300 transform rounded-md"
            >
                <span class="mx-4 font-medium">Dashboard</span>
            </Link>

            <Link
                :href="route('clientes.index')"
                :class="{
                    'bg-gray-100 text-gray-900': route().current('clientes.index'),
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900': !route().current('clientes.index')
                }"
                class="flex items-center px-4 py-2 mt-2 transition-colors duration-300 transform rounded-md"
            >
                <span class="mx-4 font-medium">Clientes</span>
            </Link>
        </nav>
    </div>
</template>
