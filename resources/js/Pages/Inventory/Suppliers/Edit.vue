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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Proveedor</h2>
                <Link :href="route('inventario.suppliers.index')" class="text-sm text-gray-700 underline">
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
                                <div>
                                    <label for="name" class="block font-medium text-sm text-gray-700"
                                        >Nombre del Proveedor</label
                                    >
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                    />
                                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.name }}
                                    </div>
                                </div>
                                <div>
                                    <label for="contact_person" class="block font-medium text-sm text-gray-700"
                                        >Persona de Contacto (Opcional)</label
                                    >
                                    <input
                                        id="contact_person"
                                        v-model="form.contact_person"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.contact_person" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.contact_person }}
                                    </div>
                                </div>
                                <div>
                                    <label for="phone" class="block font-medium text-sm text-gray-700"
                                        >Teléfono (Opcional)</label
                                    >
                                    <input id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                                    <div v-if="form.errors.phone" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.phone }}
                                    </div>
                                </div>
                                <div>
                                    <label for="email" class="block font-medium text-sm text-gray-700"
                                        >Email (Opcional)</label
                                    >
                                    <input id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.email }}
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="address" class="block font-medium text-sm text-gray-700"
                                        >Dirección (Opcional)</label
                                    >
                                    <textarea
                                        id="address"
                                        v-model="form.address"
                                        rows="3"
                                        class="mt-1 block w-full"
                                    ></textarea>
                                    <div v-if="form.errors.address" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.address }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                                    :disabled="form.processing"
                                >
                                    Actualizar Proveedor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
