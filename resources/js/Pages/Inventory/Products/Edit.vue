<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: props.product.name,
    sku: props.product.sku,
    description: props.product.description,
    cost: props.product.cost,
    iva_percentage: props.product.iva_percentage,
    profit_margin: props.product.profit_margin,
    current_stock: props.product.current_stock,
    min_threshold: props.product.min_threshold,
    // Asume que un producto tiene al menos un proveedor y lo preselecciona
    supplier_id: props.product.suppliers.length > 0 ? props.product.suppliers[0].id : null,
});

const calculatedPrice = computed(() => {
    const cost = parseFloat(form.cost) || 0;
    const ivaPercentage = parseFloat(form.iva_percentage) || 0;
    const profitMargin = parseFloat(form.profit_margin) || 0;
    const costWithIva = cost * (1 + ivaPercentage / 100);
    const finalPrice = costWithIva * (1 + profitMargin / 100);
    return finalPrice.toFixed(2);
});

const submit = () => {
    form.patch(route('inventario.products.update', props.product.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar Producto: ${product.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Editar Producto</h2>
                <Link
                    :href="route('inventario.products.index')"
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
                            <!-- SECCIÓN 1: DATOS DEL PRODUCTO -->
                            <div>
                                <h3
                                    class="text-xl font-bold text-gray-900 dark:text-white mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                                >
                                    Datos del Producto
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label
                                            for="name"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Nombre del Producto</label
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
                                            for="sku"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >SKU (Código Único)</label
                                        >
                                        <input
                                            id="sku"
                                            v-model="form.sku"
                                            type="text"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                        <div v-if="form.errors.sku" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                            {{ form.errors.sku }}
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="supplier_id"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Proveedor Principal</label
                                        >
                                        <select
                                            id="supplier_id"
                                            v-model="form.supplier_id"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        >
                                            <option
                                                v-for="supplier in suppliers"
                                                :key="supplier.id"
                                                :value="supplier.id"
                                            >
                                                {{ supplier.name }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="form.errors.supplier_id"
                                            class="text-sm text-red-600 dark:text-red-400 mt-1"
                                        >
                                            {{ form.errors.supplier_id }}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <label
                                        for="description"
                                        class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                        >Descripción (Opcional)</label
                                    >
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                        placeholder="Descripción del producto..."
                                    ></textarea>
                                </div>
                            </div>

                            <!-- SECCIÓN 2: PRECIOS Y STOCK -->
                            <div>
                                <h3
                                    class="text-xl font-bold text-gray-900 dark:text-white mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                                >
                                    Precios y Stock
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div>
                                        <label
                                            for="cost"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Precio de Costo ($)</label
                                        >
                                        <input
                                            id="cost"
                                            v-model="form.cost"
                                            type="number"
                                            step="0.01"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.cost"
                                            class="text-sm text-red-600 dark:text-red-400 mt-1"
                                        >
                                            {{ form.errors.cost }}
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="iva_percentage"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >IVA (%)</label
                                        >
                                        <input
                                            id="iva_percentage"
                                            v-model="form.iva_percentage"
                                            type="number"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="profit_margin"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Margen de Ganancia (%)</label
                                        >
                                        <input
                                            id="profit_margin"
                                            v-model="form.profit_margin"
                                            type="number"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                    </div>
                                    <div
                                        class="bg-gray-100 dark:bg-gray-700 p-6 rounded-xl border border-gray-200 dark:border-gray-600 flex items-center justify-center"
                                    >
                                        <div class="text-center">
                                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                                >Precio de Venta Calculado</label
                                            >
                                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                                ${{ calculatedPrice }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            for="current_stock"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Stock Actual</label
                                        >
                                        <input
                                            id="current_stock"
                                            v-model="form.current_stock"
                                            type="number"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="min_threshold"
                                            class="block font-medium text-sm text-gray-700 dark:text-gray-300"
                                            >Umbral de Stock Bajo</label
                                        >
                                        <input
                                            id="min_threshold"
                                            v-model="form.min_threshold"
                                            type="number"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
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
                                    <span v-else>Actualizar Producto</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
