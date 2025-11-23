<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    supplier: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.supplier.name,
    contact_person: props.supplier.contact_person,
    phone: props.supplier.phone,
    email: props.supplier.email,
    address: props.supplier.address,
});

const submit = () => {
    form.patch(route('inventario.suppliers.update', props.supplier.id));
};
</script>

<template>
    <Head :title="`Editar Proveedor: ${supplier.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Editar Proveedor</h2>
                <Link
                    :href="route('inventario.suppliers.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline transition"
                >
                    &larr; Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-8">
                        <form class="space-y-8" @submit.prevent="submit">
                            <div>
                                <h3
                                    class="text-xl font-bold text-gray-900 dark:text-white mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                                >
                                    Información del Proveedor
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            for="name"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Nombre del Proveedor</label
                                        >
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                            autofocus
                                        />
                                        <div
                                            v-if="form.errors.name"
                                            class="text-sm text-red-600 dark:text-red-400 mt-1"
                                        >
                                            {{ form.errors.name }}
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="contact_person"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Persona de Contacto (Opcional)</label
                                        >
                                        <input
                                            id="contact_person"
                                            v-model="form.contact_person"
                                            type="text"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                        />
                                        <div
                                            v-if="form.errors.contact_person"
                                            class="text-sm text-red-600 dark:text-red-400 mt-1"
                                        >
                                            {{ form.errors.contact_person }}
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="phone"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Teléfono (Opcional)</label
                                        >
                                        <input
                                            id="phone"
                                            v-model="form.phone"
                                            type="text"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                        />
                                        <div
                                            v-if="form.errors.phone"
                                            class="text-sm text-red-600 dark:text-red-400 mt-1"
                                        >
                                            {{ form.errors.phone }}
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="email"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Email (Opcional)</label
                                        >
                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                        />
                                        <div
                                            v-if="form.errors.email"
                                            class="text-sm text-red-600 dark:text-red-400 mt-1"
                                        >
                                            {{ form.errors.email }}
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label
                                            for="address"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Dirección (Opcional)</label
                                        >
                                        <textarea
                                            id="address"
                                            v-model="form.address"
                                            rows="4"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            placeholder="Dirección completa del proveedor..."
                                        ></textarea>
                                        <div
                                            v-if="form.errors.address"
                                            class="text-sm text-red-600 dark:text-red-400 mt-1"
                                        >
                                            {{ form.errors.address }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700"
                            >
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">Actualizando...</span>
                                    <span v-else>Actualizar Proveedor</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
