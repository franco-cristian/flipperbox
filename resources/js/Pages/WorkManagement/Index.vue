<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { ref } from 'vue';

defineProps({ workOrders: Object, filters: Object });
const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

const statusClass = (status) => ({
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800':
        status === 'Pendiente',
    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800':
        status === 'En Progreso',
    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border border-green-200 dark:border-green-800':
        status === 'Completada',
    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800':
        status === 'Cancelada',
});

const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });

// Lógica Modal (igual que antes)
const confirmingOrderDeletion = ref(false);
const orderToDelete = ref(null);
const confirmOrderDeletion = (o) => {
    orderToDelete.value = o;
    confirmingOrderDeletion.value = true;
};
const deleteOrder = () => {
    router.delete(route('work-orders.destroy', orderToDelete.value.id), { onSuccess: () => closeModal() });
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
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Órdenes de Trabajo</h2>
                <p v-if="can('gestionar ordenes de trabajo')" class="text-sm text-gray-500 dark:text-gray-400">
                    Para crear una orden, ve al detalle del cliente.
                </p>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <SearchInput :model-value="filters.search" placeholder="Buscar por ID, patente, cliente..." />
                </div>

                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors"
                >
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th class="px-6 py-4 font-bold">ID</th>
                                        <th class="px-6 py-4 font-bold">Vehículo</th>
                                        <th class="px-6 py-4 font-bold">Cliente</th>
                                        <th class="px-6 py-4 font-bold">Estado</th>
                                        <th class="px-6 py-4 font-bold">Fecha</th>
                                        <th class="px-6 py-4 font-bold text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="workOrder in workOrders.data"
                                        :key="workOrder.id"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    >
                                        <th class="px-6 py-4 font-mono font-bold text-gray-900 dark:text-white">
                                            #{{ workOrder.id }}
                                        </th>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium">
                                            {{ workOrder.vehicle.patente }}
                                            <span
                                                v-if="workOrder.vehicle.deleted_at"
                                                class="ml-2 text-[10px] uppercase bg-red-100 text-red-600 px-2 py-0.5 rounded-full border border-red-200"
                                                >Archivado</span
                                            >
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                            {{ workOrder.vehicle.cliente.name }}
                                            {{ workOrder.vehicle.cliente.apellido }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 font-bold text-[10px] uppercase tracking-wider rounded-full"
                                                :class="statusClass(workOrder.status)"
                                            >
                                                {{ workOrder.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                            {{ formatDate(workOrder.entry_date) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                :href="route('work-orders.show', workOrder.id)"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4 transition"
                                                >Ver Detalle</Link
                                            >
                                            <button
                                                v-if="
                                                    can('gestionar ordenes de trabajo') &&
                                                    workOrder.status !== 'Completada'
                                                "
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition"
                                                @click="confirmOrderDeletion(workOrder)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="workOrders.data.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron órdenes.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div
                        v-if="workOrders.links.length > 3"
                        class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    >
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
