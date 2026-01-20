<script setup>
import { Head, Link } from '@inertiajs/vue3';
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import {onMounted} from "vue";
import SocialLinks from "@/Components/SocialLinks.vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

onMounted(() => {
    particleMixin.methods.createFallingLogos();
})
</script>


<template>
    <Head title="Welcome" />
    <div class="min-h-screen flex items-center justify-center text-white text-3xl fixed top-0 left-0 font-bold relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-radial dark:bg-gradient-radial animate-gradient"></div>
    </div>
    <div class="absolute top-0 left-0 w-full select-none">
        <div class="relative sm:flex justify-center items-center min-h-screen">
            <div class="sm:fixed sm:top-0 sm:end-0 p-6 text-end z-10">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Dashboard
                </Link>
            </div>
            <!-- Falling Logos Container -->
<!--            <div id="falling-logos"></div>-->

            <div class="flex flex-col gap-5 px-5">
                <div class="text-center text-gray-700 dark:text-gray-300">
                    <div class="relative w-fit mx-auto">
                        <div class="flex items-center gap-1">
                            <h1 class="lg:text-6xl md:text-4xl text-3xl font-bold leading-none text-AB dark:text-green-400 font-[Montserrat] drop-shadow-md whitespace-nowrap">
                                {{ $appName }}
                            </h1>
                        </div>
                        <span class="absolute bottom-0 -right-5 text-[0.60rem] text-AB">
                            {{ $page.props.appVersion }}
                        </span>
                    </div>

                    <p class="mt-4 max-w-2xl mx-auto text-gray-300 leading-none">
                        Your gateway to DA-Crop Biotechnology Center's proprietary web apps and services.
                    </p>
                    <blockquote class="mt-2 font-semibold text-gray-300 leading-none">Better Crops, Better Lives</blockquote>
                </div>
                <div class="flex flex-col items-center justify-center gap-2 text-sm w-full">
                    <h3 class="dark:text-white opacity-25 leading-none select-none">Apps & Services</h3>
                    <div class="flex flex-col md:flex-row items-center justify-center w-full drop-shadow-md">
                        <Link :href="route('forms.guest.index')" class="flex items-center gap-1 dark:text-green-400 hover:text-white p-2 text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 md:border-r">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-event" viewBox="0 0 16 16">
                                <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                            </svg>
                            Event Forms
                        </Link>
                        <Link :href="route('labReq.guest.index')" class="flex items-center gap-1 dark:text-green-400 hover:text-white p-2 text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 md:border-r">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks" viewBox="0 0 16 16">
                                <path d="M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 .708-.708l.646.647 1.646-1.647a.5.5 0 0 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                            </svg>
                            Access and Use Request Form
                        </Link>
                        <Link :href="route('inventory.public.outgoing.index')" class="flex items-center gap-1 dark:text-green-400 hover:text-white p-2 text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-boxes" viewBox="0 0 16 16">
                                <path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571zm1 3.134 2.75 1.571v-3.134L8.5 9.933zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567zm2.242-2.433V3.504L8.5 5.076V8.21zM7.5 8.21V5.076L4.75 3.504v3.134zM5.258 2.643 8 4.21l2.742-1.567L8 1.076zM15 9.933l-2.75 1.571v3.134L15 13.067zM3.75 14.638v-3.134L1 9.933v3.134z"/>
                            </svg>
                            Outgoing Inventory Form
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <social-links />
</template>

