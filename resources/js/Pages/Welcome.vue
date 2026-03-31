<script setup>
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import { onMounted, ref, computed } from "vue";
import SocialLinks from "@/Components/SocialLinks.vue";
import ServiceCard from "@/Components/ServiceCard.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";
import GuideTourControls from "@/Components/GuideTourControls.vue";
import { useAppContext } from "@/Modules/composables/useAppContext";
import { useGuideTour } from "@/Modules/composables/useGuideTour";

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
});

const showNetworkModal = ref(false);
const showPrivacyNotice = ref(false);
const isCheckingNetwork = ref(false);
const { deploymentAccess, isAdminUser, publicServices } = useAppContext();
const welcomeGuide = useGuideTour("welcome", { autoStart: false });
const localNetworkUrl = computed(
  () => deploymentAccess.value?.local_url ?? "http://192.168.36.10",
);

const isInternetAccess = computed(() => {
  return deploymentAccess.value?.channel === "internet";
});

const visibleServices = computed(() => {
  const allowedServices = deploymentAccess.value?.services ?? {};

  if (isAdminUser.value) {
    return publicServices.value;
  }

  return publicServices.value.filter(
    (service) =>
      !service.visibilityKey || allowedServices[service.visibilityKey] !== false,
  );
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
  <Head title="Welcome" />
  <main-bg />
  <GuideTourControls guide-key="welcome" :auto-start="false" />
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
          <div data-guide="welcome-services" class="flex flex-wrap justify-center gap-4 w-full max-w-7xl relative">
            <div class="contents">
            <ServiceCard
              v-for="(service, index) in visibleServices"
              :key="service.id || index"
              :title="service.title"
              :description="service.description"
              :icon="service.icon"
              :href="route(service.routeName)"
              :color="service.color"
              :external="service.external"
              class="min-w-[200px] w-[20%]"
            />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <social-links />

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
