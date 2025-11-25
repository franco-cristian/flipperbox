<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: Object,
});

const statusClass = (status) => ({
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': status === 'Pendiente',
    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': status === 'En Progreso',
    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': status === 'Completada',
    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': status === 'Cancelada',
});

const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <Head title="Mi Portal" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Portal del Cliente</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Tarjeta de Bienvenida -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            ¡Bienvenido/a, {{ user.name }} {{ user.apellido }}!
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Aquí puedes ver el historial de servicios para tus vehículos registrados.
                        </p>
                    </div>
                </div>

                <!-- Vehículos y Historial -->
                <div v-if="user.vehiculos && user.vehiculos.length > 0" class="space-y-6">
                    <div
                        v-for="vehiculo in user.vehiculos"
                        :key="vehiculo.id"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-8">
                            <h3
                                class="text-xl font-bold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-4 mb-6"
                            >
                                {{ vehiculo.marca }} {{ vehiculo.modelo }}
                                <span
                                    class="font-mono bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-3 py-1 rounded-xl text-base ml-2"
                                >
                                    {{ vehiculo.patente }}
                                </span>
                            </h3>

                            <div v-if="vehiculo.work_orders && vehiculo.work_orders.length > 0">
                                <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">
                                    Historial de Servicios:
                                </h4>
                                <ul class="space-y-4">
                                    <li
                                        v-for="orden in vehiculo.work_orders"
                                        :key="orden.id"
                                        class="p-4 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200 flex justify-between items-center"
                                    >
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                {{ orden.description }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                Fecha: {{ formatDate(orden.entry_date) }}
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span
                                                class="px-3 py-1 text-xs font-semibold leading-tight rounded-full"
                                                :class="statusClass(orden.status)"
                                            >
                                                {{ orden.status }}
                                            </span>
                                            <Link
                                                :href="route('work-orders.show', orden.id)"
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-md transition-all duration-200 text-sm"
                                            >
                                                Ver Detalle
                                            </Link>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">
                                    Este vehículo aún no tiene un historial de servicios.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sin vehículos -->
                <div v-else class="text-center py-12">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300 p-8"
                    >
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Aún no has registrado ningún vehículo.</p>
                        <Link
                            :href="route('cliente.vehiculos.index')"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-md transition-all duration-200 inline-flex items-center gap-2"
                        >
                            Ir a Mis Vehículos
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
