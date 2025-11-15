<script>
export default {
    name: 'GuestFormPage',
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
    <div class="absolute top-0 left-0 w-full h-full z-[999] flex justify-center">
        <div class="relative sm:flex flex-col gap-5 sm:justify-center sm:items-center min-h-screen w-fit">
            <div class="md:relative flex flex-col lg:gap-5 w-full" :class="{ 'justify-center': !!$slots.search }">
                <!-- Header / search / top content -->
                <slot name="top">
                    <div v-show="delayReady" class="border select-none p-2 md:rounded-md flex flex-col gap-2 bg-gray-100 w-full drop-shadow-lg">
                        <div class="relative flex flex-row bg-AB text-white p-2 px-4 rounded-md gap-2 shadow py-4">
                            <img src="/imgs/logo.png" alt="logo" class="w-auto h-16" />
                            <div class="flex flex-col justify-center">
                                <label class="font-semibold text-xl">{{ title }}</label>
                                <p v-if="subtitle" class="text-sm">
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

    <!-- Background gradient -->
    <div class="min-h-[100vh] max-h-screen flex items-center justify-center text-white text-3xl font-bold relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-radial animate-gradient"></div>
    </div>
</template>

<style scoped>
</style>

