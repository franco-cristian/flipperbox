<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

const page = usePage();
const show = ref(false);
const style = ref('success');
const message = ref('');
let removeFinishEventListener = null;

// Esta función se llamará cada vez que Inertia termine una visita a una página
const handleFlashMessage = () => {
    const flash = page.props.flash;
    if (flash && (flash.success || flash.error)) {
        style.value = flash.success ? 'success' : 'danger';
        message.value = flash.success || flash.error;
        show.value = true;

        // Ocultar el mensaje automáticamente después de 5 segundos
        setTimeout(() => {
            show.value = false;
            // Limpiamos el flash prop para la siguiente navegación
            page.props.flash = {}; 
        }, 5000);
    }
};

// Nos enganchamos a los eventos de Inertia
onMounted(() => {
    handleFlashMessage(); // Verificar si hay un mensaje en la carga inicial
    removeFinishEventListener = router.on('finish', handleFlashMessage);
});

onUnmounted(() => {
    if (removeFinishEventListener) {
        removeFinishEventListener();
    }
});

// Estilos para los diferentes tipos de notificación
const styles = {
    success: 'bg-green-100 border-green-500 text-green-700',
    danger: 'bg-red-100 border-red-500 text-red-700',
};
</script>

<template>
    <div>
        <transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
            <div v-if="show && message" :class="styles[style]" class="border-l-4 p-4 mx-8 mt-4" role="alert">
                <div class="flex">
                    <div class="py-1">
                        <svg v-if="style === 'success'" class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <svg v-if="style === 'danger'" class="w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="font-bold">{{ style === 'success' ? 'Éxito' : 'Error' }}</p>
                        <p>{{ message }}</p>
                    </div>
                    <div class="ml-auto">
                        <button @click.prevent="show = false" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>