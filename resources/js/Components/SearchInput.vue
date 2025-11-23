<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: 'Buscar...',
    },
    routeParams: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.modelValue);

// Usamos debounce para esperar 300ms después de que el usuario deje de escribir
const updateSearch = debounce((value) => {
    router.get(
        route(route().current()),
        { search: value, ...props.routeParams },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}, 300);

watch(search, (value) => {
    updateSearch(value);
});
</script>

<template>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>
        </div>
        <input
            v-model="search"
            type="text"
            class="pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full transition duration-150 ease-in-out"
            :placeholder="placeholder"
        />
    </div>
</template>
