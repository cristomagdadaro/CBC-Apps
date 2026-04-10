<script setup>
import { ref, computed } from "vue";
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

const { autoEnabled, guideDefinition, startGuide, toggleAutoGuides } = useGuideTour(
    props.guideKey,
    { autoStart: props.autoStart }
);

const guideLabel = computed(() => guideDefinition?.title || "Guide");

const toggle = () => open.value = !open.value;
const close = () => open.value = false;

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
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 bg-black/20 backdrop-blur-sm z-[999] md:hidden"
            @click="close"
        />
    </transition>

    <!-- Main Container -->
    <div
        data-guide="guide-controls"
        class="fixed bottom-6 left-6 z-[1000] flex flex-col items-start gap-3"
    >
        <!-- Desktop: Floating Pill -->
        <div
            class="hidden md:flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200/50 dark:border-gray-700/50 rounded-full px-2 py-1.5 shadow-xl shadow-gray-900/10 dark:shadow-black/30 backdrop-blur-md bg-opacity-90 dark:bg-opacity-90 transition-all duration-300 hover:shadow-2xl hover:shadow-gray-900/15 hover:scale-[1.02]"
            :class="compact ? 'scale-90 origin-bottom-left' : ''"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
        >
            <!-- Guide Icon -->
            <button class="p-2.5 rounded-full text-AB dark:text-emerald-400" @click="startGuide()">
                <LuHelpCircle class="w-5 h-5" />
            </button>

            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

            <!-- Start Guide -->
            <button
                type="button"
                @click="startGuide()"
                class="group relative p-2.5 flex items-center gap-2 rounded-full hover:bg-AB/10 dark:hover:bg-AB/20 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:text-AB dark:hover:text-emerald-400"
            >
                <LuPlay class="w-5 h-5" />
                <span v-if="!compact" class="text-sm font-medium pr-1">
                    {{ guideLabel }}
                </span>
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap"
                >
                    Start {{ guideLabel }}
                </span>
            </button>

            <!-- Auto Toggle -->
            <button
                type="button"
                @click="toggleAutoGuides()"
                class="group relative p-2.5 rounded-full transition-all duration-200"
                :class="autoEnabled
                    ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/50'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-AB dark:hover:text-emerald-400'"
            >
                <LuSettings2 class="w-5 h-5" />
                <span
                    class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap"
                >
                    {{ autoEnabled ? "Disable Auto Guides" : "Enable Auto Guides" }}
                </span>
            </button>
        </div>

        <!-- Mobile: FAB with Expandable Menu -->
        <div class="md:hidden flex flex-col items-start gap-3">
            <!-- Menu Panel -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-8 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-8 scale-95"
            >
                <div
                    v-if="open"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden min-w-[280px] mb-2"
                >
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-AB to-AB/80 px-4 py-3 flex items-center justify-between">
                        <span class="text-white font-semibold text-sm flex items-center gap-2">
                            <LuHelpCircle class="w-4 h-4" />
                            Guided Tour
                        </span>
                        <button
                            @click="close"
                            class="text-white/80 hover:text-white transition-colors p-1 rounded-full hover:bg-white/20"
                        >
                            <LuX class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Menu Items -->
                    <div class="p-2 space-y-1">
                        <!-- Start Guide -->
                        <button
                            type="button"
                            @click="handleStartGuide"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-AB/10 dark:hover:bg-AB/20 transition-all duration-200 group"
                        >
                            <div class="w-8 h-8 rounded-lg bg-AB/10 dark:bg-AB/20 flex items-center justify-center text-AB group-hover:bg-AB group-hover:text-white transition-all">
                                <LuPlay class="w-4 h-4" />
                            </div>
                            <div class="flex-1 text-left">
                                <span class="text-sm font-medium">Start {{ guideLabel }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Begin interactive walkthrough
                                </p>
                            </div>
                        </button>

                        <!-- Auto Toggle -->
                        <button
                            type="button"
                            @click="handleToggleAuto"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group"
                            :class="autoEnabled
                                ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/30'
                                : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        >
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                                :class="autoEnabled
                                    ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 group-hover:bg-gray-500 group-hover:text-white'"
                            >
                                <LuSettings2 class="w-4 h-4" />
                            </div>
                            <div class="flex-1 text-left">
                                <span class="text-sm font-medium">
                                    {{ autoEnabled ? "Auto Guides On" : "Auto Guides Off" }}
                                </span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
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
                class="w-14 h-14 rounded-full bg-AB text-white shadow-lg shadow-AB/30 flex items-center justify-center hover:shadow-xl hover:shadow-AB/40 hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-AB/20"
                :class="{ 'rotate-90': open }"
                aria-label="Toggle guide menu"
            >
                <LuHelpCircle v-if="!open" class="w-6 h-6" />
                <LuX v-else class="w-6 h-6" />
            </button>
        </div>
    </div>
</template>
