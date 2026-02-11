<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    title: String,
    description: String,
    icon: [String, Object],
    href: String,
    external: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <component
        :is="external ? 'a' : Link"
        :href="href"
        :target="external ? '_blank' : undefined"
        :rel="external ? 'noopener noreferrer' : undefined"
        class="group relative h-full overflow-hidden rounded-xl bg-gradient-to-br from-white via-gray-50 to-gray-100 dark:from-gray-800 dark:via-gray-900 dark:to-gray-950 border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:border-AC dark:hover:border-AA hover:shadow-lg dark:hover:shadow-lg hover:shadow-AC/25 dark:hover:shadow-AA/20 hover:-translate-y-1"
    >
        <!-- Background gradient on hover -->
        <div class="absolute inset-0 bg-gradient-to-br from-AC/5 via-AB/5 to-AA/5 dark:from-AC/15 dark:to-AA/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

        <!-- Top accent line -->
        <div class="absolute top-0 left-0 h-1 bg-gradient-to-r from-AC via-AB to-transparent dark:from-AA dark:via-AD dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="width: 100%"></div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col h-full">
            <!-- Icon container -->
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-AC/10 to-AB/5 dark:from-AC/25 dark:to-AA/15 p-2 text-AC dark:text-AA group-hover:from-AC/15 group-hover:to-AB/10 dark:group-hover:from-AC/35 dark:group-hover:to-AA/25 group-hover:text-AC dark:group-hover:text-AA transition-all duration-300 shadow-sm dark:shadow-none">
                <component :is="icon" class="w-6 h-6" v-if="typeof icon === 'object'" />
                <span v-else class="text-lg">{{ icon }}</span>
            </div>

            <!-- Title -->
            <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white group-hover:text-AC dark:group-hover:text-AA transition-colors duration-300">
                {{ title }}
            </h3>

            <!-- Description -->
            <p class="mb-4 flex-grow text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-300 leading-relaxed">
                {{ description }}
            </p>

            <!-- Arrow indicator -->
            <div class="inline-flex items-center text-AC dark:text-AA text-sm font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <span>Explore</span>
                <svg class="ml-2 w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>
    </component>
</template>

<style scoped>
</style>
