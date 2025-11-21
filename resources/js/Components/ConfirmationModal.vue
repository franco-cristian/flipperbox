<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close', 'confirm']);

defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Confirmar Acción' },
    message: { type: String, default: '¿Estás seguro de que deseas realizar esta acción?' },

    // --- NUEVAS PROPS PARA FLEXIBILIDAD ---
    confirmButtonText: { type: String, default: 'Confirmar' },
    confirmButtonClass: { type: String, default: 'bg-red-600 hover:bg-red-700' }, // Color por defecto (peligro)
});

const closeModal = () => emit('close');
const confirmAction = () => emit('confirm');
</script>

<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ title }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ message }}
            </p>

            <div class="mt-6 flex justify-end">
                <button
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none"
                    @click="closeModal"
                >
                    Cancelar
                </button>

                <!-- AHORA EL BOTÓN ES DINÁMICO -->
                <button
                    class="ml-3 px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none"
                    :class="confirmButtonClass"
                    @click="confirmAction"
                >
                    {{ confirmButtonText }}
                </button>
            </div>
        </div>
    </Modal>
</template>
