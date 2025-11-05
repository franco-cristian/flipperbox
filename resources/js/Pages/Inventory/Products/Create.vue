<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    sku: '',
    description: '',
    price: 0,
    current_stock: 0,
    min_threshold: 5,
});

const submit = () => {
    form.post(route('inventario.products.store'));
};
</script>

<template>
    <Head title="Crear Producto" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nuevo Producto</h2>
                <Link :href="route('inventario.products.index')" class="text-sm text-gray-700 underline">
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
                                <!-- Campo Nombre -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block font-medium text-sm text-gray-700">Nombre del Producto</label>
                                    <input id="name" type="text" v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required autofocus />
                                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                                </div>

                                <!-- Campo SKU -->
                                <div>
                                    <label for="sku" class="block font-medium text-sm text-gray-700">SKU (Código Único)</label>
                                    <input id="sku" type="text" v-model="form.sku" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.sku" class="text-sm text-red-600 mt-1">{{ form.errors.sku }}</div>
                                </div>

                                <!-- Campo Precio de Venta -->
                                <div>
                                    <label for="price" class="block font-medium text-sm text-gray-700">Precio de Venta ($)</label>
                                    <input id="price" type="number" step="0.01" v-model="form.price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.price" class="text-sm text-red-600 mt-1">{{ form.errors.price }}</div>
                                </div>

                                <!-- Campo Stock Inicial -->
                                <div>
                                    <label for="current_stock" class="block font-medium text-sm text-gray-700">Stock Inicial</label>
                                    <input id="current_stock" type="number" v-model="form.current_stock" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.current_stock" class="text-sm text-red-600 mt-1">{{ form.errors.current_stock }}</div>
                                </div>

                                <!-- Campo Umbral Mínimo -->
                                <div>
                                    <label for="min_threshold" class="block font-medium text-sm text-gray-700">Umbral de Stock Bajo</label>
                                    <input id="min_threshold" type="number" v-model="form.min_threshold" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
                                    <div v-if="form.errors.min_threshold" class="text-sm text-red-600 mt-1">{{ form.errors.min_threshold }}</div>
                                </div>
                                
                                <!-- Campo Descripción -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block font-medium text-sm text-gray-700">Descripción (Opcional)</label>
                                    <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                                    <div v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</div>
                                </div>
                            </div>

                            <!-- Botón de Envío -->
                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700" :disabled="form.processing">
                                    Guardar Producto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>