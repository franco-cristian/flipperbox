<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

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
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                    Agregar Vehículo a: <span class="font-bold">{{ cliente.name }} {{ cliente.apellido }}</span>
                </h2>
                <Link
                    :href="route('clientes.show', { user: cliente.id })"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline transition"
                >
                    &larr; Volver al cliente
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Campo Patente -->
                                <div>
                                    <InputLabel for="patente" value="Patente" />
                                    <TextInput
                                        id="patente"
                                        v-model="form.patente"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.patente" />
                                </div>

                                <!-- Campo Marca -->
                                <div>
                                    <InputLabel for="marca" value="Marca" />
                                    <TextInput
                                        id="marca"
                                        v-model="form.marca"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.marca" />
                                </div>

                                <!-- Campo Modelo -->
                                <div>
                                    <InputLabel for="modelo" value="Modelo" />
                                    <TextInput
                                        id="modelo"
                                        v-model="form.modelo"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.modelo" />
                                </div>

                                <!-- Campo Año -->
                                <div>
                                    <InputLabel for="anio" value="Año" />
                                    <TextInput
                                        id="anio"
                                        v-model.number="form.anio"
                                        type="number"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.anio" />
                                </div>

                                <!-- Campo Kilometraje -->
                                <div>
                                    <InputLabel for="kilometraje" value="Kilometraje (Opcional)" />
                                    <TextInput
                                        id="kilometraje"
                                        v-model.number="form.kilometraje"
                                        type="number"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.kilometraje" />
                                </div>

                                <!-- Campo VIN -->
                                <div>
                                    <InputLabel for="vin" value="N° de Chasis (VIN) (Opcional)" />
                                    <TextInput id="vin" v-model="form.vin" type="text" class="mt-1 block w-full" />
                                    <InputError class="mt-2" :message="form.errors.vin" />
                                </div>

                                <!-- Campo Número de Motor -->
                                <div>
                                    <InputLabel for="numero_motor" value="Número de Motor (Opcional)" />
                                    <TextInput
                                        id="numero_motor"
                                        v-model="form.numero_motor"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.numero_motor" />
                                </div>

                                <!-- Campo Observaciones -->
                                <div class="md:col-span-2">
                                    <InputLabel for="observaciones" value="Observaciones (Opcional)" />
                                    <textarea
                                        id="observaciones"
                                        v-model="form.observaciones"
                                        rows="4"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm transition-colors duration-200"
                                        placeholder="Observaciones adicionales sobre el vehículo..."
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.observaciones" />
                                </div>
                            </div>

                            <!-- Botón de Envío -->
                            <div
                                class="flex items-center justify-end pt-6 border-t border-gray-100 dark:border-gray-700"
                            >
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">Guardando...</span>
                                    <span v-else>Guardar Vehículo</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
