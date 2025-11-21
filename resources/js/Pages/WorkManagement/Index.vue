<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

defineProps({
    workOrders: {
        type: Object,
        required: true,
    },
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

// Helper para dar estilo al estado de la orden
const statusClass = (status) => {
    return {
        'bg-yellow-100 text-yellow-800': status === 'Pendiente',
        'bg-blue-100 text-blue-800': status === 'En Progreso',
        'bg-green-100 text-green-800': status === 'Completada',
        'bg-red-100 text-red-800': status === 'Cancelada',
    };
};

// Helper para formatear fechas
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('es-ES', options);
};

// --- Lógica para el Modal de Eliminación ---
const confirmingOrderDeletion = ref(false);
const orderToDelete = ref(null);

const confirmOrderDeletion = (workOrder) => {
    orderToDelete.value = workOrder;
    confirmingOrderDeletion.value = true;
};

const deleteOrder = () => {
    router.delete(route('work-orders.destroy', orderToDelete.value.id), {
        onSuccess: () => closeModal(),
        preserveScroll: true, // Mantiene al usuario en la misma página de la paginación
    });
};

const closeModal = () => {
    confirmingOrderDeletion.value = false;
    orderToDelete.value = null;
};
</script>

<template>
    <Head title="Órdenes de Trabajo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Órdenes de Trabajo</h2>
                <!-- El botón Crear ahora es solo un placeholder, la creación se inicia desde el cliente -->
                <p v-if="can('gestionar ordenes de trabajo')" class="text-sm text-gray-500">
                    Para crear una orden, ve al detalle del cliente y selecciona un vehículo.
                </p>
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
                                        <th scope="col" class="px-6 py-3">ID</th>
                                        <th scope="col" class="px-6 py-3">Vehículo (Patente)</th>
                                        <th scope="col" class="px-6 py-3">Cliente</th>
                                        <th scope="col" class="px-6 py-3">Estado</th>
                                        <th scope="col" class="px-6 py-3">Fecha Ingreso</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="workOrder in workOrders.data"
                                        :key="workOrder.id"
                                        class="bg-white border-b hover:bg-gray-50"
                                    >
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            #{{ workOrder.id }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ workOrder.vehicle.patente }}
                                            <span
                                                v-if="workOrder.vehicle.deleted_at"
                                                class="ml-2 text-xs text-red-500 bg-red-100 px-2 py-1 rounded-full"
                                                >Archivado</span
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ workOrder.vehicle.cliente.nombre }}
                                            {{ workOrder.vehicle.cliente.apellido }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-2 py-1 font-semibold leading-tight text-xs rounded-full"
                                                :class="statusClass(workOrder.status)"
                                            >
                                                {{ workOrder.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">{{ formatDate(workOrder.entry_date) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <Link
                                                :href="route('work-orders.show', workOrder.id)"
                                                class="font-medium text-blue-600 hover:underline mr-4"
                                                >Ver Detalle
                                            </Link>

                                            <!-- Botón de Eliminación Condicional -->
                                            <button
                                                v-if="
                                                    can('gestionar ordenes de trabajo') &&
                                                    workOrder.status !== 'Completada'
                                                "
                                                class="font-medium text-red-600 hover:underline"
                                                @click="confirmOrderDeletion(workOrder)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="workOrders.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No se encontraron órdenes de trabajo.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="workOrders.links.length > 3" class="mt-6">
                        <Pagination :links="workOrders.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <ConfirmationModal
            :show="confirmingOrderDeletion"
            title="Eliminar Orden de Trabajo"
            :message="`¿Estás seguro de que deseas eliminar la orden #${orderToDelete?.id}? Si se utilizaron productos, el stock será devuelto. Esta acción no se puede deshacer.`"
            @close="closeModal"
            @confirm="deleteOrder"
        />
    </AuthenticatedLayout>
</template>
