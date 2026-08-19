<script>
import SocialLinks from "@/Components/SocialLinks.vue";
import GuideTourControls from "@/Components/GuideTourControls.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";
import { Link } from "@inertiajs/vue3"; // Added to ensure <Link> works

export default {
    name: 'GuestFormPage',
    components: {GuideTourControls, SocialLinks, MainBg, Link},
    props: {
        /** Main title text in the colored header bar */
        title: {
            type: String,
            required: true,
        },
        /** Subtitle / helper text under the title */
        subtitle: {
            type: String,
            default: '',
        },
        /** Whether to show the main inner card content (v-show) */
        delayReady: {
            type: Boolean,
            default: true,
        },
        maxWidth: {
            type: String,
            default: 'max-w-full',
        },
        guideKey: {
            type: String,
            default: 'guest-page',
        },
    },
    methods: {
        goBack() {
            window.history.back();
        }
    }
};
</script>

<template>
    <!-- Background gradient (fixed behind content) -->
    <main-bg></main-bg>
    <guide-tour-controls :guide-key="guideKey" />

    <!-- Main content overlay -->
    <!-- UX: Added horizontal padding and vertical padding to prevent edge-hugging on mobile -->
    <div id="main-content-overlay" class="flex justify-center min-h-screen px-4 sm:px-6 py-6 sm:py-10 m-0 md:m-5 overflow-visible">
        <div class="relative flex flex-col md:gap-6 justify-start items-center w-full h-full overflow-visible pointer-events-none mt-8 md:mt-0">
            <div class="md:relative flex flex-col gap-6 w-full overflow-visible pointer-events-auto" :class="maxWidth">
                
                <!-- Header / search / top content -->
                <slot name="top">
    <div v-show="delayReady" class="w-full flex flex-col gap-4">
        
        <!-- Premium Frosted Glass Header -->
        <div data-guide="guest-page-header" 
             class="relative flex flex-row items-start sm:items-center gap-2.5 sm:gap-4 p-4 w-full bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border border-gray-200/60 dark:border-slate-700/60 shadow-lg rounded-xl sm:rounded-2xl transition-all duration-300">
            
            <!-- Mobile Back Button -->
            <!-- UX Fix: Reduced padding, aligned near the top to match the title -->
            <button @click="goBack" class="md:hidden flex-shrink-0 flex items-center justify-center p-1.5 mt-0.5 sm:mt-0 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-lg hover:bg-slate-200/50 dark:hover:bg-slate-800/50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            
            <!-- UX Fix: Shrunk logo slightly on mobile (w-10 h-10) to give text more horizontal space -->
            <Link href="/" class="flex-shrink-0 mt-0.5 sm:mt-0">
                <!-- Visible in Light Mode, Hidden in Dark Mode -->
                <img src="/imgs/logo-black.png" alt="logo" class="block dark:hidden w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 drop-shadow-md" />
                
                <!-- Hidden in Light Mode, Visible in Dark Mode -->
                <img src="/imgs/logo.png" alt="logo" class="hidden dark:block w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 drop-shadow-md" />
            </Link>
            
            <div class="flex flex-col justify-start flex-1 min-w-0">
                <!-- UX Fix: Removed 'truncate' and added 'leading-tight' so long titles safely wrap to the next line -->
                <h1 class="font-extrabold text-base sm:text-lg lg:text-xl text-slate-800 dark:text-slate-100 uppercase tracking-wide leading-tight">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="text-[0.7rem] sm:text-xs md:text-sm leading-snug font-medium text-slate-600 dark:text-slate-400 mt-1 sm:mt-0.5">
                    {{ subtitle }}
                </p>
            </div>
        </div>
        
        <slot name="search" />
    </div>
</slot>

                <!-- Main body content under header -->
                <div data-guide="guest-page-content" class="w-full mt-2 sm:mt-0">
                    <slot />
                </div>
                
            </div>
        </div>
    </div>

    <social-links />
</template>