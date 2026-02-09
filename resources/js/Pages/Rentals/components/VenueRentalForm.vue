<script>
import RentalVenue from "@/Modules/domain/RentalVenue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";

export default {
    name: 'VenueRentalForm',
    mixins: [ApiMixin, FormLocalMixin],
    props: {
        venueOptions: {
            type: Array,
            default: () => []
        }
    },
    beforeMount() {
        this.model = new RentalVenue();
        this.setFormAction('create');
    },
    data() {
        return {
            availabilityChecking: false,
            isAvailable: true,
            availabilityMessage: '',
            employee_id: null,
        };
    },
    computed: {
        minDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        }
    },
    methods: {
        async checkAvailability() {
            if (!this.form.venue_type || !this.form.date_from || !this.form.date_to) {
                return;
            }

            this.availabilityChecking = true;
            try {
                const response = await this.fetchGetApi('api.rental.venues.check-availability', { routeParams: {venueType: this.form.venue_type, dateFrom: this.form.date_from, dateTo: this.form.date_to} });
                this.isAvailable = response.available;
                this.availabilityMessage = response.message;
            } catch (error) {
                this.availabilityMessage = 'Error checking availability';
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
        async submitProxyCreate() {
            if (!this.isAvailable) {
                this.form.errors.general = 'Please select available dates';
                return;
            }

            const data = await this.submitCreate();
            
            if (data && data.error || data.status === 422 || data.status === 500) {
                this.form.errors.general = data.message || 'Failed to submit rental request';
                return;
            }
            
            this.successMessage = (data && data.message) ? data.message : 'Rental request submitted successfully';
            this.showSuccessModal = true;
            this.$emit('submitted', data.data);
        },
    }
};
</script>

<template>
    <SuccessModal
        :show="showSuccessModal"
        title="Success!"
        :message="successMessage"
        @close="showSuccessModal = false"
    />
    <div v-if="form" class="bg-white p-2 rounded-md flex flex-col gap-2 max-w-xl drop-shadow-lg lg:mx-0 my-4 md:mt-0">
        <form @submit.prevent="submitProxyCreate" class="space-y-3 bg-white rounded-lg p-3">
            <!-- General Error -->
            <div v-if="form.errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ form.errors.general }}
            </div>
            <div v-if="form.date_from && form.date_to && form.venue_type" class="px-4 py-2 rounded-md" :class="isAvailable ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex items-center gap-2">
                    <loader-icon v-if="availabilityChecking" class="text-AB" />
                    <span :class="isAvailable ? 'text-green-700' : 'text-red-700'">
                        {{ availabilityMessage || 'Checking availability...' }}
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
                :options="venueOptions.map(v => ({ name: v.name, label: v.label }))"
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
                <PrimaryButton :disabled="processing || !isAvailable" class="justify-center flex-1">
                    <span v-if="processing" class="flex items-center justify-center gap-2">
                        <loader-icon />
                        Submitting...
                    </span>
                    <span v-else>Submit Venue Rental Request</span>
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
