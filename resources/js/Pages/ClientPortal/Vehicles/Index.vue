<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

defineProps({ vehiculos: Object });

const confirmingVehicleDeletion = ref(false);
const vehicleToDelete = ref(null);

const confirmVehicleDeletion = (vehiculo) => {
    vehicleToDelete.value = vehiculo;
    confirmingVehicleDeletion.value = true;
};

const deleteVehicle = () => {
    router.delete(route('cliente.vehiculos.destroy', vehicleToDelete.value.id), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

const closeModal = () => (confirmingVehicleDeletion.value = false);
</script>
<template>
    <Head title="Mis Vehículos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis Vehículos</h2>
                <Link
                    :href="route('cliente.vehiculos.create')"
                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition"
                >
                    Agregar Nuevo Vehículo
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
                                        <th scope="col" class="px-6 py-3">Patente</th>
                                        <th scope="col" class="px-6 py-3">Marca</th>
                                        <th scope="col" class="px-6 py-3">Modelo</th>
                                        <th scope="col" class="px-6 py-3">Año</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="vehiculo in vehiculos.data"
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
                                                :href="route('cliente.vehiculos.edit', vehiculo.id)"
                                                class="font-medium text-blue-600 hover:underline mr-4"
                                                >Editar</Link
                                            >
                                            <button
                                                class="font-medium text-red-600 hover:underline"
                                                @click="confirmVehicleDeletion(vehiculo)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="vehiculos.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            No tienes vehículos registrados. ¡Agrega tu primero!
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="vehiculos.links.length > 3" class="p-6 border-t">
                        <Pagination :links="vehiculos.links" />
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal
            :show="confirmingVehicleDeletion"
            title="Eliminar Vehículo"
            :message="`¿Estás seguro de que deseas eliminar tu vehículo con patente ${vehicleToDelete?.patente}?`"
            @close="closeModal"
            @confirm="deleteVehicle"
        />
    </AuthenticatedLayout>
</template>
