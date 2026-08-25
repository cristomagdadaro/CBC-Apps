<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useGuideTour } from "@/Modules/composables/useGuideTour";

const props = defineProps({
    guideKey: {
        type: String,
        required: true,
    },
    autoStart: {
        type: Boolean,
        default: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const open = ref(false);
const isHovered = ref(false);
const isMobileVisible = ref(true);
let inactivityTimer = null;

const resetInactivityTimer = () => {
    isMobileVisible.value = true;
    if (inactivityTimer) clearTimeout(inactivityTimer);

    if (open.value) return;

    inactivityTimer = setTimeout(() => {
        if (!open.value) {
            isMobileVisible.value = false;
        }
    }, 5000);
};

watch(open, (isOpen) => {
    if (isOpen) {
        isMobileVisible.value = true;
        if (inactivityTimer) clearTimeout(inactivityTimer);
    } else {
        resetInactivityTimer();
    }
});

onMounted(() => {
    resetInactivityTimer();
    window.addEventListener("scroll", resetInactivityTimer, { passive: true, capture: true });
});

onUnmounted(() => {
    if (inactivityTimer) clearTimeout(inactivityTimer);
    window.removeEventListener("scroll", resetInactivityTimer, { capture: true });
});

const { autoEnabled, guideDefinition, startGuide, toggleAutoGuides } = useGuideTour(props.guideKey, { autoStart: props.autoStart });

const guideLabel = computed(() => guideDefinition?.title || "Guide");

const toggle = () => (open.value = !open.value);
const close = () => (open.value = false);

const handleStartGuide = () => {
    startGuide();
    close();
};

const handleToggleAuto = () => {
    toggleAutoGuides();
};
</script>

<template>
    <!-- Mobile Backdrop -->
    <transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div
            v-if="open"
            class="fixed inset-0 z-[999] bg-black/40 md:hidden"
            @click="close" />
    </transition>

    <!-- Main Container -->
    <div
        data-guide="guide-controls"
        class="fixed bottom-3.5 left-3.5 z-[1000] flex flex-col items-start gap-2 sm:bottom-6 sm:left-6 sm:gap-3">
        <!-- Desktop: Floating Pill -->
        <div
            class="hidden items-center gap-1 rounded-full border border-gray-200 bg-white px-2 py-1.5 shadow-xl transition-all duration-300 hover:scale-[1.02] md:flex dark:border-slate-800 dark:bg-slate-900"
            :class="compact ? 'origin-bottom-left scale-90' : ''"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false">
            <!-- Guide Icon -->
            <button
                class="rounded-full p-2.5 text-AB dark:text-emerald-400"
                @click="startGuide()">
                <LuHelpCircle class="h-5 w-5" />
            </button>

            <div class="mx-1 h-6 w-px bg-gray-300 dark:bg-gray-600" />

            <!-- Start Guide -->
            <button
                type="button"
                @click="startGuide()"
                class="group relative flex items-center gap-2 rounded-full p-2.5 text-gray-600 transition-all duration-200 hover:bg-AB/10 hover:text-AB dark:text-gray-300 dark:hover:bg-AB/20 dark:hover:text-emerald-400">
                <LuPlay class="h-5 w-5" />
                <span
                    v-if="!compact"
                    class="pr-1 text-sm font-medium">
                    {{ guideLabel }}
                </span>
                <span class="pointer-events-none absolute -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-xs text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100 dark:bg-white dark:text-gray-900">Start {{ guideLabel }}</span>
            </button>

            <!-- Auto Toggle -->
            <button
                type="button"
                @click="toggleAutoGuides()"
                class="group relative rounded-full p-2.5 transition-all duration-200"
                :class="autoEnabled ? 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50' : 'text-gray-600 hover:bg-gray-100 hover:text-AB dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-emerald-400'">
                <LuSettings2 class="h-5 w-5" />
                <span class="pointer-events-none absolute -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-xs text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100 dark:bg-white dark:text-gray-900">
                    {{ autoEnabled ? "Disable Auto Guides" : "Enable Auto Guides" }}
                </span>
            </button>
        </div>

        <!-- Mobile: FAB with Expandable Menu -->
        <div
            class="flex flex-col items-start gap-2 transition-all duration-500 ease-in-out md:hidden"
            :class="isMobileVisible || open ? 'translate-y-0 opacity-100' : 'pointer-events-none translate-y-12 opacity-0'">
            <!-- Menu Panel -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-8 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-8 scale-95">
                <div
                    v-if="open"
                    class="mb-1.5 min-w-[260px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <!-- Header -->
                    <div class="flex items-center justify-between bg-gradient-to-r from-lime-600 to-emerald-600 px-3.5 py-2.5">
                        <span class="flex items-center gap-2 text-xs font-semibold text-white">
                            <LuHelpCircle class="h-4 w-4" />
                            Guided Tour
                        </span>
                        <button
                            @click="close"
                            class="rounded-full p-1 text-white/80 transition-colors hover:bg-white/20 hover:text-white">
                            <LuX class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Menu Items -->
                    <div class="space-y-1 p-2">
                        <!-- Start Guide -->
                        <button
                            type="button"
                            @click="handleStartGuide"
                            class="group flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 text-slate-700 transition-all duration-200 hover:bg-lime-500/10 dark:text-slate-200 dark:hover:bg-lime-400/20">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-lime-500/10 text-lime-600 transition-all group-hover:bg-lime-500 group-hover:text-white dark:bg-lime-400/20 dark:text-lime-400">
                                <LuPlay class="h-3.5 w-3.5" />
                            </div>
                            <div class="flex-1 text-left">
                                <span class="text-xs font-semibold">Start {{ guideLabel }}</span>
                                <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">Begin interactive walkthrough</p>
                            </div>
                        </button>

                        <!-- Auto Toggle -->
                        <button
                            type="button"
                            @click="handleToggleAuto"
                            class="group flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 transition-all duration-200"
                            :class="autoEnabled ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/30' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800'">
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-lg transition-all"
                                :class="autoEnabled ? 'bg-emerald-100 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white dark:bg-emerald-900/30' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-700 group-hover:text-white dark:bg-slate-800'">
                                <LuSettings2 class="h-3.5 w-3.5" />
                            </div>
                            <div class="flex-1 text-left">
                                <span class="text-xs font-semibold">
                                    {{ autoEnabled ? "Auto Guides On" : "Auto Guides Off" }}
                                </span>
                                <p class="text-[0.68rem] text-slate-500 dark:text-slate-400">
                                    {{ autoEnabled ? "Guides appear automatically" : "Manual guide activation" }}
                                </p>
                            </div>
                        </button>
                    </div>
                </div>
            </transition>

            <!-- FAB Toggle Button -->
            <button
                type="button"
                @click="toggle"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-white opacity-85 shadow-md transition-all duration-300 hover:scale-105 hover:opacity-100 focus:outline-none active:scale-95 sm:h-14 sm:w-14 sm:shadow-lg dark:border-slate-700 dark:bg-slate-800"
                :class="{ 'rotate-90 bg-lime-600 text-white opacity-100': open }"
                aria-label="Toggle guide menu">
                <LuHelpCircle
                    v-if="!open"
                    class="h-5 w-5 text-lime-400 sm:h-6 sm:w-6" />
                <LuX
                    v-else
                    class="h-5 w-5 sm:h-6 sm:w-6" />
            </button>
        </div>
    </div>
</template>
