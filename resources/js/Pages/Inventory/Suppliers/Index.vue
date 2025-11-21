<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

defineProps({
    suppliers: {
        type: Object,
        required: true,
    },
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

// Lógica para el Modal de Eliminación
const confirmingSupplierDeletion = ref(false);
const supplierToDelete = ref(null);

const confirmSupplierDeletion = (supplier) => {
    supplierToDelete.value = supplier;
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
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Proveedores</h2>
                <Link
                    v-if="can('gestionar proveedores')"
                    :href="route('inventario.suppliers.create')"
                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition"
                >
                    Crear Nuevo Proveedor
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nombre</th>
                                        <th scope="col" class="px-6 py-3">Persona de Contacto</th>
                                        <th scope="col" class="px-6 py-3">Teléfono</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="supplier in suppliers.data"
                                        :key="supplier.id"
                                        class="bg-white border-b hover:bg-gray-50"
                                    >
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ supplier.name }}
                                        </th>
                                        <td class="px-6 py-4">{{ supplier.contact_person }}</td>
                                        <td class="px-6 py-4">{{ supplier.phone }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <Link
                                                v-if="can('gestionar proveedores')"
                                                :href="route('inventario.suppliers.edit', supplier.id)"
                                                class="font-medium text-blue-600 hover:underline mr-4"
                                                >Editar</Link
                                            >
                                            <button
                                                v-if="can('gestionar proveedores')"
                                                class="font-medium text-red-600 hover:underline"
                                                @click="confirmSupplierDeletion(supplier)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            No se encontraron proveedores.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="suppliers.links.length > 3" class="p-6 border-t">
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
