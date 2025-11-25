<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';

// --- PROPS ---
const props = defineProps({
    workOrder: Object,
    products: Array, // Catálogo de productos disponibles
    services: Array, // Catálogo de servicios disponibles
    mechanics: Array, // Lista de mecánicos disponibles
});

// --- COMPUTED PROPERTIES ---
// Propiedad computada para determinar si la orden está finalizada (no se puede editar)
const isFinalized = computed(() => props.workOrder.status === 'Completada' || props.workOrder.status === 'Cancelada');

// --- HELPERS ---
const can = (permission) => usePage().props.auth.user.permissions.includes(permission);
const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

// --- FORMULARIOS ---
const addProductForm = useForm({ product_id: null, quantity: 1 });
const addServiceForm = useForm({ service_id: null });
const addExternalCostForm = useForm({ description: '', cost: 0, price: 0 });
const assignMechanicForm = useForm({ mechanic_id: props.workOrder.mechanic?.id || null });

// --- FUNCIONES DE SUBMIT (CON RECARGA DE DATOS PARA SOLUCIONAR BUG DE RENDERIZADO) ---
const submitProduct = () =>
    addProductForm.post(route('work-orders.products.add', props.workOrder.id), {
        preserveScroll: true,
        onSuccess: () => {
            addProductForm.reset();
            router.reload({ only: ['workOrder'] });
        },
    });
const submitService = () =>
    addServiceForm.post(route('work-orders.services.add', props.workOrder.id), {
        preserveScroll: true,
        onSuccess: () => {
            addServiceForm.reset();
            router.reload({ only: ['workOrder'] });
        },
    });
const submitExternalCost = () =>
    addExternalCostForm.post(route('work-orders.external-costs.add', props.workOrder.id), {
        preserveScroll: true,
        onSuccess: () => {
            addExternalCostForm.reset();
            router.reload({ only: ['workOrder'] });
        },
    });
const submitMechanicAssignment = () =>
    assignMechanicForm.patch(route('work-orders.assign-mechanic', props.workOrder.id), {
        preserveState: true,
        preserveScroll: true,
    });

// --- LÓGICA PARA MODALES DE CONFIRMACIÓN ---
const confirmingAction = ref(false);
const actionToConfirm = ref({}); // Objeto para guardar la configuración completa del modal

// Función genérica para abrir el modal con una configuración específica
const setupConfirmation = (config) => {
    actionToConfirm.value = config;
    confirmingAction.value = true;
};

const confirmItemDeletion = (item, type) => {
    let routeName = '';
    let params = {};
    let itemName = '';

    if (type === 'product') {
        routeName = 'work-orders.products.remove';
        params = { workOrder: props.workOrder.id, product: item.id };
        itemName = item.name;
    } else if (type === 'service') {
        routeName = 'work-orders.services.remove';
        params = { workOrder: props.workOrder.id, service: item.id };
        itemName = item.name;
    } else if (type === 'externalCost') {
        routeName = 'work-orders.external-costs.remove';
        params = { externalCost: item.id };
        itemName = item.description;
    }

    setupConfirmation({
        title: 'Quitar Ítem',
        message: `¿Estás seguro de que deseas quitar "${itemName}" de la orden de trabajo?`,
        confirmButtonText: 'Sí, Quitar',
        confirmButtonClass: 'bg-red-600 hover:bg-red-700',
        method: () =>
            router.delete(route(routeName, params), {
                onSuccess: () => closeModal(),
                preserveScroll: true,
            }),
    });
};

const confirmStatusChange = (status) => {
    let config = {};
    if (status === 'Completada') {
        config = {
            title: 'Completar Orden de Trabajo',
            message:
                '¿Estás seguro de que deseas marcar esta orden como "Completada"? Ya no podrás realizar modificaciones.',
            confirmButtonText: 'Sí, Completar',
            confirmButtonClass: 'bg-green-600 hover:bg-green-700',
        };
    } else if (status === 'Cancelada') {
        config = {
            title: 'Cancelar Orden de Trabajo',
            message:
                '¿Estás seguro de que deseas cancelar esta orden? Si se utilizaron productos del inventario, el stock será devuelto.',
            confirmButtonText: 'Sí, Cancelar Orden',
            confirmButtonClass: 'bg-orange-600 hover:bg-orange-700',
        };
    }

    config.method = () =>
        router.patch(
            route('work-orders.update', props.workOrder.id),
            { status: status },
            {
                onSuccess: () => closeModal(),
                preserveScroll: true,
            }
        );

    setupConfirmation(config);
};

const closeModal = () => (confirmingAction.value = false);
</script>

<template>
    <Head :title="`Orden de Trabajo #${workOrder.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                    Detalle de Orden de Trabajo #{{ workOrder.id }}
                </h2>
                <Link
                    :href="route('work-orders.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 underline transition"
                >
                    &larr; Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Detalles e Items -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Mensaje de Advertencia si está Finalizada -->
                    <div
                        v-if="isFinalized"
                        class="p-4 rounded-xl border transition-colors duration-300"
                        :class="
                            workOrder.status === 'Completada'
                                ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-300'
                                : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300'
                        "
                    >
                        <p class="font-bold">
                            Esta orden de trabajo está {{ workOrder.status.toLowerCase() }} y ya no puede ser
                            modificada.
                        </p>
                    </div>

                    <!-- Productos del Inventario -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Productos del Inventario</h3>
                        </div>
                        <div class="p-6">
                            <form
                                v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                class="flex gap-4 items-end mb-6"
                                @submit.prevent="submitProduct"
                            >
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Producto</label
                                    >
                                    <select
                                        v-model="addProductForm.product_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                    >
                                        <option :value="null" disabled>Selecciona un producto</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.name }} (Stock: {{ product.current_stock }})
                                        </option>
                                    </select>
                                    <div
                                        v-if="addProductForm.errors.product_id"
                                        class="text-sm text-red-600 dark:text-red-400 mt-1"
                                    >
                                        {{ addProductForm.errors.product_id }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Cantidad</label
                                    >
                                    <input
                                        v-model="addProductForm.quantity"
                                        type="number"
                                        class="mt-1 block w-24 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                    />
                                    <div
                                        v-if="addProductForm.errors.quantity"
                                        class="text-sm text-red-600 dark:text-red-400 mt-1"
                                    >
                                        {{ addProductForm.errors.quantity }}
                                    </div>
                                </div>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 text-sm"
                                    :disabled="addProductForm.processing"
                                >
                                    Agregar
                                </button>
                            </form>
                            <table
                                v-if="workOrder.products && workOrder.products.length > 0"
                                class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                            >
                                <thead
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold tracking-wider">Producto</th>
                                        <th scope="col" class="px-6 py-3 font-bold tracking-wider text-center">
                                            Cantidad
                                        </th>
                                        <th scope="col" class="px-6 py-3 font-bold tracking-wider text-right">Total</th>
                                        <th
                                            v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                            scope="col"
                                            class="px-6 py-3 font-bold tracking-wider text-right"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="product in workOrder.products"
                                        :key="`prod-${product.id}`"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                    >
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ product.name }}
                                            <span
                                                v-if="product.deleted_at"
                                                class="ml-2 text-xs text-red-500 bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded-full"
                                                >Archivado</span
                                            >
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            {{ product.pivot.quantity }} x ${{ product.pivot.unit_price }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                            ${{ (product.pivot.quantity * product.pivot.unit_price).toFixed(2) }}
                                        </td>
                                        <td
                                            v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                            class="px-6 py-4 text-right"
                                        >
                                            <button
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition text-sm"
                                                @click="confirmItemDeletion(product, 'product')"
                                            >
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                                No hay productos agregados a esta orden.
                            </p>
                        </div>
                    </div>

                    <!-- Servicios (Mano de Obra) -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Servicios (Mano de Obra)</h3>
                        </div>
                        <div class="p-6">
                            <form
                                v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                class="flex gap-4 items-end mb-6"
                                @submit.prevent="submitService"
                            >
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Servicio</label
                                    >
                                    <select
                                        v-model="addServiceForm.service_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                    >
                                        <option :value="null" disabled>Selecciona un servicio</option>
                                        <option v-for="service in services" :key="service.id" :value="service.id">
                                            {{ service.name }}
                                        </option>
                                    </select>
                                </div>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 text-sm"
                                    :disabled="addServiceForm.processing"
                                >
                                    Agregar
                                </button>
                            </form>
                            <table
                                v-if="workOrder.services && workOrder.services.length > 0"
                                class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                            >
                                <thead
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold tracking-wider">Servicio</th>
                                        <th scope="col" class="px-6 py-3 font-bold tracking-wider text-right">
                                            Precio
                                        </th>
                                        <th
                                            v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                            scope="col"
                                            class="px-6 py-3 font-bold tracking-wider text-right"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="service in workOrder.services"
                                        :key="`serv-${service.id}`"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                    >
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ service.name }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                            ${{ service.pivot.price }}
                                        </td>
                                        <td
                                            v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                            class="px-6 py-4 text-right"
                                        >
                                            <button
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition text-sm"
                                                @click="confirmItemDeletion(service, 'service')"
                                            >
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                                No hay servicios agregados a esta orden.
                            </p>
                        </div>
                    </div>

                    <!-- Costos Externos -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Costos Externos</h3>
                        </div>
                        <div class="p-6">
                            <form
                                v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                class="space-y-4 mb-6"
                                @submit.prevent="submitExternalCost"
                            >
                                <div>
                                    <label
                                        for="ext-desc"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Descripción</label
                                    >
                                    <input
                                        id="ext-desc"
                                        v-model="addExternalCostForm.description"
                                        type="text"
                                        placeholder="Ej: Repuesto comprado por fuera"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                        required
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            for="ext-cost"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                            >Costo ($)</label
                                        >
                                        <input
                                            id="ext-cost"
                                            v-model="addExternalCostForm.cost"
                                            type="number"
                                            step="0.01"
                                            placeholder="Lo que costó"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="ext-price"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                            >Precio Venta ($)</label
                                        >
                                        <input
                                            id="ext-price"
                                            v-model="addExternalCostForm.price"
                                            type="number"
                                            step="0.01"
                                            placeholder="A cobrar al cliente"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                            required
                                        />
                                    </div>
                                </div>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 w-full text-sm"
                                    :disabled="addExternalCostForm.processing"
                                >
                                    Agregar Costo Externo
                                </button>
                            </form>
                            <table
                                v-if="workOrder.external_costs && workOrder.external_costs.length > 0"
                                class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                            >
                                <thead
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-bold tracking-wider">Descripción</th>
                                        <th scope="col" class="px-6 py-3 font-bold tracking-wider text-right">
                                            Precio
                                        </th>
                                        <th
                                            v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                            scope="col"
                                            class="px-6 py-3 font-bold tracking-wider text-right"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="cost in workOrder.external_costs"
                                        :key="`ext-${cost.id}`"
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                    >
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ cost.description }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                            ${{ cost.price }}
                                        </td>
                                        <td
                                            v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                                            class="px-6 py-4 text-right"
                                        >
                                            <button
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition text-sm"
                                                @click="confirmItemDeletion(cost, 'externalCost')"
                                            >
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                                No hay costos externos agregados a esta orden.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Estado y Total -->
                <div class="lg:col-span-1 space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Detalles de la Orden</h3>
                            <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                                <p>
                                    <strong class="text-gray-900 dark:text-white">Cliente:</strong>
                                    {{ workOrder.vehicle.cliente.name }}
                                    {{ workOrder.vehicle.cliente.apellido }}
                                </p>
                                <p>
                                    <strong class="text-gray-900 dark:text-white">Vehículo:</strong>
                                    {{ workOrder.vehicle.marca }} {{ workOrder.vehicle.modelo }} ({{
                                        workOrder.vehicle.patente
                                    }})
                                </p>
                                <p>
                                    <strong class="text-gray-900 dark:text-white">Fecha Ingreso:</strong>
                                    {{ formatDate(workOrder.entry_date) }}
                                </p>
                                <p>
                                    <strong class="text-gray-900 dark:text-white">Descripción:</strong>
                                    {{ workOrder.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Asignar Mecánico</h3>
                            <form @submit.prevent="submitMechanicAssignment">
                                <select
                                    v-model="assignMechanicForm.mechanic_id"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                    @change="submitMechanicAssignment"
                                >
                                    <option :value="null">Sin asignar</option>
                                    <option v-for="mechanic in mechanics" :key="mechanic.id" :value="mechanic.id">
                                        {{ mechanic.name }}
                                    </option>
                                </select>
                            </form>
                            <p class="text-sm mt-4">
                                <strong class="text-gray-900 dark:text-white">Estado Actual: </strong
                                ><span
                                    class="px-3 py-1 font-semibold leading-tight text-xs rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300':
                                            workOrder.status === 'Pendiente',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300':
                                            workOrder.status === 'En Progreso',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300':
                                            workOrder.status === 'Completada',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300':
                                            workOrder.status === 'Cancelada',
                                    }"
                                    >{{ workOrder.status }}</span
                                >
                            </p>
                        </div>
                    </div>
                    <!-- Panel de Acciones de la Orden -->
                    <div
                        v-if="!isFinalized && can('gestionar ordenes de trabajo')"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Acciones de la Orden</h3>
                            <div class="space-y-3">
                                <button
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow-md transition-all duration-200 text-sm"
                                    @click="confirmStatusChange('Completada')"
                                >
                                    Marcar como Completada
                                </button>
                                <button
                                    class="w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl shadow-md transition-all duration-200 text-sm"
                                    @click="confirmStatusChange('Cancelada')"
                                >
                                    Marcar como Cancelada
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300"
                    >
                        <div class="p-6 text-center">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Total de la Orden</h3>
                            <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">
                                ${{ parseFloat(workOrder.total || 0).toFixed(2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingAction"
            :title="actionToConfirm.title"
            :message="actionToConfirm.message"
            :confirm-button-text="actionToConfirm.confirmButtonText"
            :confirm-button-class="actionToConfirm.confirmButtonClass"
            @close="closeModal"
            @confirm="actionToConfirm.method"
        />
    </AuthenticatedLayout>
</template>
