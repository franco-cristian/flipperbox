<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();

// El estado inicial ahora se carga desde las props globales
const notifications = ref(page.props.notifications || []);
const showDropdown = ref(false);

const unreadCount = computed(() => notifications.value.filter((n) => !n.read_at).length);
const canViewNotifications = computed(() => page.props.auth.user?.permissions?.includes('gestionar reservas'));

onMounted(() => {
    if (canViewNotifications.value) {
        // AHORA (Notificación estándar de Laravel)
        // Usamos el ID del usuario autenticado para escuchar SU canal privado
        window.Echo.private(`App.Models.User.${page.props.auth.user.id}`).notification((notification) => {
            const newNotification = {
                id: notification.id,
                data: {
                    message: notification.message, // Nota: Ya no viene dentro de 'data' anidado, Laravel lo aplana
                    url: notification.url,
                },
                created_at: new Date().toISOString(),
                read_at: null,
            };
            notifications.value.unshift(newNotification);

            // Opcional: Reproducir un sonido
            // playNotificationSound();
        });
    }
});

const markAsRead = () => {
    if (unreadCount.value > 0) {
        // Enviamos una petición al backend para marcar como leídas
        router.post(
            route('notifications.markAsRead'),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    notifications.value.forEach((n) => (n.read_at = new Date().toISOString()));
                },
            }
        );
    }
};

// Función opcional para reproducir sonido de notificación
//const playNotificationSound = () => {
    // implementar un sonido de notificación aquí
    // const audio = new Audio('/sounds/notification.mp3');
    // audio.play().catch(e => console.log('Error reproduciendo sonido:', e));
//};
</script>

<template>
    <div v-if="canViewNotifications" class="relative">
        <!-- Icono de la Campanita -->
        <!-- Agregamos z-50 para que el botón quede por encima de todo si es necesario -->
        <button
            class="relative z-50 text-gray-500 hover:text-gray-700 focus:outline-none"
            @click="
                showDropdown = !showDropdown;
                markAsRead();
            "
        >
            <svg
                class="h-6 w-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                ></path>
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
            >
                {{ unreadCount }}
            </span>
        </button>

        <!-- NUEVO: Overlay invisible para detectar clic afuera -->
        <!-- Este div cubre toda la pantalla. Al hacer clic en él, cierra el dropdown -->
        <div v-show="showDropdown" class="fixed inset-0 z-40 cursor-default" @click="showDropdown = false"></div>

        <!-- Dropdown de Notificaciones -->
        <!-- Eliminamos @click.away y ajustamos el z-index a 50 para que flote sobre el overlay -->
        <div
            v-show="showDropdown"
            class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50"
        >
            <div class="py-2">
                <div v-if="notifications.length > 0">
                    <!-- Limitamos a las últimas 5 para que no sea enorme -->
                    <Link
                        v-for="notification in notifications.slice(0, 5)"
                        :key="notification.id"
                        :href="notification.data.url"
                        class="flex items-center px-4 py-3 border-b hover:bg-gray-100 -mx-2"
                        @click="showDropdown = false"
                    >
                        <div class="mx-3">
                            <p class="text-sm text-gray-600">{{ notification.data.message }}</p>
                            <p class="text-xs text-gray-400">
                                {{ new Date(notification.created_at).toLocaleString('es-ES') }}
                            </p>
                        </div>
                    </Link>
                </div>
                <div v-else class="px-4 py-3 text-sm text-gray-500 text-center">No hay notificaciones.</div>
            </div>
        </div>
    </div>
</template>
