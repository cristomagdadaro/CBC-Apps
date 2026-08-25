<script>
import RentalVenue from "@/Modules/domain/RentalVenue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import CalendarModule from "@/Components/CalendarModule.vue";
import Modal from "@/Components/Modal.vue";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import {
    Loader2,
    Info,
    X,
    Building2,
    CalendarDays,
    CheckCircle2,
    ChevronDown,
} from "lucide-vue-next";

export default {
    name: "VenueRentalForm",
    components: {
        CalendarModule,
        Modal,
        Loader2,
        Info,
        X,
        Building2,
        CalendarDays,
        CheckCircle2,
        ChevronDown,
    },
    mixins: [ApiMixin, FormLocalMixin],
    props: {
        venueOptions: {
            type: Array,
            default: () => [],
        },
    },
    beforeMount() {
        this.model = new RentalVenue();
        this.setFormAction("create");
    },
    data() {
        return {
            availabilityChecking: false,
            isAvailable: true,
            availabilityMessage: "",
            employee_id: null,
            calendarLoading: false,
            calendarEvents: [],
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
            venueRates: [],
            showRatesModal: false,
        };
    },
    computed: {
        minDate() {
            const today = new Date();
            return today.toISOString().split("T")[0];
        },
        statusColors() {
            return {
                pending: "#FBBF24",
                approved: "#10B981",
                in_progress: "#6366F1",
                rejected: "#EF4444",
                cancelled: "#64748B",
                completed: "#334155",
            };
        },
        statusOptions() {
            return [
                { key: "pending", label: "Pending" },
                { key: "approved", label: "Approved" },
                { key: "in_progress", label: "In Progress" },
                { key: "rejected", label: "Rejected" },
                { key: "cancelled", label: "Cancelled" },
                { key: "completed", label: "Completed" },
            ];
        },
        venueTypeOptions() {
            return this.venueOptions.map((option) => ({
                key: option.name,
                label: option.label,
                color: "#64748B",
            }));
        },
        isGuestContext() {
            return !this.$page?.props?.auth?.user?.id;
        },
    },
    watch: {
        "form.destination_region"(value) {
            if (!this.form) return;
            if (!value) {
                this.form.destination_province = null;
                this.form.destination_city = null;
                return;
            }

            this.form.destination_province = null;
            this.form.destination_city = null;
        },
        "form.destination_province"(value) {
            if (!this.form) return;
            if (!value) {
                this.form.destination_city = null;
                return;
            }

            this.form.destination_city = null;
        },
    },
    methods: {
        formatRate(value) {
            if (value === null || value === undefined || value === "")
                return "";
            const num = Number(value);
            if (isNaN(num)) return value; // Return as-is if it's text (e.g. "free")

            return (
                "₱" +
                num.toLocaleString("en-US", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                })
            );
        },
        async fetchVenueRates() {
            try {
                const response = await this.fetchGetApi(
                    "api.guest.rental.venues.rates",
                );
                this.venueRates = response?.data || [];
            } catch (e) {
                console.error("Failed to fetch venue rates", e);
            }
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        configureRealtime() {
            this.cleanupRealtime();

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: this.isGuestContext ? "public" : "private",
                    channel: this.isGuestContext
                        ? "public.rentals.calendar"
                        : "rentals.calendar",
                    event: "rentals.calendar.changed",
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ]);
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                this.loadCalendarEvents();
            }, 400);
        },
        routeNameFor(type) {
            const routeMap = {
                index: "api.guest.rental.venues.index",
                create: this.isGuestContext
                    ? "api.guest.rental.venues.store"
                    : "api.rental.venues.store",
                checkAvailability: this.isGuestContext
                    ? "api.guest.rental.venues.check-availability"
                    : "api.rental.venues.check-availability",
            };

            return routeMap[type];
        },
        async checkAvailability() {
            if (
                !this.form.venue_type ||
                !this.form.date_from ||
                !this.form.date_to
            ) {
                return;
            }

            this.availabilityChecking = true;
            try {
                const response = await this.fetchGetApi(
                    this.routeNameFor("checkAvailability"),
                    {
                        routeParams: {
                            venueType: this.form.venue_type,
                            dateFrom: this.form.date_from,
                            dateTo: this.form.date_to,
                        },
                    },
                );
                this.isAvailable = response.available;
                this.availabilityMessage = response.message;
            } catch (error) {
                this.availabilityMessage = "Error checking availability";
            } finally {
                this.availabilityChecking = false;
            }
        },
        handleVenueTypeChange(value) {
            this.form.venue_type = value;
            this.checkAvailability();
        },
        handleDateChange() {
            this.checkAvailability();
        },
        handlePersonnelFound(data) {
            this.form.requested_by = data.fullName || this.form.requested_by;
            if (data.phone) {
                this.form.contact_number = data.phone;
            }
        },
        handleDestinationRegionChange(value) {
            this.form.destination_region = value;
        },
        handleDestinationProvinceChange(value) {
            this.form.destination_province = value;
        },
        normalizeCalendarEvents(rows = []) {
            return rows.map((rental) => ({
                id: rental.id,
                label: `${rental.venue_type || "Venue"} booking`,
                subtitle: rental.status || "",
                type: rental.venue_type || "venue",
                status: rental.status || "pending",
                date_from: rental.date_from,
                date_to: rental.date_to,
                checkoutPage: "rental.venue.show",
                checkoutPageId: rental.id,
                checkoutPageTarget: "_blank",
            }));
        },
        async loadCalendarEvents() {
            this.calendarLoading = true;

            try {
                const response = await this.fetchGetApi(
                    this.routeNameFor("index"),
                    {
                        statuses:
                            "pending,approved,in_progress,rejected,cancelled,completed",
                    },
                );

                const rows = Array.isArray(response?.data)
                    ? response.data
                    : Array.isArray(response)
                      ? response
                      : [];

                this.calendarEvents = this.normalizeCalendarEvents(rows);
            } catch (error) {
                this.calendarEvents = [];
            } finally {
                this.calendarLoading = false;
            }
        },
        async submitProxyCreate() {
            if (!this.isAvailable) {
                this.form.errors.general = "Please select available dates";
                return;
            }

            const data = this.isGuestContext
                ? await this.fetchPostApi(
                      this.routeNameFor("create"),
                      this.form.data(),
                  )
                : await this.submitCreate();

            if (
                (data && data.error) ||
                data.status === 422 ||
                data.status === 500
            ) {
                this.form.errors.general =
                    data.message || "Failed to submit rental request";
                return;
            }

            await this.loadCalendarEvents();

            this.successMessage =
                data && data.message
                    ? data.message
                    : "Rental request submitted successfully";
            this.showSuccessModal = true;
            this.$emit("submitted", data?.data?.data ?? data?.data ?? data);
        },
    },
    mounted() {
        this.loadCalendarEvents();
        this.configureRealtime();
        this.fetchVenueRates();
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
    },
};
</script>

<template>
    <SuccessModal
        :show="showSuccessModal"
        title="Success!"
        :message="successMessage"
        @close="showSuccessModal = false"
    />

    <div class="grid lg:grid-cols-4 gap-6">
        <!-- Left Column: Form -->
        <div
            v-if="form"
            data-guide="rental-form-shell"
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800 p-6 rounded-2xl shadow-sm h-fit lg:col-span-1"
        >
            <div
                class="flex items-center gap-3 pb-5 mb-5 border-b border-slate-100 dark:border-slate-800/60"
            >
                <div
                    class="p-2.5 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl shadow-sm shrink-0"
                >
                    <Building2
                        class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                    />
                </div>
                <div>
                    <p
                        class="text-[0.65rem] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-0.5"
                    >
                        Booking
                    </p>
                    <h2
                        class="text-lg font-bold text-slate-900 dark:text-white tracking-tight"
                    >
                        Venue Request
                    </h2>
                </div>
            </div>

            <form
                @submit.prevent="submitProxyCreate"
                class="space-y-5 w-full h-fit"
            >
                <!-- General Error -->
                <div
                    v-if="form.errors.general"
                    class="p-4 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 rounded-xl text-rose-700 dark:text-rose-400 text-xs font-bold shadow-sm"
                >
                    {{ form.errors.general }}
                </div>

                <!-- Availability Status -->
                <div
                    v-if="form.date_from && form.date_to && form.venue_type"
                    class="px-4 py-3.5 rounded-xl transition-all duration-300 shadow-sm border"
                    :class="
                        isAvailable
                            ? 'bg-emerald-50/70 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30'
                            : 'bg-rose-50/70 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30'
                    "
                >
                    <div class="flex items-center gap-2.5">
                        <Loader2
                            v-if="availabilityChecking"
                            class="text-indigo-500 animate-spin w-4 h-4"
                        />
                        <CheckCircle2
                            v-else-if="isAvailable"
                            class="text-emerald-600 dark:text-emerald-400 w-4 h-4"
                        />
                        <X
                            v-else
                            class="text-rose-600 dark:text-rose-400 w-4 h-4"
                        />

                        <span
                            class="text-[0.65rem] font-bold uppercase tracking-wider"
                            :class="
                                isAvailable
                                    ? 'text-emerald-700 dark:text-emerald-400'
                                    : 'text-rose-700 dark:text-rose-400'
                            "
                        >
                            {{
                                availabilityMessage ||
                                "Checking availability..."
                            }}
                        </span>
                    </div>
                </div>

                <!-- Venue Type -->
                <div class="space-y-2">
                    <custom-dropdown
                        required
                        placeholder="Select a venue"
                        @selectedChange="handleVenueTypeChange"
                        :value="form.venue_type"
                        :with-all-option="false"
                        :options="
                            venueOptions.map((v) => ({
                                name: v.name,
                                label: v.label,
                            }))
                        "
                        :error="form.errors.venue_type"
                        class="w-full"
                    >
                        <template #icon>
                            <ChevronDown
                                class="h-4 w-4 text-slate-400 dark:text-slate-500"
                            />
                        </template>
                    </custom-dropdown>

                    <div class="flex justify-end">
                        <button
                            v-if="venueRates && venueRates.length > 0"
                            type="button"
                            @click="showRatesModal = true"
                            class="inline-flex items-center gap-1 text-[0.65rem] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
                        >
                            <Info class="w-3.5 h-3.5" /> View Rates
                        </button>
                    </div>
                </div>

                <!-- Event Name -->
                <TextInput
                    id="event_name"
                    required
                    v-model="form.event_name"
                    type="text"
                    placeholder="Name of your event"
                    :error="form.errors.event_name"
                    class="block w-full"
                />

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <DateInput
                        id="date_from"
                        required
                        v-model="form.date_from"
                        :min="minDate"
                        @change="handleDateChange"
                        :error="form.errors.date_from"
                        class="block w-full"
                    />
                    <TimeInput
                        id="time_from"
                        required
                        v-model="form.time_from"
                        @change="handleDateChange"
                        :error="form.errors.time_from"
                        class="block w-full"
                    />
                </div>

                <!-- Time Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <DateInput
                        id="date_to"
                        required
                        v-model="form.date_to"
                        type="date"
                        :min="form.date_from || minDate"
                        @change="handleDateChange"
                        :error="form.errors.date_to"
                        class="block w-full"
                    />
                    <TimeInput
                        id="time_to"
                        required
                        v-model="form.time_to"
                        @change="handleDateChange"
                        :error="form.errors.time_to"
                        class="block w-full"
                    />
                </div>

                <!-- Expected Attendees -->
                <TextInput
                    id="expected_attendees"
                    required
                    v-model.number="form.expected_attendees"
                    type="number"
                    min="1"
                    placeholder="Number of attendees"
                    :error="form.errors.expected_attendees"
                    class="block w-full"
                />

                <!-- Requested By / Personnel Lookup -->
                <div class="space-y-5">
                    <!-- prettier-ignore -->
                    <PersonnelLookup v-model="employee_id" @found="handlePersonnelFound" />
                    <TextInput
                        id="requested_by"
                        required
                        v-model="form.requested_by"
                        type="text"
                        placeholder="Full name"
                        :error="form.errors.requested_by"
                        class="block w-full"
                    />
                </div>

                <!-- Division / Organization -->
                <TextInput
                    id="organization"
                    required
                    v-model="form.organization"
                    type="text"
                    placeholder="e.g. Crop Biotechnology Center"
                    :error="form.errors.organization"
                    class="block w-full"
                />

                <!-- Contact Number -->
                <TextInput
                    id="contact_number"
                    required
                    v-model="form.contact_number"
                    type="tel"
                    placeholder="09XX-XXX-XXXX"
                    :error="form.errors.contact_number"
                    class="block w-full"
                />

                <!-- Notes -->
                <TextArea
                    id="notes"
                    v-model="form.notes"
                    placeholder="Any additional information..."
                    class="block w-full"
                ></TextArea>

                <!-- Submit Button -->
                <div
                    class="pt-5 border-t border-slate-100 dark:border-slate-800/60 mt-6"
                >
                    <button
                        type="submit"
                        :disabled="processing || !isAvailable"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3.5 text-sm shadow-sm transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none"
                    >
                        <Loader2
                            v-if="processing"
                            class="animate-spin w-4 h-4"
                        />
                        <span>{{
                            processing
                                ? "Submitting..."
                                : "Submit Venue Request"
                        }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Calendar -->
        <div
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800 p-6 rounded-2xl shadow-sm lg:col-span-3 h-fit flex flex-col gap-4"
        >
            <div
                class="border-b border-slate-100 dark:border-slate-800/60 pb-4 flex items-center gap-3"
            >
                <div
                    class="p-2 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl shadow-sm shrink-0"
                >
                    <CalendarDays
                        class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                    />
                </div>
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white tracking-tight"
                    >
                        Venue Availability Calendar
                    </h3>
                    <p
                        class="text-xs font-medium text-slate-500 dark:text-slate-400"
                    >
                        Check current venue workflow states before submitting.
                    </p>
                </div>
            </div>

            <div
                v-if="calendarLoading"
                class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 flex items-center gap-2 justify-center py-16"
            >
                <Loader2 class="w-5 h-5 text-indigo-500 animate-spin" />
                Loading booking calendars...
            </div>

            <calendar-module
                v-else
                title="Venue Requests"
                :events="calendarEvents"
                :type-options="venueTypeOptions"
                :status-options="statusOptions"
                :status-colors="statusColors"
                :show-today="true"
                :show-type-filter="true"
                :show-status-filter="true"
                :show-stats="false"
                class="!bg-transparent !shadow-none !border-0"
            />
        </div>
    </div>

    <!-- Enhanced Pricing Modal -->
    <Modal
        :show="showRatesModal"
        maxWidth="5xl"
        @close="showRatesModal = false"
    >
        <div class="bg-white dark:bg-slate-900 max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div
                class="px-6 py-5 border-b border-slate-100 dark:border-slate-800/60 flex items-center justify-between bg-white dark:bg-slate-900 sticky top-0 z-10"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="p-2 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl"
                    >
                        <Building2
                            class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
                        />
                    </div>
                    <div>
                        <h2
                            class="text-base font-bold text-slate-900 dark:text-white tracking-tight"
                        >
                            DA-CBC Venue Rental Rates
                        </h2>
                        <p
                            class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400"
                        >
                            Disclaimer: These rates aren't updated in real-time,
                            kindly call 451 for confirmation. Thank you!
                        </p>
                    </div>
                </div>
                <button
                    @click="showRatesModal = false"
                    class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto bg-slate-50/50 dark:bg-slate-900">
                <div
                    class="overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm bg-white dark:bg-slate-800/50"
                >
                    <table
                        class="w-full text-left border-collapse whitespace-nowrap"
                    >
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th
                                    class="px-5 py-4 font-bold uppercase tracking-wider text-[0.65rem] text-slate-500 dark:text-slate-400 border-b border-slate-200/60 dark:border-slate-700/60 w-1/4"
                                >
                                    Venue
                                </th>
                                <th
                                    class="px-5 py-4 font-bold uppercase tracking-wider text-[0.65rem] text-slate-500 dark:text-slate-400 border-b border-slate-200/60 dark:border-slate-700/60 text-center w-24"
                                >
                                    Maximum Capacity
                                </th>
                                <th
                                    class="px-5 py-4 font-bold uppercase tracking-wider text-[0.65rem] text-slate-500 dark:text-slate-400 border-b border-slate-200/60 dark:border-slate-700/60"
                                >
                                    Outsider/Guest
                                </th>
                                <th
                                    class="px-5 py-4 font-bold uppercase tracking-wider text-[0.65rem] text-slate-500 dark:text-slate-400 border-b border-slate-200/60 dark:border-slate-700/60"
                                >
                                    Core Funds(20% only)
                                </th>
                                <th
                                    class="px-5 py-4 font-bold uppercase tracking-wider text-[0.65rem] text-slate-500 dark:text-slate-400 border-b border-slate-200/60 dark:border-slate-700/60"
                                >
                                    External/Trust Funds (50% only)
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-700/60"
                        >
                            <tr
                                v-for="(rate, i) in venueRates"
                                :key="i"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/80 transition-colors group"
                            >
                                <td class="px-5 py-5 align-top">
                                    <div
                                        class="font-bold text-sm text-slate-900 dark:text-white whitespace-normal leading-snug"
                                    >
                                        {{ rate.venue }}
                                    </div>
                                </td>
                                <td class="px-5 py-5 align-top text-center">
                                    <div
                                        class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono text-xs font-bold"
                                    >
                                        {{ rate.pax }}
                                    </div>
                                </td>

                                <!-- Outsider Column -->
                                <td class="px-5 py-5 align-top min-w-[220px]">
                                    <div class="space-y-2.5">
                                        <div
                                            v-if="rate.outsider.status"
                                            class="inline-flex px-2 py-1 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-[0.65rem] font-bold uppercase tracking-widest"
                                        >
                                            {{ rate.outsider.status }}
                                        </div>

                                        <div
                                            v-if="rate.outsider.weekday"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Weekday</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.outsider.weekday,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.outsider.weekend"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Weekend</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.outsider.weekend,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.outsider.half_day"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Half Day</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.outsider.half_day,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.outsider.per_day"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Day</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.outsider.per_day,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.outsider.per_hour"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Hour</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.outsider.per_hour,
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>

                                <!-- Internal Core Column -->
                                <td class="px-5 py-5 align-top min-w-[220px]">
                                    <div class="space-y-2.5">
                                        <div
                                            v-if="rate.internal_core_20.status"
                                            class="inline-flex px-2 py-1 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-[0.65rem] font-bold uppercase tracking-widest"
                                        >
                                            {{ rate.internal_core_20.status }}
                                        </div>

                                        <div
                                            v-if="rate.internal_core_20.weekday"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Weekday</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_core_20
                                                            .weekday,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.internal_core_20.weekend"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Weekend</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_core_20
                                                            .weekend,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="
                                                rate.internal_core_20.half_day
                                            "
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Half Day</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_core_20
                                                            .half_day,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.internal_core_20.per_day"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Day</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_core_20
                                                            .per_day,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="
                                                rate.internal_core_20.per_hour
                                            "
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Hour</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_core_20
                                                            .per_hour,
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>

                                <!-- Internal Ext/Trust Column -->
                                <td class="px-5 py-5 align-top min-w-[220px]">
                                    <div class="space-y-2.5">
                                        <div
                                            v-if="rate.internal_ext_50.status"
                                            class="inline-flex px-2 py-1 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-[0.65rem] font-bold uppercase tracking-widest"
                                        >
                                            {{ rate.internal_ext_50.status }}
                                        </div>

                                        <div
                                            v-if="rate.internal_ext_50.weekday"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Weekday</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_ext_50
                                                            .weekday,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.internal_ext_50.weekend"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Weekend</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_ext_50
                                                            .weekend,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.internal_ext_50.half_day"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Half Day</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_ext_50
                                                            .half_day,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.internal_ext_50.per_day"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Day</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_ext_50
                                                            .per_day,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            v-if="rate.internal_ext_50.per_hour"
                                            class="flex justify-between items-center text-xs"
                                        >
                                            <span
                                                class="text-slate-500 dark:text-slate-400 font-medium"
                                                >Hour</span
                                            >
                                            <span
                                                class="font-mono font-bold text-slate-900 dark:text-slate-200"
                                                >{{
                                                    formatRate(
                                                        rate.internal_ext_50
                                                            .per_hour,
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="px-6 py-4 border-t border-slate-100 dark:border-slate-800/60 bg-white dark:bg-slate-900 flex justify-end"
            >
                <button
                    type="button"
                    @click="showRatesModal = false"
                    class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition-all"
                >
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>
