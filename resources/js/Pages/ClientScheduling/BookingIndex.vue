<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
const upcomingReservations = computed(() => 
    props.userReservations.filter(r => r.status === 'Confirmada')
);

const pastReservations = computed(() => 
    props.userReservations.filter(r => r.status !== 'Confirmada')
);

// SOLO para UX - mostrar qué vehículos tienen reservas activas
const vehiclesWithActiveReservations = computed(() => {
    if (!props.userReservations || !props.vehicles) return new Set();
    // FILTRAR SOLO RESERVAS CONFIRMADAS
    const confirmedReservations = props.userReservations.filter(r => r.status === 'Confirmada');
    return new Set(confirmedReservations.map(r => r.vehicle_id));
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
            day: 'numeric' 
        });
    } catch (error) {
        console.error('Error formateando fecha seleccionada:', error);
        return dateString;
    }
};

// Función para obtener información de cancelación
const getCancelInfo = (reservation) => {
    if (!reservation.can_cancel) {
        const hours = reservation.hours_until_reservation;
        if (hours <= 0) {
            return { canCancel: false, message: 'La reserva ya pasó o es hoy' };
        } else {
            return { 
                canCancel: false, 
                message: `Solo se puede cancelar hasta 24 horas antes. Faltan ${Math.abs(hours)} horas` 
            };
        }
    }
    return { canCancel: true, message: 'Puedes cancelar esta reserva' };
};

const form = useForm({
    vehicle_id: props.vehicles && props.vehicles.length > 0 ? props.vehicles[0].id : null,
    reservation_date: '',
    notes: '',
});

const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

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
    const userReservationForThisDay = props.userReservations?.find(r => {
        // Comparar directamente los strings YYYY-MM-DD y SOLO reservas confirmadas
        return r.reservation_date === dateStr && r.status === 'Confirmada';
    });
    
    if (userReservationForThisDay) {
        const vehicle = props.vehicles?.find(v => v.id === userReservationForThisDay.vehicle_id);
        return { 
            hasUserReservation: true,
            vehiclePatente: vehicle?.patente || '',
            isPast: false,
            capacity: null,
            reservationId: userReservationForThisDay.id,
            canCancel: userReservationForThisDay.can_cancel
        };
    }

    // Verificar si es una fecha pasada
    if (date < today) {
        return { 
            isPast: true, 
            hasUserReservation: false,
            capacity: null
        };
    }

    const capacity = props.capacities?.find(c => c.date === dateStr);
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
            }
        });
    }
};

const changeMonth = (offset) => {
    const newDate = new Date(props.currentYear, props.currentMonth - 1);
    newDate.setMonth(newDate.getMonth() + offset);
    
    router.get(route('cliente.reservations.index'), {
        year: newDate.getFullYear(),
        month: newDate.getMonth() + 1,
    }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Solicitar Reserva" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Solicitar Reserva de Cupo</h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- CASO 1: USUARIO SIN VEHÍCULOS -->
                <div v-if="!hasVehicles" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-lg font-medium text-gray-900">¡Un paso más!</h3>
                        <p class="mt-2 text-sm text-gray-600">Para poder solicitar una reserva, primero necesitas agregar al menos un vehículo a tu perfil.</p>
                        <Link :href="route('cliente.vehiculos.create')" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition">
                            Agregar mi Primer Vehículo
                        </Link>
                    </div>
                </div>
                
                <!-- CASO 2: USUARIO CON VEHÍCULOS -->
                <div v-else class="space-y-6">
                    <!-- SECCIÓN DE PRÓXIMAS RESERVAS -->
                    <div v-if="upcomingReservations.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Mis Próximas Reservas</h3>
                            <div class="space-y-2">
                                <div v-for="reservation in upcomingReservations" :key="reservation.id" class="text-sm text-gray-700 p-3 bg-blue-50 rounded-md flex justify-between items-center">
                                    <div>
                                        <strong>{{ formatDisplayDate(reservation.reservation_date) }}</strong> - 
                                        {{ vehicles.find(v => v.id === reservation.vehicle_id)?.marca }} 
                                        ({{ vehicles.find(v => v.id === reservation.vehicle_id)?.patente }})
                                    </div>
                                    <button 
                                        v-if="reservation.can_cancel"
                                        @click="openCancelModal(reservation.id, $event)"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 border border-red-300 rounded hover:bg-red-50 transition"
                                        title="Cancelar esta reserva">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN DEL CALENDARIO -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Solicitar Nueva Reserva</h3>
                            
                            <div class="flex justify-between items-center mb-4">
                                <button @click="changeMonth(-1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 transition">&lt;</button>
                                <h3 class="text-lg font-semibold">{{ displayMonth }} {{ displayYear }}</h3>
                                <button @click="changeMonth(1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 transition">&gt;</button>
                            </div>
                            <div class="grid grid-cols-7 gap-2 text-center">
                                <div v-for="day in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']" :key="day" class="font-bold text-sm text-gray-600 py-2">{{ day }}</div>
                                <div v-for="blank in firstDayOfMonth" :key="`blank-${blank}`"></div>
                                <div v-for="day in daysInMonth" :key="day"
                                     @click="openModal(day)"
                                     :class="{
                                         'cursor-not-allowed bg-gray-100 text-gray-400': getDayInfo(day).isPast || getDayInfo(day).hasUserReservation || !getDayInfo(day).capacity || getDayInfo(day).capacity.available_slots <= 0,
                                         'cursor-pointer hover:bg-blue-50': !getDayInfo(day).isPast && !getDayInfo(day).hasUserReservation && getDayInfo(day).capacity && getDayInfo(day).capacity.available_slots > 0,
                                         'bg-indigo-500 text-white cursor-default': getDayInfo(day).hasUserReservation,
                                     }"
                                     class="p-2 border rounded min-h-[7rem] flex flex-col justify-between transition">
                                    <div class="font-bold text-right">{{ day }}</div>
                                    <div class="text-xs text-left">
                                        <div v-if="getDayInfo(day).hasUserReservation">
                                            <p class="font-bold">Mi Reserva</p>
                                            <p class="text-indigo-200">{{ getDayInfo(day).vehiclePatente }}</p>
                                            <p v-if="getDayInfo(day).canCancel" class="text-indigo-200 text-xs mt-1">
                                                ✔️ Cancelable
                                            </p>
                                            <p v-else class="text-indigo-200 text-xs mt-1">
                                                ⚠️ No cancelable
                                            </p>
                                        </div>
                                        <div v-else-if="!getDayInfo(day).isPast && getDayInfo(day).capacity">
                                            <p v-if="getDayInfo(day).capacity.available_slots > 5" class="font-bold text-green-600">Disponible</p>
                                            <p v-else-if="getDayInfo(day).capacity.available_slots > 0" class="font-bold text-orange-500">Pocos Cupos</p>
                                            <p v-else class="font-bold text-red-500">Completo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN HISTORIAL DE RESERVAS -->
                    <div v-if="pastReservations.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Reservas</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Fecha</th>
                                            <th scope="col" class="px-6 py-3">Vehículo</th>
                                            <th scope="col" class="px-6 py-3">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="reservation in pastReservations" :key="reservation.id" class="bg-white border-b hover:bg-gray-50">
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                {{ formatDisplayDate(reservation.reservation_date) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ vehicles.find(v => v.id === reservation.vehicle_id)?.marca }} 
                                                {{ vehicles.find(v => v.id === reservation.vehicle_id)?.modelo }}
                                                ({{ vehicles.find(v => v.id === reservation.vehicle_id)?.patente }})
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full" 
                                                      :class="{
                                                          'bg-green-100 text-green-800': reservation.status === 'Asistió',
                                                          'bg-red-100 text-red-800': reservation.status === 'Cancelada',
                                                          'bg-gray-100 text-gray-800': reservation.status === 'Ausente',
                                                      }">
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
        <div v-if="showBookingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeModal">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h3 class="text-lg font-bold mb-4">Confirmar Reserva para el {{ selectedDay }}</h3>
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label for="vehicle_id" class="block font-medium text-sm text-gray-700">¿Qué vehículo traerás?</label>
                            <select id="vehicle_id" v-model="form.vehicle_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <!-- MOSTRAMOS TODOS los vehículos - el backend validará cuáles pueden reservar -->
                                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                                    {{ vehicle.marca }} {{ vehicle.modelo }} ({{ vehicle.patente }})
                                    <!-- SOLO mostrar "Ya tiene reserva activa" para reservas CONFIRMADAS -->
                                    <span v-if="vehiclesWithActiveReservations.has(vehicle.id)"> - Ya tiene reserva activa</span>
                                </option>
                            </select>
                            <div v-if="form.errors.vehicle_id" class="text-sm text-red-600 mt-1">{{ form.errors.vehicle_id }}</div>
                        </div>
                        <div>
                            <label for="notes" class="block font-medium text-sm text-gray-700">Notas Adicionales (Opcional)</label>
                            <textarea id="notes" v-model="form.notes" rows="3" placeholder="Ej: Falla en el motor, ruido al frenar, etc." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            <div v-if="form.errors.notes" class="text-sm text-red-600 mt-1">{{ form.errors.notes }}</div>
                        </div>
                         <div v-if="form.errors.reservation_date" class="text-sm text-red-600 mt-1 p-3 bg-red-50 rounded-md">{{ form.errors.reservation_date }}</div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition" :disabled="form.processing">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition" :disabled="form.processing">
                            <span v-if="form.processing">Enviando...</span>
                            <span v-else>Confirmar Solicitud</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN DE CANCELACIÓN -->
        <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h3 class="text-lg font-bold mb-4 text-red-600">Confirmar Cancelación</h3>
                <p class="mb-4">¿Estás seguro de que quieres cancelar esta reserva?</p>
                <p class="mb-4 text-sm text-gray-600">Esta acción no se puede deshacer.</p>
                <div class="flex justify-end space-x-3">
                    <button @click="closeCancelModal" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                        Mantener Reserva
                    </button>
                    <button @click="confirmCancel" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                        Sí, Cancelar Reserva
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>