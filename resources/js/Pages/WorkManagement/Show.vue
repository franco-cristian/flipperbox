<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

// --- PROPS ---
const props = defineProps({
    workOrder: Object,
    products: Array, // Catálogo de productos disponibles
    services: Array, // Catálogo de servicios disponibles
});

// --- HELPERS ---
const can = (permission) => usePage().props.auth.user.permissions.includes(permission);
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

// --- FORMULARIOS PARA AÑADIR ÍTEMS ---
const addProductForm = useForm({ product_id: null, quantity: 1 });
const addServiceForm = useForm({ service_id: null });
const addExternalCostForm = useForm({ description: '', cost: 0, price: 0 });

const submitProduct = () => addProductForm.post(route('work-orders.products.add', props.workOrder.id), { preserveScroll: true, onSuccess: () => addProductForm.reset() });
const submitService = () => addServiceForm.post(route('work-orders.services.add', props.workOrder.id), { preserveScroll: true, onSuccess: () => addServiceForm.reset() });
const submitExternalCost = () => addExternalCostForm.post(route('work-orders.external-costs.add', props.workOrder.id), { preserveScroll: true, onSuccess: () => addExternalCostForm.reset() });

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
        onSuccess: () => closeModal(),
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
                    <!-- Detalles de la Orden -->
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Detalles Generales</h3>
                        <p><strong>Cliente:</strong> {{ workOrder.vehicle.cliente.nombre }} {{ workOrder.vehicle.cliente.apellido }}</p>
                        <p><strong>Vehículo:</strong> {{ workOrder.vehicle.marca }} {{ workOrder.vehicle.modelo }} ({{ workOrder.vehicle.patente }})</p>
                        <p><strong>Descripción:</strong> {{ workOrder.description }}</p>
                        <p><strong>Mecánico Asignado:</strong> {{ workOrder.mechanic?.name || 'No asignado' }}</p>
                    </div>
                    <!-- Productos Utilizados -->
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Productos del Inventario</h3>
                        <form v-if="can('gestionar ordenes de trabajo')" @submit.prevent="submitProduct" class="flex gap-4 items-end mb-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium">Producto</label>
                                <select v-model="addProductForm.product_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option :value="null" disabled>Selecciona un producto</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} (Stock: {{ product.current_stock }})</option>
                                </select>
                                <div v-if="addProductForm.errors.product_id" class="text-sm text-red-600 mt-1">{{ addProductForm.errors.product_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Cantidad</label>
                                <input type="number" v-model="addProductForm.quantity" class="mt-1 block w-24 border-gray-300 rounded-md shadow-sm" />
                                <div v-if="addProductForm.errors.quantity" class="text-sm text-red-600 mt-1">{{ addProductForm.errors.quantity }}</div>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md h-fit" :disabled="addProductForm.processing">Agregar</button>
                        </form>
                        <table v-if="workOrder.products && workOrder.products.length > 0" class="w-full text-sm">
                            <tbody>
                                <tr v-for="product in workOrder.products" :key="product.id" class="border-b">
                                    <td class="py-2">{{ product.name }}</td>
                                    <td class="py-2 text-center">{{ product.pivot.quantity }} x ${{ product.pivot.unit_price }}</td>
                                    <td class="py-2 text-right font-bold">${{ (product.pivot.quantity * product.pivot.unit_price).toFixed(2) }}</td>
                                    <td v-if="can('gestionar ordenes de trabajo')" class="py-2 text-right">
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
                                <label class="block text-sm font-medium">Servicio</label>
                                <select v-model="addServiceForm.service_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option :value="null" disabled>Selecciona un servicio</option>
                                    <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md h-fit" :disabled="addServiceForm.processing">Agregar</button>
                        </form>
                        <table v-if="workOrder.services && workOrder.services.length > 0" class="w-full text-sm">
                           <tbody>
                                <tr v-for="service in workOrder.services" :key="service.id" class="border-b">
                                    <td class="py-2">{{ service.name }}</td>
                                    <td class="py-2 text-right font-bold">${{ service.pivot.price }}</td>
                                    <td v-if="can('gestionar ordenes de trabajo')" class="py-2 text-right">
                                        <button @click="confirmItemDeletion(service, 'service')" class="text-red-500 hover:text-red-700 text-xs">Quitar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-gray-500 italic">No hay servicios agregados a esta orden.</p>
                    </div>
                     <!-- Costos Externos -->
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Costos Externos</h3>
                        <form v-if="can('gestionar ordenes de trabajo')" @submit.prevent="submitExternalCost" class="space-y-4 mb-4">
                            <input type="text" placeholder="Descripción" v-model="addExternalCostForm.description" class="block w-full border-gray-300 rounded-md shadow-sm" required/>
                            <div class="flex gap-4">
                                <input type="number" step="0.01" placeholder="Costo" v-model="addExternalCostForm.cost" class="block w-full border-gray-300 rounded-md shadow-sm" required/>
                                <input type="number" step="0.01" placeholder="Precio Venta" v-model="addExternalCostForm.price" class="block w-full border-gray-300 rounded-md shadow-sm" required/>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md w-full" :disabled="addExternalCostForm.processing">Agregar Costo Externo</button>
                        </form>
                         <table v-if="workOrder.externalCosts && workOrder.externalCosts.length > 0" class="w-full text-sm">
                            <tbody>
                                <tr v-for="cost in workOrder.externalCosts" :key="cost.id" class="border-b">
                                    <td class="py-2">{{ cost.description }}</td>
                                    <td class="py-2 text-right font-bold">${{ cost.price }}</td>
                                     <td v-if="can('gestionar ordenes de trabajo')" class="py-2 text-right">
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
                         <h3 class="text-lg font-medium text-gray-900 mb-4">Estado de la Orden</h3>
                         <span class="px-4 py-2 font-bold leading-tight text-lg rounded-full" 
                            :class="{ 
                                'bg-yellow-100 text-yellow-800': workOrder.status === 'Pendiente',
                                'bg-blue-100 text-blue-800': workOrder.status === 'En Progreso',
                                'bg-green-100 text-green-800': workOrder.status === 'Completada',
                                'bg-red-100 text-red-800': workOrder.status === 'Cancelada',
                            }">
                             {{ workOrder.status }}
                         </span>
                         <div class="mt-6">
                             <h3 class="text-lg font-medium text-gray-900">Total</h3>
                             <p class="text-4xl font-extrabold text-gray-900">${{ parseFloat(workOrder.total || 0).toFixed(2) }}</p>
                         </div>
                     </div>
                </div>
            </div>
        </div>

        <ConfirmationModal 
            :show="confirmingItemDeletion" 
            @close="closeModal"
            @confirm="deleteItem"
            title="Eliminar Ítem"
            message="¿Estás seguro de que deseas eliminar este ítem de la orden de trabajo?"
        />
    </AuthenticatedLayout>
</template>