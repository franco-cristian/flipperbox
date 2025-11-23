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
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Mis Vehículos</h2>
                <Link
                    :href="route('cliente.vehiculos.create')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 text-sm"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Vehículo
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <div class="overflow-x-auto">
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
                                        v-for="vehiculo in vehiculos.data"
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
                                                :href="route('cliente.vehiculos.edit', vehiculo.id)"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4 transition"
                                                >Editar</Link
                                            >
                                            <button
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition"
                                                @click="confirmVehicleDeletion(vehiculo)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="vehiculos.data.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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
                                                <p class="text-lg font-medium">No tienes vehículos registrados.</p>
                                                <p class="text-sm">¡Agrega tu primer vehículo!</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="vehiculos.links.length > 3"
                        class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    >
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
