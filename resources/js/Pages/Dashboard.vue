<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import BarChart from '@/Components/Dashboard/BarChart.vue';
import DoughnutChart from '@/Components/Dashboard/DoughnutChart.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    ingresosMes: Number,
    dailyActivityChart: Object,
    topProducts: Array,
    lowStockProducts: Array,
    workOrderStatusChart: Object,
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
    }).format(value);

// --- Datos para los gráficos ---
const barChartData = computed(() => ({
    labels: props.dailyActivityChart?.labels || [],
    datasets: [
        {
            label: 'Órdenes Completadas',
            backgroundColor: '#3b82f6',
            data: props.dailyActivityChart?.data || [],
        },
    ],
}));

const doughnutChartData = computed(() => ({
    labels: props.workOrderStatusChart?.labels || [],
    datasets: [
        {
            backgroundColor: ['#facc15', '#3b82f6', '#22c55e', '#ef4444', '#8b5cf6'], // Colores para diferentes estados
            data: props.workOrderStatusChart?.data || [],
        },
    ],
}));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard de Administración</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Widget 1: Ingresos del Mes -->
                    <StatCard>
                        <template #title>Ingresos del Mes (Completadas)</template>
                        <template #content>
                            <p class="text-3xl font-bold text-gray-900">
                                {{ formatCurrency(ingresosMes) }}
                            </p>
                        </template>
                    </StatCard>

                    <!-- Widget 2: Estado de Órdenes -->
                    <StatCard>
                        <template #title>Estado de Órdenes (Últimos 30 días)</template>
                        <template #content>
                            <div class="h-32">
                                <DoughnutChart
                                    v-if="
                                        workOrderStatusChart &&
                                        workOrderStatusChart.data &&
                                        workOrderStatusChart.data.length
                                    "
                                    :chart-data="doughnutChartData"
                                />
                                <p v-else class="text-gray-500 text-center py-8">No hay datos disponibles.</p>
                            </div>
                        </template>
                    </StatCard>

                    <!-- Widget 3: Top Productos -->
                    <StatCard class="lg:col-span-2">
                        <template #title>Top 5 Productos Rentables (Mes Actual)</template>
                        <template #content>
                            <ul v-if="topProducts.length > 0" class="space-y-3">
                                <li
                                    v-for="product in topProducts"
                                    :key="product.name"
                                    class="flex justify-between items-center text-sm"
                                >
                                    <span class="text-gray-700">{{ product.name }}</span>
                                    <span class="font-semibold text-gray-900">{{
                                        formatCurrency(product.total_revenue)
                                    }}</span>
                                </li>
                            </ul>
                            <p v-else class="text-gray-500">No hay ventas registradas este mes.</p>
                        </template>
                    </StatCard>

                    <!-- Widget 4: Gráfico de Actividad -->
                    <StatCard class="md:col-span-2 lg:col-span-3">
                        <template #title>Órdenes Completadas (Últimos 30 días)</template>
                        <template #content>
                            <div class="h-64">
                                <BarChart
                                    v-if="
                                        dailyActivityChart && dailyActivityChart.data && dailyActivityChart.data.length
                                    "
                                    :chart-data="barChartData"
                                />
                                <p v-else class="text-gray-500 text-center py-16">
                                    No hay actividad registrada en los últimos 30 días.
                                </p>
                            </div>
                        </template>
                    </StatCard>

                    <!-- Widget 5: Productos con Stock Bajo -->
                    <StatCard>
                        <template #title>Alerta de Stock Bajo (Top 5)</template>
                        <template #content>
                            <ul v-if="lowStockProducts.length > 0" class="space-y-3">
                                <li
                                    v-for="product in lowStockProducts"
                                    :key="product.name"
                                    class="flex justify-between items-center text-sm"
                                >
                                    <span class="text-gray-700">{{ product.name }}</span>
                                    <span class="font-semibold text-red-600">
                                        {{ product.current_stock }} /
                                        <span class="text-gray-500">{{ product.min_threshold }}</span>
                                    </span>
                                </li>
                            </ul>
                            <p v-else class="text-gray-500">No hay productos con stock bajo.</p>
                        </template>
                    </StatCard>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
