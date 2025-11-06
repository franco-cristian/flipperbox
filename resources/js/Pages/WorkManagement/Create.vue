<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    vehiculo: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    vehicle_id: props.vehiculo.id,
    description: '',
});

const submit = () => {
    form.post(route('work-orders.store'));
};
</script>
<template>
    <Head title="Crear Orden de Trabajo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Orden de Trabajo</h2>
                <Link :href="route('clientes.show', vehiculo.cliente_id)" class="text-sm text-gray-700 underline">
                    &larr; Volver al Cliente
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6 p-4 border rounded-md">
                            <h3 class="font-bold text-lg">Vehículo Seleccionado</h3>
                            <p class="text-sm text-gray-600">Cliente: {{ vehiculo.cliente.nombre }} {{ vehiculo.cliente.apellido }}</p>
                            <p class="text-sm text-gray-600">Patente: {{ vehiculo.patente }}</p>
                            <p class="text-sm text-gray-600">Modelo: {{ vehiculo.marca }} {{ vehiculo.modelo }} ({{ vehiculo.anio }})</p>
                        </div>
                        <form @submit.prevent="submit">
                            <div>
                                <label for="description" class="block font-medium text-sm text-gray-700">Descripción del Problema o Servicio Solicitado</label>
                                <textarea id="description" v-model="form.description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required autofocus></textarea>
                                <div v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" class="px-6 py-3 bg-gray-800 text-white rounded-md hover:bg-gray-700 font-semibold" :disabled="form.processing">
                                    Crear Orden de Trabajo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
