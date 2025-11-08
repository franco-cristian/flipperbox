<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    cliente: {
        type: Object,
        required: true,
    },
    vehiculo: {
        type: Object,
        required: true,
    }
});

const form = useForm({
    patente: props.vehiculo.patente,
    marca: props.vehiculo.marca,
    modelo: props.vehiculo.modelo,
    anio: props.vehiculo.anio,
    kilometraje: props.vehiculo.kilometraje,
    vin: props.vehiculo.vin,
    numero_motor: props.vehiculo.numero_motor,
    observaciones: props.vehiculo.observaciones,
});

const submit = () => {
    form.patch(route('clientes.vehiculos.update', { user: props.cliente.id, vehiculo: props.vehiculo.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar Vehículo: ${vehiculo.patente}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                 <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Editar Vehículo: <span class="font-bold">{{ vehiculo.patente }}</span>
                </h2>
                <Link :href="route('clientes.show', { user: cliente.id })" class="text-sm text-gray-700 underline">
                    &larr; Volver al cliente
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Campo Patente -->
                                <div>
                                    <label for="patente" class="block font-medium text-sm text-gray-700">Patente</label>
                                    <input id="patente" type="text" v-model="form.patente" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required autofocus />
                                    <div v-if="form.errors.patente" class="text-sm text-red-600 mt-1">{{ form.errors.patente }}</div>
                                </div>

                                <!-- Campo Marca -->
                                <div>
                                    <label for="marca" class="block font-medium text-sm text-gray-700">Marca</label>
                                    <input id="marca" type="text" v-model="form.marca" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.marca" class="text-sm text-red-600 mt-1">{{ form.errors.marca }}</div>
                                </div>

                                <!-- Campo Modelo -->
                                <div>
                                    <label for="modelo" class="block font-medium text-sm text-gray-700">Modelo</label>
                                    <input id="modelo" type="text" v-model="form.modelo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.modelo" class="text-sm text-red-600 mt-1">{{ form.errors.modelo }}</div>
                                </div>

                                <!-- Campo Año -->
                                <div>
                                    <label for="anio" class="block font-medium text-sm text-gray-700">Año</label>
                                    <input id="anio" type="number" v-model="form.anio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.anio" class="text-sm text-red-600 mt-1">{{ form.errors.anio }}</div>
                                </div>

                                <!-- Campo Kilometraje -->
                                <div>
                                    <label for="kilometraje" class="block font-medium text-sm text-gray-700">Kilometraje (Opcional)</label>
                                    <input id="kilometraje" type="number" v-model="form.kilometraje" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <div v-if="form.errors.kilometraje" class="text-sm text-red-600 mt-1">{{ form.errors.kilometraje }}</div>
                                </div>

                                <!-- Campo VIN -->
                                <div>
                                    <label for="vin" class="block font-medium text-sm text-gray-700">N° de Chasis (VIN) (Opcional)</label>
                                    <input id="vin" type="text" v-model="form.vin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <div v-if="form.errors.vin" class="text-sm text-red-600 mt-1">{{ form.errors.vin }}</div>
                                </div>

                                <!-- Campo Número de Motor -->
                                <div>
                                    <label for="numero_motor" class="block font-medium text-sm text-gray-700">Número de Motor (Opcional)</label>
                                    <input id="numero_motor" type="text" v-model="form.numero_motor" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <div v-if="form.errors.numero_motor" class="text-sm text-red-600 mt-1">{{ form.errors.numero_motor }}</div>
                                </div>

                                <!-- Campo Observaciones -->
                                <div class="md:col-span-2">
                                    <label for="observaciones" class="block font-medium text-sm text-gray-700">Observaciones (Opcional)</label>
                                    <textarea id="observaciones" v-model="form.observaciones" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                                    <div v-if="form.errors.observaciones" class="text-sm text-red-600 mt-1">{{ form.errors.observaciones }}</div>
                                </div>
                            </div>

                            <!-- Botón de Envío -->
                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700" :disabled="form.processing">
                                    Actualizar Vehículo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>