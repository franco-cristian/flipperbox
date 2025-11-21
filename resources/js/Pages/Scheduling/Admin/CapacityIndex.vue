<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    capacities: Array,
    currentYear: Number,
    currentMonth: Number,
});

const selectedDate = ref(null);
const selectedCapacity = ref(null);

const form = useForm({
    date: '',
    total_slots: 10,
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

// Computed properties
const displayMonth = computed(() => monthNames[props.currentMonth - 1]);
const displayYear = computed(() => props.currentYear);

const daysInMonth = computed(() => {
    return new Date(props.currentYear, props.currentMonth, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    const date = new Date(props.currentYear, props.currentMonth - 1, 1);
    let day = date.getDay();
    return day === 0 ? 6 : day - 1;
});

// Función para obtener capacidad por fecha
const getCapacityForDate = (day) => {
    const dateStr = `${props.currentYear}-${String(props.currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    // Buscar la capacidad para esta fecha
    const capacity = props.capacities.find((c) => {
        // Extraer solo la parte de la fecha (YYYY-MM-DD) del string ISO
        const capacityDate = c.date.split('T')[0];
        return capacityDate === dateStr;
    });

    return capacity;
};

const openModal = (day) => {
    const date = new Date(props.currentYear, props.currentMonth - 1, day);
    selectedDate.value = date.toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    form.date = `${props.currentYear}-${String(props.currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    const capacity = getCapacityForDate(day);
    form.total_slots = capacity ? capacity.total_slots : 10;
    selectedCapacity.value = capacity;
};

const closeModal = () => {
    selectedDate.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(route('admin.scheduling.capacities.store'), {
        onSuccess: () => {
            closeModal();
        },
        onError: (_errors) => {
            // Los errores se mostrarán automáticamente en los campos
        },
        preserveState: false,
    });
};

const changeMonth = (offset) => {
    const newDate = new Date(props.currentYear, props.currentMonth - 1);
    newDate.setMonth(newDate.getMonth() + offset);

    router.get(
        route('admin.scheduling.capacities.index'),
        {
            year: newDate.getFullYear(),
            month: newDate.getMonth() + 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <Head title="Gestión de Cupos" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Cupos Diarios</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Controles del Calendario -->
                        <div class="flex justify-between items-center mb-4">
                            <button
                                class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 transition"
                                @click="changeMonth(-1)"
                            >
                                &lt;
                            </button>
                            <h3 class="text-lg font-semibold">{{ displayMonth }} {{ displayYear }}</h3>
                            <button
                                class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 transition"
                                @click="changeMonth(1)"
                            >
                                &gt;
                            </button>
                        </div>

                        <!-- Calendario -->
                        <div class="grid grid-cols-7 gap-2 text-center">
                            <div
                                v-for="day in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']"
                                :key="day"
                                class="font-bold text-sm text-gray-600 py-2"
                            >
                                {{ day }}
                            </div>
                            <div v-for="blank in firstDayOfMonth" :key="`blank-${blank}`"></div>
                            <div
                                v-for="day in daysInMonth"
                                :key="day"
                                class="p-2 border rounded cursor-pointer hover:bg-gray-50 min-h-[6rem] flex flex-col justify-between transition"
                                @click="openModal(day)"
                            >
                                <div class="font-bold text-right">{{ day }}</div>
                                <div v-if="getCapacityForDate(day)" class="text-xs text-left">
                                    <p>
                                        Reservados:
                                        <span class="font-bold text-green-600">{{
                                            getCapacityForDate(day).booked_slots
                                        }}</span>
                                    </p>
                                    <p>
                                        Total:
                                        <span class="font-bold text-blue-600">{{
                                            getCapacityForDate(day).total_slots
                                        }}</span>
                                    </p>
                                </div>
                                <div v-else class="text-xs text-left text-gray-400">Sin definir</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar capacidad -->
        <div
            v-if="selectedDate"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeModal"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Establecer Capacidad para el {{ selectedDate }}</h3>

                <!-- Mostrar errores generales del formulario -->
                <div v-if="form.hasErrors" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <p>Por favor, corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside mt-1">
                        <li v-for="error in Object.values(form.errors)" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submit">
                    <div>
                        <label for="total_slots" class="block font-medium text-sm text-gray-700"
                            >Número Total de Cupos</label
                        >
                        <input
                            id="total_slots"
                            v-model="form.total_slots"
                            type="number"
                            :class="{ 'border-red-500': form.errors.total_slots }"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            min="1"
                            required
                        />
                        <div v-if="form.errors.total_slots" class="text-sm text-red-600 mt-1">
                            {{ form.errors.total_slots }}
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
