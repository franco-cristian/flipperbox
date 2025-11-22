<template>
    <div v-bind="$attrs">
        <!-- Logo para Modo Claro (se oculta en dark) -->
        <img src="/images/logo-light.png" alt="FlipperBox Logo" class="dark:hidden h-full w-auto object-contain" />
        <!-- Logo para Modo Oscuro (se oculta en light) -->
        <img src="/images/logo-dark.png" alt="FlipperBox Logo" class="hidden dark:block h-full w-auto object-contain" />
    </div>
</template>
