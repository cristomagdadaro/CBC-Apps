<script setup>
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import {onMounted, ref, computed} from "vue";
import SocialLinks from "@/Components/SocialLinks.vue";
import ServiceCard from "@/Components/ServiceCard.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";
import GuideTourControls from "@/Components/GuideTourControls.vue";
import {useAppContext} from "@/Modules/composables/useAppContext";
import {useGuideTour} from "@/Modules/composables/useGuideTour";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    serviceMetrics: {
        type: Object,
        default: () => ({}),
    },
});

const showNetworkModal = ref(false);
const showPrivacyNotice = ref(false);
const isCheckingNetwork = ref(false);
const {deploymentAccess, isAdminUser, publicServices} = useAppContext();
const welcomeGuide = useGuideTour("welcome", {autoStart: false});
const localNetworkUrl = computed(
    () => deploymentAccess.value?.local_url ?? "http://192.168.36.10",
);

const isInternetAccess = computed(() => {
    return deploymentAccess.value?.channel === "internet";
});

const visibleServices = computed(() => {
    const allowedServices = deploymentAccess.value?.services ?? {};

    if (isAdminUser.value) {
        return publicServices.value.map((service) => ({
            ...service,
            badgeCount: resolveServiceMetric(service.id),
        }));
    }

    return publicServices.value.filter(
        (service) =>
            !service.visibilityKey || allowedServices[service.visibilityKey] !== false,
    ).map((service) => ({
        ...service,
        badgeCount: resolveServiceMetric(service.id),
    }));
});

const hasHiddenLocalServices = computed(() => {
    if (isAdminUser.value) {
        return false;
    }

    return publicServices.value.some(
        (service) =>
            service.visibilityKey &&
            deploymentAccess.value?.services?.[service.visibilityKey] === false,
    );
});

const testNetworkAndRedirect = async () => {
    isCheckingNetwork.value = true;
    window.location.href = localNetworkUrl.value;
};

const dismissNetworkModal = () => {
    showNetworkModal.value = false;
    // Remember user's choice for this session
    sessionStorage.setItem("declinedLocalNetwork", "true");
};

const resolveServiceMetric = (serviceId) => {
    const metrics = props.serviceMetrics ?? {};

    if (!Object.prototype.hasOwnProperty.call(metrics, serviceId)) {
        return null;
    }

    return metrics[serviceId];
};

const agreeToPrivacyNotice = async () => {
    showPrivacyNotice.value = false;
    welcomeGuide.setPrivacyConsent(true);

    if (!welcomeGuide.hasSeenGuide("welcome")) {
        await welcomeGuide.startGuide("welcome");
    }
};

onMounted(() => {
    particleMixin.methods.createFallingLogos();

    if (!welcomeGuide.hasPrivacyConsent()) {
        showPrivacyNotice.value = true;
    } else if (!welcomeGuide.hasSeenGuide("welcome")) {
        welcomeGuide.maybeStartGuide();
    }

    if (
        isInternetAccess.value &&
        hasHiddenLocalServices.value &&
        !sessionStorage.getItem("declinedLocalNetwork")
    ) {
        setTimeout(() => {
            showNetworkModal.value = true;
        }, 500);
    }
});
</script>

<template>
    <Head title="Welcome"/>
    <main-bg/>
    <GuideTourControls guide-key="welcome" :auto-start="false"/>
    <div class="absolute top-0 left-0 w-full min-h-screen pointer-events-none overflow-y-auto">
        <div class="relative flex justify-center items-center min-h-screen pointer-events-none py-6 sm:py-10 md:py-14 px-3 sm:px-6">
            <div class="flex flex-col items-center gap-4 sm:gap-7 w-full max-w-7xl pointer-events-auto my-auto">
                
                <!-- Hero Section -->
                <div
                    data-guide="welcome-hero"
                    class="text-center text-gray-800 dark:text-gray-100 px-2"
                >
                    <div class="relative w-fit mx-auto">
                        <div class="flex items-center justify-center gap-2">
                            <h1
                                class="text-3xl sm:text-5xl md:text-6xl font-extrabold leading-none text-lime-400 dark:text-lime-400 font-[Montserrat] tracking-tight whitespace-nowrap"
                            >
                                {{ $appName }}
                            </h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-lime-500/20 text-lime-300 border border-lime-500/30 text-[0.65rem] font-bold shadow-sm self-start mt-1">
                                {{ $page.props.appVersion }}
                            </span>
                        </div>
                    </div>

                    <p class="mt-2.5 sm:mt-3.5 max-w-xl mx-auto text-xs sm:text-base font-medium text-slate-100 dark:text-slate-200 leading-snug drop-shadow-sm">
                        Your gateway to DA-Crop Biotechnology Center's proprietary web apps and services.
                    </p>
                    <blockquote class="mt-1.5 sm:mt-2 text-xs sm:text-sm font-semibold italic text-lime-300 dark:text-lime-300 drop-shadow-sm">
                        "Better Crops, Better Lives"
                    </blockquote>
                </div>

                <!-- Apps & Services Section -->
                <div class="flex flex-col items-center justify-center gap-4 sm:gap-6 w-full">
                    <div class="text-center">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-lime-500/10 dark:bg-lime-400/15 border border-lime-500/30 text-lime-300 dark:text-lime-300 font-extrabold text-xs sm:text-sm uppercase tracking-widest shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span>
                            Apps & Services
                        </div>
                    </div>

                    <h3 v-if="isAdminUser"
                        class="text-red-500 dark:text-red-400 text-xs sm:text-sm font-bold tracking-wider uppercase bg-amber-400/90 dark:bg-amber-500/90 py-1.5 sm:py-2 px-3 sm:px-4 rounded-xl shadow-md flex flex-col text-center border border-amber-300/50"
                    >
                        <span>Admin View - All Services</span>
                        <span class="text-[0.68rem] sm:text-xs opacity-90 font-normal">Bypassed Module Access Controls</span>
                    </h3>
                    
                    <!-- Services Grid: 1 col mobile, 2 col tablet, 4 col desktop -->
                    <div data-guide="welcome-services"
                         class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4.5 w-full max-w-7xl relative px-1 sm:px-0">
                        <ServiceCard
                            v-for="(service, index) in visibleServices"
                            :key="service.id || index"
                            :title="service.title"
                            :description="service.description"
                            :icon="service.icon"
                            :href="route(service.routeName)"
                            :badge-count="service.badgeCount"
                            :color="service.color"
                            :external="service.external"
                        />
                    </div>

                    <!-- Mobile App Downloads Section -->
                    <div class="mt-4 sm:mt-8 flex flex-col items-center gap-4 sm:gap-5 w-full max-w-2xl px-2 sm:px-0" data-guide='mobile-app-version'>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-lime-500/10 dark:bg-lime-400/15 border border-lime-500/30 text-lime-400 dark:text-lime-400 font-extrabold text-[0.68rem] sm:text-xs uppercase tracking-widest shadow-sm">
                            Mobile App Version
                        </div>
                        
                        <div class="flex flex-wrap justify-center gap-3 w-full bg-white/50 dark:bg-slate-900/50 backdrop-blur-md border border-gray-200 dark:border-slate-800 rounded-2xl p-4 sm:p-6 shadow-lg sm:shadow-xl transition-all duration-300 hover:border-lime-500/50 dark:hover:border-lime-400/50 hover:shadow-lime-500/10">
                            <!-- Android Active Button -->
                            <a :href="route('download.android')" 
                               class="group relative flex items-center gap-3.5 bg-slate-900 dark:bg-black hover:bg-slate-800 dark:hover:bg-slate-900 border border-slate-700 text-white px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl transition-all duration-300 shadow-md hover:shadow-lime-500/20 hover:border-lime-500/50 hover:-translate-y-0.5 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-lime-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-lime-400 transition-transform duration-300 group-hover:scale-110 relative z-10" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M2.76 3.061a.5.5 0 0 1 .679.2l1.283 2.352A8.9 8.9 0 0 1 8 5a8.9 8.9 0 0 1 3.278.613l1.283-2.352a.5.5 0 1 1 .878.478l-1.252 2.295C14.475 7.266 16 9.477 16 12H0c0-2.523 1.525-4.734 3.813-5.966L2.56 3.74a.5.5 0 0 1 .2-.678ZM5 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m6 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg>
                                <div class="flex flex-col items-start leading-none relative z-10">
                                    <span class="text-[0.6rem] sm:text-[0.65rem] uppercase text-slate-300 tracking-wider">Get it on</span>
                                    <span class="text-sm sm:text-base font-bold mt-1 text-white">Android</span>
                                </div>
                            </a>

                            <!-- iOS Disabled Button -->
                            <button disabled 
                                    class="flex items-center gap-3.5 bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-gray-500 dark:text-slate-500 px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl cursor-not-allowed opacity-70">
                                <svg class="w-7 h-7 sm:w-8 sm:h-8 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                    <path d="M11.182.008C11.148-.03 9.923.023 8.857 1.18c-1.066 1.156-.902 2.482-.878 2.516s1.52.087 2.475-1.258.762-2.391.728-2.43m3.314 11.733c-.048-.096-2.325-1.234-2.113-3.422s1.675-2.789 1.698-2.854-.597-.79-1.254-1.157a3.7 3.7 0 0 0-1.563-.434c-.108-.003-.483-.095-1.254.116-.508.139-1.653.589-1.968.607-.316.018-1.256-.522-2.267-.665-.647-.125-1.333.131-1.824.328-.49.196-1.422.754-2.074 2.237-.652 1.482-.311 3.83-.067 4.56s.625 1.924 1.273 2.796c.576.984 1.34 1.667 1.659 1.899s1.219.386 1.843.067c.502-.308 1.408-.485 1.766-.472.357.013 1.061.154 1.782.539.571.197 1.111.115 1.652-.105.541-.221 1.324-1.059 2.238-2.758q.52-1.185.473-1.282"/>
                                </svg>
                                <div class="flex flex-col items-start leading-none">
                                    <span class="text-[0.6rem] sm:text-[0.65rem] uppercase tracking-wider">Coming to</span>
                                    <span class="text-sm sm:text-base font-bold mt-1">iOS</span>
                                </div>
                            </button>
                        </div>

                        <!-- Important Notice Card -->
                        <div class="mx-auto bg-amber-500/30 border border-amber-500/20 rounded-xl p-3 sm:p-4 text-left flex items-start gap-3 mt-1">
                            
                            <div class="flex flex-col gap-1">
                                
                                <span class="flex items-center gap-1 text-[0.68rem] sm:text-xs font-extrabold uppercase tracking-wider text-amber-300">
                                    <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    Important Notice
                                </span>
                                <p class="text-[0.7rem] sm:text-xs text-slate-200 dark:text-slate-300 leading-relaxed text-justify">
                                    Upon installation, kindly proceed and accept all requested permissions to ensure all native system features work correctly. Please ignore system security warnings; they are standard warnings because the application is built and distributed directly by DA-CBC and not distributed through the official Play Store or App Store.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <social-links/>

    <!-- PhilRice Network Detection Modal -->
    <div
        v-if="showNetworkModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4"
    >
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl shadow-2xl p-5 sm:p-8 max-w-md w-full">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2 sm:mb-3">
                PhilRice Network Detected?
            </h3>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-slate-300 mb-5 sm:mb-6 leading-relaxed">
                Some services are only available from the PhilRice local deployment. If you're
                connected to that network, you can switch now and see the full local-only
                service list.
            </p>
            <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3">
                <button
                    @click="dismissNetworkModal"
                    :disabled="isCheckingNetwork"
                    class="w-full sm:flex-1 px-4 py-2.5 text-xs sm:text-sm font-semibold text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-xl hover:bg-gray-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    No, Continue Online
                </button>
                <button
                    @click="testNetworkAndRedirect"
                    :disabled="isCheckingNetwork"
                    class="w-full sm:flex-1 px-4 py-2.5 text-xs sm:text-sm bg-lime-600 dark:bg-lime-600 text-white font-bold rounded-xl hover:bg-lime-700 dark:hover:bg-lime-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2 shadow-md shadow-lime-600/20"
                >
                    <span v-if="isCheckingNetwork">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                    </span>
                    <span v-else>Yes, PhilRice Network</span>
                </button>
            </div>
        </div>
    </div>
</template>
