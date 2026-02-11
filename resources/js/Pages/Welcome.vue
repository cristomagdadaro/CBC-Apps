<script setup>
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import { onMounted } from "vue";
import SocialLinks from "@/Components/SocialLinks.vue";
import ServiceCard from "@/Components/ServiceCard.vue";
import CalendarIcon from "@/Components/Icons/CalendarIcon.vue";
import FesIcon from "@/Components/Icons/FesIcon.vue";
import BookmarkIcon from "@/Components/Icons/BookmarkIcon.vue";
import FlagIcon from "@/Components/Icons/FlagIcon.vue";
import BoxesIcon from "@/Components/Icons/BoxesIcon.vue";
import TruckIcon from "@/Components/Icons/TruckIcon.vue";
import BuildingIcon from "@/Components/Icons/BuildingIcon.vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const services = [
    {
        title: "Event Forms",
        description: "Register and participate in DA-CBC events with comprehensive event forms",
        icon: CalendarIcon,
        href: route("forms.guest.index"),
    },
    {
        title: "FES Request Form",
        description: "Facility, Equipment, and Supplies Request Form (Borrower's Slip)",
        href: route("labReq.guest.index"),
        icon: FesIcon,
    },
    {
        title: "Lab Equipment Logger",
        description: "Access and track laboratory equipment availability and usage logs",
        href: route("laboratory.equipments.show"),
        icon: BookmarkIcon,
    },
    {
        title: "Inventory Checkout",
        description: "Check out and track supplies and equipment from our inventory",
        href: route("inventory.public.outgoing.index"),
        icon: BoxesIcon,
    },
    {
        title: "Vehicle Rental",
        description: "Reserve and book available vehicles for your research activities",
        href: "/rental/vehicle",
        icon: TruckIcon,
        external: true,
    },
    {
        title: "Venue Rental",
        description: "Reserve meeting rooms and event spaces for your activities",
        href: "/rental/venue",
        icon: BuildingIcon,
        external: true,
    },
    {
        title: "File a Report",
        description: "Report incidents, maintenance issues, or equipment damage",
        href: route("suppEquipReports.create.guest"),
        icon: FlagIcon,
    },
];

onMounted(() => {
    particleMixin.methods.createFallingLogos();
})
</script>


<template>
    <Head title="Welcome" />
    <div class="fixed top-0 left-0 w-full h-full z-0 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-radial animate-gradient"></div>
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
                <div class="flex flex-col items-center justify-center gap-6 text-sm w-full">
                    <div class="text-center">
                        <h3 class="text-gray-900 dark:text-white text-xl font-bold tracking-wider uppercase">Apps & Services</h3>
                        <div class="h-1.5 w-16 bg-gradient-to-r from-AC via-AB to-AA dark:from-AA dark:via-AD dark:to-AB mt-3 mx-auto rounded-full shadow-lg dark:shadow-AC/30"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 w-full max-w-7xl">
                        <ServiceCard
                            v-for="(service, index) in services"
                            :key="index"
                            :title="service.title"
                            :description="service.description"
                            :icon="service.icon"
                            :href="service.href"
                            :external="service.external"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <social-links />
</template>

