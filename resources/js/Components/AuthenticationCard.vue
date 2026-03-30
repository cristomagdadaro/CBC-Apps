<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-transparent dark:bg-gray-900 pointer-events-none">
        <div class="pointer-events-auto">
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-l pointer-events-auto">
            <slot />
        </div>
    </div>
</template>
