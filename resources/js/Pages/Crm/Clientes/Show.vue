<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
    cliente: {
        type: Object,
        required: true,
    },
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

const confirmingVehicleDeletion = ref(false);
const vehicleToDelete = ref(null);

const confirmVehicleDeletion = (vehiculo) => {
    vehicleToDelete.value = vehiculo;
    confirmingVehicleDeletion.value = true;
};

const deleteVehicle = () => {
    router.delete(route('clientes.vehiculos.destroy', { user: props.cliente.id, vehiculo: vehicleToDelete.value.id }), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

const closeModal = () => {
    confirmingVehicleDeletion.value = false;
    vehicleToDelete.value = null;
};
</script>

<template>
    <Head :title="`Cliente: ${cliente.name} ${cliente.apellido}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Detalle del Cliente</h2>
                <Link
                    :href="route('clientes.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline transition"
                >
                    &larr; Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Sección de Datos del Cliente -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <section>
                            <header>
                                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                                    {{ cliente.name }} {{ cliente.apellido }}
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Información de contacto y personal del cliente.
                                </p>
                            </header>
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Email:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{
                                        cliente.email || 'No especificado'
                                    }}</span>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Teléfono:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{
                                        cliente.telefono || 'No especificado'
                                    }}</span>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Documento:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">
                                        <span v-if="cliente.documento_valor" class="flex items-center gap-2">
                                            <span
                                                class="text-xs font-bold uppercase bg-gray-100 dark:bg-gray-600 px-2 py-0.5 rounded text-gray-500 dark:text-gray-400"
                                            >
                                                {{ cliente.documento_tipo || 'DNI' }}
                                            </span>
                                            {{ cliente.documento_valor }}
                                        </span>
                                        <span v-else class="text-gray-400 italic">No especificado</span>
                                    </span>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Sección de Vehículos Asociados -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <section>
                            <header class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                                        Vehículos Registrados
                                    </h2>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        Listado de vehículos asociados a este cliente.
                                    </p>
                                </div>
                                <Link
                                    v-if="can('crear vehiculos')"
                                    :href="route('clientes.vehiculos.create', { user: cliente.id })"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        ></path>
                                    </svg>
                                    Agregar Vehículo
                                </Link>
                            </header>
                            <div class="mt-6 overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <tr>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Patente</th>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Marca</th>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Modelo</th>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Año</th>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider text-right">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr
                                            v-for="vehiculo in cliente.vehiculos"
                                            :key="vehiculo.id"
                                            class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                        >
                                            <th
                                                scope="row"
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                            >
                                                {{ vehiculo.patente }}
                                            </th>
                                            <td class="px-6 py-4">{{ vehiculo.marca }}</td>
                                            <td class="px-6 py-4">{{ vehiculo.modelo }}</td>
                                            <td class="px-6 py-4">{{ vehiculo.anio }}</td>
                                            <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                                <Link
                                                    :href="route('work-orders.create', { vehiculo: vehiculo.id })"
                                                    class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 mr-4 transition"
                                                    >Crear OT</Link
                                                >
                                                <Link
                                                    v-if="can('editar vehiculos')"
                                                    :href="
                                                        route('clientes.vehiculos.edit', {
                                                            user: cliente.id,
                                                            vehiculo: vehiculo.id,
                                                        })
                                                    "
                                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4 transition"
                                                    >Editar</Link
                                                >
                                                <button
                                                    v-if="can('eliminar vehiculos')"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition"
                                                    @click="confirmVehicleDeletion(vehiculo)"
                                                >
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="cliente.vehiculos.length === 0">
                                            <td
                                                colspan="5"
                                                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                            >
                                                <div class="flex flex-col items-center justify-center">
                                                    <svg
                                                        class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                        ></path>
                                                    </svg>
                                                    <p class="text-lg font-medium">
                                                        Este cliente no tiene vehículos registrados.
                                                    </p>
                                                    <p class="text-sm">Agrega un vehículo para comenzar.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN PARA VEHÍCULOS -->
        <ConfirmationModal
            :show="confirmingVehicleDeletion"
            title="Eliminar Vehículo"
            :message="`¿Estás seguro de que deseas eliminar el vehículo con patente ${vehicleToDelete?.patente}? Esta acción no se puede deshacer.`"
            @close="closeModal"
            @confirm="deleteVehicle"
        />
    </AuthenticatedLayout>
</template>
