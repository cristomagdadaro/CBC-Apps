<script setup>
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import { onMounted, ref, computed } from "vue";
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

const showNetworkModal = ref(false);
const isCheckingNetwork = ref(false);
const localNetworkUrl = "http://192.168.36.10";

const isInternetAccess = computed(() => {
    // Check if current URL is the internet deployment
    return window.location.origin.includes("dacbc.philrice.gov.ph");
});

const services = [
    {
        title: "Center Calendar",
        description: "View the center's unified calendar of events, bookings, and equipment logging schedules",
        icon: CalendarIcon,
        href: route("forms.guest.index"),
    },
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
        title: "ICT Equipment Logger",
        description: "Access and track ICT equipment availability and usage logs",
        href: route("ict.equipments.show"),
        icon: BookmarkIcon,
    },
    {
        title: "Supplies Checkout",
        description: "Check out and track supplies and equipment from our inventory",
        href: route("inventory.public.outgoing.index"),
        icon: BoxesIcon,
    },
    {
        title: "Vehicle Rental",
        description: "Reserve and book available vehicles for your research activities",
        href: route("rental.vehicle.guest"),
        icon: TruckIcon,
        external: true,
    },
    {
        title: "Venue Rental",
        description: "Reserve meeting rooms and event spaces for your activities",
        href: route("rental.venue.guest"),
        icon: BuildingIcon,
        external: true,
    },
    {
        title: "File a Report",
        description: "Report incidents, maintenance issues, or equipment damage",
        href: route("suppEquipReports.create.guest"),
        icon: FlagIcon,
    },
    {
        title: "Experiment Monitoring",
        description: "Monitor and track ongoing experiments in the laboratory",
        href: route("laboratory.monitoring.guest"),
        icon: FlagIcon,
    },
];

const testNetworkAndRedirect = async () => {
    isCheckingNetwork.value = true;
    try {
        // Call backend endpoint to test local network connectivity
        const response = await fetch(route("api.test-local-network"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        const data = await response.json();

        if (data.isReachable) {
            // Local network is reachable, redirect
            window.location.href = localNetworkUrl;
        } else {
            // Local network not reachable
            isCheckingNetwork.value = false;
            showNetworkModal.value = false;
        }
    } catch (error) {
        // Error testing network, dismiss modal and stay on internet version
        isCheckingNetwork.value = false;
        showNetworkModal.value = false;
    }
};

const dismissNetworkModal = () => {
    showNetworkModal.value = false;
    // Remember user's choice for this session
    sessionStorage.setItem("declinedLocalNetwork", "true");
};

onMounted(() => {
    particleMixin.methods.createFallingLogos();

    // Show network modal only if:
    // 1. Accessed from internet URL
    // 2. Not already declined in this session
    if (isInternetAccess.value && !sessionStorage.getItem("declinedLocalNetwork")) {
        // Small delay to let page fully load first
        setTimeout(() => {
            showNetworkModal.value = true;
        }, 500);
    }
});
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
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 w-full max-w-7xl">
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

    <!-- PhilRice Network Detection Modal -->
    <div v-if="showNetworkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-8 max-w-md mx-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                PhilRice Network Detected?
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6">
                We detected you're accessing from the internet. If you're connected to the PhilRice network, we can redirect you to the faster local deployment for better performance.
            </p>
            <div class="flex gap-3">
                <button
                    @click="dismissNetworkModal"
                    :disabled="isCheckingNetwork"
                    class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    No, Continue Online
                </button>
                <button
                    @click="testNetworkAndRedirect"
                    :disabled="isCheckingNetwork"
                    class="flex-1 px-4 py-2 bg-AB dark:bg-green-600 text-white rounded-lg hover:bg-opacity-90 dark:hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
                >
                    <span v-if="isCheckingNetwork">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span v-else>Yes, I'm on PhilRice Network</span>
                </button>
            </div>
        </div>
    </div>
</template>

