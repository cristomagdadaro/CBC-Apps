<script>
import { computed, ref, watchEffect, shallowRef } from "vue";
import { usePage } from "@inertiajs/vue3";
import OverviewTopic from "./Topics/OverviewTopic.vue";
import CustomFormTopic from "./Topics/CustomFormTopic.vue";
import FESRequestFormTopic from "./Topics/FESRequestFormTopic.vue";
import InventorySystemTopic from "./Topics/InventorySystemTopic.vue";
import InventoryTransactionsTopic from "./Topics/InventoryTransactionsTopic.vue";
import InventoryReportTopic from "./Topics/InventoryReportTopic.vue";
import AddSupplierTopic from "./Topics/AddSupplierTopic.vue";
import AddItemTopic from "./Topics/AddItemTopic.vue";
import ProfilePasswordTopic from "./Topics/ProfilePasswordTopic.vue";
import ConsoleLoggerTopic from "./Topics/ConsoleLoggerTopic.vue";
import RentalServicesTopic from "./Topics/RentalServicesTopic.vue";
import SystemOptionsTopic from "./Topics/SystemOptionsTopic.vue";
import CertificateGeneratorTopic from "./Topics/CertificateGeneratorTopic.vue";
import IconsLibraryTopic from "./Topics/IconsLibraryTopic.vue";
import GoogleCalendarTopic from "./Topics/GoogleCalendarTopic.vue";
import ResearchMonitoringTopic from "./Topics/ResearchMonitoringTopic.vue";
import DriverJsGuidesTopic from "./Topics/DriverJsGuidesTopic.vue";

export default {
    name: "ManualsIndex",
    components: {
        OverviewTopic,
        CustomFormTopic,
        FESRequestFormTopic,
        InventorySystemTopic,
        InventoryTransactionsTopic,
        InventoryReportTopic,
        AddSupplierTopic,
        AddItemTopic,
        ProfilePasswordTopic,
        ConsoleLoggerTopic,
        RentalServicesTopic,
        SystemOptionsTopic,
        CertificateGeneratorTopic,
        IconsLibraryTopic,
        GoogleCalendarTopic,
        ResearchMonitoringTopic,
        DriverJsGuidesTopic,
    },
    setup() {
        const page = usePage();
        const initialSection = typeof window !== "undefined" ? new URLSearchParams(window.location.search).get("section") : null;
        const activeSection = ref(initialSection || "overview");
        const developerOnlyTopicIds = ["consoleLogger", "iconsLibrary", "driverJsGuides"];
        const showDeveloperSections = computed(() => Boolean(page.props?.auth?.user?.id));

        const sections = {
            overview: {
                title: "Overview",
                icon: "LuBookOpen",
                component: OverviewTopic,
            },
            addCustomForm: {
                title: "How to add a new custom form",
                icon: "LuFileText",
                component: CustomFormTopic,
            },
            fesRequestForm: {
                title: "How to use Facilities, Equipment, and Supplies Request Form",
                icon: "LuLandmark",
                component: FESRequestFormTopic,
            },
            inventorySystem: {
                title: "How to use the Inventory System",
                icon: "LuBox",
                component: InventorySystemTopic,
            },
            inventoryTransactions: {
                title: "How to add Incoming or Outgoing Transactions and set Logger Availability",
                icon: "LuBarChart2",
                component: InventoryTransactionsTopic,
            },
            inventoryReport: {
                title: "How to file a Report",
                icon: "LuFileBox",
                component: InventoryReportTopic,
            },
            addSupplier: {
                title: "How to add a new Supplier",
                icon: "LuHandshake",
                component: AddSupplierTopic,
            },
            addItem: {
                title: "How to add a new Item",
                icon: "LuTag",
                component: AddItemTopic,
            },
            profilePassword: {
                title: "How to update Profile and Password",
                icon: "LuUserCog",
                component: ProfilePasswordTopic,
            },
            consoleLogger: {
                title: "Console Logger (Development)",
                icon: "LuTerminal",
                component: ConsoleLoggerTopic,
            },
            rentalServices: {
                title: "Rental Services Module",
                icon: "LuCar",
                component: RentalServicesTopic,
            },
            systemOptions: {
                title: "How to use System Options and maintain Logger Modes",
                icon: "LuSettings",
                component: SystemOptionsTopic,
            },
            certificateGenerator: {
                title: "How to use Certificate Generator",
                icon: "LuGraduationCap",
                component: CertificateGeneratorTopic,
            },
            iconsLibrary: {
                title: "Icons Library",
                icon: "LuPalette",
                component: IconsLibraryTopic,
            },
            googleCalendar: {
                title: "Google Calendar Integration",
                icon: "LuCalendar",
                component: GoogleCalendarTopic,
            },
            researchMonitoring: {
                title: "Research Monitoring Module",
                icon: "LuDna",
                component: ResearchMonitoringTopic,
            },
            driverJsGuides: {
                title: "Driver.js Tour Guides",
                icon: "LuCompass",
                component: DriverJsGuidesTopic,
            },
        };

        const menuItems = [
            { id: "overview", label: "Overview", icon: "LuBookOpen" },
            { id: "addCustomForm", label: "How to add a custom form", icon: "LuFileText" },
            { id: "fesRequestForm", label: "How to use FES Request Form", icon: "LuLandmark" },
            { id: "inventorySystem", label: "How to use the Inventory System", icon: "LuBox" },
            {
                id: "inventoryTransactions",
                label: "Incoming/Outgoing + Logger Availability",
                icon: "LuBarChart2",
            },
            { id: "rentalServices", label: "Rental Services Module", icon: "LuCar" },
            { id: "inventoryReport", label: "How to file a Report", icon: "LuFileBox" },
            { id: "addSupplier", label: "How to add a new Supplier", icon: "LuHandshake" },
            { id: "addItem", label: "How to add a new Item", icon: "LuTag" },
            {
                id: "profilePassword",
                label: "How to update Profile and Password",
                icon: "LuUserCog",
            },
            { id: "consoleLogger", label: "Console Logger (Development)", icon: "LuTerminal" },
            { id: "systemOptions", label: "System Options + Logger Modes", icon: "LuSettings" },
            {
                id: "certificateGenerator",
                label: "How to use Certificate Generator",
                icon: "LuGraduationCap",
            },
            { id: "iconsLibrary", label: "Icons Library", icon: "LuPalette" },
            { id: "googleCalendar", label: "Google Calendar Integration", icon: "LuCalendar" },
            { id: "researchMonitoring", label: "Research Monitoring Module", icon: "LuDna" },
            { id: "driverJsGuides", label: "Driver.js Tour Guides", icon: "LuCompass" },
        ];

        const visibleSections = computed(() => {
            if (showDeveloperSections.value) {
                return sections;
            }

            return Object.fromEntries(Object.entries(sections).filter(([key]) => !developerOnlyTopicIds.includes(key)));
        });

        const visibleMenuItems = computed(() => {
            if (showDeveloperSections.value) {
                return menuItems;
            }

            return menuItems.filter((item) => !developerOnlyTopicIds.includes(item.id));
        });

        watchEffect(() => {
            if (!visibleSections.value[activeSection.value]) {
                activeSection.value = "overview";
            }
        });

        return {
            activeSection,
            showDeveloperSections,
            visibleSections,
            visibleMenuItems,
        };
    },
};
</script>

<template>
    <AppLayout title="System Manuals">
        <template #header>
            <ActionHeaderLayout
                :title="showDeveloperSections ? 'System Manuals & Guides' : 'Public Manuals & Guides'"
                :subtitle="showDeveloperSections ? 'Comprehensive guides to help you navigate and utilize the system effectively.' : 'Step-by-step user guides for public visitors and operational staff.'" />
        </template>

        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-4">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-1">
                    <nav class="sticky top-6 flex flex-col gap-1 rounded-2xl border border-slate-200/60 bg-white/80 p-3 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                        <h3 class="mb-1 px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Topics</h3>
                        <button
                            v-for="item in visibleMenuItems"
                            :key="item.id"
                            @click="activeSection = item.id"
                            :class="['flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm font-medium transition-all duration-200', activeSection === item.id ? 'bg-indigo-50 text-indigo-700 shadow-sm ring-1 ring-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200']">
                            <div class="flex items-center gap-3 truncate">
                                <component
                                    :is="item.icon"
                                    :class="['h-4 w-4 shrink-0', activeSection === item.id ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500']" />
                                <span class="truncate">{{ item.label }}</span>
                            </div>
                            <LuChevronRight
                                v-if="activeSection === item.id"
                                class="h-4 w-4 shrink-0 text-indigo-400 opacity-70" />
                        </button>
                    </nav>
                </div>

                <!-- Content Area -->
                <div class="lg:col-span-3">
                    <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                        <!-- Content Header -->
                        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 dark:border-slate-800/60 dark:bg-slate-800/20">
                            <div class="flex items-center gap-3.5">
                                <div class="shrink-0 rounded-xl border border-indigo-100 bg-indigo-50 p-2.5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                                    <component
                                        :is="visibleSections[activeSection]?.icon"
                                        class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                                        {{ visibleSections[activeSection]?.title }}
                                    </h1>
                                </div>
                            </div>
                        </div>

                        <!-- Component Rendering -->
                        <div class="p-6">
                            <component
                                :is="visibleSections[activeSection]?.component"
                                :show-developer-sections="showDeveloperSections" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Scoped styles have been simplified because deep stylings break modern topic layouts 
   that already implement their own typography & spacing utility classes */
:deep(p:not([class])) {
    margin-bottom: 1rem;
    line-height: 1.6;
}

:deep(h3:not([class])) {
    margin-top: 1.5rem;
    font-size: 1.125rem;
    line-height: 1.75rem;
    font-weight: 700;
}

:deep(h4:not([class])) {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

:deep(ul:not([class])) {
    margin: 0.5rem 0;
    list-style-type: disc;
    padding-left: 1.5rem;
}

:deep(ol:not([class])) {
    margin: 0.5rem 0;
    list-style-type: decimal;
    padding-left: 1.5rem;
}

:deep(li:not([class])) {
    margin-bottom: 0.5rem;
}
</style>
