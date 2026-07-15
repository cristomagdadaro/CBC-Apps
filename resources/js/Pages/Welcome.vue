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
    <div class="absolute top-0 left-0 w-full pointer-events-none">
        <div class="relative sm:flex justify-center items-center min-h-screen  pointer-events-none">
            <div class="flex flex-col gap-5 px-5 md:px-0 py-10 md:py-0 pointer-events-auto">
                <div
                    data-guide="welcome-hero"
                    class="text-center text-gray-700 dark:text-gray-300"
                >
                    <div class="relative w-fit mx-auto">
                        <div class="flex items-center gap-1">
                            <h1
                                class="lg:text-6xl md:text-4xl text-3xl font-bold leading-none text-lime-500 font-[Montserrat] drop-shadow-md whitespace-nowrap"
                            >
                                {{ $appName }}
                            </h1>
                        </div>
                        <span class="absolute bottom-0 -right-5 text-[0.60rem] text-lime-500">
              {{ $page.props.appVersion }}
            </span>
                    </div>

                    <p class="mt-4 max-w-2xl mx-auto text-gray-50 leading-none">
                        Your gateway to DA-Crop Biotechnology Center's proprietary web apps and
                        services.
                    </p>
                    <blockquote class="mt-2 font-semibold text-gray-50 leading-none">
                        Better Crops, Better Lives
                    </blockquote>
                </div>
                <div class="flex flex-col items-center justify-center gap-6 text-sm w-full">
                    <div class="text-center group cursor-default">
                        <h3
                            class="text-lime-500 text-xl font-bold tracking-wider uppercase"
                        >
                            Apps & Services
                        </h3>
                        <div class="h-1.5 w-16 mt-3 mx-auto rounded-full shadow-lg bg-gray-300 group-hover:w-full group-hover:h-0.5 group-hover:mt-0 group-hover:mb-4 duration-500"></div>
                    </div>
                    <h3 v-if="isAdminUser"
                        class="text-red-500 text-xl font-bold tracking-wider uppercase bg-yellow-500 py-2 px-4 rounded-lg shadow flex flex-col text-center"
                    >
                        <span>Admin View - All Services</span>
                        <span>Bypassed the Module Access Controls</span>
                    </h3>
                    <div data-guide="welcome-services"
                         class="flex flex-wrap justify-center gap-4 w-full max-w-7xl relative">
                        <div class="contents">
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
                                class="min-w-[300px] md:min-w-[200px] w-[20%]"
                            />
                        </div>
                    </div>

                    <!-- Mobile App Downloads -->
                    <div class="mt-8 flex flex-col items-center gap-4 bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 backdrop-blur-sm">
                        <h4 class="text-gray-100 dark:text-gray-400 uppercase tracking-widest text-xs font-semibold">
                            Mobile App Version
                        </h4>
                        
                        <div class="flex flex-wrap justify-center gap-4">
                            <!-- Android Active Button -->
                            <a :href="route('download.android')" 
                            class="group flex items-center gap-4 bg-gray-950 dark:bg-black hover:bg-gray-900 border border-gray-800 dark:border-gray-900 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-md hover:shadow-xl hover:-translate-y-1">
                                <svg class="w-8 h-8 text-lime-500 transition-transform duration-300 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M2.76 3.061a.5.5 0 0 1 .679.2l1.283 2.352A8.9 8.9 0 0 1 8 5a8.9 8.9 0 0 1 3.278.613l1.283-2.352a.5.5 0 1 1 .878.478l-1.252 2.295C14.475 7.266 16 9.477 16 12H0c0-2.523 1.525-4.734 3.813-5.966L2.56 3.74a.5.5 0 0 1 .2-.678ZM5 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m6 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg>
                                <div class="flex flex-col items-start leading-none">
                                    <span class="text-[0.65rem] uppercase text-gray-400 tracking-wider">Get it on</span>
                                    <span class="text-lg font-bold mt-1">Android</span>
                                </div>
                            </a>

                            <!-- iOS Disabled Button -->
                            <button disabled 
                                    class="flex items-center gap-4 bg-gray-100 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800/80 text-gray-400 dark:text-gray-500 px-6 py-3 rounded-xl cursor-not-allowed opacity-60">
                                <svg class="w-8 h-8 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                    <path d="M11.182.008C11.148-.03 9.923.023 8.857 1.18c-1.066 1.156-.902 2.482-.878 2.516s1.52.087 2.475-1.258.762-2.391.728-2.43m3.314 11.733c-.048-.096-2.325-1.234-2.113-3.422s1.675-2.789 1.698-2.854-.597-.79-1.254-1.157a3.7 3.7 0 0 0-1.563-.434c-.108-.003-.483-.095-1.254.116-.508.139-1.653.589-1.968.607-.316.018-1.256-.522-2.267-.665-.647-.125-1.333.131-1.824.328-.49.196-1.422.754-2.074 2.237-.652 1.482-.311 3.83-.067 4.56s.625 1.924 1.273 2.796c.576.984 1.34 1.667 1.659 1.899s1.219.386 1.843.067c.502-.308 1.408-.485 1.766-.472.357.013 1.061.154 1.782.539.571.197 1.111.115 1.652-.105.541-.221 1.324-1.059 2.238-2.758q.52-1.185.473-1.282"/>
                                </svg>
                                <div class="flex flex-col items-start leading-none">
                                    <span class="text-[0.65rem] uppercase tracking-wider text-gray-400 dark:text-gray-500">Coming Soon for</span>
                                    <span class="text-lg font-bold mt-1">iOS</span>
                                </div>
                            </button>
                        </div>

                        <!-- Important Notice Card -->
                        <div class="max-w-md mx-auto flex items-start gap-3">
                            <div class="flex flex-col gap-1 text-left">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-500 flex items-center gap-1">
                                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    Important Notice
                                </span>
                                <p class="text-xs text-gray-200 dark:text-gray-300 leading-relaxed text-justify">
                                    Upon installation, kindly proceed and accept all requested permissions to ensure all native system features work correctly. Please ignore system security warnings; they are standard warnings because the application is built and distributed directly from DA-CBC and not distributed through the official Play Store or App Store.
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-8 max-w-md mx-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                PhilRice Network Detected?
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6">
                Some services are only available from the PhilRice local deployment. If you're
                connected to that network, you can switch now and see the full local-only
                service list.
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
                    <span v-else>Yes, I'm on PhilRice Network</span>
                </button>
            </div>
        </div>
    </div>
</template>
