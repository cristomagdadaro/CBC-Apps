<script>
import SocialLinks from "@/Components/SocialLinks.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";

export default {
    name: 'GuestFormPage',
    components: {SocialLinks, MainBg},
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
    },
};
</script>

<template>
    <!-- Background gradient (fixed behind content) -->
    <main-bg></main-bg>

    <!-- Main content overlay -->
    <div class="fixed top-0 left-0 w-full h-full flex justify-center overflow-y-auto">
        <div class="relative sm:flex flex-col md:gap-5 justify-start items-center md:w-fit w-full">
            <div class="md:relative flex flex-col md:gap-5 w-full p-3 md:p-5" :class="maxWidth">
                <!-- Header / search / top content -->
                <slot name="top">
                    <div v-show="delayReady" class="p-0 md:rounded-md flex flex-col gap-2 md:drop-shadow-lg mb-0 w-full">
                        <div class="relative flex flex-row bg-AB text-white p-2 px-4 md:rounded-md gap-2 shadow py-4 w-full">
                            <Link href="/" class="flex-shrink-0">
                                <img src="/imgs/logo.png" alt="logo" class="w-16 h-16" />
                            </Link>
                            <div class="flex flex-col justify-center flex-1 min-w-0">
                                <label class="font-semibold text-base md:text-xl">{{ title }}</label>
                                <p v-if="subtitle" class="text-sm leading-tight">
                                    {{ subtitle }}
                                </p>
                            </div>
                        </div>
                        <slot name="search" />
                    </div>
                </slot>

                <!-- Main body content under header -->
                <slot />
            </div>
        </div>
    </div>

    <social-links />
</template>

<style scoped>
</style>

