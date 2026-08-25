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
        class="sm:p-4.5 group relative flex h-full w-full flex-col overflow-visible rounded-2xl border border-gray-200 bg-white p-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:border-lime-500 hover:shadow-md hover:ring-1 hover:ring-lime-500/40 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-lime-400 dark:hover:ring-lime-400/40">
        <!-- Subtle background tint on hover -->
        <div class="pointer-events-none absolute inset-0 rounded-2xl bg-lime-500/[0.02] opacity-0 transition-opacity duration-300 group-hover:opacity-100 dark:bg-lime-400/[0.03]"></div>

        <!-- Metric Badge -->
        <span
            v-if="badgeCount !== 0 && badgeCount !== null && badgeCount !== undefined && badgeCount !== ''"
            class="absolute -right-2.5 -top-2.5 z-20 inline-flex h-6 min-w-[1.6rem] shrink-0 items-center justify-center rounded-full border border-white/20 bg-gradient-to-r from-lime-500 to-emerald-500 px-2 text-[0.68rem] font-extrabold text-white shadow-md shadow-lime-500/20 sm:h-7 sm:text-xs dark:from-lime-500 dark:to-emerald-600">
            {{ badgeCount }}
        </span>

        <!-- Content -->
        <div class="relative z-10 flex h-full flex-col">
            <div class="mb-2 flex items-center gap-3 sm:mb-3">
                <!-- Icon container -->
                <div
                    :class="`${colorClasses[color]}`"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-white/20 to-transparent p-2 text-white shadow-md shadow-slate-900/10 transition-all duration-300 group-hover:scale-105 sm:h-11 sm:w-11">
                    <component
                        :is="icon"
                        class="h-4 w-4 sm:h-5 sm:w-5"
                        v-if="typeof icon === 'object'" />
                    <span
                        v-else
                        class="text-sm font-bold sm:text-lg">
                        {{ icon }}
                    </span>
                </div>

                <!-- Title -->
                <h3 class="min-w-0 text-sm font-bold leading-tight text-slate-900 transition-colors duration-300 group-hover:text-lime-600 sm:text-base dark:text-slate-100 dark:group-hover:text-lime-400">
                    {{ title }}
                </h3>
            </div>

            <!-- Description -->
            <p class="mb-2 flex-grow text-xs leading-snug text-slate-600 transition-colors duration-300 group-hover:text-slate-800 sm:mb-3 sm:text-sm sm:leading-relaxed dark:text-slate-300 dark:group-hover:text-slate-200">
                {{ description }}
            </p>

            <!-- Action Link Indicator -->
            <div class="mt-auto flex items-center pt-1 text-[0.7rem] font-bold uppercase tracking-wider text-lime-600 transition-all duration-300 group-hover:opacity-100 sm:text-xs sm:opacity-80 dark:text-lime-400">
                <span>Explore</span>
                <svg
                    class="ml-1.5 h-3.5 w-3.5 transform transition-transform duration-300 group-hover:translate-x-1"
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
