<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    clientes: {
        type: Object,
        required: true,
    },
});
const can = (permission) => usePage().props.auth.user.permissions.includes(permission);
</script>

<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Clientes</h2>
                <Link v-if="can('crear clientes')" :href="route('clientes.create')" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition">
                    Crear Nuevo Cliente
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
                                        <th scope="col" class="px-6 py-3">Nombre Completo</th>
                                        <th scope="col" class="px-6 py-3">Email</th>
                                        <th scope="col" class="px-6 py-3">Teléfono</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="cliente in clientes.data" :key="cliente.id" class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <!-- AQUÍ ESTÁ EL CAMBIO PRINCIPAL -->
                                            <Link :href="route('clientes.show', cliente.id)" class="hover:underline text-blue-600">
                                                {{ cliente.nombre }} {{ cliente.apellido }}
                                            </Link>
                                        </th>
                                        <td class="px-6 py-4">{{ cliente.email }}</td>
                                        <td class="px-6 py-4">{{ cliente.telefono }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <Link v-if="can('editar clientes')" :href="route('dashboard')" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                            <Link v-if="can('eliminar clientes')" :href="route('dashboard')" class="font-medium text-red-600 hover:underline">Eliminar</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="clientes.data.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No se encontraron clientes.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="clientes.links.length > 3" class="p-6 border-t">
                        <Pagination :links="clientes.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>