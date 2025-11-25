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
        <nav class="flex items-center justify-center flex-wrap gap-1">
            <template v-for="(link, key) in links" :key="key">
                <!-- Sin URL -->
                <div
                    v-if="link.url === null"
                    class="px-3 py-2 text-sm leading-4 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800"
                    v-html="link.label"
                />

                <!-- Con URL -->
                <Link
                    v-else
                    class="px-3 py-2 text-sm leading-4 border rounded-md transition-colors duration-150"
                    :class="{
                        'bg-blue-600 text-white border-blue-600': link.active,
                        'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700':
                            !link.active,
                    }"
                    :href="link.url"
                >
                    <span v-html="link.label"></span>
                </Link>
            </template>
        </nav>
    </div>
</template>
