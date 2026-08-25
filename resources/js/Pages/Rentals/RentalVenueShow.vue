<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    name: "RentalVenueShow",
    mixins: [ApiMixin],
    props: {
        rental_id: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            loading: true,
            error: "",
            rental: null,
        };
    },
    computed: {
        canViewContactNumber() {
            return Boolean(this.$page.props.auth?.user && this.rental?.contact_number);
        },
        statusConfig() {
            const configs = {
                pending: {
                    color: "bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800",
                    icon: "LuClock3",
                    label: "Pending Approval",
                    accent: "amber",
                },
                approved: {
                    color: "bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800",
                    icon: "LuCheckCircle2",
                    label: "Approved",
                    accent: "emerald",
                },
                in_progress: {
                    color: "bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800",
                    icon: "LuClock",
                    label: "In Progress",
                    accent: "emerald",
                },
                rejected: {
                    color: "bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-800",
                    icon: "LuXCircle",
                    label: "Rejected",
                    accent: "red",
                },
                completed: {
                    color: "bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700",
                    icon: "LuCheckCircle2",
                    label: "Event Completed",
                    accent: "slate",
                },
                cancelled: {
                    color: "bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700",
                    icon: "LuXCircle",
                    label: "Cancelled",
                    accent: "gray",
                },
            };

            const status = this.rental?.status?.toLowerCase();

            return (
                configs[status] || {
                    color: "bg-gray-50 text-gray-800 border-gray-200",
                    icon: "LuAlertCircle",
                    label: this.rental?.status || "Unknown",
                    accent: "gray",
                }
            );
        },
        formatDuration() {
            if (!this.rental?.date_from || !this.rental?.date_to) return null;

            const start = new Date(this.rental.date_from);
            const end = new Date(this.rental.date_to);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            return diffDays === 1 ? "1 day" : `${diffDays} days`;
        },
    },
    mounted() {
        this.loadRental();
    },
    methods: {
        formatDateTime(date, time) {
            if (!date) return "Not specified";

            const dateObj = new Date(date);
            const formatted = dateObj.toLocaleDateString("en-US", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
            });

            return time ? `${formatted} at ${time}` : formatted;
        },
        async loadRental() {
            this.loading = true;
            this.error = "";

            try {
                const payload = await this.fetchGetApi("api.guest.rental.venues.show", {
                    routeParams: this.rental_id,
                });
                this.rental = payload?.data ?? null;
            } catch (err) {
                this.error = err?.message || "Failed to load venue rental details.";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <Head title="Venue Rental Details" />

    <GuestFormPage
        title="Venue Rental Details"
        subtitle="View your event booking details and status."
        guide-key="rental-venue-detail"
        :delay-ready="true"
        max-width="max-w-4xl">
        <!-- Loading State -->
        <div
            v-if="loading"
            class="rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg p-8 shadow-sm">
            <div class="flex flex-col items-center justify-center space-y-4 py-12">
                <LuLoader2 class="h-10 w-10 animate-spin text-indigo-600 dark:text-indigo-400" />
                <div class="text-center">
                    <p class="text-sm font-medium text-slate-900 dark:text-white">Loading venue details...</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Please wait a moment</p>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="rounded-2xl border border-red-200 dark:border-red-900/50 bg-red-50/50 dark:bg-red-900/20 backdrop-blur-lg p-8">
            <div class="flex flex-col items-center justify-center space-y-4 py-8 text-center">
                <div class="rounded-full bg-red-100 dark:bg-red-900/50 p-4 ring-4 ring-red-50 dark:ring-red-900/20">
                    <LuAlertCircle class="h-8 w-8 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-200">Unable to Load Details</h3>
                    <p class="mt-1 max-w-sm text-sm text-red-700 dark:text-red-300">{{ error }}</p>
                </div>
                <button
                    @click="loadRental"
                    class="mt-2 inline-flex items-center gap-2 rounded-lg bg-red-600 dark:bg-red-700 px-5 py-2.5 text-sm font-medium text-white transition-all hover:bg-red-700 dark:hover:bg-red-600 hover:shadow-lg hover:shadow-red-600/20 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:scale-95">
                    Try Again
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="!rental"
            class="rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg p-8 shadow-sm">
            <div class="flex flex-col items-center justify-center space-y-4 py-12 text-center">
                <div class="rounded-full bg-slate-100 dark:bg-slate-800 p-4">
                    <LuBuilding2 class="h-8 w-8 text-slate-400 dark:text-slate-500" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Booking Not Found</h3>
                    <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">The venue rental you're looking for doesn't exist or may have been removed from our system.</p>
                </div>
            </div>
        </div>

        <!-- Content State -->
        <div
            v-else
            data-guide="rental-details"
            class="space-y-6">
            <!-- Status Banner -->
            <div :class="['rounded-2xl border-2 p-4 sm:p-6 backdrop-blur-lg', statusConfig.color]">
                <div class="flex items-center justify-between gap-3 sm:gap-5">
                    <!-- Event Header -->
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <div class="rounded-xl bg-blue-600 dark:bg-blue-500 p-2 sm:p-3 text-white shadow-lg shadow-blue-600/20 shrink-0">
                            <LuBuilding2 class="h-5 w-5 sm:h-6 sm:w-6" />
                        </div>
                        <div class="leading-tight">
                            <h2 class="text-sm sm:text-lg font-bold">
                                {{ rental.event_name || "Venue booking" }}
                            </h2>
                            <p class="text-xs sm:text-sm opacity-80 uppercase tracking-wider mt-0.5">
                                {{ rental.venue_type_label || rental.venue_type || "Venue" }}
                                <span
                                    v-if="formatDuration"
                                    class="normal-case opacity-90">
                                    &bull; {{ formatDuration }} duration
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
                        <component
                            :is="statusConfig.icon"
                            class="h-5 w-5 sm:h-6 sm:w-6" />
                        <div class="leading-tight hidden sm:block">
                            <p class="text-base sm:text-lg font-bold">{{ statusConfig.label }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Booking Status</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Card -->
            <div class="overflow-hidden rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border border-gray-100 dark:border-slate-800 shadow-sm">
                <div class="p-6">
                    <!-- Date & Time Section -->
                    <div class="mb-8">
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <LuCalendar class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                            Event Schedule
                        </h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="group relative overflow-hidden rounded-xl border border-gray-100 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-800/50 p-5 transition-all hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:shadow-md">
                                <div class="absolute -right-4 -top-4 rounded-full bg-indigo-100 dark:bg-indigo-900/50 p-3 opacity-0 transition-opacity group-hover:opacity-100">
                                    <LuCalendar class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="relative">
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Start Date & Time</p>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ formatDateTime(rental.date_from, rental.time_from) }}
                                    </p>
                                </div>
                            </div>

                            <div class="group relative overflow-hidden rounded-xl border border-gray-100 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-800/50 p-5 transition-all hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:shadow-md">
                                <div class="absolute -right-4 -top-4 rounded-full bg-indigo-100 dark:bg-indigo-900/50 p-3 opacity-0 transition-opacity group-hover:opacity-100">
                                    <LuClock class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="relative">
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">End Date & Time</p>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ formatDateTime(rental.date_to, rental.time_to) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid gap-8 lg:grid-cols-2">
                        <div class="space-y-4">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                                <LuUsers class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                Booking Snapshot
                            </h3>
                            <div class="rounded-xl bg-indigo-50/50 dark:bg-indigo-900/20 border border-indigo-100/50 dark:border-indigo-900/30 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Booking Reference</p>
                                        <p class="mt-1 text-sm sm:text-base font-bold text-indigo-900 dark:text-indigo-200 font-mono tracking-widest">
                                            {{ rental.booking_id || "NULL" }}
                                        </p>
                                    </div>
                                    <div class="rounded-full bg-indigo-100 dark:bg-indigo-900/50 p-2 sm:p-3">
                                        <LuUsers class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                                <LuUser class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                Public Details
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 rounded-lg border border-gray-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 p-3">
                                    <LuBuilding2 class="mt-0.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Venue Type</p>
                                        <p class="font-medium text-slate-900 dark:text-white">
                                            {{ rental.venue_type_label || rental.venue_type || "Not specified" }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-lg border border-gray-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 p-3">
                                    <LuUsers class="mt-0.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Division / Organization</p>
                                        <p class="font-medium text-slate-900 dark:text-white">
                                            {{ rental.organization || "Not specified" }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-lg border border-gray-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 p-3">
                                    <LuClock3 class="mt-0.5 h-4 w-4 text-slate-400 dark:text-slate-500" />
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Current Status</p>
                                        <p class="font-medium text-slate-900 dark:text-white">
                                            {{ statusConfig.label }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 rounded-xl border border-indigo-100 dark:border-indigo-900/30 bg-indigo-50/50 dark:bg-indigo-900/10 p-5">
                        <div class="flex items-start gap-3">
                            <LuAlertCircle class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" />
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-800 dark:text-indigo-300">Privacy Notice</p>
                                <p class="mt-1 text-sm leading-relaxed text-indigo-900 dark:text-indigo-300/80">This public page only shows non-sensitive booking details. Contact the rentals team if you need the full internal request record.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestFormPage>
</template>
