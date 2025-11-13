<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const notifications = ref([]);
const unreadCount = ref(0);
const showDropdown = ref(false);

// Solo los usuarios con permiso pueden ver y escuchar notificaciones
const canViewNotifications = usePage().props.auth.user.permissions.includes('gestionar reservas');

onMounted(() => {
    if (canViewNotifications) {
        // Escuchamos en el canal privado 'admin-notifications'
        window.Echo.private('admin-notifications')
            .listen('.reservation.made', (e) => {
                // Cuando llega un evento 'reservation.made', lo añadimos al principio de la lista
                notifications.value.unshift(e);
                unreadCount.value++;
            });
    }
});

const markAsRead = () => {
    unreadCount.value = 0;
};
</script>

<template>
    <div v-if="canViewNotifications" class="relative">
        <!-- Icono de la Campanita -->
        <button @click="showDropdown = !showDropdown; markAsRead()" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <!-- Contador de No Leídas -->
            <span v-if="unreadCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ unreadCount }}
            </span>
        </button>

        <!-- Dropdown de Notificaciones -->
        <div v-show="showDropdown" @click.away="showDropdown = false" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-20" style="display: none;">
            <div class="py-2">
                <div v-if="notifications.length > 0">
                    <Link v-for="(notification, index) in notifications" :key="index" :href="notification.url" @click="showDropdown = false" class="flex items-center px-4 py-3 border-b hover:bg-gray-100 -mx-2">
                        <div class="mx-3">
                            <p class="text-sm text-gray-600">{{ notification.message }}</p>
                            <p class="text-xs text-gray-400">{{ notification.date }}</p>
                        </div>
                    </Link>
                </div>
                <div v-else class="px-4 py-3 text-sm text-gray-500 text-center">
                    No hay notificaciones nuevas.
                </div>
            </div>
            <a href="#" class="block bg-gray-800 text-white text-center font-bold py-2">Ver todas las notificaciones</a>
        </div>
    </div>
</template>
