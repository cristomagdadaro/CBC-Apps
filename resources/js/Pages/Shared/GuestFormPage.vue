<script>
import SocialLinks from "@/Components/SocialLinks.vue";
import GuideTourControls from "@/Components/GuideTourControls.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";

export default {
    name: 'GuestFormPage',
    components: {GuideTourControls, SocialLinks, MainBg},
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
    <div id="main-content-overlay" class="flex justify-center m-0 md:m-5 overflow-visible">
        <div class="relative sm:flex flex-col md:gap-5 justify-start items-center w-full h-full overflow-visible pointer-events-none">
            <div class="md:relative flex flex-col md:gap-5 w-full overflow-visible pointer-events-auto" :class="maxWidth">
                <!-- Header / search / top content -->
                <slot name="top">
                    <div v-show="delayReady" class="p-0 md:rounded-md flex flex-col gap-2 md:drop-shadow-lg mb-0 w-full">
                        <div data-guide="guest-page-header" class="relative flex flex-row bg-AB text-white p-2 px-4 md:rounded-md gap-2 shadow py-4 w-full items-center">
                            <button @click="goBack" class="md:hidden flex-shrink-0 flex items-center justify-center p-1.5 -ml-1 mr-1 text-white/90 hover:text-white rounded-md hover:bg-white/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <Link href="/" class="flex-shrink-0">
                                <img src="/imgs/logo.png" alt="logo" class="w-12 h-12 md:w-16 md:h-16" />
                            </Link>
                            <div class="flex flex-col justify-center flex-1 min-w-0">
                                <label class="font-semibold text-base sm:text-lg uppercase">{{ title }}</label>
                                <p v-if="subtitle" class="text-sm leading-tight font-light sm:font-normal">
                                    {{ subtitle }}
                                </p>
                            </div>
                        </div>
                        <slot name="search" />
                    </div>
                </slot>

                <!-- Main body content under header -->
                <div data-guide="guest-page-content">
                    <slot />
                </div>
            </div>
        </div>
    </div>

    <social-links />
</template>

<style scoped>
</style>
