<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
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
    cost: 0.0,
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
    const costWithIva = cost * (1 + ivaPercentage / 100);
    const finalPrice = costWithIva * (1 + profitMargin / 100);
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
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Crear Nuevo Producto</h2>
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
                                        <InputLabel for="name" value="Nombre del Producto" />
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
                                    <div>
                                        <InputLabel for="sku" value="SKU (Código Único)" />
                                        <TextInput
                                            id="sku"
                                            v-model="form.sku"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.sku" />
                                    </div>
                                    <div>
                                        <InputLabel for="supplier_id" value="Proveedor" />
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
                                        <InputError class="mt-2" :message="form.errors.supplier_id" />
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <InputLabel for="description" value="Descripción (Opcional)" />
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
                                        <InputLabel for="cost" value="Precio de Costo ($)" />
                                        <input
                                            id="cost"
                                            v-model.number="form.cost"
                                            type="number"
                                            step="0.01"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.cost" />
                                    </div>
                                    <div>
                                        <InputLabel for="iva_percentage" value="IVA (%)" />
                                        <input
                                            id="iva_percentage"
                                            v-model.number="form.iva_percentage"
                                            type="number"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <InputLabel for="profit_margin" value="Margen de Ganancia (%)" />
                                        <input
                                            id="profit_margin"
                                            v-model.number="form.profit_margin"
                                            type="number"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                    </div>
                                    <div
                                        class="bg-gray-100 dark:bg-gray-700 p-6 rounded-xl border border-gray-200 dark:border-gray-600 flex items-center justify-center"
                                    >
                                        <div class="text-center">
                                            <InputLabel value="Precio de Venta Calculado" />
                                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                                ${{ calculatedPrice }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <InputLabel for="current_stock" value="Stock Inicial" />
                                        <input
                                            id="current_stock"
                                            v-model.number="form.current_stock"
                                            type="number"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition-colors duration-200"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <InputLabel for="min_threshold" value="Umbral de Stock Bajo" />
                                        <input
                                            id="min_threshold"
                                            v-model.number="form.min_threshold"
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
                                    <span v-if="form.processing">Guardando...</span>
                                    <span v-else>Guardar Producto</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
