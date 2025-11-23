<script setup>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

defineProps({
    chartData: { type: Object, required: true },
});

// Opciones fijas que funcionan en ambos modos
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            bodyColor: '#ffffff',
            titleColor: '#ffffff',
            padding: 12,
            cornerRadius: 8,
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: {
                    size: 12,
                },
                color: '#6B7280', // Color gris que funciona en ambos modos
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.1)',
            },
            ticks: {
                font: {
                    size: 12,
                },
                stepSize: 1,
                color: '#6B7280', // Color gris que funciona en ambos modos
            },
        },
    },
};
</script>

<template>
    <Bar :data="chartData" :options="chartOptions" />
</template>
