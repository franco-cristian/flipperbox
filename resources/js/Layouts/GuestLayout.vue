<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout>
        <div class="min-h-[calc(100vh-140px)] flex flex-col justify-center items-center relative overflow-hidden py-12">
            <!-- Video Fondo -->
            <div class="absolute inset-0 z-0">
                <video autoplay loop muted playsinline class="w-full h-full object-cover">
                    <source src="/videos/mechanic-bg.mp4" type="video/mp4" />
                </video>
                <div class="absolute inset-0 bg-black/60"></div>
            </div>

            <!-- Tarjeta de Formulario -->
            <div
                class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl rounded-2xl"
            >
                <!-- Slot para el formulario -->
                <slot />
            </div>
        </div>
    </PublicLayout>
</template>
