<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.service.name,
    description: props.service.description,
    price: props.service.price,
});

const submit = () => {
    form.patch(route('services.update', props.service.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar Servicio: ${service.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Editar Servicio</h2>
                <Link
                    :href="route('services.index')"
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
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Nombre -->
                                <div>
                                    <InputLabel for="name" value="Nombre del Servicio" />
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

                                <!-- Descripción -->
                                <div>
                                    <InputLabel for="description" value="Descripción (Opcional)" />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- Precio -->
                                <div>
                                    <InputLabel for="price" value="Precio de Mano de Obra ($)" />
                                    <TextInput
                                        id="price"
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full sm:w-1/3"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.price" />
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-700"
                            >
                                <button
                                    type="submit"
                                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 flex items-center gap-2 disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">Guardando...</span>
                                    <span v-else>Actualizar Servicio</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
