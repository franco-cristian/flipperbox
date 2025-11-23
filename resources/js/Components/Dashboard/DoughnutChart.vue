<script setup>
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

defineProps({
    chartData: { type: Object, required: true },
});

// Opciones fijas que funcionan en ambos modos
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'left',
            labels: {
                padding: 15,
                usePointStyle: true,
                pointStyle: 'circle',
                font: {
                    size: 11,
                },
                boxWidth: 10,
                color: '#6B7280', // Color gris que funciona en ambos modos
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            bodyColor: '#ffffff',
            titleColor: '#ffffff',
            padding: 12,
            cornerRadius: 8,
        },
    },
};
</script>

<template>
    <Doughnut :data="chartData" :options="chartOptions" />
</template>
