<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";

defineProps({
    policy: String,
});
</script>

<template>
    <Head title="Privacy Policy" />

    <div class="font-sans text-slate-900 dark:text-slate-100 antialiased min-h-screen bg-slate-50 dark:bg-slate-950">
        <div class="py-6 sm:py-12 px-3.5 sm:px-6 flex flex-col items-center justify-center min-h-screen">
            <div class="shrink-0 mb-4 sm:mb-6">
                <Link
                    href="/"
                    class="transition-transform hover:scale-105 inline-block">
                    <AuthenticationCardLogo />
                </Link>
            </div>

            <div
                class="w-full max-w-4xl p-5 sm:p-10 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-xl rounded-2xl prose dark:prose-invert max-w-none text-xs sm:text-sm md:text-base leading-relaxed tracking-normal"
                v-html="policy" />

            <div class="mt-6 text-center">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-lime-600 dark:text-lime-400 hover:underline">
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Home
                </Link>
            </div>
        </div>
    </div>
</template>
