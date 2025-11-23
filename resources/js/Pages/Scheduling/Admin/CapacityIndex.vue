<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
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
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Gestión de Cupos Diarios</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <!-- Controles del Calendario -->
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

                        <!-- Calendario -->
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
                                class="p-3 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 min-h-[5rem] flex flex-col justify-between transition-all duration-200 bg-white dark:bg-gray-800"
                                @click="openModal(day)"
                            >
                                <div class="font-bold text-right text-gray-700 dark:text-gray-300">{{ day }}</div>
                                <div v-if="getCapacityForDate(day)" class="text-xs text-left space-y-1">
                                    <p class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Reservados:</span>
                                        <span class="font-bold text-green-600 dark:text-green-400">{{
                                            getCapacityForDate(day).booked_slots
                                        }}</span>
                                    </p>
                                    <p class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total:</span>
                                        <span class="font-bold text-blue-600 dark:text-blue-400">{{
                                            getCapacityForDate(day).total_slots
                                        }}</span>
                                    </p>
                                </div>
                                <div v-else class="text-xs text-left text-gray-400 dark:text-gray-500 italic">
                                    Sin definir
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar capacidad -->
        <div
            v-if="selectedDate"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300"
            @click.self="closeModal"
        >
            <div
                class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100 dark:border-gray-700 transition-colors duration-300"
            >
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6">
                    Establecer Capacidad para el {{ selectedDate }}
                </h3>

                <!-- Mostrar errores generales del formulario -->
                <div
                    v-if="form.hasErrors"
                    class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl"
                >
                    <p class="font-semibold">Por favor, corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li v-for="error in Object.values(form.errors)" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <InputLabel for="total_slots" value="Número Total de Cupos" />
                        <input
                            id="total_slots"
                            v-model.number="form.total_slots"
                            type="number"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm transition-colors duration-200"
                            :class="{ 'border-red-500 dark:border-red-400': form.errors.total_slots }"
                            min="1"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.total_slots" />
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
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
