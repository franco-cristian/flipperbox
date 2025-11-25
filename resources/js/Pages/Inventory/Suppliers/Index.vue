<!-- Archivo: resources/js/Pages/Inventory/Suppliers/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { ref } from 'vue';

defineProps({ suppliers: Object, filters: Object });
const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

const confirmingSupplierDeletion = ref(false);
const supplierToDelete = ref(null);
const confirmSupplierDeletion = (s) => {
    supplierToDelete.value = s;
    confirmingSupplierDeletion.value = true;
};
const deleteSupplier = () => {
    router.delete(route('inventario.suppliers.destroy', supplierToDelete.value.id), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};
const closeModal = () => {
    confirmingSupplierDeletion.value = false;
    supplierToDelete.value = null;
};
</script>

<template>
    <Head title="Proveedores" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Gestión de Proveedores</h2>
                <Link
                    v-if="can('gestionar proveedores')"
                    :href="route('inventario.suppliers.create')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 text-sm"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nuevo Proveedor
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <SearchInput :model-value="filters.search" placeholder="Buscar proveedor..." />
                </div>
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors"
                >
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th class="px-6 py-4 font-bold tracking-wider">Proveedor</th>
                                        <th class="px-6 py-4 font-bold tracking-wider">Contacto</th>
                                        <th class="px-6 py-4 font-bold tracking-wider text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="supplier in suppliers.data"
                                        :key="supplier.id"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    >
                                        <th class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-base font-semibold text-gray-900 dark:text-white">
                                                {{ supplier.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ supplier.email }}
                                            </div>
                                        </th>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                            <div class="font-medium">{{ supplier.contact_person }}</div>
                                            <div class="text-xs">{{ supplier.phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                v-if="can('gestionar proveedores')"
                                                :href="route('inventario.suppliers.edit', supplier.id)"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4 transition"
                                                >Editar</Link
                                            >
                                            <button
                                                v-if="can('gestionar proveedores')"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition"
                                                @click="confirmSupplierDeletion(supplier)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron proveedores.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div
                        v-if="suppliers.links.length > 3"
                        class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    >
                        <Pagination :links="suppliers.links" />
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal
            :show="confirmingSupplierDeletion"
            title="Eliminar Proveedor"
            :message="`¿Estás seguro de que deseas eliminar al proveedor '${supplierToDelete?.name}'? Esta acción no se puede deshacer.`"
            @close="closeModal"
            @confirm="deleteSupplier"
        />
    </AuthenticatedLayout>
</template>
