<script setup>
import { Head, Link } from '@inertiajs/vue3';
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import {onMounted} from "vue";
import BaseBtn from "@/Components/Buttons/BaseBtn.vue";

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
            <div v-if="canLogin" class="sm:fixed sm:top-0 sm:end-0 p-6 text-end z-10">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Log in
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="ms-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Register
                    </Link>
                </template>
            </div>
            <!-- Falling Logos Container -->
<!--            <div id="falling-logos"></div>-->

            <div class="flex flex-col gap-5 px-5">
                <div class="text-center text-gray-700 dark:text-gray-300">
                    <div class="relative w-fit mx-auto">
                        <h1 class="lg:text-6xl md:text-4xl text-3xl font-bold leading-none text-AB dark:text-green-400 font-[Montserrat] drop-shadow-md whitespace-nowrap">
                            {{ $appName }}
                        </h1>
                        <span class="absolute bottom-0 -right-5 text-[0.60rem] text-AB">
                        v1.12
                    </span>
                    </div>

                    <p class="mt-4 max-w-2xl mx-auto text-AB leading-none">
                        Your gateway to DA-Crop Biotechnology Center's proprietary web apps and services.
                    </p>
                    <blockquote class="mt-2 font-semibold text-AB leading-none">Better Crops, Better Lives</blockquote>
                </div>
                <div class="flex flex-col items-center justify-center gap-2 text-sm w-full">
                    <h3 class="dark:text-white opacity-25 leading-none select-none">Apps & Services</h3>
                    <div class="flex items-center justify-center w-full drop-shadow-md">
                        <Link :href="route('forms.guest.index')" class="dark:text-green-400 hover:text-white text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 border-r">
                            Events
                        </Link>
                        <Link :href="route('forms.guest.index')" class="dark:text-green-400 hover:text-white text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 border-r">
                            Booking
                        </Link>
                        <Link :href="route('forms.guest.index')" class="dark:text-green-400 hover:text-white text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 border-r">
                            Games
                        </Link>
                        <Link :href="route('labReq.guest.index')" class="dark:text-green-400 hover:text-white text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4 border-r">
                            Laboratory Request
                        </Link>
                        <Link :href="route('forms.guest.index')" class="dark:text-green-400 hover:text-white text-AB hover:bg-green-600 duration-200 dark:hover:text-AA md:text-lg text-normal px-4">
                            Inventory
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

