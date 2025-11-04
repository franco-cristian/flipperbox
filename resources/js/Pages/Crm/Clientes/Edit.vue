<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

// Recibimos la prop 'cliente' que nos envía el controlador
const props = defineProps({
    cliente: {
        type: Object,
        required: true,
    },
});

// Inicializamos el formulario con los datos existentes del cliente
const form = useForm({
    nombre: props.cliente.nombre,
    apellido: props.cliente.apellido,
    email: props.cliente.email,
    telefono: props.cliente.telefono,
    documento_tipo: props.cliente.documento_tipo,
    documento_valor: props.cliente.documento_valor,
});

const submit = () => {
    // Usamos el método PATCH (o PUT) para la actualización, pasando el ID del cliente
    form.patch(route('clientes.update', props.cliente.id), {
        // Opcional: preservar el scroll en caso de error de validación
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar Cliente: ${cliente.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Cliente</h2>
                <Link :href="route('clientes.index')" class="text-sm text-gray-700 underline">
                    &larr; Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Campos del formulario (iguales a Create.vue) -->
                                <div>
                                    <label for="nombre" class="block font-medium text-sm text-gray-700">Nombre</label>
                                    <input id="nombre" type="text" v-model="form.nombre" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required autofocus />
                                    <div v-if="form.errors.nombre" class="text-sm text-red-600 mt-1">{{ form.errors.nombre }}</div>
                                </div>
                                <div>
                                    <label for="apellido" class="block font-medium text-sm text-gray-700">Apellido</label>
                                    <input id="apellido" type="text" v-model="form.apellido" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.apellido" class="text-sm text-red-600 mt-1">{{ form.errors.apellido }}</div>
                                </div>
                                <div>
                                    <label for="telefono" class="block font-medium text-sm text-gray-700">Teléfono</label>
                                    <input id="telefono" type="text" v-model="form.telefono" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.telefono" class="text-sm text-red-600 mt-1">{{ form.errors.telefono }}</div>
                                </div>
                                <div>
                                    <label for="email" class="block font-medium text-sm text-gray-700">Email (Opcional)</label>
                                    <input id="email" type="email" v-model="form.email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                                </div>
                                <div>
                                    <label for="documento_tipo" class="block font-medium text-sm text-gray-700">Tipo de Documento (Opcional)</label>
                                    <select id="documento_tipo" v-model="form.documento_tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option>DNI</option>
                                        <option>Pasaporte</option>
                                        <option>Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="documento_valor" class="block font-medium text-sm text-gray-700">Número de Documento (Opcional)</label>
                                    <input id="documento_valor" type="text" v-model="form.documento_valor" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                                    <div v-if="form.errors.documento_valor" class="text-sm text-red-600 mt-1">{{ form.errors.documento_valor }}</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700" :disabled="form.processing">
                                    Actualizar Cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
