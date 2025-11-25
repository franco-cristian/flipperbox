<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    tasks: Array,
});

const statusClass = (status) => ({
    'bg-yellow-100 text-yellow-800 border-yellow-200': status === 'Pendiente',
    'bg-blue-100 text-blue-800 border-blue-200': status === 'En Progreso',
});
</script>

<template>
    <Head title="Mis Tareas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Mis Tareas Asignadas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Mensaje si no hay tareas -->
                <div
                    v-if="tasks.length === 0"
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl p-8 text-center"
                >
                    <p class="text-gray-500 text-lg">¡Todo limpio! No tienes vehículos asignados por el momento.</p>
                </div>

                <!-- Lista de Tareas (Cards) -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-2xl border border-gray-200 dark:border-gray-700 flex flex-col"
                    >
                        <!-- Cabecera de la Tarjeta -->
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-start"
                        >
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ task.vehicle?.marca }} {{ task.vehicle?.modelo }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono mt-1">
                                    Patente: {{ task.vehicle?.patente }}
                                </p>
                            </div>
                            <span
                                class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full border"
                                :class="statusClass(task.status)"
                            >
                                {{ task.status }}
                            </span>
                        </div>

                        <!-- Cuerpo: Lo que hay que hacer -->
                        <div class="p-6 flex-1">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                                Trabajo a realizar:
                            </h4>
                            <p class="text-gray-800 dark:text-gray-200 text-base leading-relaxed whitespace-pre-line">
                                {{ task.description }}
                            </p>
                        </div>

                        <!-- Footer: Info adicional -->
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 flex justify-between"
                        >
                            <span
                                >Cliente: {{ task.vehicle?.cliente?.name }} {{ task.vehicle?.cliente?.apellido }}</span
                            >
                            <span>Ingreso: {{ new Date(task.entry_date).toLocaleDateString('es-AR') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
