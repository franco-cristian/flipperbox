<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

// --- PROPS ---
const props = defineProps({
    workOrder: Object,
    products: Array,
    services: Array,
    mechanics: Array,
});

// --- HELPERS ---
const can = (permission) => usePage().props.auth.user.permissions.includes(permission);
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

// --- FORMULARIOS PARA AÑADIR ÍTEMS ---
const addProductForm = useForm({ product_id: null, quantity: 1 });
const addServiceForm = useForm({ service_id: null });
const addExternalCostForm = useForm({ description: '', cost: 0, price: 0 });

// --- FORMULARIO PARA ASIGNAR MECÁNICO ---
const assignMechanicForm = useForm({
    mechanic_id: props.workOrder.mechanic?.id || null,
});

// --- FUNCIONES DE SUBMIT ---
const submitProduct = () => {
    addProductForm.post(route('work-orders.products.add', props.workOrder.id), { 
        preserveScroll: true, 
        onSuccess: () => {
            addProductForm.reset();
            router.reload({ only: ['workOrder'] });
        }
    });
};

const submitService = () => {
    addServiceForm.post(route('work-orders.services.add', props.workOrder.id), { 
        preserveScroll: true, 
        onSuccess: () => {
            addServiceForm.reset();
            router.reload({ only: ['workOrder'] });
        }
    });
};

const submitExternalCost = () => {
    addExternalCostForm.post(route('work-orders.external-costs.add', props.workOrder.id), { 
        preserveScroll: true, 
        onSuccess: () => {
            addExternalCostForm.reset();
            router.reload({ only: ['workOrder'] });
        }
    });
};

const submitMechanicAssignment = () => {
    assignMechanicForm.patch(route('work-orders.assign-mechanic', props.workOrder.id), {
        preserveState: true,
        preserveScroll: true,
    });
};

// --- LÓGICA PARA MODALES DE ELIMINACIÓN ---
const confirmingItemDeletion = ref(false);
const itemToDelete = ref(null);
const deleteRoute = ref('');

const confirmItemDeletion = (item, type) => {
    itemToDelete.value = item;
    if (type === 'product') {
        deleteRoute.value = route('work-orders.products.remove', { workOrder: props.workOrder.id, product: item.id });
    } else if (type === 'service') {
        deleteRoute.value = route('work-orders.services.remove', { workOrder: props.workOrder.id, service: item.id });
    } else if (type === 'externalCost') {
        deleteRoute.value = route('work-orders.external-costs.remove', { externalCost: item.id });
    }
    confirmingItemDeletion.value = true;
};

const deleteItem = () => {
    router.delete(deleteRoute.value, {
        onSuccess: () => {
            closeModal();
            router.reload({ only: ['workOrder'] });
        },
        preserveScroll: true,
    });
};

const closeModal = () => {
    confirmingItemDeletion.value = false;
    itemToDelete.value = null;
    deleteRoute.value = '';
};
</script>

<template>
    <Head :title="`Orden de Trabajo #${workOrder.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle de Orden de Trabajo #{{ workOrder.id }}</h2>
                <Link :href="route('work-orders.index')" class="text-sm text-gray-700 underline">&larr; Volver al listado</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Detalles e Items -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Productos Utilizados -->
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Productos del Inventario</h3>
                        <form v-if="can('gestionar ordenes de trabajo')" @submit.prevent="submitProduct" class="flex gap-4 items-end mb-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Producto</label>
                                <select v-model="addProductForm.product_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option :value="null" disabled>Selecciona un producto</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} (Stock: {{ product.current_stock }})</option>
                                </select>
                                <div v-if="addProductForm.errors.product_id" class="text-sm text-red-600 mt-1">{{ addProductForm.errors.product_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                                <input type="number" v-model="addProductForm.quantity" class="mt-1 block w-24 border-gray-300 rounded-md shadow-sm text-sm" />
                                <div v-if="addProductForm.errors.quantity" class="text-sm text-red-600 mt-1">{{ addProductForm.errors.quantity }}</div>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md h-fit text-sm" :disabled="addProductForm.processing">Agregar</button>
                        </form>
                        <table v-if="workOrder.products && workOrder.products.length > 0" class="w-full text-sm">
                            <tbody>
                                <tr v-for="product in workOrder.products" :key="`prod-${product.id}`" class="border-b">
                                    <td class="py-2">{{ product.name }}</td>
                                    <td class="py-2 text-center">{{ product.pivot.quantity }} x ${{ product.pivot.unit_price }}</td>
                                    <td class="py-2 text-right font-bold">${{ (product.pivot.quantity * product.pivot.unit_price).toFixed(2) }}</td>
                                    <td v-if="can('gestionar ordenes de trabajo')" class="py-2 text-right pl-4">
                                        <button @click="confirmItemDeletion(product, 'product')" class="text-red-500 hover:text-red-700 text-xs">Quitar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-gray-500 italic">No hay productos agregados a esta orden.</p>
                    </div>
                    <!-- Servicios (Mano de Obra) -->
                    <div class="bg-white shadow sm:rounded-lg p-6">
                         <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Servicios (Mano de Obra)</h3>
                         <form v-if="can('gestionar ordenes de trabajo')" @submit.prevent="submitService" class="flex gap-4 items-end mb-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Servicio</label>
                                <select v-model="addServiceForm.service_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option :value="null" disabled>Selecciona un servicio</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md h-fit text-sm" :disabled="addServiceForm.processing">Agregar</button>
                        </form>
                        <table v-if="workOrder.services && workOrder.services.length > 0" class="w-full text-sm">
                           <tbody>
                                <tr v-for="service in workOrder.services" :key="`serv-${service.id}`" class="border-b">
                                    <td class="py-2">{{ service.name }}</td>
                                    <td class="py-2 text-right font-bold">${{ service.pivot.price }}</td>
                                    <td v-if="can('gestionar ordenes de trabajo')" class="py-2 text-right pl-4">
                                        <button @click="confirmItemDeletion(service, 'service')" class="text-red-500 hover:text-red-700 text-xs">Quitar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-gray-500 italic">No hay servicios agregados a esta orden.</p>
                    </div>
                     <!-- Costos Externos - CAMBIO CLAVE AQUÍ -->
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Costos Externos</h3>
                        <form v-if="can('gestionar ordenes de trabajo')" @submit.prevent="submitExternalCost" class="space-y-4 mb-4">
                            <div>
                                <label for="ext-desc" class="block text-sm font-medium text-gray-700">Descripción</label>
                                <input id="ext-desc" type="text" placeholder="Ej: Repuesto comprado por fuera" v-model="addExternalCostForm.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required/>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="ext-cost" class="block text-sm font-medium text-gray-700">Costo ($)</label>
                                    <input id="ext-cost" type="number" step="0.01" placeholder="Lo que costó" v-model="addExternalCostForm.cost" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required/>
                                </div>
                                <div>
                                    <label for="ext-price" class="block text-sm font-medium text-gray-700">Precio Venta ($)</label>
                                    <input id="ext-price" type="number" step="0.01" placeholder="A cobrar al cliente" v-model="addExternalCostForm.price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required/>
                                </div>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md w-full text-sm" :disabled="addExternalCostForm.processing">Agregar Costo Externo</button>
                        </form>
                         <!-- CAMBIO: external_costs en lugar de externalCosts -->
                         <table v-if="workOrder.external_costs && workOrder.external_costs.length > 0" class="w-full text-sm">
                            <tbody>
                                <tr v-for="cost in workOrder.external_costs" :key="`ext-${cost.id}`" class="border-b">
                                    <td class="py-2">{{ cost.description }}</td>
                                    <td class="py-2 text-right font-bold">${{ cost.price }}</td>
                                     <td v-if="can('gestionar ordenes de trabajo')" class="py-2 text-right pl-4">
                                        <button @click="confirmItemDeletion(cost, 'externalCost')" class="text-red-500 hover:text-red-700 text-xs">Quitar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-gray-500 italic">No hay costos externos agregados a esta orden.</p>
                    </div>
                </div>
                <!-- Columna Derecha: Estado y Total -->
                <div class="lg:col-span-1 space-y-6">
                     <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detalles de la Orden</h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Cliente:</strong> {{ workOrder.vehicle.cliente.nombre }} {{ workOrder.vehicle.cliente.apellido }}</p>
                            <p><strong>Vehículo:</strong> {{ workOrder.vehicle.marca }} {{ workOrder.vehicle.modelo }} ({{ workOrder.vehicle.patente }})</p>
                            <p><strong>Fecha Ingreso:</strong> {{ formatDate(workOrder.entry_date) }}</p>
                            <p><strong>Descripción:</strong> {{ workOrder.description }}</p>
                        </div>
                     </div>
                     <div class="bg-white shadow sm:rounded-lg p-6">
                         <h3 class="text-lg font-medium text-gray-900 mb-4">Asignar Mecánico</h3>
                         <form @submit.prevent="submitMechanicAssignment">
                            <select v-model="assignMechanicForm.mechanic_id" @change="submitMechanicAssignment" class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option :value="null">Sin asignar</option>
                                <option v-for="mechanic in mechanics" :key="mechanic.id" :value="mechanic.id">{{ mechanic.name }}</option>
                            </select>
                         </form>
                         <p class="text-sm mt-4"><strong>Estado Actual: </strong>
                            <span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full" 
                                :class="{ 
                                    'bg-yellow-100 text-yellow-800': workOrder.status === 'Pendiente',
                                    'bg-blue-100 text-blue-800': workOrder.status === 'En Progreso',
                                    'bg-green-100 text-green-800': workOrder.status === 'Completada',
                                    'bg-red-100 text-red-800': workOrder.status === 'Cancelada',
                                }">
                                {{ workOrder.status }}
                            </span>
                         </p>
                     </div>
                     <div class="bg-white shadow sm:rounded-lg p-6 text-right">
                         <h3 class="text-lg font-medium text-gray-900">Total de la Orden</h3>
                         <p class="text-4xl font-extrabold text-gray-900 mt-2">${{ parseFloat(workOrder.total || 0).toFixed(2) }}</p>
                     </div>
                </div>
            </div>
        </div>

        <ConfirmationModal 
            :show="confirmingItemDeletion" 
            @close="closeModal"
            @confirm="deleteItem"
            title="Quitar Ítem"
            message="¿Estás seguro de que deseas quitar este ítem de la orden de trabajo?"
        />
    </AuthenticatedLayout>
</template>