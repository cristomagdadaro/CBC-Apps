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
    <div class="absolute top-0 left-0 w-full ">
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
                    <h3 class="dark:text-white opacity-25 leading-none ">Apps & Services</h3>
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
                            FES Request Form
                        </Link>
                        <Link :href="route('suppEquipReports.create.guest')" class="flex items-center gap-1 dark:text-green-400 hover:text-white p-2 text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 md:border-r">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-flag-fill" viewBox="0 0 16 16">
                                <path d="M14.778.085A.5.5 0 0 1 15 .5V8a.5.5 0 0 1-.314.464L14.5 8l.186.464-.003.001-.006.003-.023.009a12 12 0 0 1-.397.15c-.264.095-.631.223-1.047.35-.816.252-1.879.523-2.71.523-.847 0-1.548-.28-2.158-.525l-.028-.01C7.68 8.71 7.14 8.5 6.5 8.5c-.7 0-1.638.23-2.437.477A20 20 0 0 0 3 9.342V15.5a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 1 0v.282c.226-.079.496-.17.79-.26C4.606.272 5.67 0 6.5 0c.84 0 1.524.277 2.121.519l.043.018C9.286.788 9.828 1 10.5 1c.7 0 1.638-.23 2.437-.477a20 20 0 0 0 1.349-.476l.019-.007.004-.002h.001"/>
                            </svg>
                            File a Report
                        </Link>
                        <Link :href="route('inventory.public.outgoing.index')" class="flex items-center gap-1 dark:text-green-400 hover:text-white p-2 text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 md:border-r">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-boxes" viewBox="0 0 16 16">
                                <path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571zm1 3.134 2.75 1.571v-3.134L8.5 9.933zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567zm2.242-2.433V3.504L8.5 5.076V8.21zM7.5 8.21V5.076L4.75 3.504v3.134zM5.258 2.643 8 4.21l2.742-1.567L8 1.076zM15 9.933l-2.75 1.571v3.134L15 13.067zM3.75 14.638v-3.134L1 9.933v3.134z"/>
                            </svg>
                            Outgoing Inventory Form
                        </Link>
                        <Link href="/rental/vehicle" class="flex items-center gap-1 dark:text-green-400 hover:text-white p-2 text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 md:border-r">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.885V10.5a1.5 1.5 0 0 1-1.5 1.5H10a2 2 0 1 1-4 0H4a2 2 0 1 1-3.998-.084A1.487 1.487 0 0 1 0 10.5zm1.068 2.185L2.6 4h2.4v5H1.068zM4 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm6 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                            </svg>
                            Vehicle Rental
                        </Link>
                        <Link href="/rental/venue" class="flex items-center gap-1 dark:text-green-400 hover:text-white p-2 text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building" viewBox="0 0 16 16">
                                <path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm.5 3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm2.5 0a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                            </svg>
                            Venue Rental
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <social-links />
</template>

