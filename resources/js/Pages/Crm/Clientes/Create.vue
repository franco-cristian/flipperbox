<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    apellido: '',
    telefono: '',
    email: '',
    documento_tipo: 'DNI',
    documento_valor: '',
});

const submit = () => {
    form.post(route('clientes.store'));
};
</script>

<template>
    <Head title="Crear Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Crear Nuevo Cliente</h2>
                <Link
                    :href="route('clientes.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline transition"
                >
                    &larr; Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nombre -->
                                <div>
                                    <InputLabel for="name" value="Nombre" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <!-- Apellido -->
                                <div>
                                    <InputLabel for="apellido" value="Apellido" />
                                    <TextInput
                                        id="apellido"
                                        v-model="form.apellido"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.apellido" />
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <InputLabel for="telefono" value="Teléfono" />
                                    <TextInput
                                        id="telefono"
                                        v-model="form.telefono"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Ej: 3704..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.telefono" />
                                </div>

                                <!-- Email -->
                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <!-- Tipo Doc -->
                                <div>
                                    <InputLabel for="documento_tipo" value="Tipo de Documento" />
                                    <select
                                        id="documento_tipo"
                                        v-model="form.documento_tipo"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    >
                                        <option>DNI</option>
                                        <option>Pasaporte</option>
                                        <option>Otro</option>
                                    </select>
                                </div>

                                <!-- Valor Doc -->
                                <div>
                                    <InputLabel for="documento_valor" value="Número de Documento" />
                                    <TextInput
                                        id="documento_valor"
                                        v-model="form.documento_valor"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.documento_valor" />
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-700"
                            >
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 flex items-center gap-2"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">Guardando...</span>
                                    <span v-else>Guardar Cliente</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
