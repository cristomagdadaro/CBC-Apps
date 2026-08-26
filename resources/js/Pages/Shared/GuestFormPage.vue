<script>
import SocialLinks from "@/Components/SocialLinks.vue";
import GuideTourControls from "@/Components/GuideTourControls.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";
import { Link } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";

export default {
    name: "GuestFormPage",
    components: { GuideTourControls, SocialLinks, MainBg, Link, ApplicationMark },
    props: {
        title: {
            type: String,
            required: true,
        },
        subtitle: {
            type: String,
            default: "",
        },
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
    <main-bg></main-bg>
    <guide-tour-controls :guide-key="guideKey" />
    <div
        id="main-content-overlay"
        class="m-0 flex min-h-screen justify-center overflow-visible px-4 py-6 sm:px-6 sm:py-10 md:m-5">
        <div class="pointer-events-none relative mt-2 flex h-full w-full flex-col items-center justify-start overflow-visible md:mt-0 md:gap-6">
            <div
                class="pointer-events-auto flex w-full flex-col gap-3 overflow-visible md:relative md:gap-6"
                :class="maxWidth">
                <slot name="top">
                    <div
                        v-show="delayReady"
                        class="flex w-full flex-col gap-4">
                        <div
                            data-guide="guest-page-header"
                            class="relative flex w-full flex-row items-start gap-2.5 rounded-xl border border-gray-200/60 bg-white/80 p-4 !pl-1 shadow-lg backdrop-blur-lg transition-all duration-300 sm:items-center sm:gap-4 sm:rounded-2xl sm:!pl-4 dark:border-slate-700/60 dark:bg-slate-900/80">
                            <button
                                @click="goBack"
                                class="my-auto flex flex-shrink-0 items-center justify-center rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-200/50 hover:text-slate-900 md:hidden dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-white">
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
                            <Link
                                href="/"
                                class="my-auto flex-shrink-0">
                                <ApplicationMark class="h-full min-h-10 w-auto text-indigo-600 md:h-10 lg:h-12 dark:text-indigo-500" />
                            </Link>

                            <div class="flex min-w-0 flex-1 flex-col justify-start">
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
