<script>
import RentalVenue from "@/Modules/domain/RentalVenue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import CalendarModule from "@/Components/CalendarModule.vue";

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
                rejected: "#EF4444",
            };
        },
        statusOptions() {
            return [
                { key: "pending", label: "Pending" },
                { key: "approved", label: "Approved" },
                { key: "rejected", label: "Declined" },
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
            this.form.requested_by = data.fullName;
            this.form.contact_number = data.phone;
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
                label: rental.event_name || rental.venue_type,
                subtitle: rental.requested_by || "",
                type: rental.venue_type || "venue",
                status: rental.status || "pending",
                date_from: rental.date_from,
                date_to: rental.date_to,
            }));
        },
        async loadCalendarEvents() {
            this.calendarLoading = true;

            try {
                const response = await this.fetchGetApi(
                    this.routeNameFor("index"),
                    {
                        statuses: "pending,approved,rejected",
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
    <div class="grid grid-cols-4 gap-6">
        <div
            v-if="form"
            class="bg-white s p-2 rounded-md flex gap-2 drop-shadow-lg h-fit"
        >
            <form
                @submit.prevent="submitProxyCreate"
                class="space-y-3 bg-white rounded-lg p-3 w-full h-fit"
            >
                <!-- General Error -->
                <div
                    v-if="form.errors.general"
                    class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"
                >
                    {{ form.errors.general }}
                </div>
                <div
                    v-if="form.date_from && form.date_to && form.venue_type"
                    class="px-4 py-2 rounded-md"
                    :class="
                        isAvailable
                            ? 'bg-green-50 border border-green-200'
                            : 'bg-red-50 border border-red-200'
                    "
                >
                    <div class="flex items-center gap-2">
                        <loader-icon
                            v-if="availabilityChecking"
                            class="text-AB"
                        />
                        <span
                            :class="
                                isAvailable ? 'text-green-700' : 'text-red-700'
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
                        <caret-down class="h-4 w-4 text-gray-600" />
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Destination Region" required />
                        <SelectRegion
                            v-model="form.destination_region"
                            :error="form.errors.destination_region"
                            @update:modelValue="handleDestinationRegionChange"
                        />
                    </div>
                    <div>
                        <InputLabel value="Destination Province" required />
                        <SelectProvince
                            v-model="form.destination_province"
                            :region="form.destination_region"
                            :disabled="!form.destination_region"
                            :error="form.errors.destination_province"
                            @update:modelValue="handleDestinationProvinceChange"
                        />
                    </div>
                    <div>
                        <InputLabel value="Destination City" required />
                        <SelectCity
                            v-model="form.destination_city"
                            :region="form.destination_region"
                            :province="form.destination_province"
                            :disabled="!form.destination_province"
                            :error="form.errors.destination_city"
                        />
                    </div>
                </div>

                <TextInput
                    id="destination_location"
                    label="Destination Location"
                    required
                    v-model="form.destination_location"
                    type="text"
                    placeholder="Specific destination / address"
                    :error="form.errors.destination_location"
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
                </div>

                <!-- Time Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <TimeInput
                        id="time_from"
                        label="Start Time"
                        required
                        v-model="form.time_from"
                        @change="handleDateChange"
                        :error="form.errors.time_from"
                        class="mt-1 block w-full"
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                ></TextArea>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-6 border-t">
                    <PrimaryButton
                        :disabled="processing || !isAvailable"
                        class="justify-center flex-1"
                    >
                        <span
                            v-if="processing"
                            class="flex items-center justify-center gap-2"
                        >
                            <loader-icon />
                            Submitting...
                        </span>
                        <span v-else>Submit Venue Rental Request</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
        <div class="bg-white p-4 rounded-lg shadow col-span-3">
            <h3 class="text-base font-semibold text-gray-900 mb-2">
                Venue Availability Calendar
            </h3>
            <p class="text-sm text-gray-600 mb-3">
                Check pending, approved, and declined venue requests before
                submitting.
            </p>
            <div v-if="calendarLoading" class="text-sm text-gray-500 flex items-center gap-2 justify-center">
                <loader-icon class="w-6 h-6 text-gray-500 animate-spin" />
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
            />
        </div>
    </div>
</template>
