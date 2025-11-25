<script setup>
import { Link } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ChatbotWidget from '@/Components/ChatbotWidget.vue';

const { isDark, toggleDarkMode } = useDarkMode();

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="min-h-screen flex flex-col font-sans text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 transition-colors duration-500"
    >
        <!-- NAVBAR -->
        <nav
            class="z-50 w-full px-6 py-4 flex justify-between items-center border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md sticky top-0 shadow-sm transition-colors duration-500"
        >
            <!-- Logo FlipperBox -->
            <Link href="/" class="flex items-center gap-3 h-10 group">
                <ApplicationLogo class="h-full w-auto group-hover:scale-105 transition transform" />
            </Link>

            <div class="flex items-center gap-4">
                <!-- Switch Dark Mode -->
                <button
                    class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300"
                    :title="isDark ? 'Cambiar a modo Claro' : 'Cambiar a modo Oscuro'"
                    @click="toggleDarkMode"
                >
                    <svg
                        v-if="isDark"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                        />
                    </svg>
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                </button>

                <!-- Links de Auth -->
                <div v-if="canLogin" class="flex gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="font-bold hover:text-blue-600 dark:hover:text-blue-400 transition flex items-center"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-5 py-2 rounded-full font-bold text-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-white/10 transition text-sm"
                        >
                            Ingresar
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-5 py-2 rounded-full bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold shadow-lg shadow-cyan-500/30 transition transform hover:scale-105 border border-white/10 text-sm hidden sm:block"
                        >
                            Registrarse
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="flex-grow flex flex-col">
            <slot />
        </main>

        <!-- FOOTER -->
        <footer
            class="z-10 py-8 text-center text-sm border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 transition-colors duration-500"
        >
            <p>&copy; {{ new Date().getFullYear() }} Flipper Servicios. Av. Gendarmería Nacional 2758, Formosa.</p>
        </footer>

        <ChatbotWidget />
    </div>
</template>
