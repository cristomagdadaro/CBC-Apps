<template>
    <div class="pointer-events-none flex min-h-screen flex-col items-center bg-transparent pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900">
        <div class="pointer-events-auto">
            <slot name="logo" />
        </div>

        <div class="pointer-events-auto mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-l dark:bg-gray-800">
            <slot />
        </div>
    </div>
</template>
