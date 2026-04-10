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
    color: {
        type: String,
        default: "AB",
    },
});

const colorClasses = {
    blue: "bg-blue-500",
    violet: "bg-violet-500",
    amber: "bg-amber-500",
    emerald: "bg-emerald-500",
    cyan: "bg-cyan-500",
    orange: "bg-orange-500",
    rose: "bg-rose-500",
    indigo: "bg-indigo-500",
};

function slugify(title) {
    return title
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}
</script>

<template>
    <component
        :is="external ? 'a' : Link"
        :href="href"
        :target="external ? '_blank' : undefined"
        :rel="external ? 'noopener noreferrer' : undefined"
        :data-guide="'services-'+slugify(title)"
        class="group relative h-full overflow-hidden rounded-xl bg-white dark:bg-[#1e293b] border border-gray-200/80 dark:border-slate-600/50 p-3 md:p-4 transition-all duration-300 hover:border-AC/60 dark:hover:border-AA/60 hover:shadow-lg hover:shadow-AC/10 dark:hover:shadow-xl dark:hover:shadow-black/20 hover:-translate-y-1"
    >
        <!-- Subtle background glow on hover -->
        <div class="absolute inset-0 bg-gradient-to-br from-AC/[0.03] to-AB/[0.03] dark:from-AA/[0.08] dark:to-AC/[0.05] opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        </div>

        <!-- Top accent line -->
        <div class="absolute top-0 left-0 h-[2px] bg-gradient-to-r from-AC to-AB dark:from-AA dark:to-AC w-0 group-hover:w-full transition-all duration-500 ease-out">
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col h-full">
            <div class="flex items-center gap-3 mb-1 md:mb-2">
                <!-- Icon container -->
                <div :class="`${colorClasses[color]}`" class="inline-flex h-8 w-8 md:h-12 md:w-12 items-center justify-center rounded-md md:rounded-xl bg-gradient-to-br from-AC/10 to-AB/5 dark:from-AA/20 dark:to-AC/10 dark:ring-1 dark:ring-AA/20 p-2 text-white transition-all duration-300 shadow-sm dark:shadow-none">
                    <component :is="icon" class="w-4 h-4 md:w-6 md:h-6" v-if="typeof icon === 'object'" />
                    <span v-else class="text-lg">{{ icon }}</span>
                </div>

                <!-- Title -->
                <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100 group-hover:text-AC dark:group-hover:text-AA transition-colors duration-300 leading-none">
                    {{ title }}
                </h3>
            </div>

            <!-- Description -->
            <p class="md:mb-2 flex-grow text-sm text-gray-600 dark:text-slate-400 group-hover:text-gray-700 dark:group-hover:text-slate-300 transition-colors duration-300 leading-relaxed leading-snug text-xs md:text-base">
                {{ description }}
            </p>

            <!-- Arrow indicator -->
            <div class="hidden md:flex items-center text-AC dark:text-AA text-xs font-medium group-hover:opacity-100 opacity-0 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                <span>Explore</span>
                <svg class="ml-2 w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-300"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>
    </component>
</template>

<style scoped>
</style>
