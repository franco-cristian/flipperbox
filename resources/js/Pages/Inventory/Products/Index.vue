<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    products: {
        type: Object,
        required: true,
    },
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);
</script>

<template>
    <Head title="Productos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Productos</h2>
                <Link v-if="can('gestionar inventario')" :href="route('dashboard')" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition">
                    Crear Nuevo Producto
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">SKU</th>
                                        <th scope="col" class="px-6 py-3">Nombre</th>
                                        <th scope="col" class="px-6 py-3">Precio Venta</th>
                                        <th scope="col" class="px-6 py-3">Stock Actual</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in products.data" :key="product.id" class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ product.sku }}</th>
                                        <td class="px-6 py-4">{{ product.name }}</td>
                                        <td class="px-6 py-4">${{ product.price }}</td>
                                        <td class="px-6 py-4">{{ product.current_stock }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <Link v-if="can('gestionar inventario')" :href="route('dashboard')" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                            <button v-if="can('gestionar inventario')" class="font-medium text-red-600 hover:underline">Eliminar</button>
                                        </td>
                                    </tr>
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No se encontraron productos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="products.links.length > 3" class="p-6 border-t">
                        <Pagination :links="products.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
