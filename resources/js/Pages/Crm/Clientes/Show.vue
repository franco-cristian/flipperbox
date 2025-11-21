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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle del Cliente</h2>
                <Link :href="route('clientes.index')" class="text-sm text-gray-700 underline">
                    &larr; Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Sección de Datos del Cliente -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">{{ cliente.name }} {{ cliente.apellido }}</h2>
                            <p class="mt-1 text-sm text-gray-600">Información de contacto y personal del cliente.</p>
                        </header>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="font-bold text-gray-700">Email:</span>
                                <span class="ml-2 text-gray-900">{{ cliente.email || 'No especificado' }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Teléfono:</span>
                                <span class="ml-2 text-gray-900">{{ cliente.telefono || 'No especificado' }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Documento:</span>
                                <span class="ml-2 text-gray-900"
                                    >{{ cliente.documento_tipo || '' }}
                                    {{ cliente.documento_valor || 'No especificado' }}</span
                                >
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Sección de Vehículos Asociados -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <header class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900">Vehículos Registrados</h2>
                                <p class="mt-1 text-sm text-gray-600">Listado de vehículos asociados a este cliente.</p>
                            </div>
                            <Link
                                v-if="can('crear vehiculos')"
                                :href="route('clientes.vehiculos.create', { user: cliente.id })"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition"
                            >
                                Agregar Vehículo
                            </Link>
                        </header>
                        <div class="mt-6 overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Patente</th>
                                        <th scope="col" class="px-6 py-3">Marca</th>
                                        <th scope="col" class="px-6 py-3">Modelo</th>
                                        <th scope="col" class="px-6 py-3">Año</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="vehiculo in cliente.vehiculos"
                                        :key="vehiculo.id"
                                        class="bg-white border-b hover:bg-gray-50"
                                    >
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ vehiculo.patente }}
                                        </th>
                                        <td class="px-6 py-4">{{ vehiculo.marca }}</td>
                                        <td class="px-6 py-4">{{ vehiculo.modelo }}</td>
                                        <td class="px-6 py-4">{{ vehiculo.anio }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <Link
                                                :href="route('work-orders.create', { vehiculo: vehiculo.id })"
                                                class="font-medium text-green-600 hover:underline mr-4"
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
                                                class="font-medium text-blue-600 hover:underline mr-4"
                                                >Editar</Link
                                            >
                                            <button
                                                v-if="can('eliminar vehiculos')"
                                                class="font-medium text-red-600 hover:underline"
                                                @click="confirmVehicleDeletion(vehiculo)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="cliente.vehiculos.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            Este cliente no tiene vehículos registrados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
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
