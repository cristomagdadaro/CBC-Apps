<script>
import SocialLinks from "@/Components/SocialLinks.vue";

export default {
    name: 'GuestFormPage',
    components: {SocialLinks},
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
    },
};
</script>

<template>
    <!-- Background gradient (fixed behind content) -->
    <div class="fixed top-0 left-0 w-full h-full z-0 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-radial animate-gradient"></div>
    </div>

    <!-- Main content overlay -->
    <div class="fixed top-0 left-0 w-full h-full z-50 flex justify-center overflow-y-auto">
        <div class="relative sm:flex flex-col md:gap-5 justify-start items-center w-full md:w-fit mt-0 md:mt-[5%] pb-8">
            <div class="md:relative flex flex-col md:gap-5 w-full max-w-6xl" :class="{ 'justify-center': !!$slots.search }">
                <!-- Header / search / top content -->
                <slot name="top">
                    <div v-show="delayReady" class="p-0 md:rounded-md flex flex-col gap-2 w-full md:drop-shadow-lg mb-0">
                        <div class="relative flex flex-row bg-AB text-white p-2 px-4 md:rounded-md gap-2 shadow py-4">
                            <Link href="/">
                                <img src="/imgs/logo.png" alt="logo" class="w-16 h-16" />
                            </Link>
                            <div class="flex flex-col justify-center">
                                <label class="font-semibold text-base md:text-xl">{{ title }}</label>
                                <p v-if="subtitle" class="text-sm leading-tight">
                                    {{ subtitle }}
                                </p>
                            </div>
                        </div>
                        <slot name="search" />
                    </div>
                </slot>
            </div>

            <!-- Main body content under header -->
            <slot />
        </div>
    </div>

    <social-links />
</template>

<style scoped>
</style>

