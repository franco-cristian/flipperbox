<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue'; // <-- Importamos el modal
import { ref } from 'vue';

defineProps({
    clientes: {
        type: Object,
        required: true,
    },
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

// --- Lógica para el Modal de Eliminación ---
const confirmingClientDeletion = ref(false);
const clientToDelete = ref(null);

const confirmClientDeletion = (cliente) => {
    clientToDelete.value = cliente;
    confirmingClientDeletion.value = true;
};

const deleteClient = () => {
    router.delete(route('clientes.destroy', clientToDelete.value.id), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

const closeModal = () => {
    confirmingClientDeletion.value = false;
    clientToDelete.value = null;
};
// --- Fin de la Lógica del Modal ---
</script>

<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <template #header>
            <!-- ... (sin cambios aquí) ... -->
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <!-- ... (thead sin cambios) ... -->
                                <tbody>
                                    <tr v-for="cliente in clientes.data" :key="cliente.id" class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <Link :href="route('clientes.show', cliente.id)" class="hover:underline text-blue-600">
                                                {{ cliente.nombre }} {{ cliente.apellido }}
                                            </Link>
                                        </th>
                                        <td class="px-6 py-4">{{ cliente.email }}</td>
                                        <td class="px-6 py-4">{{ cliente.telefono }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <!-- ENLACE DE EDICIÓN CORREGIDO -->
                                            <Link v-if="can('editar clientes')" :href="route('clientes.edit', cliente.id)" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                            <!-- BOTÓN DE ELIMINACIÓN CORREGIDO -->
                                            <button v-if="can('eliminar clientes')" @click="confirmClientDeletion(cliente)" class="font-medium text-red-600 hover:underline">Eliminar</button>
                                        </td>
                                    </tr>
                                    <!-- ... (tr para 'no se encontraron clientes' sin cambios) ... -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="clientes.links.length > 3" class="p-6 border-t">
                        <Pagination :links="clientes.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN -->
        <ConfirmationModal 
            :show="confirmingClientDeletion" 
            @close="closeModal"
            @confirm="deleteClient"
            title="Eliminar Cliente"
            :message="`¿Estás seguro de que deseas eliminar a ${clientToDelete?.nombre} ${clientToDelete?.apellido}? Esta acción no se puede deshacer.`"
        />
    </AuthenticatedLayout>
</template>
