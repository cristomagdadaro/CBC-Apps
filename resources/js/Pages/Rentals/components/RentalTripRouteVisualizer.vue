<script setup>
import { computed } from "vue";
import { buildTripRoute, getTripTypeMeta } from "@/Pages/Rentals/constants/tripWorkflows";

const props = defineProps({
    tripType: {
        type: String,
        default: "dedicated_trip",
    },
    destinationLocation: {
        type: String,
        default: "",
    },
    destinationStops: {
        type: Array,
        default: () => [],
    },
    isSharedRide: {
        type: Boolean,
        default: false,
    },
    sharedRideReference: {
        type: String,
        default: "",
    },
    originLabel: {
        type: String,
        default: "CES",
    },
});

const tripMeta = computed(() => getTripTypeMeta(props.tripType));
const destinationLabels = computed(() => {
    return [props.destinationLocation, ...props.destinationStops].map((value) => String(value || "").trim()).filter(Boolean);
});
const routeSteps = computed(() => buildTripRoute(props.tripType, destinationLabels.value, props.originLabel));

// Extracted styles to guarantee Tailwind compilation and support dark mode
const STEP_STYLES = {
    origin: "border-blue-200 bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30",
    stop: "border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30",
    return: "border-slate-200 bg-slate-50 text-slate-700 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600/60",
    transfer: "border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30",
};

const getStepClass = (kind) => STEP_STYLES[kind] || STEP_STYLES.transfer;
</script>

<template>
    <!-- Optimized Glassmorphic Container -->
    <div class="rounded-2xl border border-gray-200/70 dark:border-slate-700/60 bg-white/80 dark:bg-slate-800/80 p-5 shadow-sm backdrop-blur-xl">
        <!-- Header Section -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2.5 text-sm font-bold text-gray-900 dark:text-slate-100 uppercase tracking-wide">
                    <LuShield class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <span>{{ tripMeta.label }}</span>
                </div>
            </div>

            <div
                v-if="isSharedRide"
                class="inline-flex items-center gap-2 rounded-full border border-violet-200 dark:border-violet-500/30 bg-violet-50 dark:bg-violet-500/10 px-3 py-1 text-xs font-bold text-violet-700 dark:text-violet-400 shadow-sm self-start">
                <LuUsers class="h-3.5 w-3.5" />
                <span>
                    Shared Ride
                    <span
                        v-if="sharedRideReference"
                        class="opacity-70 font-semibold ml-1">
                        · {{ sharedRideReference }}
                    </span>
                </span>
            </div>
        </div>

        <!-- Visual Route Flow -->
        <div class="mt-5 flex flex-wrap items-center gap-2.5">
            <template
                v-for="(step, index) in routeSteps"
                :key="`${step.kind}-${step.label}-${index}`">
                <!-- Route Step Badge -->
                <div :class="['inline-flex max-w-full items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold shadow-sm transition-colors', getStepClass(step.kind)]">
                    <LuStar
                        v-if="step.kind === 'origin'"
                        class="h-4 w-4 shrink-0" />
                    <LuRefreshCw
                        v-else-if="step.kind === 'transfer'"
                        class="h-4 w-4 shrink-0" />
                    <LuMapPin
                        v-else
                        class="h-4 w-4 shrink-0" />
                    <span class="truncate">{{ step.label }}</span>
                </div>

                <!-- Separator Arrow -->
                <LuArrowRight
                    v-if="index < routeSteps.length - 1"
                    class="h-4 w-4 text-gray-400 dark:text-slate-500 shrink-0" />
            </template>
        </div>

        <!-- Meta Information -->
        <div
            v-if="destinationLabels.length > 1"
            class="mt-5 rounded-xl border border-dashed border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/30 px-4 py-3 text-xs font-medium text-gray-500 dark:text-slate-400">
            <span class="font-bold text-gray-700 dark:text-slate-300">
                {{ destinationLabels.length }}
            </span>
            declared stops included in this trip workflow.
        </div>
    </div>
</template>
