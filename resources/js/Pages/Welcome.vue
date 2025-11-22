<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Bienvenido" />

    <PublicLayout :can-login="canLogin" :can-register="canRegister">
        <!-- 1. HERO SECTION -->
        <div
            class="relative min-h-[calc(100vh-74px)] flex flex-col justify-center items-center text-center px-4 overflow-hidden pb-20"
        >
            <!-- Video de Fondo -->
            <div class="absolute inset-0 z-0">
                <video autoplay loop muted playsinline class="w-full h-full object-cover">
                    <source src="/videos/mechanic-bg.mp4" type="video/mp4" />
                </video>
                <!-- Overlay oscuro -->
                <div class="absolute inset-0 bg-black/60"></div>
            </div>

            <!-- Contenido del Hero -->
            <div class="relative z-10 max-w-4xl mx-auto mt-10">
                <!-- Logo del Taller -->
                <div class="mb-10 transform hover:scale-105 transition duration-700 ease-out inline-block">
                    <img
                        src="/images/logo-full.jpg"
                        alt="Flipper Servicios"
                        class="h-32 md:h-52 mx-auto rounded-3xl shadow-2xl border-4 border-white/10"
                    />
                </div>

                <h1 class="text-5xl md:text-7xl font-black text-white tracking-tight mb-6 drop-shadow-2xl">
                    Tu Taller,
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500"
                        >Evolucionado.</span
                    >
                </h1>

                <p class="text-xl text-gray-200 mb-12 max-w-2xl mx-auto leading-relaxed font-light drop-shadow-md">
                    Gestión inteligente de turnos, historial clínico de tu vehículo y alertas en tiempo real.
                    <span class="text-white font-medium">La confianza de siempre, ahora digital.</span>
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-5">
                    <Link
                        :href="route('register')"
                        class="px-8 py-4 rounded-xl bg-blue-600 text-white font-bold text-lg shadow-xl hover:bg-blue-500 transition transform hover:-translate-y-1 w-full sm:w-auto"
                    >
                        Solicitar Turno Ahora
                    </Link>
                    <a
                        href="#servicios"
                        class="px-8 py-4 rounded-xl bg-white/10 backdrop-blur-md border border-white/30 text-white font-semibold text-lg hover:bg-white/20 transition w-full sm:w-auto"
                    >
                        Ver Servicios
                    </a>
                </div>
            </div>

            <!-- Indicador de Scroll (Flecha) -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce text-white/50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7"></path>
                </svg>
            </div>
        </div>

        <!-- 2. SERVICIOS -->
        <section id="servicios" class="relative z-10 py-24 px-6 transition-colors duration-500">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <span class="text-cyan-600 dark:text-cyan-400 font-bold uppercase tracking-wider text-sm"
                        >¿Por qué elegirnos?</span
                    >
                    <h2 class="text-3xl md:text-4xl font-black mt-3 text-gray-900 dark:text-white">
                        Innovación al Servicio de tu Vehículo
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <!-- Card 1 -->
                    <div
                        class="p-8 rounded-[2rem] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-cyan-500 dark:hover:border-cyan-500 transition-all duration-300 hover:-translate-y-2 group shadow-lg hover:shadow-2xl dark:shadow-none"
                    >
                        <div
                            class="w-16 h-16 rounded-2xl bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center text-4xl mb-8 group-hover:rotate-6 transition duration-300"
                        >
                            📅
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Turnos Online 24/7</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-lg">
                            Reserva tu lugar desde tu celular en segundos, las 24hs. Sin llamadas, sin esperas.
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div
                        class="p-8 rounded-[2rem] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300 hover:-translate-y-2 group shadow-lg hover:shadow-2xl dark:shadow-none"
                    >
                        <div
                            class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-4xl mb-8 group-hover:rotate-6 transition duration-300"
                        >
                            🔧
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Historial Digital</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-lg">
                            Accede al historial completo de reparaciones y servicios de tu vehículo. Transparencia total
                            en cada servicio.
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div
                        class="p-8 rounded-[2rem] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 transition-all duration-300 hover:-translate-y-2 group shadow-lg hover:shadow-2xl dark:shadow-none"
                    >
                        <div
                            class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-4xl mb-8 group-hover:rotate-6 transition duration-300"
                        >
                            🔔
                        </div>
                        <h3 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Alertas en Vivo</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-lg">
                            Recibe notificaciones instantáneas cuando tu vehículo esté listo para retirar o necesite un
                            servicio.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
