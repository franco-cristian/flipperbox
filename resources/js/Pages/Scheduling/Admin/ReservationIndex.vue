<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref, watch } from 'vue';
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
watch(
    filters,
    throttle(() => {
        router.get(route('admin.scheduling.reservations.index'), filters.value, {
            preserveState: true,
            replace: true,
        });
    }, 300),
    { deep: true }
);

const statusClass = (status) => ({
    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': status === 'Confirmada',
    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': status === 'Asistió',
    'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300': status === 'Ausente',
    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': status === 'Cancelada',
});

const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });

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
        method: () =>
            router.patch(
                route('admin.scheduling.reservations.update', reservation.id),
                { status: newStatus },
                {
                    onSuccess: () => closeModal(),
                    preserveScroll: true,
                }
            ),
    });
};

const closeModal = () => (confirmingAction.value = false);
</script>

<template>
    <Head title="Gestión de Reservas" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                    Gestión de Reservas de Clientes
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <!-- Filtros -->
                    <div class="p-8 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                            <div>
                                <InputLabel for="status" value="Filtrar por Estado" />
                                <select
                                    id="status"
                                    v-model="filters.status"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                >
                                    <option value="">Todos</option>
                                    <option value="Confirmada">Confirmada</option>
                                    <option value="Asistió">Asistió</option>
                                    <option value="Ausente">Ausente</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="date" value="Filtrar por Fecha" />
                                <input
                                    id="date"
                                    v-model="filters.date"
                                    type="date"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Fecha Reserva</th>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Cliente</th>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Vehículo</th>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider text-right">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="reservation in reservations.data"
                                        :key="reservation.id"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                    >
                                        <th
                                            scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                        >
                                            {{ formatDate(reservation.reservation_date) }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ reservation.user.name }} {{ reservation.user.apellido }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ reservation.vehicle.marca }} {{ reservation.vehicle.modelo }} ({{
                                                reservation.vehicle.patente
                                            }})
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 font-semibold leading-tight text-xs rounded-full"
                                                :class="statusClass(reservation.status)"
                                            >
                                                {{ reservation.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                            <div v-if="reservation.status === 'Confirmada'">
                                                <button
                                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl shadow-md transition-all duration-200 mr-3"
                                                    :disabled="isFutureReservation(reservation.reservation_date)"
                                                    :class="{
                                                        'opacity-50 cursor-not-allowed': isFutureReservation(
                                                            reservation.reservation_date
                                                        ),
                                                    }"
                                                    title="Solo se puede marcar en o después de la fecha de la reserva"
                                                    @click="confirmStatusChange(reservation, 'Asistió')"
                                                >
                                                    Asistió
                                                </button>
                                                <button
                                                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-xl shadow-md transition-all duration-200"
                                                    :disabled="isFutureReservation(reservation.reservation_date)"
                                                    :class="{
                                                        'opacity-50 cursor-not-allowed': isFutureReservation(
                                                            reservation.reservation_date
                                                        ),
                                                    }"
                                                    title="Solo se puede marcar en o después de la fecha de la reserva"
                                                    @click="confirmStatusChange(reservation, 'Ausente')"
                                                >
                                                    Ausente
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="reservations.data.length === 0">
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
                                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    ></path>
                                                </svg>
                                                <p class="text-lg font-medium">No se encontraron reservas</p>
                                                <p class="text-sm">con los filtros actuales.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="reservations.links.length > 3"
                        class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    >
                        <Pagination :links="reservations.links" />
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingAction"
            :title="actionToConfirm.title"
            :message="actionToConfirm.message"
            :confirm-button-text="actionToConfirm.confirmButtonText"
            :confirm-button-class="actionToConfirm.confirmButtonClass"
            @close="closeModal"
            @confirm="actionToConfirm.method"
        />
    </AuthenticatedLayout>
</template>
