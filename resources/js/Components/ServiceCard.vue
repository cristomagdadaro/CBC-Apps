<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    title: String,
    description: String,
    icon: [String, Object],
    href: String,
    badgeCount: {
        type: [Number, String, null],
        default: null,
    },
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
    teal: "bg-teal-500",
};

function slugify(title) {
    return title
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
}
</script>

<template>
    <component
        :is="external ? 'a' : Link"
        :href="href"
        :target="external ? '_blank' : undefined"
        :rel="external ? 'noopener noreferrer' : undefined"
        :data-guide="'services-' + slugify(title)"
        class="group relative flex flex-col h-full w-full overflow-visible rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 p-3.5 sm:p-4.5 transition-all duration-300 hover:border-lime-500 dark:hover:border-lime-400 hover:ring-1 hover:ring-lime-500/40 dark:hover:ring-lime-400/40 hover:shadow-md hover:-translate-y-0.5">
        <!-- Subtle background tint on hover -->
        <div class="absolute inset-0 rounded-2xl bg-lime-500/[0.02] dark:bg-lime-400/[0.03] opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

        <!-- Metric Badge -->
        <span
            v-if="badgeCount !== 0 && badgeCount !== null && badgeCount !== undefined && badgeCount !== ''"
            class="inline-flex items-center justify-center min-w-[1.6rem] h-6 sm:h-7 px-2 rounded-full bg-gradient-to-r from-lime-500 to-emerald-500 dark:from-lime-500 dark:to-emerald-600 text-white text-[0.68rem] sm:text-xs font-extrabold shadow-md shadow-lime-500/20 shrink-0 absolute -top-2.5 -right-2.5 z-20 border border-white/20">
            {{ badgeCount }}
        </span>

        <!-- Content -->
        <div class="relative z-10 flex flex-col h-full">
            <div class="flex items-center gap-3 mb-2 sm:mb-3">
                <!-- Icon container -->
                <div
                    :class="`${colorClasses[color]}`"
                    class="inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl bg-gradient-to-br from-white/20 to-transparent p-2 text-white transition-all duration-300 shadow-md shadow-slate-900/10 shrink-0 group-hover:scale-105">
                    <component
                        :is="icon"
                        class="w-4 h-4 sm:w-5 sm:h-5"
                        v-if="typeof icon === 'object'" />
                    <span
                        v-else
                        class="text-sm sm:text-lg font-bold">
                        {{ icon }}
                    </span>
                </div>

                <!-- Title -->
                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 group-hover:text-lime-600 dark:group-hover:text-lime-400 transition-colors duration-300 leading-tight min-w-0">
                    {{ title }}
                </h3>
            </div>

            <!-- Description -->
            <p class="mb-2 sm:mb-3 flex-grow text-xs sm:text-sm text-slate-600 dark:text-slate-300 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors duration-300 leading-snug sm:leading-relaxed">
                {{ description }}
            </p>

            <!-- Action Link Indicator -->
            <div class="flex items-center text-lime-600 dark:text-lime-400 text-[0.7rem] sm:text-xs font-bold uppercase tracking-wider group-hover:opacity-100 sm:opacity-80 transition-all duration-300 mt-auto pt-1">
                <span>Explore</span>
                <svg
                    class="ml-1.5 w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform duration-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>
    </component>
</template>

<style scoped></style>
