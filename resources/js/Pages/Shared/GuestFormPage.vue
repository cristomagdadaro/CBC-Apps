<script>
import SocialLinks from "@/Components/SocialLinks.vue";
import GuideTourControls from "@/Components/GuideTourControls.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";
import { Link } from "@inertiajs/vue3"; // Added to ensure <Link> works

export default {
    name: "GuestFormPage",
    components: { GuideTourControls, SocialLinks, MainBg, Link },
    props: {
        /** Main title text in the colored header bar */
        title: {
            type: String,
            required: true,
        },
        /** Subtitle / helper text under the title */
        subtitle: {
            type: String,
            default: "",
        },
        /** Whether to show the main inner card content (v-show) */
        delayReady: {
            type: Boolean,
            default: true,
        },
        maxWidth: {
            type: String,
            default: "max-w-full",
        },
        guideKey: {
            type: String,
            default: "guest-page",
        },
    },
    methods: {
        goBack() {
            window.history.back();
        },
    },
};
</script>

<template>
    <!-- Background gradient (fixed behind content) -->
    <main-bg></main-bg>
    <guide-tour-controls :guide-key="guideKey" />

    <!-- Main content overlay -->
    <!-- UX: Added horizontal padding and vertical padding to prevent edge-hugging on mobile -->
    <div
        id="main-content-overlay"
        class="m-0 flex min-h-screen justify-center overflow-visible px-4 py-6 sm:px-6 sm:py-10 md:m-5">
        <div class="pointer-events-none relative mt-8 flex h-full w-full flex-col items-center justify-start overflow-visible md:mt-0 md:gap-6">
            <div
                class="pointer-events-auto flex w-full flex-col gap-6 overflow-visible md:relative"
                :class="maxWidth">
                <!-- Header / search / top content -->
                <slot name="top">
                    <div
                        v-show="delayReady"
                        class="flex w-full flex-col gap-4">
                        <!-- Premium Frosted Glass Header -->
                        <div
                            data-guide="guest-page-header"
                            class="relative flex w-full flex-row items-start gap-2.5 rounded-xl border border-gray-200/60 bg-white/80 p-4 shadow-lg backdrop-blur-lg transition-all duration-300 sm:items-center sm:gap-4 sm:rounded-2xl dark:border-slate-700/60 dark:bg-slate-900/80">
                            <!-- Mobile Back Button -->
                            <!-- UX Fix: Reduced padding, aligned near the top to match the title -->
                            <button
                                @click="goBack"
                                class="mt-0.5 flex flex-shrink-0 items-center justify-center rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-200/50 hover:text-slate-900 sm:mt-0 md:hidden dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="22"
                                    height="22"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                            </button>

                            <!-- UX Fix: Shrunk logo slightly on mobile (w-10 h-10) to give text more horizontal space -->
                            <Link
                                href="/"
                                class="mt-0.5 flex-shrink-0 sm:mt-0">
                                <!-- Visible in Light Mode, Hidden in Dark Mode -->
                                <img
                                    src="/imgs/logo-black.png"
                                    alt="logo"
                                    class="block h-10 w-10 drop-shadow-md sm:h-12 sm:w-12 md:h-14 md:w-14 dark:hidden" />

                                <!-- Hidden in Light Mode, Visible in Dark Mode -->
                                <img
                                    src="/imgs/logo.png"
                                    alt="logo"
                                    class="hidden h-10 w-10 drop-shadow-md sm:h-12 sm:w-12 md:h-14 md:w-14 dark:block" />
                            </Link>

                            <div class="flex min-w-0 flex-1 flex-col justify-start">
                                <!-- UX Fix: Removed 'truncate' and added 'leading-tight' so long titles safely wrap to the next line -->
                                <h1 class="text-base font-extrabold uppercase leading-tight tracking-wide text-slate-800 sm:text-lg lg:text-xl dark:text-slate-100">
                                    {{ title }}
                                </h1>
                                <p
                                    v-if="subtitle"
                                    class="mt-1 text-[0.7rem] font-medium leading-snug text-slate-600 sm:mt-0.5 sm:text-xs md:text-sm dark:text-slate-400">
                                    {{ subtitle }}
                                </p>
                            </div>
                        </div>

                        <slot name="search" />
                    </div>
                </slot>

                <!-- Main body content under header -->
                <div
                    data-guide="guest-page-content"
                    class="mt-2 w-full sm:mt-0">
                    <slot />
                </div>
            </div>
        </div>
    </div>

    <social-links />
</template>
