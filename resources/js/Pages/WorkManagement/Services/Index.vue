<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { ref } from 'vue';

defineProps({
    services: Object,
    filters: Object,
});

const can = (permission) => usePage().props.auth.user.permissions.includes(permission);

// --- Lógica Modal ---
const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const confirmDeletion = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    router.delete(route('services.destroy', itemToDelete.value.id), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

const closeModal = () => {
    confirmingDeletion.value = false;
    itemToDelete.value = null;
};

const formatCurrency = (val) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(val);
</script>

<template>
    <Head title="Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">Gestión de Servicios</h2>
                <Link
                    v-if="can('gestionar servicios')"
                    :href="route('services.create')"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                        ></path>
                    </svg>
                    Nuevo Servicio
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Barra de Búsqueda -->
                <div class="mb-6">
                    <SearchInput :model-value="filters.search" placeholder="Buscar por nombre o descripción..." />
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
                                        <th class="px-6 py-4 font-bold tracking-wider">Servicio</th>
                                        <th class="px-6 py-4 font-bold tracking-wider">Descripción</th>
                                        <th class="px-6 py-4 font-bold tracking-wider">Precio Base</th>
                                        <th class="px-6 py-4 font-bold tracking-wider text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="service in services.data"
                                        :key="service.id"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    >
                                        <th
                                            class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap"
                                        >
                                            {{ service.name }}
                                        </th>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400 max-w-md truncate">
                                            {{ service.description || '-' }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-gray-900 dark:text-gray-300">
                                            {{ formatCurrency(service.price) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                v-if="can('gestionar servicios')"
                                                :href="route('services.edit', service.id)"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4 transition"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                v-if="can('gestionar servicios')"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition"
                                                @click="confirmDeletion(service)"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="services.data.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <p class="text-lg font-medium">No se encontraron servicios.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div
                        v-if="services.links.length > 3"
                        class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                    >
                        <Pagination :links="services.links" />
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingDeletion"
            title="Eliminar Servicio"
            :message="`¿Estás seguro de que deseas eliminar el servicio '${itemToDelete?.name}'? Esta acción no se puede deshacer.`"
            @close="closeModal"
            @confirm="deleteItem"
        />
    </AuthenticatedLayout>
</template>
