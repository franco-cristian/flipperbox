<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close', 'confirm']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirmar Acción'
    },
    message: {
        type: String,
        default: '¿Estás seguro de que deseas realizar esta acción?'
    }
});

const closeModal = () => {
    emit('close');
};

const confirmAction = () => {
    emit('confirm');
};
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
                <button @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none">
                    Cancelar
                </button>

                <button @click="confirmAction" class="ml-3 px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none">
                    Confirmar Eliminación
                </button>
            </div>
        </div>
    </Modal>
</template>
