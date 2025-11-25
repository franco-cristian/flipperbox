<template>
    <div
        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-2xl dark:hover:shadow-gray-900/50 hover:border-blue-200 dark:hover:border-blue-900/50"
    >
        <div class="p-6">
            <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 tracking-widest uppercase mb-4">
                <slot name="title" />
            </h3>
            <div class="mt-1">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
