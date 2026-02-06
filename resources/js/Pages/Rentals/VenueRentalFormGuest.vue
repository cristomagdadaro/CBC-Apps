<script>
export default {
    name: 'VenueRentalFormGuest',
    props: {
        title: {
            type: String,
            default: 'Venue Rental Form'
        }
    },
    emits: ['submitted'],
    data() {
        return {
            form: {
                venue_type: '',
                date_from: '',
                date_to: '',
                time_from: '08:00',
                time_to: '17:00',
                expected_attendees: '',
                event_name: '',
                requested_by: '',
                contact_number: '',
                notes: '',
            },
            errors: {},
            loading: false,
            availabilityChecking: false,
            isAvailable: true,
            availabilityMessage: '',
            venueOptions: [
                { name: 'plenary', label: 'Plenary Hall' },
                { name: 'training_room', label: 'Training Room' },
                { name: 'mph', label: 'Multi-Purpose Hall' }
            ],
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
                const response = await fetch(`/api/rental-venues/check-availability/${this.form.venue_type}/${this.form.date_from}/${this.form.date_to}`);
                const data = await response.json();
                this.isAvailable = data.available;
                this.availabilityMessage = data.available
                    ? 'Venue is available for your selected dates'
                    : 'Venue is not available for your selected dates';
            } catch (error) {
                this.availabilityMessage = 'Error checking availability';
            } finally {
                this.availabilityChecking = false;
            }
        },
        handleDateChange() {
            this.checkAvailability();
        },
        clearErrors(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },
        async submitForm() {
            if (!this.isAvailable) {
                this.errors.general = 'Please select available dates';
                return;
            }

            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('/api/rental-venues', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = data.errors || {};
                    } else if (response.status === 409) {
                        this.errors.general = data.message;
                    } else {
                        this.errors.general = data.message || 'An error occurred';
                    }
                    return;
                }

                // Success
                this.$emit('submitted', data.data);
                this.resetForm();
            } catch (error) {
                this.errors.general = 'Failed to submit form. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        resetForm() {
            this.form = {
                venue_type: '',
                date_from: '',
                date_to: '',
                time_from: '08:00',
                time_to: '17:00',
                expected_attendees: '',
                event_name: '',
                requested_by: '',
                contact_number: '',
                notes: '',
            };
            this.isAvailable = true;
            this.availabilityMessage = '';
        }
    }
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <div class="pb-6 border-b">
                    <h1 class="text-3xl font-bold text-gray-800">{{ title }}</h1>
                    <p class="text-gray-600 text-sm mt-2">Book a venue for your event. Fields marked with <span class="text-red-600">*</span> are required.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6 mt-6">
                    <!-- General Error -->
                    <div v-if="errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {{ errors.general }}
                    </div>

                    <!-- Venue Type -->
                    <div>
                        <InputLabel for="venue_type" value="Venue Type *" />
                        <select
                            id="venue_type"
                            v-model="form.venue_type"
                            @change="handleDateChange"
                            :class="{'border-red-500': errors.venue_type}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                        >
                            <option value="">Select a venue</option>
                            <option v-for="venue in venueOptions" :key="venue.name" :value="venue.name">
                                {{ venue.label }}
                            </option>
                        </select>
                        <InputError :message="errors.venue_type?.[0]" class="mt-2" />
                    </div>

                    <!-- Event Name -->
                    <div>
                        <InputLabel for="event_name" value="Event Name *" />
                        <TextInput
                            id="event_name"
                            v-model="form.event_name"
                            type="text"
                            placeholder="Name of your event"
                            :error="errors.event_name?.[0]"
                            @input="clearErrors('event_name')"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.event_name?.[0]" class="mt-2" />
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="date_from" value="Start Date *" />
                            <TextInput
                                id="date_from"
                                v-model="form.date_from"
                                type="date"
                                :min="minDate"
                                @change="handleDateChange"
                                :error="errors.date_from?.[0]"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="errors.date_from?.[0]" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="date_to" value="End Date *" />
                            <TextInput
                                id="date_to"
                                v-model="form.date_to"
                                type="date"
                                :min="form.date_from || minDate"
                                @change="handleDateChange"
                                :error="errors.date_to?.[0]"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="errors.date_to?.[0]" class="mt-2" />
                        </div>
                    </div>

                    <!-- Availability Status -->
                    <div v-if="form.date_from && form.date_to && form.venue_type" class="p-4 rounded-lg" :class="isAvailable ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                        <div class="flex items-center gap-2">
                            <loader-icon v-if="availabilityChecking" class="text-AB" />
                            <span :class="isAvailable ? 'text-green-700' : 'text-red-700'">
                                {{ availabilityMessage || 'Checking availability...' }}
                            </span>
                        </div>
                    </div>

                    <!-- Time Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="time_from" value="Start Time *" />
                            <TextInput
                                id="time_from"
                                v-model="form.time_from"
                                type="time"
                                :error="errors.time_from?.[0]"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="errors.time_from?.[0]" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="time_to" value="End Time *" />
                            <TextInput
                                id="time_to"
                                v-model="form.time_to"
                                type="time"
                                :error="errors.time_to?.[0]"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="errors.time_to?.[0]" class="mt-2" />
                        </div>
                    </div>

                    <!-- Expected Attendees -->
                    <div>
                        <InputLabel for="expected_attendees" value="Expected Attendees *" />
                        <TextInput
                            id="expected_attendees"
                            v-model.number="form.expected_attendees"
                            type="number"
                            min="1"
                            placeholder="Number of attendees"
                            :error="errors.expected_attendees?.[0]"
                            @input="clearErrors('expected_attendees')"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.expected_attendees?.[0]" class="mt-2" />
                    </div>

                    <!-- Requested By -->
                    <div>
                        <InputLabel for="requested_by" value="Your Name *" />
                        <TextInput
                            id="requested_by"
                            v-model="form.requested_by"
                            type="text"
                            placeholder="Full name"
                            :error="errors.requested_by?.[0]"
                            @input="clearErrors('requested_by')"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.requested_by?.[0]" class="mt-2" />
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <InputLabel for="contact_number" value="Contact Number *" />
                        <TextInput
                            id="contact_number"
                            v-model="form.contact_number"
                            type="tel"
                            placeholder="09XX-XXX-XXXX"
                            :error="errors.contact_number?.[0]"
                            @input="clearErrors('contact_number')"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="errors.contact_number?.[0]" class="mt-2" />
                    </div>

                    <!-- Notes -->
                    <div>
                        <InputLabel for="notes" value="Additional Notes (Optional)" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            placeholder="Any additional information or special requirements"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                            rows="2"
                        ></textarea>
                        <InputError :message="errors.notes?.[0]" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 pt-6 border-t">
                        <PrimaryButton :disabled="loading || !isAvailable" class="flex-1">
                            <span v-if="loading" class="flex items-center justify-center gap-2">
                                <loader-icon />
                                Submitting...
                            </span>
                            <span v-else>Submit Venue Rental Request</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
