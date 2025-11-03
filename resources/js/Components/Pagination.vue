<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3">
        <nav class="flex items-center justify-center">
            <template v-for="(link, key) in links" :key="key">
                <!-- Si el enlace no tiene URL (ej. '...'), lo mostramos deshabilitado -->
                <div v-if="link.url === null"
                     class="px-3 py-2 mx-1 text-sm leading-4 text-gray-400 border rounded-md"
                     v-html="link.label" />
                
                <!-- Si es un enlace normal, lo renderizamos con <Link> -->
                <Link v-else
                      class="px-3 py-2 mx-1 text-sm leading-4 border rounded-md focus:outline-none focus:border-indigo-500 hover:bg-gray-100"
                      :class="{ 'bg-gray-800 text-white': link.active }"
                      :href="link.url"
                      v-html="link.label" />
            </template>
        </nav>
    </div>
</template>