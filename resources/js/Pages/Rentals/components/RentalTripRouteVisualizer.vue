<script setup>
import { computed } from "vue";
import { LuArrowRight, LuFlag, LuMapPin, LuRefreshCw, LuUsers } from "@/Components/Icons";
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
    return [props.destinationLocation, ...props.destinationStops]
        .map((value) => String(value || "").trim())
        .filter(Boolean);
});
const routeSteps = computed(() => buildTripRoute(props.tripType, destinationLabels.value, props.originLabel));

const stepClass = (kind) => {
    switch (kind) {
        case "origin":
            return "border-blue-200 bg-blue-50 text-blue-700";
        case "stop":
            return "border-emerald-200 bg-emerald-50 text-emerald-700";
        case "return":
            return "border-slate-200 bg-slate-50 text-slate-700";
        default:
            return "border-amber-200 bg-amber-50 text-amber-700";
    }
};
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-5 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                    <LuShield class="h-4 w-4 text-blue-600" />
                    <span>{{ tripMeta.label }}</span>
                </div>
                <p class="mt-1 text-sm text-gray-500 hidden">{{ tripMeta.description }}</p>
            </div>
            <div v-if="isSharedRide" class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-medium text-violet-700">
                <LuUsers class="h-3.5 w-3.5" />
                <span>
                    Shared Ride
                    <span v-if="sharedRideReference">· {{ sharedRideReference }}</span>
                </span>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <template v-for="(step, index) in routeSteps" :key="`${step.kind}-${step.label}-${index}`">
                <div class="inline-flex max-w-full items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium" :class="stepClass(step.kind)">
                    <LuStar v-if="step.kind === 'origin'" class="h-4 w-4 shrink-0" />
                    <LuRefreshCw v-else-if="step.kind === 'transfer'" class="h-4 w-4 shrink-0" />
                    <LuMapPin v-else class="h-4 w-4 shrink-0" />
                    <span class="truncate">{{ step.label }}</span>
                </div>
                <LuArrowRight v-if="index < routeSteps.length - 1" class="h-4 w-4 text-gray-300" />
            </template>
        </div>

        <div v-if="destinationLabels.length > 1" class="mt-4 rounded-xl border border-dashed border-gray-200 bg-white px-4 py-3 text-xs text-gray-500">
            {{ destinationLabels.length }} declared stops included in this trip workflow.
        </div>
    </div>
</template>
