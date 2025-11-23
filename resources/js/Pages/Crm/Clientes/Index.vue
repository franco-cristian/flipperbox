<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { ref } from 'vue';

defineProps({
    clientes: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

// --- Lógica de Eliminación ---
const confirmingClientDeletion = ref(false);
const clientToDelete = ref(null);

const confirmClientDeletion = (cliente) => {
    clientToDelete.value = cliente;
    confirmingClientDeletion.value = true;
};

const deleteClient = () => {
    router.delete(route('clientes.destroy', { user: clientToDelete.value.id }), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

const closeModal = () => {
    confirmingClientDeletion.value = false;
    clientToDelete.value = null;
};

// --- Lógica de WhatsApp ---
const getWhatsAppLink = (phone) => {
    if (!phone) return '#';
    const cleanNumber = phone.replace(/\D/g, '');
    return `https://wa.me/549${cleanNumber}`;
};
</script>

<template>
    <Head title="Clientes" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Gestión de Clientes</h2>
                <Link
                    v-if="can('crear clientes')"
                    :href="route('clientes.create')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 text-sm"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nuevo Cliente
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Barra de Herramientas (Búsqueda) -->
                <div class="mb-6 w-full">
                    <div class="w-full max-w-full">
                        <SearchInput :model-value="filters.search" placeholder="Buscar por nombre, DNI, email..." />
                    </div>
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
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Cliente</th>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Contacto</th>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider">Documento</th>
                                        <th scope="col" class="px-6 py-4 font-bold tracking-wider text-right">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="cliente in clientes.data"
                                        :key="cliente.id"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                    >
                                        <!-- Nombre y Avatar -->
                                        <th scope="row" class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-lg mr-3"
                                                >
                                                    {{ cliente.name.charAt(0) }}
                                                </div>
                                                <div>
                                                    <Link
                                                        :href="route('clientes.show', { user: cliente.id })"
                                                        class="text-base font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition"
                                                    >
                                                        {{ cliente.name }} {{ cliente.apellido }}
                                                    </Link>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ cliente.email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Contacto con WhatsApp -->
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <a
                                                    v-if="cliente.telefono"
                                                    :href="getWhatsAppLink(cliente.telefono)"
                                                    target="_blank"
                                                    class="flex items-center text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium transition gap-1"
                                                    title="Enviar mensaje por WhatsApp"
                                                >
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.463 1.065 2.876 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"
                                                        />
                                                    </svg>
                                                    {{ cliente.telefono }}
                                                </a>
                                                <span v-else class="text-gray-400 italic text-xs">Sin teléfono</span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                            <div v-if="cliente.documento_valor">
                                                <span
                                                    class="text-xs font-bold uppercase bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-500 dark:text-gray-400 mr-1"
                                                    >{{ cliente.documento_tipo || 'DNI' }}</span
                                                >
                                                {{ cliente.documento_valor }}
                                            </div>
                                            <span v-else class="text-gray-400 italic text-xs">-</span>
                                        </td>

                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                            <Link
                                                v-if="can('editar clientes')"
                                                :href="route('clientes.edit', { user: cliente.id })"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4 transition"
                                                >Editar</Link
                                            >
                                            <button
                                                v-if="can('eliminar clientes')"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition"
                                                @click="confirmClientDeletion(cliente)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Empty State -->
                                    <tr v-if="clientes.data.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg
                                                    class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                    ></path>
                                                </svg>
                                                <p class="text-lg font-medium">No se encontraron clientes.</p>
                                                <p class="text-sm">Intenta ajustar tu búsqueda o agrega uno nuevo.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="clientes.links.length > 3"
                        class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    >
                        <Pagination :links="clientes.links" />
                    </div>
                </div>
            </div>

            <!-- MODAL DE CONFIRMACIÓN -->
            <ConfirmationModal
                :show="confirmingClientDeletion"
                title="Eliminar Cliente"
                :message="`¿Estás seguro de que deseas eliminar a ${clientToDelete?.name} ${clientToDelete?.apellido}? Esta acción no se puede deshacer.`"
                @close="closeModal"
                @confirm="deleteClient"
            />
        </div>
    </AuthenticatedLayout>
</template>
