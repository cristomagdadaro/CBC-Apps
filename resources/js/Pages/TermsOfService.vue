<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";

defineProps({
    terms: String,
});
</script>

<template>
    <Head title="Terms of Service" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="flex min-h-screen flex-col items-center justify-center px-3.5 py-6 sm:px-6 sm:py-12">
            <div class="mb-4 shrink-0 sm:mb-6">
                <Link
                    href="/"
                    class="inline-block transition-transform hover:scale-105">
                    <AuthenticationCardLogo />
                </Link>
            </div>

            <div
                class="prose w-full max-w-4xl max-w-none rounded-2xl border border-gray-200 bg-white p-5 text-xs leading-relaxed tracking-normal shadow-xl dark:prose-invert sm:p-10 sm:text-sm md:text-base dark:border-slate-800 dark:bg-slate-900"
                v-html="terms" />

            <div class="mt-6 text-center">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 text-xs font-semibold text-lime-600 hover:underline sm:text-sm dark:text-lime-400">
                    <svg
                        class="h-4 w-4"
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
