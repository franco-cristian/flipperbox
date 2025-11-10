<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({ patente: '', marca: '', modelo: '', anio: new Date().getFullYear(), kilometraje: '' });
const submit = () => form.post(route('cliente.vehiculos.store'));
</script>
<template>
    <Head title="Agregar Vehículo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agregar Nuevo Vehículo</h2>
                <Link :href="route('cliente.vehiculos.index')" class="text-sm text-gray-700 underline">&larr; Volver a Mis Vehículos</Link>
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
                                    <input id="patente" type="text" v-model="form.patente" class="mt-1 block w-full" required autofocus />
                                    <div v-if="form.errors.patente" class="text-sm text-red-600 mt-1">{{ form.errors.patente }}</div>
                                </div>
                                <div>
                                    <label for="marca" class="block font-medium text-sm text-gray-700">Marca</label>
                                    <input id="marca" type="text" v-model="form.marca" class="mt-1 block w-full" required />
                                    <div v-if="form.errors.marca" class="text-sm text-red-600 mt-1">{{ form.errors.marca }}</div>
                                </div>
                                <div>
                                    <label for="modelo" class="block font-medium text-sm text-gray-700">Modelo</label>
                                    <input id="modelo" type="text" v-model="form.modelo" class="mt-1 block w-full" required />
                                    <div v-if="form.errors.modelo" class="text-sm text-red-600 mt-1">{{ form.errors.modelo }}</div>
                                </div>
                                <div>
                                    <label for="anio" class="block font-medium text-sm text-gray-700">Año</label>
                                    <input id="anio" type="number" v-model="form.anio" class="mt-1 block w-full" required />
                                    <div v-if="form.errors.anio" class="text-sm text-red-600 mt-1">{{ form.errors.anio }}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="kilometraje" class="block font-medium text-sm text-gray-700">Kilometraje (Opcional)</label>
                                    <input id="kilometraje" type="number" v-model="form.kilometraje" class="mt-1 block w-full" />
                                    <div v-if="form.errors.kilometraje" class="text-sm text-red-600 mt-1">{{ form.errors.kilometraje }}</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md" :disabled="form.processing">Guardar Vehículo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
