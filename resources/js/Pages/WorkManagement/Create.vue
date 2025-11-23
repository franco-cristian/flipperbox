<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

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
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Nueva Orden de Trabajo</h2>
                <Link
                    :href="route('clientes.show', { user: vehiculo.user_id })"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline transition"
                >
                    &larr; Volver al Cliente
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <!-- Información del Vehículo -->
                        <div
                            class="mb-8 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600"
                        >
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-3">Vehículo Seleccionado</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Cliente:</span>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ vehiculo.cliente.name }} {{ vehiculo.cliente.apellido }}
                                    </p>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Patente:</span>
                                    <p
                                        class="text-gray-900 dark:text-white font-mono bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded inline-block"
                                    >
                                        {{ vehiculo.patente }}
                                    </p>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Marca y Modelo:</span>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ vehiculo.marca }} {{ vehiculo.modelo }}
                                    </p>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Año:</span>
                                    <p class="text-gray-900 dark:text-white">{{ vehiculo.anio }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario -->
                        <form class="space-y-6" @submit.prevent="submit">
                            <div>
                                <InputLabel for="description" value="Descripción del Problema o Servicio Solicitado" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="6"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm transition-colors duration-200"
                                    required
                                    autofocus
                                    placeholder="Describa el problema o servicio requerido..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div
                                class="flex items-center justify-end pt-6 border-t border-gray-100 dark:border-gray-700"
                            >
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 flex items-center gap-2"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">Creando...</span>
                                    <span v-else>Crear Orden de Trabajo</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
