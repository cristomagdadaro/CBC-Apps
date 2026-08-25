<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import RentalTripRouteVisualizer from "@/Pages/Rentals/components/RentalTripRouteVisualizer.vue";
import { getTripTypeMeta } from "@/Pages/Rentals/constants/tripWorkflows";

// Extracted outside the component to prevent recreation on every render
// Explicit class definitions ensure Tailwind's JIT compiler processes them correctly
const STATUS_CONFIGS = {
    approved: {
        badge: "bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20",
        border: "bg-emerald-500",
        header: "bg-emerald-50 dark:bg-emerald-900/20",
        iconColor: "text-emerald-600 dark:text-emerald-400",
        iconBg: "bg-emerald-100 dark:bg-emerald-500/20",
        icon: "LuCheckCircle2",
        label: "Approved",
    },
    in_progress: {
        badge: "bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20",
        border: "bg-blue-500",
        header: "bg-blue-50 dark:bg-blue-900/20",
        iconColor: "text-blue-600 dark:text-blue-400",
        iconBg: "bg-blue-100 dark:bg-blue-500/20",
        icon: "LuCar",
        label: "In Progress",
    },
    rejected: {
        badge: "bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20",
        border: "bg-red-500",
        header: "bg-red-50 dark:bg-red-900/20",
        iconColor: "text-red-600 dark:text-red-400",
        iconBg: "bg-red-100 dark:bg-red-500/20",
        icon: "LuXCircle",
        label: "Rejected",
    },
    cancelled: {
        badge: "bg-gray-100 text-gray-700 border-gray-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600",
        border: "bg-gray-400 dark:bg-slate-500",
        header: "bg-gray-50 dark:bg-slate-800",
        iconColor: "text-gray-600 dark:text-slate-400",
        iconBg: "bg-gray-200 dark:bg-slate-700",
        icon: "LuXCircle",
        label: "Cancelled",
    },
    completed: {
        badge: "bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700",
        border: "bg-slate-500 dark:bg-slate-400",
        header: "bg-slate-50 dark:bg-slate-900/50",
        iconColor: "text-slate-600 dark:text-slate-400",
        iconBg: "bg-slate-200 dark:bg-slate-800",
        icon: "LuCheckCircle2",
        label: "Completed",
    },
    pending: {
        badge: "bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20",
        border: "bg-amber-500",
        header: "bg-amber-50 dark:bg-amber-900/20",
        iconColor: "text-amber-600 dark:text-amber-400",
        iconBg: "bg-amber-100 dark:bg-amber-500/20",
        icon: "LuAlertCircle",
        label: "Pending",
    },
};

export default {
    name: "RentalVehicleApprovalCard",
    components: {
        RentalTripRouteVisualizer,
    },
    mixins: [ApiMixin],
    props: {
        data: {
            type: Object,
            required: true,
        },
        vehicleOptions: {
            type: Array,
            default: () => [],
        },
    },
    emits: ["updated", "failedUpdate"],
    data() {
        return {
            showModal: false,
            formState: { ...this.data },
            processing: false,
        };
    },
    computed: {
        canApprove() {
            return this.$isAdminUser || (this.$currentPermissions ?? []).includes("rental.request.approve");
        },
        tripTypeMeta() {
            return getTripTypeMeta(this.formState.trip_type);
        },
        assignedVehicleLabel() {
            return this.formState.vehicle_type || "Vehicle to be assigned upon approval";
        },
        statusConfig() {
            return STATUS_CONFIGS[this.formState.status] || STATUS_CONFIGS.pending;
        },
    },
    watch: {
        data: {
            handler(newData) {
                this.formState = { ...newData };
            },
            deep: true,
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return "N/A";
            return new Date(date).toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
                year: "numeric",
            });
        },
        formatTime(time) {
            if (!time) return "";
            return time;
        },
        async updateStatus(status) {
            if (!this.canApprove || this.processing) return;

            if (status === "approved" && !this.formState.vehicle_type) {
                this.$emit("failedUpdate", new Error("Select a vehicle before approving this request."));
                return;
            }

            this.processing = true;

            try {
                const response = await this.fetchPutApi("api.rental.vehicles.update-status", this.formState.id, {
                    status,
                    vehicle_type: this.formState.vehicle_type,
                    notes: this.formState.notes,
                });
                const data = response?.data ?? response;
                this.formState = { ...data.data };
                this.$emit("updated", this.formState);
                this.showModal = false;
            } catch (error) {
                this.$emit("failedUpdate", error);
            } finally {
                this.processing = false;
            }
        },
    },
};
</script>

<template>
    <!-- Optimized Card UI -->
    <div
        @click="showModal = true"
        class="group relative cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:border-gray-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-600">
        <!-- Status Indicator Line -->
        <div :class="['absolute left-0 top-0 h-full w-1.5 transition-colors', statusConfig.border]"></div>

        <div class="p-5 pl-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <div class="mb-1.5 flex items-center gap-2.5">
                        <div :class="['shrink-0 rounded-lg p-1.5', statusConfig.iconBg]">
                            <LuCar :class="['h-4 w-4', statusConfig.iconColor]" />
                        </div>
                        <h3 class="truncate text-base font-bold text-gray-900 dark:text-slate-100">
                            {{ formState.requested_by || "Unknown" }}
                        </h3>
                    </div>

                    <p class="mb-1 text-sm font-medium text-gray-600 dark:text-slate-400">
                        {{ tripTypeMeta.label }}
                    </p>
                    <p class="mb-3 line-clamp-1 text-xs text-gray-500 dark:text-slate-500">
                        {{ assignedVehicleLabel }}
                    </p>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg border border-gray-100 bg-gray-50 p-2.5 text-xs text-gray-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                        <span class="flex items-center gap-1.5 font-medium">
                            <LuCalendar class="h-3.5 w-3.5 text-gray-400 dark:text-slate-500" />
                            {{ formatDate(formState.date_from) }} -
                            {{ formatDate(formState.date_to) }}
                        </span>
                        <span class="flex items-center gap-1.5 font-medium">
                            <LuClock class="h-3.5 w-3.5 text-gray-400 dark:text-slate-500" />
                            {{ formatTime(formState.time_from) }} -
                            {{ formatTime(formState.time_to) }}
                        </span>
                    </div>
                </div>

                <div class="flex shrink-0 flex-col items-end gap-2">
                    <span :class="['inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold', statusConfig.badge]">
                        <component
                            :is="statusConfig.icon"
                            class="h-3.5 w-3.5" />
                        {{ statusConfig.label }}
                    </span>
                    <span class="text-[0.65rem] font-medium text-gray-400 dark:text-slate-500">Updated {{ formatDate(formState.updated_at) }}</span>
                </div>
            </div>

            <!-- Hover Prompt -->
            <div class="mt-3.5 flex translate-x-2 transform items-center justify-end text-xs font-semibold text-gray-400 opacity-0 transition-opacity duration-300 group-hover:translate-x-0 group-hover:opacity-100 dark:text-slate-500">
                <span>View Details</span>
                <LuChevronRight class="ml-0.5 h-3.5 w-3.5" />
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <DialogModal
        max-width="2xl"
        :show="showModal"
        @close="showModal = false">
        <template #title>
            <div :class="['relative overflow-hidden border-b border-gray-200/50 px-6 py-5 dark:border-slate-700/50', statusConfig.header]">
                <div class="pointer-events-none absolute -right-4 -top-4 opacity-10">
                    <LuCar class="h-32 w-32" />
                </div>

                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <span :class="['inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-bold shadow-sm', statusConfig.badge]">
                                <component
                                    :is="statusConfig.icon"
                                    class="h-3.5 w-3.5" />
                                {{ statusConfig.label }}
                            </span>
                            <span class="text-xs font-medium text-gray-500 dark:text-slate-400">Updated {{ formatDate(formState.updated_at) }}</span>
                        </div>
                        <h2 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-slate-100">Vehicle Rental Request</h2>
                        <p class="mt-0.5 text-sm font-medium text-gray-600 dark:text-slate-400">
                            {{ tripTypeMeta.label }}
                        </p>
                    </div>
                    <button
                        @click="showModal = false"
                        class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-200/50 hover:text-gray-600 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300">
                        <LuXCircle class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </template>

        <template #content>
            <div class="space-y-6">
                <!-- Requester Info -->
                <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-slate-700/50 dark:bg-slate-800">
                    <div class="rounded-xl bg-blue-50 p-3 shadow-inner dark:bg-blue-500/10">
                        <LuUser class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="flex-1">
                        <p class="mb-0.5 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Requested By</p>
                        <p class="text-lg font-bold leading-tight text-gray-900 dark:text-slate-100">
                            {{ formState.requested_by || "N/A" }}
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-slate-400">
                            {{ assignedVehicleLabel }}
                        </p>
                    </div>
                </div>

                <RentalTripRouteVisualizer
                    :trip-type="formState.trip_type"
                    :destination-location="formState.destination_location"
                    :destination-stops="formState.destination_stops || []"
                    :is-shared-ride="Boolean(formState.is_shared_ride)"
                    :shared-ride-reference="formState.shared_ride_reference || ''" />

                <!-- Date & Time Card -->
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <div class="mb-1.5 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                <LuCalendar class="h-4 w-4" />
                                Date Range
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-slate-200">
                                {{ formatDate(formState.date_from) }}
                                <span class="mx-1 text-gray-400">→</span>
                                {{ formatDate(formState.date_to) }}
                            </p>
                        </div>
                        <div>
                            <div class="mb-1.5 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                                <LuClock class="h-4 w-4" />
                                Time
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-slate-200">
                                {{ formatTime(formState.time_from) || "N/A" }}
                                <span class="mx-1 text-gray-400">→</span>
                                {{ formatTime(formState.time_to) || "N/A" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div
                        v-if="formState.purpose"
                        class="rounded-xl border border-gray-100 bg-white p-4 dark:border-slate-700/50 dark:bg-slate-800">
                        <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                            <LuFileText class="h-4 w-4" />
                            Purpose
                        </div>
                        <p class="text-sm font-medium text-gray-800 dark:text-slate-300">
                            {{ formState.purpose }}
                        </p>
                    </div>

                    <div
                        v-if="formState.contact_number"
                        class="rounded-xl border border-gray-100 bg-white p-4 dark:border-slate-700/50 dark:bg-slate-800">
                        <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                            <LuUser class="h-4 w-4" />
                            Contact
                        </div>
                        <p class="text-sm font-medium text-gray-800 dark:text-slate-300">
                            {{ formState.contact_number }}
                        </p>
                    </div>
                </div>

                <!-- Members of Party -->
                <div
                    v-if="formState.members_of_party?.length"
                    class="rounded-xl border border-gray-100 bg-white p-4 dark:border-slate-700/50 dark:bg-slate-800">
                    <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                        <LuUsers class="h-4 w-4" />
                        Members of Party ({{ formState.members_of_party.length }})
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="(member, index) in formState.members_of_party"
                            :key="index"
                            class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">
                            {{ member }}
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex w-full flex-col rounded-lg border-gray-200 bg-gray-100 dark:border-slate-700 dark:bg-gray-800 dark:bg-slate-900">
                <!-- Reviewer Form Section -->
                <div
                    v-if="canApprove"
                    class="border-b border-gray-100 bg-gray-50/50 p-6 dark:border-slate-800/50 dark:bg-slate-800/20">
                    <div class="mx-auto flex max-w-2xl flex-col gap-4">
                        <custom-dropdown
                            label="Assign Vehicle"
                            required
                            searchable
                            :withAllOption="false"
                            :show-valid-indicator="false"
                            :options="vehicleOptions"
                            @selectedChange="formState.vehicle_type = $event"
                            :value="formState.vehicle_type"
                            class="w-full">
                            <template #icon="{ open }">
                                <LuChevronRight
                                    :class="open ? 'rotate-90' : ''"
                                    class="h-4 w-4 transition-transform duration-300" />
                            </template>
                        </custom-dropdown>

                        <text-area
                            v-model="formState.notes"
                            :rows="3"
                            label="Reviewer Notes (Optional)"
                            class="w-full rounded-xl border-gray-300 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="Add internal notes about this decision..." />
                    </div>
                </div>

                <div
                    v-else-if="formState.notes"
                    class="border-b border-gray-100 bg-amber-50/50 p-6 dark:border-slate-800/50 dark:bg-amber-900/10">
                    <p class="mb-1.5 text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-500">Reviewer Notes</p>
                    <p class="text-sm font-medium text-gray-800 dark:text-slate-300">
                        {{ formState.notes }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div
                    v-if="canApprove"
                    class="flex flex-col items-center justify-end gap-3 px-6 py-5 sm:flex-row">
                    <button
                        @click="updateStatus('cancelled')"
                        :disabled="['cancelled', 'completed'].includes(formState.status) || processing"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:focus:ring-offset-slate-900">
                        <LuXCircle class="h-4 w-4" />
                        Cancel Request
                    </button>

                    <button
                        @click="updateStatus('rejected')"
                        :disabled="['rejected', 'completed'].includes(formState.status) || processing"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold shadow-sm transition-all focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:focus:ring-offset-slate-900"
                        :class="formState.status === 'rejected' ? 'border border-transparent bg-gray-100 text-gray-400 dark:bg-slate-800 dark:text-slate-500' : 'border border-red-200 bg-white text-red-600 hover:border-red-300 hover:bg-red-50 dark:border-red-900/50 dark:bg-slate-800 dark:text-red-400 dark:hover:border-red-800 dark:hover:bg-red-900/20'">
                        <LuXCircle
                            v-if="!processing"
                            class="h-4 w-4" />
                        <span v-if="formState.status === 'rejected'">Rejected</span>
                        <span v-else>Reject</span>
                    </button>

                    <button
                        @click="updateStatus('approved')"
                        :disabled="['approved', 'in_progress', 'completed'].includes(formState.status) || processing"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold shadow-md transition-all focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:focus:ring-offset-slate-900"
                        :class="formState.status === 'approved' ? 'border border-transparent bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-emerald-600 text-white shadow-emerald-600/20 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600'">
                        <LuLoader2
                            v-if="processing"
                            class="h-4 w-4 animate-spin" />
                        <LuCheckCircle2
                            v-else
                            class="h-4 w-4" />
                        <span v-if="formState.status === 'approved'">Approved</span>
                        <span v-else>Approve & Assign</span>
                    </button>
                </div>

                <div
                    v-else
                    class="flex items-center justify-center border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-slate-400">You don't have permission to modify this request</p>
                </div>
            </div>
        </template>
    </DialogModal>
</template>
