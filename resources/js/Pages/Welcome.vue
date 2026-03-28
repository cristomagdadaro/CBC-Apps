<script setup>
import particleMixin from "@/Modules/mixins/ParticleMixin.js";
import {
  LuCalendarDays,
  LuMicroscope,
  LuCalendar,
  LuBookOpen,
  LuPackage,
  LuFlag,
  LuFlaskConical,
  LuClipboardList,
  LuCpu,
} from "@/Components/Icons";
import { onMounted, ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import SocialLinks from "@/Components/SocialLinks.vue";
import ServiceCard from "@/Components/ServiceCard.vue";
import MainBg from "@/Pages/Shared/MainBg.vue";

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
});

const showNetworkModal = ref(false);
const isCheckingNetwork = ref(false);
const page = usePage();

const deploymentAccess = computed(() => page.props.deployment_access ?? {});
const localNetworkUrl = computed(
  () => deploymentAccess.value?.local_url ?? "http://192.168.36.10",
);

const isInternetAccess = computed(() => {
  return deploymentAccess.value?.channel === "internet";
});

const services = [
  {
    title: "Bookings & Rentals",
    description: "Reserve vehicles, venues, and equipment for your events",
    icon: LuCalendarDays,
    href: route("rental.bookings.guest"),
    color: "blue",
    visibilityKey: "rentals",
  },
  {
    title: "Event Registration",
    description: "Register and participate in DA-CBC events and activities",
    icon: LuCalendar,
    href: route("forms.guest.index"),
    color: "violet",
    visibilityKey: "forms",
  },
  {
    title: "FES Requests",
    description: "Request facilities, equipment, and supplies",
    icon: LuClipboardList,
    href: route("labReq.guest.index"),
    color: "amber",
    visibilityKey: "fes",
  },
  {
    title: "Lab Equipment Log",
    description: "Track and manage laboratory equipment usage",
    icon: LuMicroscope,
    href: route("laboratory.equipments.show"),
    color: "emerald",
    visibilityKey: "equipment_logger",
  },
  {
    title: "ICT Equipment Log",
    description: "Monitor ICT assets and equipment availability",
    icon: LuCpu,
    href: route("ict.equipments.show"),
    color: "cyan",
    visibilityKey: "equipment_logger",
  },
  {
    title: "Supplies Checkout",
    description: "Request and track inventory and supply items",
    icon: LuPackage,
    href: route("inventory.public.outgoing.index"),
    color: "orange",
    visibilityKey: "supplies_checkout",
  },
  {
    title: "Incident Reports",
    description: "Report issues, damages, or maintenance needs",
    icon: LuFlag,
    href: route("suppEquipReports.create.guest"),
    color: "rose",
    visibilityKey: "inventory",
  },
  {
    title: "Experiment Log",
    description: "Monitor ongoing laboratory experiments and research",
    icon: LuFlaskConical,
    href: route("laboratory.monitoring.guest"),
    color: "indigo",
    visibilityKey: "research",
  },
  {
    title: "Manuals & Guides",
    description: "Browse public help topics, operational instructions, and user guides",
    icon: LuBookOpen,
    href: route("manuals.index"),
    color: "indigo",
  },
];

const visibleServices = computed(() => {
  const allowedServices = deploymentAccess.value?.services ?? {};

  return services.filter(
    (service) =>
      !service.visibilityKey || allowedServices[service.visibilityKey] !== false,
  );
});

const hasHiddenLocalServices = computed(() => {
  return services.some(
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

onMounted(() => {
  particleMixin.methods.createFallingLogos();

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
  <div class="absolute top-0 left-0 w-full">
    <div class="relative sm:flex justify-center items-center min-h-screen">
      <div class="flex flex-col gap-5 px-5">
        <div class="text-center text-gray-700 dark:text-gray-300">
          <div class="relative w-fit mx-auto">
            <div class="flex items-center gap-1">
              <h1
                class="lg:text-6xl md:text-4xl text-3xl font-bold leading-none text-lime-500 dark:text-green-400 font-[Montserrat] drop-shadow-md whitespace-nowrap"
              >
                {{ $appName }}
              </h1>
            </div>
            <span class="absolute bottom-0 -right-5 text-[0.60rem] text-lime-500">
              {{ $page.props.appVersion }}
            </span>
          </div>

          <p class="mt-4 max-w-2xl mx-auto text-gray-300 leading-none">
            Your gateway to DA-Crop Biotechnology Center's proprietary web apps and
            services.
          </p>
          <blockquote class="mt-2 font-semibold text-gray-300 leading-none">
            Better Crops, Better Lives
          </blockquote>
        </div>
        <div class="flex flex-col items-center justify-center gap-6 text-sm w-full">
          <div class="text-center">
            <h3
              class="text-lime-500 dark:text-white text-xl font-bold tracking-wider uppercase"
            >
              Apps & Services
            </h3>
            <div
              class="h-1.5 w-16 bg-gradient-to-r from-AC via-AB to-AA dark:from-AA dark:via-AD dark:to-AB mt-3 mx-auto rounded-full shadow-lg dark:shadow-AC/30"
            ></div>
          </div>
          <div
            class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 w-full max-w-7xl"
          >
            <ServiceCard
              v-for="(service, index) in visibleServices"
              :key="index"
              :title="service.title"
              :description="service.description"
              :icon="service.icon"
              :href="service.href"
              :color="service.color"
              :external="service.external"
            />
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
