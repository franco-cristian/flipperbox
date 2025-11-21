<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900 relative overflow-hidden"
    >
        <!-- 1. FONDO -->
        <div class="absolute inset-0 z-0">
            <!-- Opción A: Video -->
            <video autoplay loop muted playsinline class="w-full h-full object-cover opacity-40">
                <source src="/videos/mechanic-bg.mp4" type="video/mp4" />
            </video>
            <!-- Opción B: Imagen estática -->
            <!-- <img src="/images/mechanic-bg.jpg" class="w-full h-full object-cover opacity-40"> -->

            <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-gray-900/90"></div>
        </div>

        <!-- 2. LOGO -->
        <div class="z-10 mb-6">
            <Link href="/">
                <img
                    src="/images/logo-full.jpg"
                    alt="FlipperBox"
                    class="w-32 h-auto rounded-xl shadow-lg border border-white/10"
                />
            </Link>
        </div>

        <!-- 3. TARJETA DEL FORMULARIO -->
        <div
            class="z-10 w-full sm:max-w-md mt-6 px-8 py-8 bg-white/10 backdrop-blur-md border border-white/10 shadow-2xl overflow-hidden sm:rounded-2xl"
        >
            <slot />
        </div>
    </div>
</template>
