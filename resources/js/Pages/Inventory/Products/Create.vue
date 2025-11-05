<!-- Archivo: resources/js/Pages/Inventory/Products/Create.vue (VERSIÓN FINAL SIMPLIFICADA) -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    suppliers: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    sku: '',
    description: '',
    cost: 0.00, // <-- ÚNICO CAMPO DE COSTO
    iva_percentage: 21,
    profit_margin: 40,
    current_stock: 0,
    min_threshold: 5,
    supplier_id: props.suppliers.length > 0 ? props.suppliers[0].id : null,
});

const calculatedPrice = computed(() => {
    const cost = parseFloat(form.cost) || 0;
    const ivaPercentage = parseFloat(form.iva_percentage) || 0;
    const profitMargin = parseFloat(form.profit_margin) || 0;
    const costWithIva = cost * (1 + (ivaPercentage / 100));
    const finalPrice = costWithIva * (1 + (profitMargin / 100));
    return finalPrice.toFixed(2);
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
                            <!-- SECCIÓN 1: DATOS DEL PRODUCTO -->
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Datos del Producto</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="name" class="block font-medium text-sm text-gray-700">Nombre del Producto</label>
                                    <input id="name" type="text" v-model="form.name" class="mt-1 block w-full" required autofocus />
                                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label for="sku" class="block font-medium text-sm text-gray-700">SKU (Código Único)</label>
                                    <input id="sku" type="text" v-model="form.sku" class="mt-1 block w-full" required />
                                    <div v-if="form.errors.sku" class="text-sm text-red-600 mt-1">{{ form.errors.sku }}</div>
                                </div>
                                <div>
                                    <label for="supplier_id" class="block font-medium text-sm text-gray-700">Proveedor</label>
                                    <select id="supplier_id" v-model="form.supplier_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                                    </select>
                                    <div v-if="form.errors.supplier_id" class="text-sm text-red-600 mt-1">{{ form.errors.supplier_id }}</div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <label for="description" class="block font-medium text-sm text-gray-700">Descripción (Opcional)</label>
                                <textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full"></textarea>
                            </div>

                            <!-- SECCIÓN 2: PRECIOS Y STOCK -->
                            <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4 border-b pb-2">Precios y Stock</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <label for="cost" class="block font-medium text-sm text-gray-700">Precio de Costo ($)</label>
                                    <input id="cost" type="number" step="0.01" v-model="form.cost" class="mt-1 block w-full" required />
                                    <div v-if="form.errors.cost" class="text-sm text-red-600 mt-1">{{ form.errors.cost }}</div>
                                </div>
                                <div>
                                    <label for="iva_percentage" class="block font-medium text-sm text-gray-700">IVA (%)</label>
                                    <input id="iva_percentage" type="number" v-model="form.iva_percentage" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <label for="profit_margin" class="block font-medium text-sm text-gray-700">Margen de Ganancia (%)</label>
                                    <input id="profit_margin" type="number" v-model="form.profit_margin" class="mt-1 block w-full" required />
                                </div>
                                <div class="bg-gray-100 p-4 rounded-md flex items-center justify-center">
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700">Precio de Venta Calculado</label>
                                        <p class="text-2xl font-bold text-gray-900 mt-1">${{ calculatedPrice }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label for="current_stock" class="block font-medium text-sm text-gray-700">Stock Inicial</label>
                                    <input id="current_stock" type="number" v-model="form.current_stock" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <label for="min_threshold" class="block font-medium text-sm text-gray-700">Umbral de Stock Bajo</label>
                                    <input id="min_threshold" type="number" v-model="form.min_threshold" class="mt-1 block w-full" required />
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end mt-8">
                                <button type="submit" class="px-6 py-3 bg-gray-800 text-white rounded-md hover:bg-gray-700 font-semibold" :disabled="form.processing">
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