<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    cliente: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    patente: '',
    marca: '',
    modelo: '',
    anio: new Date().getFullYear(),
    kilometraje: '',
    vin: '',
    numero_motor: '',
    observaciones: '',
});

const submit = () => {
    form.post(route('clientes.vehiculos.store', { user: props.cliente.id }));
};
</script>

<template>
    <Head title="Agregar Vehículo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Agregar Vehículo a: <span class="font-bold">{{ cliente.name }} {{ cliente.apellido }}</span>
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
                                    <input
                                        id="patente"
                                        v-model="form.patente"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        required
                                        autofocus
                                    />
                                    <div v-if="form.errors.patente" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.patente }}
                                    </div>
                                </div>

                                <!-- Campo Marca -->
                                <div>
                                    <label for="marca" class="block font-medium text-sm text-gray-700">Marca</label>
                                    <input
                                        id="marca"
                                        v-model="form.marca"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        required
                                    />
                                    <div v-if="form.errors.marca" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.marca }}
                                    </div>
                                </div>

                                <!-- Campo Modelo -->
                                <div>
                                    <label for="modelo" class="block font-medium text-sm text-gray-700">Modelo</label>
                                    <input
                                        id="modelo"
                                        v-model="form.modelo"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        required
                                    />
                                    <div v-if="form.errors.modelo" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.modelo }}
                                    </div>
                                </div>

                                <!-- Campo Año -->
                                <div>
                                    <label for="anio" class="block font-medium text-sm text-gray-700">Año</label>
                                    <input
                                        id="anio"
                                        v-model="form.anio"
                                        type="number"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        required
                                    />
                                    <div v-if="form.errors.anio" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.anio }}
                                    </div>
                                </div>

                                <!-- Campo Kilometraje -->
                                <div>
                                    <label for="kilometraje" class="block font-medium text-sm text-gray-700"
                                        >Kilometraje (Opcional)</label
                                    >
                                    <input
                                        id="kilometraje"
                                        v-model="form.kilometraje"
                                        type="number"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    />
                                    <div v-if="form.errors.kilometraje" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.kilometraje }}
                                    </div>
                                </div>

                                <!-- Campo VIN -->
                                <div>
                                    <label for="vin" class="block font-medium text-sm text-gray-700"
                                        >N° de Chasis (VIN) (Opcional)</label
                                    >
                                    <input
                                        id="vin"
                                        v-model="form.vin"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    />
                                    <div v-if="form.errors.vin" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.vin }}
                                    </div>
                                </div>

                                <!-- Campo Número de Motor -->
                                <div>
                                    <label for="numero_motor" class="block font-medium text-sm text-gray-700"
                                        >Número de Motor (Opcional)</label
                                    >
                                    <input
                                        id="numero_motor"
                                        v-model="form.numero_motor"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    />
                                    <div v-if="form.errors.numero_motor" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.numero_motor }}
                                    </div>
                                </div>

                                <!-- Campo Observaciones -->
                                <div class="md:col-span-2">
                                    <label for="observaciones" class="block font-medium text-sm text-gray-700"
                                        >Observaciones (Opcional)</label
                                    >
                                    <textarea
                                        id="observaciones"
                                        v-model="form.observaciones"
                                        rows="3"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    ></textarea>
                                    <div v-if="form.errors.observaciones" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.observaciones }}
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de Envío -->
                            <div class="flex items-center justify-end mt-6">
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                                    :disabled="form.processing"
                                >
                                    Guardar Vehículo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
