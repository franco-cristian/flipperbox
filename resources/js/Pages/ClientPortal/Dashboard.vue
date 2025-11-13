<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const statusClass = (status) => ({
    'bg-yellow-100 text-yellow-800': status === 'Pendiente',
    'bg-blue-100 text-blue-800': status === 'En Progreso',
    'bg-green-100 text-green-800': status === 'Completada',
    'bg-red-100 text-red-800': status === 'Cancelada',
});

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <Head title="Mi Portal" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Portal del Cliente</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">¡Bienvenido, {{ user.name }}!</h3>
                    <p class="mt-1 text-sm text-gray-600">Aquí puedes ver el historial de servicios para tus vehículos registrados.</p>
                </div>

                <div v-if="user.vehiculos && user.vehiculos.length > 0" class="space-y-6">
                    <div v-for="vehiculo in user.vehiculos" :key="vehiculo.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-800 border-b pb-4 mb-4">
                                {{ vehiculo.marca }} {{ vehiculo.modelo }} <span class="font-mono bg-gray-100 text-gray-600 px-2 py-1 rounded-md text-base">{{ vehiculo.patente }}</span>
                            </h3>
                            <div v-if="vehiculo.work_orders && vehiculo.work_orders.length > 0">
                                <h4 class="text-md font-medium text-gray-600 mb-2">Historial de Servicios:</h4>
                                <ul class="space-y-3">
                                    <li v-for="orden in vehiculo.work_orders" :key="orden.id" class="p-3 border rounded-md hover:bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold">{{ orden.description }}</p>
                                            <p class="text-sm text-gray-500">Fecha: {{ formatDate(orden.entry_date) }}</p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span class="px-2 py-1 text-xs font-semibold leading-tight rounded-full" :class="statusClass(orden.status)">
                                                {{ orden.status }}
                                            </span>
                                            <Link :href="route('work-orders.show', orden.id)" class="text-blue-600 hover:underline text-sm">Ver Detalle</Link>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-sm text-gray-500">Este vehículo aún no tiene un historial de servicios.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-10">
                    <p class="text-gray-600">Aún no has registrado ningún vehículo.</p>
                    <Link :href="route('cliente.vehiculos.index')" class="mt-4 inline-block text-blue-600 hover:underline">
                        Ve a "Mis Vehículos" para agregar tu primero.
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
