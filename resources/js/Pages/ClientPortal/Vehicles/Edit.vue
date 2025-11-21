<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ vehiculo: Object });

const form = useForm({
    patente: props.vehiculo.patente,
    marca: props.vehiculo.marca,
    modelo: props.vehiculo.modelo,
    anio: props.vehiculo.anio,
    kilometraje: props.vehiculo.kilometraje,
});

const submit = () => form.patch(route('cliente.vehiculos.update', props.vehiculo.id));
</script>
<template>
    <Head :title="`Editar Vehículo: ${vehiculo.patente}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Vehículo</h2>
                <Link :href="route('cliente.vehiculos.index')" class="text-sm text-gray-700 underline"
                    >&larr; Volver a Mis Vehículos</Link
                >
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="patente" class="block font-medium text-sm text-gray-700">Patente</label>
                                    <input
                                        id="patente"
                                        v-model="form.patente"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                    />
                                    <div v-if="form.errors.patente" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.patente }}
                                    </div>
                                </div>
                                <div>
                                    <label for="marca" class="block font-medium text-sm text-gray-700">Marca</label>
                                    <input
                                        id="marca"
                                        v-model="form.marca"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <div v-if="form.errors.marca" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.marca }}
                                    </div>
                                </div>
                                <div>
                                    <label for="modelo" class="block font-medium text-sm text-gray-700">Modelo</label>
                                    <input
                                        id="modelo"
                                        v-model="form.modelo"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <div v-if="form.errors.modelo" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.modelo }}
                                    </div>
                                </div>
                                <div>
                                    <label for="anio" class="block font-medium text-sm text-gray-700">Año</label>
                                    <input
                                        id="anio"
                                        v-model="form.anio"
                                        type="number"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <div v-if="form.errors.anio" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.anio }}
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="kilometraje" class="block font-medium text-sm text-gray-700"
                                        >Kilometraje (Opcional)</label
                                    >
                                    <input
                                        id="kilometraje"
                                        v-model="form.kilometraje"
                                        type="number"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.kilometraje" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.kilometraje }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-gray-800 text-white rounded-md"
                                    :disabled="form.processing"
                                >
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
