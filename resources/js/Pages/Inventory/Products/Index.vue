<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { ref } from 'vue';

defineProps({
    products: Object,
    filters: Object,
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

// --- Lógica Modal de eliminación ---
const confirmingProductDeletion = ref(false);
const productToDelete = ref(null);
const confirmProductDeletion = (product) => {
    productToDelete.value = product;
    confirmingProductDeletion.value = true;
};
const deleteProduct = () => {
    router.delete(route('inventario.products.destroy', productToDelete.value.id), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};
const closeModal = () => {
    confirmingProductDeletion.value = false;
    productToDelete.value = null;
};

const formatCurrency = (val) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(val);
</script>

<template>
    <Head title="Productos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Gestión de Productos</h2>
                <Link
                    v-if="can('gestionar inventario')"
                    :href="route('inventario.products.create')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 text-sm"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                        ></path>
                    </svg>
                    Nuevo Producto
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Barra de Búsqueda -->
                <div class="mb-6">
                    <SearchInput :model-value="filters.search" placeholder="Buscar por nombre, SKU..." />
                </div>

                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                >
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th class="px-6 py-4 font-bold tracking-wider">Producto</th>
                                        <th class="px-6 py-4 font-bold tracking-wider">Precio</th>
                                        <th class="px-6 py-4 font-bold tracking-wider">Stock</th>
                                        <th class="px-6 py-4 font-bold tracking-wider text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="product in products.data"
                                        :key="product.id"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    >
                                        <th class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-base font-semibold text-gray-900 dark:text-white">
                                                {{ product.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                                {{ product.sku }}
                                            </div>
                                        </th>
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-300">
                                            {{ formatCurrency(product.price) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="font-bold"
                                                    :class="
                                                        product.current_stock <= product.min_threshold
                                                            ? 'text-red-600 dark:text-red-400'
                                                            : 'text-green-600 dark:text-green-400'
                                                    "
                                                >
                                                    {{ product.current_stock }}
                                                </span>
                                                <span
                                                    v-if="product.current_stock <= product.min_threshold"
                                                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 uppercase tracking-wide"
                                                    >Bajo</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                v-if="can('gestionar inventario')"
                                                :href="route('inventario.products.edit', product.id)"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4 transition"
                                                >Editar</Link
                                            >
                                            <button
                                                v-if="can('gestionar inventario')"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition"
                                                @click="confirmProductDeletion(product)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <p class="text-lg font-medium">No se encontraron productos.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div
                        v-if="products.links.length > 3"
                        class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    >
                        <Pagination :links="products.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN PARA PRODUCTOS -->
        <ConfirmationModal
            :show="confirmingProductDeletion"
            title="Eliminar Producto"
            :message="`¿Estás seguro de que deseas eliminar el producto '${productToDelete?.name}'? Esta acción no se puede deshacer.`"
            @close="closeModal"
            @confirm="deleteProduct"
        />
    </AuthenticatedLayout>
</template>
