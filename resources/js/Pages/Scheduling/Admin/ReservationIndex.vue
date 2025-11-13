<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, watch, computed } from 'vue';
import { throttle } from 'lodash';

const props = defineProps({
    reservations: Object,
    filters: Object,
});

const confirmingAction = ref(false);
const actionToConfirm = ref({});

// Objeto reactivo para los filtros
const filters = ref({
    status: props.filters.status || '',
    date: props.filters.date || '',
});

// Observador que reacciona a los cambios en los filtros y recarga la página
watch(filters, throttle(() => {
    router.get(route('admin.scheduling.reservations.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
}, 300), { deep: true });

const statusClass = (status) => ({
    'bg-green-100 text-green-800': status === 'Confirmada',
    'bg-blue-100 text-blue-800': status === 'Asistió',
    'bg-gray-100 text-gray-800': status === 'Ausente',
    'bg-red-100 text-red-800': status === 'Cancelada',
});

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });

// --- NUEVA FUNCIÓN HELPER ---
// Función para verificar si una fecha de reserva es futura
const isFutureReservation = (dateString) => {
    const reservationDate = new Date(dateString);
    reservationDate.setHours(0, 0, 0, 0);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return reservationDate > today;
};

const setupConfirmation = (config) => {
    actionToConfirm.value = config;
    confirmingAction.value = true;
};

const confirmStatusChange = (reservation, newStatus) => {
    let title = '';
    let message = '';
    let buttonText = '';
    let buttonClass = '';

    if (newStatus === 'Asistió') {
        title = 'Marcar como Asistió';
        message = `¿Confirmas que el cliente para la reserva #${reservation.id} ha asistido?`;
        buttonText = 'Sí, Confirmar Asistencia';
        buttonClass = 'bg-green-600 hover:bg-green-700';
    } else if (newStatus === 'Ausente') {
        title = 'Marcar como Ausente';
        message = `¿Confirmas que el cliente para la reserva #${reservation.id} no ha asistido?`;
        buttonText = 'Sí, Marcar Ausente';
        buttonClass = 'bg-orange-500 hover:bg-orange-600';
    }

    setupConfirmation({
        title,
        message,
        confirmButtonText: buttonText,
        confirmButtonClass: buttonClass,
        method: () => router.patch(route('admin.scheduling.reservations.update', reservation.id), { status: newStatus }, {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        })
    });
};

const closeModal = () => confirmingAction.value = false;
</script>

<template>
    <Head title="Gestión de Reservas" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Reservas de Clientes</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Filtros -->
                    <div class="p-6 bg-gray-50 border-b">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label for="status" class="block font-medium text-sm text-gray-700">Filtrar por Estado</label>
                                <select id="status" v-model="filters.status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Todos</option>
                                    <option value="Confirmada">Confirmada</option>
                                    <option value="Asistió">Asistió</option>
                                    <option value="Ausente">Ausente</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div>
                                <label for="date" class="block font-medium text-sm text-gray-700">Filtrar por Fecha</label>
                                <input id="date" type="date" v-model="filters.date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Fecha Reserva</th>
                                        <th scope="col" class="px-6 py-3">Cliente</th>
                                        <th scope="col" class="px-6 py-3">Vehículo</th>
                                        <th scope="col" class="px-6 py-3">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="reservation in reservations.data" :key="reservation.id" class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ formatDate(reservation.reservation_date) }}</th>
                                        <td class="px-6 py-4">{{ reservation.user.name }} {{ reservation.user.apellido }}</td>
                                        <td class="px-6 py-4">{{ reservation.vehicle.marca }} {{ reservation.vehicle.modelo }} ({{ reservation.vehicle.patente }})</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full" :class="statusClass(reservation.status)">
                                                {{ reservation.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div v-if="reservation.status === 'Confirmada'">
                                                <!-- AHORA LOS BOTONES PUEDEN ESTAR DESHABILITADOS -->
                                                <button 
                                                    @click="confirmStatusChange(reservation, 'Asistió')" 
                                                    class="font-medium text-green-600 hover:underline mr-4"
                                                    :disabled="isFutureReservation(reservation.reservation_date)"
                                                    :class="{ 'opacity-50 cursor-not-allowed': isFutureReservation(reservation.reservation_date) }"
                                                    title="Solo se puede marcar en o después de la fecha de la reserva"
                                                >Asistió</button>
                                                <button 
                                                    @click="confirmStatusChange(reservation, 'Ausente')" 
                                                    class="font-medium text-orange-600 hover:underline"
                                                    :disabled="isFutureReservation(reservation.reservation_date)"
                                                    :class="{ 'opacity-50 cursor-not-allowed': isFutureReservation(reservation.reservation_date) }"
                                                    title="Solo se puede marcar en o después de la fecha de la reserva"
                                                >Ausente</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="reservations.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No se encontraron reservas con los filtros actuales.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="reservations.links.length > 3" class="p-6 border-t">
                        <Pagination :links="reservations.links" />
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal 
            :show="confirmingAction" 
            @close="closeModal"
            @confirm="actionToConfirm.method"
            :title="actionToConfirm.title"
            :message="actionToConfirm.message"
            :confirm-button-text="actionToConfirm.confirmButtonText"
            :confirm-button-class="actionToConfirm.confirmButtonClass"
        />
    </AuthenticatedLayout>
</template>