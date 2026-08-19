<script>
import RentalVenue from "@/Modules/domain/RentalVenue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import CalendarModule from "@/Components/CalendarModule.vue";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

export default {
    name: "VenueRentalForm",
    components: {
        CalendarModule,
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
                in_progress: "#3B82F6",
                rejected: "#EF4444",
                cancelled: "#6B7280",
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
                color: "#6B7280",
            }));
        },
        isGuestContext() {
            return !this.$page?.props?.auth?.user?.id;
        },
    },
    watch: {
        'form.destination_region'(value) {
            if (!this.form) return;
            if (!value) {
                this.form.destination_province = null;
                this.form.destination_city = null;
                return;
            }

            this.form.destination_province = null;
            this.form.destination_city = null;
        },
        'form.destination_province'(value) {
            if (!this.form) return;
            if (!value) {
                this.form.destination_city = null;
                return;
            }

            this.form.destination_city = null;
        },
    },
    methods: {
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
                    channel: this.isGuestContext ? "public.rentals.calendar" : "rentals.calendar",
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
                        statuses: "pending,approved,in_progress,rejected,cancelled,completed",
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
        <div
            v-if="form"
            data-guide='rental-form-shell'
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border border-gray-100 dark:border-slate-800 p-1 rounded-2xl flex gap-2 shadow-xl h-fit lg:col-span-1"
        >
            <form
                @submit.prevent="submitProxyCreate"
                class="space-y-4 bg-white/60 dark:bg-slate-800/60 rounded-xl p-5 w-full h-fit"
            >
                <!-- General Error -->
                <div
                    v-if="form.errors.general"
                    class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm"
                >
                    {{ form.errors.general }}
                </div>
                <div
                    v-if="form.date_from && form.date_to && form.venue_type"
                    class="px-4 py-3 rounded-xl transition-colors duration-300"
                    :class="
                        isAvailable
                            ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50'
                            : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50'
                    "
                >
                    <div class="flex items-center gap-2">
                        <loader-icon
                            v-if="availabilityChecking"
                            class="text-AB dark:text-emerald-400 animate-spin w-4 h-4"
                        />
                        <span
                            class="text-sm font-medium"
                            :class="
                                isAvailable ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'
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
                <custom-dropdown
                    label="Venue Type"
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
                >
                    <template #icon>
                        <caret-down class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </template>
                </custom-dropdown>

                <!-- Event Name -->
                <TextInput
                    id="event_name"
                    label="Event Name"
                    required
                    v-model="form.event_name"
                    type="text"
                    placeholder="Name of your event"
                    :error="form.errors.event_name"
                    class="mt-1 block w-full"
                />



                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <DateInput
                        id="date_from"
                        label="Start Date"
                        required
                        v-model="form.date_from"
                        :min="minDate"
                        @change="handleDateChange"
                        :error="form.errors.date_from"
                    />
                    <TimeInput
                        id="time_from"
                        label="Start Time"
                        required
                        v-model="form.time_from"
                        @change="handleDateChange"
                        :error="form.errors.time_from"
                        class="mt-1 block w-full"
                    />
                </div>

                <!-- Time Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <DateInput
                        id="date_to"
                        label="End Date"
                        required
                        v-model="form.date_to"
                        type="date"
                        :min="form.date_from || minDate"
                        @change="handleDateChange"
                        :error="form.errors.date_to"
                    />
                    <TimeInput
                        id="time_to"
                        label="End Time"
                        required
                        v-model="form.time_to"
                        @change="handleDateChange"
                        :error="form.errors.time_to"
                        class="mt-1 block w-full"
                    />
                </div>

                <!-- Expected Attendees -->
                <TextInput
                    id="expected_attendees"
                    label="Expected Attendees"
                    required
                    v-model.number="form.expected_attendees"
                    type="number"
                    min="1"
                    placeholder="Number of attendees"
                    :error="form.errors.expected_attendees"
                    class="mt-1 block w-full"
                />

                <!-- Requested By -->
                <PersonnelLookup
                    v-model="employee_id"
                    @found="handlePersonnelFound"
                />
                <TextInput
                    id="requested_by"
                    label="Your Name"
                    required
                    v-model="form.requested_by"
                    type="text"
                    placeholder="Full name"
                    :error="form.errors.requested_by"
                    class="mt-1 block w-full"
                />

                <!-- Contact Number -->
                <TextInput
                    id="contact_number"
                    label="Contact Number"
                    required
                    v-model="form.contact_number"
                    type="tel"
                    placeholder="09XX-XXX-XXXX"
                    :error="form.errors.contact_number"
                    class="mt-1 block w-full"
                />

                <!-- Notes -->
                <TextArea
                    id="notes"
                    label="Additional Notes"
                    v-model="form.notes"
                    placeholder="Any additional information"
                    class="mt-1 block w-full rounded-xl border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-AB focus:ring-AB"
                ></TextArea>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-6 border-t border-gray-100 dark:border-slate-700">
                    <PrimaryButton
                        :disabled="processing || !isAvailable"
                        class="justify-center flex-1 rounded-xl shadow-md hover:-translate-y-0.5 transition-all duration-300"
                    >
                        <span
                            v-if="processing"
                            class="flex items-center justify-center gap-2"
                        >
                            <loader-icon class="animate-spin w-4 h-4" />
                            Submitting...
                        </span>
                        <span v-else>Submit Venue Rental Request</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border border-gray-100 dark:border-slate-800 p-6 rounded-2xl shadow-xl lg:col-span-3 h-fit flex flex-col gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                    Venue Availability Calendar
                </h3>
                <p class="text-sm text-gray-500 dark:text-slate-400">
                    Check current venue workflow states before submitting.
                </p>
            </div>
            <div v-if="calendarLoading" class="text-sm text-gray-500 dark:text-slate-400 flex items-center gap-2 justify-center py-10">
                <loader-icon class="w-6 h-6 text-AB dark:text-emerald-400 animate-spin" />
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
</template>
