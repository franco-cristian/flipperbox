<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-sm font-medium text-gray-500 tracking-wider uppercase">
                <slot name="title" />
            </h3>
            <div class="mt-4">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>