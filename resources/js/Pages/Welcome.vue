<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const { isDark, toggleDarkMode } = useDarkMode();
</script>

<template>
    <Head title="Bienvenido a FlipperBox" />

    <div
        class="relative min-h-screen flex flex-col justify-center items-center selection:bg-cyan-500 selection:text-white overflow-hidden"
    >
        <!-- 1. VIDEO DE FONDO -->
        <div class="absolute inset-0 z-0">
            <video autoplay loop muted playsinline class="w-full h-full object-cover">
                <source src="/videos/mechanic-bg.mp4" type="video/mp4" />
            </video>
            <!-- Overlay Gradiente: Oscuro para legibilidad -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-black/70 via-gray-900/60 to-black/80 dark:from-black/80 dark:via-gray-900/70 dark:to-black/90"
            ></div>
        </div>

        <!-- 2. BARRA DE NAVEGACIÓN (FLOTANTE) -->
        <nav class="absolute top-0 w-full z-20 p-6 flex justify-between items-center">
            <!-- Switch Dark Mode -->
            <button
                class="p-2 rounded-full bg-white/10 backdrop-blur-md hover:bg-white/20 transition text-white"
                @click="toggleDarkMode"
            >
                <span v-if="isDark">☀️</span>
                <span v-else>🌙</span>
            </button>

            <div v-if="canLogin" class="space-x-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="text-white font-semibold hover:text-cyan-400 transition"
                >
                    Ir al Dashboard
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="text-white font-semibold hover:text-cyan-400 transition">
                        Iniciar Sesión
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-5 py-2 rounded-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold shadow-lg shadow-cyan-500/30 transition transform hover:scale-105"
                    >
                        Registrarse
                    </Link>
                </template>
            </div>
        </nav>

        <!-- 3. CONTENIDO PRINCIPAL (HERO SECTION) -->
        <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
            <!-- Logo Central con Efecto de Elevación -->
            <div class="mb-10 transform hover:scale-105 transition duration-700 ease-out">
                <img
                    src="/images/logo-full.jpg"
                    alt="Flipper Servicios"
                    class="h-32 md:h-48 mx-auto rounded-2xl shadow-2xl shadow-cyan-500/20 border border-white/10"
                />
            </div>

            <!-- Títulos y Subtítulos -->
            <h1 class="text-5xl md:text-7xl font-black text-white tracking-tight mb-6 drop-shadow-lg">
                Tu Taller,
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-600"
                    >Evolucionado.</span
                >
            </h1>
            <p class="text-lg md:text-xl text-gray-300 mb-10 max-w-2xl mx-auto leading-relaxed">
                Gestión inteligente de turnos, historial clínico de tu vehículo y notificaciones en tiempo real. La
                confianza de siempre, ahora digital.
            </p>

            <!-- Botones de Acción Principal -->
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <Link
                    :href="route('register')"
                    class="px-8 py-4 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-bold text-lg shadow-xl hover:shadow-cyan-500/40 transition transform hover:-translate-y-1"
                >
                    Solicitar Turno Ahora
                </Link>
                <a
                    href="#servicios"
                    class="px-8 py-4 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white font-semibold text-lg hover:bg-white/20 transition"
                >
                    Nuestros Servicios
                </a>
            </div>
        </div>

        <!-- 4. CARACTERÍSTICAS (GLASSMORPHISM CARDS) -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
                class="p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-cyan-500/50 transition group"
            >
                <div class="text-4xl mb-4 group-hover:scale-110 transition">📅</div>
                <h3 class="text-xl font-bold text-white mb-2">Turnos Online</h3>
                <p class="text-gray-400 text-sm">
                    Olvídate de llamar. Reserva tu lugar desde tu celular en segundos, las 24hs.
                </p>
            </div>
            <div
                class="p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-cyan-500/50 transition group"
            >
                <div class="text-4xl mb-4 group-hover:scale-110 transition">🔧</div>
                <h3 class="text-xl font-bold text-white mb-2">Historial Digital</h3>
                <p class="text-gray-400 text-sm">
                    Accede al historial completo de reparaciones y servicios de tu vehículo.
                </p>
            </div>
            <div
                class="p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-cyan-500/50 transition group"
            >
                <div class="text-4xl mb-4 group-hover:scale-110 transition">🔔</div>
                <h3 class="text-xl font-bold text-white mb-2">Alertas en Vivo</h3>
                <p class="text-gray-400 text-sm">
                    Recibe notificaciones cuando tu auto esté listo o necesite un servicio.
                </p>
            </div>
        </div>

        <!-- Footer Simple -->
        <footer class="relative z-10 mt-20 py-6 text-center text-gray-500 text-sm">
            &copy; {{ new Date().getFullYear() }} Flipper Servicios. Av. Gendarmería Nacional 2758, Formosa.
        </footer>
    </div>
</template>
