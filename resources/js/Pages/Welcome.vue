<script setup>
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import { onMounted, ref, computed } from "vue";
import SocialLinks from "@/Components/SocialLinks.vue";
import ServiceCard from "@/Components/ServiceCard.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";
import GuideTourControls from "@/Components/GuideTourControls.vue";
import { useAppContext } from "@/Modules/composables/useAppContext";
import { useGuideTour } from "@/Modules/composables/useGuideTour";

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
const { deploymentAccess, isAdminUser, publicServices } = useAppContext();
const welcomeGuide = useGuideTour("welcome", { autoStart: false });
const localNetworkUrl = computed(() => deploymentAccess.value?.local_url ?? "http://192.168.36.10");

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

    return publicServices.value
        .filter((service) => !service.visibilityKey || allowedServices[service.visibilityKey] !== false)
        .map((service) => ({
            ...service,
            badgeCount: resolveServiceMetric(service.id),
        }));
});

const hasHiddenLocalServices = computed(() => {
    if (isAdminUser.value) {
        return false;
    }

    return publicServices.value.some((service) => service.visibilityKey && deploymentAccess.value?.services?.[service.visibilityKey] === false);
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

    if (isInternetAccess.value && hasHiddenLocalServices.value && !sessionStorage.getItem("declinedLocalNetwork")) {
        setTimeout(() => {
            showNetworkModal.value = true;
        }, 500);
    }
});
</script>

<template>
    <Head title="Welcome" />
    <main-bg />
    <GuideTourControls
        guide-key="welcome"
        :auto-start="false" />

    <div class="pointer-events-none absolute left-0 top-0 min-h-screen w-full overflow-y-auto">
        <div class="pointer-events-none relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 sm:py-14">
            <div class="pointer-events-auto my-auto mt-20 flex w-full max-w-7xl flex-col items-center gap-6 sm:gap-8 md:mt-auto">
                <!-- Hero Section -->
                <div
                    data-guide="welcome-hero"
                    class="px-2 text-center text-gray-800 dark:text-gray-100">
                    <div class="relative mx-auto w-fit">
                        <div class="flex flex-col items-center justify-center gap-2 sm:flex-row">
                            <h1 class="whitespace-nowrap font-[Montserrat] text-4xl font-extrabold leading-none tracking-tight text-lime-400 drop-shadow-md sm:text-5xl md:text-6xl dark:text-lime-400">
                                {{ $appName }}
                            </h1>
                            <span class="mt-2 inline-flex items-center rounded-full border border-lime-500/30 bg-lime-500/20 px-2.5 py-0.5 text-[0.65rem] font-bold text-lime-300 shadow-sm sm:mt-1 sm:self-start sm:text-xs">
                                {{ $page.props.appVersion }}
                            </span>
                        </div>
                    </div>

                    <p class="mx-auto mt-4 max-w-xl text-sm font-medium leading-snug text-slate-100 drop-shadow-md sm:mt-5 sm:text-base dark:text-slate-200">Your gateway to DA-Crop Biotechnology Center's proprietary web apps and services.</p>
                    <blockquote class="mt-2 text-sm font-semibold italic text-lime-300 drop-shadow-sm dark:text-lime-300">"Better Crops, Better Lives"</blockquote>
                </div>

                <!-- Apps & Services Section -->
                <div class="flex w-full flex-col items-center justify-center gap-6">
                    <div class="text-center">
                        <div class="inline-flex items-center gap-2 rounded-full border border-lime-500/30 bg-lime-500/20 px-4 py-1.5 text-xs font-bold uppercase text-lime-100 shadow-md backdrop-blur-sm sm:text-sm dark:bg-lime-400/15 dark:text-lime-300">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-lime-400"></span>
                            Apps & Services
                        </div>
                    </div>

                    <!-- Admin Badge -->
                    <h3
                        v-if="isAdminUser"
                        class="flex flex-col rounded-xl border border-amber-400/40 bg-amber-500/20 px-4 py-2 text-center text-xs font-bold uppercase tracking-wider text-amber-100 shadow-lg backdrop-blur-sm sm:text-sm dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                        <span class="tracking-wide">Admin View - All Services</span>
                        <span class="text-xs font-normal lowercase opacity-90 first-letter:uppercase">Bypassed Module Access Controls</span>
                    </h3>

                    <!-- Services Grid -->
                    <div
                        data-guide="welcome-services"
                        class="relative grid w-full max-w-7xl grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
                        <ServiceCard
                            v-for="(service, index) in visibleServices"
                            :key="service.id || index"
                            :title="service.title"
                            :description="service.description"
                            :icon="service.icon"
                            :href="route(service.routeName)"
                            :badge-count="service.badgeCount"
                            :color="service.color"
                            :external="service.external" />
                    </div>

                    <!-- Mobile App Downloads Section -->
                    <div
                        class="mt-8 flex w-full max-w-2xl flex-col items-center gap-5"
                        data-guide="mobile-app-version">
                        <div class="inline-flex items-center gap-2 rounded-full border border-lime-500/20 bg-lime-900/40 px-4 py-1.5 text-[0.65rem] font-extrabold uppercase text-lime-300 shadow-sm backdrop-blur-md sm:text-xs dark:bg-lime-400/10 dark:text-lime-400">Mobile App Version</div>

                        <!-- Redesigned Glass Panel -->
                        <div class="w-full rounded-3xl border border-white/30 bg-white/20 p-6 shadow-2xl backdrop-blur-sm transition-all duration-300 sm:p-8 dark:border-slate-700/50 dark:bg-slate-900/40">
                            <div class="flex w-full flex-col justify-center gap-4 sm:flex-row">
                                <!-- Android Active Button (Styled like an App Store badge) -->
                                <a
                                    :href="route('download.android')"
                                    class="group relative flex w-full items-center justify-center gap-4 overflow-hidden rounded-xl border border-slate-700 bg-slate-900 px-6 py-3 text-white shadow-xl transition-all duration-300 hover:-translate-y-0.5 hover:border-lime-500/50 hover:bg-slate-800 hover:shadow-lime-500/20 sm:w-auto sm:justify-start">
                                    <div class="absolute inset-0 bg-gradient-to-r from-lime-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                    <svg
                                        class="relative z-10 h-8 w-8 text-lime-400 transition-transform duration-300 group-hover:scale-110"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path d="M2.76 3.061a.5.5 0 0 1 .679.2l1.283 2.352A8.9 8.9 0 0 1 8 5a8.9 8.9 0 0 1 3.278.613l1.283-2.352a.5.5 0 1 1 .878.478l-1.252 2.295C14.475 7.266 16 9.477 16 12H0c0-2.523 1.525-4.734 3.813-5.966L2.56 3.74a.5.5 0 0 1 .2-.678ZM5 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m6 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                                    </svg>
                                    <div class="relative z-10 flex flex-col items-start text-left leading-none">
                                        <span class="text-[0.65rem] font-medium uppercase tracking-wide text-slate-300">Get it on</span>
                                        <span class="mt-1 text-base font-bold tracking-tight text-white sm:text-lg">Android</span>
                                    </div>
                                </a>

                                <!-- iOS Disabled Button (Hidden by default based on previous code, but styled just in case) -->
                                <button
                                    disabled
                                    class="flex hidden w-full cursor-not-allowed items-center justify-center gap-4 rounded-xl border border-gray-200/50 bg-gray-100/50 px-6 py-3 text-gray-600 opacity-70 sm:w-auto sm:justify-start dark:border-slate-800/50 dark:bg-slate-900/50 dark:text-slate-500">
                                    <svg
                                        class="h-8 w-8 fill-current"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 16 16">
                                        <path d="M11.182.008C11.148-.03 9.923.023 8.857 1.18c-1.066 1.156-.902 2.482-.878 2.516s1.52.087 2.475-1.258.762-2.391.728-2.43m3.314 11.733c-.048-.096-2.325-1.234-2.113-3.422s1.675-2.789 1.698-2.854-.597-.79-1.254-1.157a3.7 3.7 0 0 0-1.563-.434c-.108-.003-.483-.095-1.254.116-.508.139-1.653.589-1.968.607-.316.018-1.256-.522-2.267-.665-.647-.125-1.333.131-1.824.328-.49.196-1.422.754-2.074 2.237-.652 1.482-.311 3.83-.067 4.56s.625 1.924 1.273 2.796c.576.984 1.34 1.667 1.659 1.899s1.219.386 1.843.067c.502-.308 1.408-.485 1.766-.472.357.013 1.061.154 1.782.539.571.197 1.111.115 1.652-.105.541-.221 1.324-1.059 2.238-2.758q.52-1.185.473-1.282" />
                                    </svg>
                                    <div class="flex flex-col items-start text-left leading-none">
                                        <span class="text-[0.65rem] font-medium uppercase tracking-wide">Coming to</span>
                                        <span class="mt-1 text-base font-bold tracking-tight sm:text-lg">iOS</span>
                                    </div>
                                </button>
                            </div>

                            <!-- Important Notice Card -->
                            <div class="mx-auto mt-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-100/80 p-4 text-left shadow-sm sm:p-5 dark:border-amber-700/50 dark:bg-amber-900/30">
                                <div class="flex w-full flex-col gap-1.5">
                                    <span class="flex items-center gap-1.5 text-[0.7rem] font-bold uppercase tracking-wider text-amber-700 sm:text-xs dark:text-amber-400">
                                        <svg
                                            class="h-4 w-4 shrink-0"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="10"></circle>
                                            <line
                                                x1="12"
                                                y1="8"
                                                x2="12"
                                                y2="12"></line>
                                            <line
                                                x1="12"
                                                y1="16"
                                                x2="12.01"
                                                y2="16"></line>
                                        </svg>
                                        Important Notice
                                    </span>
                                    <p class="text-justify text-[0.75rem] leading-relaxed text-amber-900/80 sm:text-sm dark:text-amber-100/70">Upon installation, kindly proceed and accept all requested permissions to ensure all native system features work correctly. Please ignore system security warnings; they are standard warnings because the application is built and distributed directly by DA-CBC and not distributed through the official Play Store or App Store.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <social-links />

    <!-- Modal remains unchanged -->
    <div
        v-if="showNetworkModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl sm:p-8 dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">PhilRice Network Detected?</h3>
            <p class="mb-6 text-sm leading-relaxed text-gray-600 dark:text-slate-300">Some services are only available from the PhilRice local deployment. If you're connected to that network, you can switch now and see the full local-only service list.</p>
            <div class="flex flex-col-reverse gap-3 sm:flex-row">
                <button
                    @click="dismissNetworkModal"
                    :disabled="isCheckingNetwork"
                    class="w-full rounded-xl border border-transparent bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50 sm:flex-1 sm:py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    No, Continue Online
                </button>
                <button
                    @click="testNetworkAndRedirect"
                    :disabled="isCheckingNetwork"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-lime-600 px-4 py-3 text-sm font-bold text-white shadow-md shadow-lime-600/20 transition-colors hover:bg-lime-700 disabled:cursor-not-allowed disabled:opacity-50 sm:flex-1 sm:py-2.5 dark:bg-lime-600 dark:hover:bg-lime-500">
                    <span v-if="isCheckingNetwork">
                        <svg
                            class="h-5 w-5 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24">
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span v-else>Yes, PhilRice Network</span>
                </button>
            </div>
        </div>
    </div>
</template>
