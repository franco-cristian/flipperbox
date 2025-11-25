<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    hasVehicles: Boolean,
    vehicles: Array,
    capacities: Array,
    currentYear: Number,
    currentMonth: Number,
    userReservations: Array,
});

const showBookingModal = ref(false);
const showCancelModal = ref(false);
const selectedDay = ref(null);
const reservationToCancel = ref(null);

// --- NUEVAS PROPIEDADES COMPUTADAS PARA SEPARAR RESERVAS ---
const upcomingReservations = computed(() => props.userReservations.filter((r) => r.status === 'Confirmada'));

const pastReservations = computed(() => props.userReservations.filter((r) => r.status !== 'Confirmada'));

// SOLO para UX - mostrar qué vehículos tienen reservas activas
const vehiclesWithActiveReservations = computed(() => {
    if (!props.userReservations || !props.vehicles) return new Set();
    // FILTRAR SOLO RESERVAS CONFIRMADAS
    const confirmedReservations = props.userReservations.filter((r) => r.status === 'Confirmada');
    return new Set(confirmedReservations.map((r) => r.vehicle_id));
});

// Función para formatear fechas de ISO a dd-mm-aaaa
const formatDisplayDate = (dateString) => {
    if (!dateString) return '';

    // Parsear directamente desde YYYY-MM-DD sin usar Date
    const [year, month, day] = dateString.split('-');
    return `${day}-${month}-${year}`;
};

// Función para formatear la fecha seleccionada en el modal
const formatSelectedDate = (dateString) => {
    if (!dateString) return '';

    try {
        // Parsear desde YYYY-MM-DD
        const [year, month, day] = dateString.split('-');
        const date = new Date(year, month - 1, day); // Crear fecha en hora local

        return date.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    } catch (error) {
        console.error('Error formateando fecha seleccionada:', error);
        return dateString;
    }
};

const form = useForm({
    vehicle_id: props.vehicles && props.vehicles.length > 0 ? props.vehicles[0].id : null,
    reservation_date: '',
    notes: '',
});

const monthNames = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
];

const displayMonth = computed(() => monthNames[props.currentMonth - 1]);
const displayYear = computed(() => props.currentYear);

const daysInMonth = computed(() => new Date(props.currentYear, props.currentMonth, 0).getDate());
const firstDayOfMonth = computed(() => {
    const day = new Date(props.currentYear, props.currentMonth - 1, 1).getDay();
    return day === 0 ? 6 : day - 1;
});

// SOLO para UX - mostrar información visual
const getDayInfo = (day) => {
    const dateStr = `${props.currentYear}-${String(props.currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    // Crear fecha local para comparación (sin problemas de timezone)
    const date = new Date(props.currentYear, props.currentMonth - 1, day);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Verificar si el usuario tiene reserva CONFIRMADA para este día - FILTRAR SOLO CONFIRMADAS
    const userReservationForThisDay = props.userReservations?.find((r) => {
        // Comparar directamente los strings YYYY-MM-DD y SOLO reservas confirmadas
        return r.reservation_date === dateStr && r.status === 'Confirmada';
    });

    if (userReservationForThisDay) {
        const vehicle = props.vehicles?.find((v) => v.id === userReservationForThisDay.vehicle_id);
        return {
            hasUserReservation: true,
            vehiclePatente: vehicle?.patente || '',
            isPast: false,
            capacity: null,
            reservationId: userReservationForThisDay.id,
            canCancel: userReservationForThisDay.can_cancel,
        };
    }

    // Verificar si es una fecha pasada
    if (date < today) {
        return {
            isPast: true,
            hasUserReservation: false,
            capacity: null,
        };
    }

    const capacity = props.capacities?.find((c) => c.date === dateStr);
    return {
        isPast: false,
        hasUserReservation: false,
        capacity: capacity || null,
    };
};

const openModal = (day) => {
    const dayInfo = getDayInfo(day);

    // SOLO verificación básica para UX
    if (dayInfo.isPast || dayInfo.hasUserReservation || !dayInfo.capacity || dayInfo.capacity.available_slots <= 0) {
        return;
    }

    const dateStr = `${props.currentYear}-${String(props.currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    selectedDay.value = formatSelectedDate(dateStr);
    form.reservation_date = dateStr;

    showBookingModal.value = true;
};

const closeModal = () => {
    showBookingModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    // El backend se encargará de TODA la validación de negocio
    form.post(route('cliente.reservations.store'), {
        onSuccess: () => {
            closeModal();
        },
        preserveState: false,
    });
};

// Funciones para cancelar reservas
const openCancelModal = (reservationId, event) => {
    if (event) {
        event.stopPropagation();
    }
    reservationToCancel.value = reservationId;
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    showCancelModal.value = false;
    reservationToCancel.value = null;
};

const confirmCancel = () => {
    if (reservationToCancel.value) {
        router.delete(route('cliente.reservations.destroy', reservationToCancel.value), {
            preserveState: false,
            onSuccess: () => {
                closeCancelModal();
            },
        });
    }
};

const changeMonth = (offset) => {
    const newDate = new Date(props.currentYear, props.currentMonth - 1);
    newDate.setMonth(newDate.getMonth() + offset);

    router.get(
        route('cliente.reservations.index'),
        {
            year: newDate.getFullYear(),
            month: newDate.getMonth() + 1,
        },
        { preserveState: true, preserveScroll: true }
    );
};
</script>

<template>
    <Head title="Solicitar Reserva" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                    Solicitar Reserva de Cupo
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- CASO 1: USUARIO SIN VEHÍCULOS -->
                <div
                    v-if="!hasVehicles"
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8 text-center">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">¡Un paso más!</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            Para poder solicitar una reserva, primero necesitas agregar al menos un vehículo a tu
                            perfil.
                        </p>
                        <Link
                            :href="route('cliente.vehiculos.create')"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200"
                        >
                            Agregar mi Primer Vehículo
                        </Link>
                    </div>
                </div>

                <!-- CASO 2: USUARIO CON VEHÍCULOS -->
                <div v-else class="space-y-6">
                    <!-- SECCIÓN DE PRÓXIMAS RESERVAS -->
                    <div
                        v-if="upcomingReservations.length > 0"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Mis Próximas Reservas</h3>
                            <div class="space-y-4">
                                <div
                                    v-for="reservation in upcomingReservations"
                                    :key="reservation.id"
                                    class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl flex justify-between items-center"
                                >
                                    <div class="text-gray-700 dark:text-gray-300">
                                        <strong class="text-gray-900 dark:text-white">{{
                                            formatDisplayDate(reservation.reservation_date)
                                        }}</strong>
                                        -
                                        {{ vehicles.find((v) => v.id === reservation.vehicle_id)?.marca }}
                                        ({{ vehicles.find((v) => v.id === reservation.vehicle_id)?.patente }})
                                    </div>
                                    <button
                                        v-if="reservation.can_cancel"
                                        class="px-4 py-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 border border-red-300 dark:border-red-600 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-200 text-sm font-medium"
                                        title="Cancelar esta reserva"
                                        @click="openCancelModal(reservation.id, $event)"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN DEL CALENDARIO -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                                Solicitar Nueva Reserva
                            </h3>

                            <div class="flex justify-between items-center mb-8">
                                <button
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm"
                                    @click="changeMonth(-1)"
                                >
                                    &larr; Anterior
                                </button>
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                                    {{ displayMonth }} {{ displayYear }}
                                </h3>
                                <button
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm"
                                    @click="changeMonth(1)"
                                >
                                    Siguiente &rarr;
                                </button>
                            </div>
                            <div class="grid grid-cols-7 gap-3 text-center">
                                <div
                                    v-for="day in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']"
                                    :key="day"
                                    class="font-bold text-sm text-gray-600 dark:text-gray-400 py-3"
                                >
                                    {{ day }}
                                </div>
                                <div v-for="blank in firstDayOfMonth" :key="`blank-${blank}`"></div>
                                <div
                                    v-for="day in daysInMonth"
                                    :key="day"
                                    :class="{
                                        'cursor-not-allowed bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-600 border-gray-300 dark:border-gray-700':
                                            getDayInfo(day).isPast ||
                                            getDayInfo(day).hasUserReservation ||
                                            !getDayInfo(day).capacity ||
                                            getDayInfo(day).capacity.available_slots <= 0,
                                        'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-gray-200 dark:border-gray-700':
                                            !getDayInfo(day).isPast &&
                                            !getDayInfo(day).hasUserReservation &&
                                            getDayInfo(day).capacity &&
                                            getDayInfo(day).capacity.available_slots > 0,
                                        'bg-blue-600 text-white cursor-default border-blue-500':
                                            getDayInfo(day).hasUserReservation,
                                    }"
                                    class="p-3 border rounded-xl min-h-[5rem] flex flex-col justify-between transition-all duration-200 bg-white dark:bg-gray-800"
                                    @click="openModal(day)"
                                >
                                    <div class="font-bold text-right text-gray-700 dark:text-white">{{ day }}</div>
                                    <div class="text-xs text-left">
                                        <div v-if="getDayInfo(day).hasUserReservation">
                                            <p class="font-bold">Mi Reserva</p>
                                            <p class="text-blue-200">{{ getDayInfo(day).vehiclePatente }}</p>
                                            <p v-if="getDayInfo(day).canCancel" class="text-blue-200 text-xs mt-1">
                                                ✔️ Cancelable
                                            </p>
                                            <p v-else class="text-blue-200 text-xs mt-1">⚠️ No cancelable</p>
                                        </div>
                                        <div v-else-if="!getDayInfo(day).isPast && getDayInfo(day).capacity">
                                            <p
                                                v-if="getDayInfo(day).capacity.available_slots > 5"
                                                class="font-bold text-green-600 dark:text-green-400"
                                            >
                                                Disponible
                                            </p>
                                            <p
                                                v-else-if="getDayInfo(day).capacity.available_slots > 0"
                                                class="font-bold text-orange-500 dark:text-orange-400"
                                            >
                                                Pocos Cupos
                                            </p>
                                            <p v-else class="font-bold text-red-500 dark:text-red-400">Completo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN HISTORIAL DE RESERVAS -->
                    <div
                        v-if="pastReservations.length > 0"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Historial de Reservas</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <tr>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Fecha</th>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Vehículo</th>
                                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr
                                            v-for="reservation in pastReservations"
                                            :key="reservation.id"
                                            class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                        >
                                            <td
                                                class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                            >
                                                {{ formatDisplayDate(reservation.reservation_date) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ vehicles.find((v) => v.id === reservation.vehicle_id)?.marca }}
                                                {{ vehicles.find((v) => v.id === reservation.vehicle_id)?.modelo }}
                                                ({{ vehicles.find((v) => v.id === reservation.vehicle_id)?.patente }})
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-3 py-1 text-xs font-semibold rounded-full"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300':
                                                            reservation.status === 'Asistió',
                                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300':
                                                            reservation.status === 'Cancelada',
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300':
                                                            reservation.status === 'Ausente',
                                                    }"
                                                >
                                                    {{ reservation.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE RESERVA -->
        <div
            v-if="showBookingModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300"
            @click.self="closeModal"
        >
            <div
                class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl w-full max-w-lg border border-gray-100 dark:border-gray-700 transition-colors duration-300"
            >
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6">
                    Confirmar Reserva para el {{ selectedDay }}
                </h3>
                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <InputLabel for="vehicle_id" value="¿Qué vehículo traerás?" />
                        <select
                            id="vehicle_id"
                            v-model="form.vehicle_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                            required
                        >
                            <!-- MOSTRAMOS TODOS los vehículos - el backend validará cuáles pueden reservar -->
                            <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                                {{ vehicle.marca }} {{ vehicle.modelo }} ({{ vehicle.patente }})
                                <!-- SOLO mostrar "Ya tiene reserva activa" para reservas CONFIRMADAS -->
                                <span v-if="vehiclesWithActiveReservations.has(vehicle.id)">
                                    - Ya tiene reserva activa</span
                                >
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.vehicle_id" />
                    </div>
                    <div>
                        <InputLabel for="notes" value="Notas Adicionales (Opcional)" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="Ej: Falla en el motor, ruido al frenar, etc."
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>
                    <div
                        v-if="form.errors.reservation_date"
                        class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl"
                    >
                        {{ form.errors.reservation_date }}
                    </div>
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <button
                            type="button"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Enviando...</span>
                            <span v-else>Confirmar Solicitud</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN DE CANCELACIÓN -->
        <div
            v-if="showCancelModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300"
        >
            <div
                class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100 dark:border-gray-700 transition-colors duration-300"
            >
                <h3 class="text-xl font-bold text-red-600 dark:text-red-400 mb-6">Confirmar Cancelación</h3>
                <p class="mb-4 text-gray-700 dark:text-gray-300">¿Estás seguro de que quieres cancelar esta reserva?</p>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Esta acción no se puede deshacer.</p>
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <button
                        class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm"
                        @click="closeCancelModal"
                    >
                        Mantener Reserva
                    </button>
                    <button
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200"
                        @click="confirmCancel"
                    >
                        Sí, Cancelar Reserva
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
