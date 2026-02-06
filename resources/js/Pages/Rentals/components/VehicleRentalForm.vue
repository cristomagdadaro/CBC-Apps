<script>
import RentalVehicle from '@/Modules/domain/RentalVehicle';
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
export default {
    name: 'VehicleRentalForm',
    mixins: [ApiMixin, FormLocalMixin],
    props: {
        title: {
            type: String,
            default: 'Vehicle Rental Form'
        },
        vehicleOptions: {
            type: Array,
            default: () => []
        }
    },
    beforeMount() {
        this.model = new RentalVehicle();
        this.setFormAction('create');
    },
    data() {
        return {
            submitted: false,
            availableVehicles: [],
            availabilityChecking: false,
            isAvailable: true,
            availabilityMessage: '',
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
            if (!this.form.vehicle_type || !this.form.date_from || !this.form.date_to) {
                return;
            }

            this.availabilityChecking = true;
            try {
                const response = await fetch(route(`rental-vehicles.check-availability`, { vehicle_type: this.form.vehicle_type, date_from: this.form.date_from, date_to: this.form.date_to }));
                const data = await response.json();
                this.isAvailable = data.available;
                this.availabilityMessage = data.available
                    ? 'Vehicle is available for your selected dates'
                    : 'Vehicle is not available for your selected dates';
            } catch (error) {
                this.availabilityMessage = 'Error checking availability';
            } finally {
                this.availabilityChecking = false;
            }
        },
        handleDateChange() {
            this.checkAvailability();
        },
        async submitProxyCreate() {
            if (!this.isAvailable) {
                this.form.errors.general = 'Please select available dates';
                return;
            }

            const data = await this.submitCreate();
            this.$emit('submitted', data.data);
        },
    }
};
</script>

<template>
    <SuccessModal
            :show="showSuccessModal"
            title="Request submitted"
            :message="successMessage"
            @close="showSuccessModal = false"
        />
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <div class="pb-6 border-b">
                    <h1 class="text-3xl font-bold text-gray-800">{{ title }}</h1>
                    <p class="text-gray-600 text-sm mt-2">Book a vehicle for your transportation needs. Fields marked with <span class="text-red-600">*</span> are required.</p>
                </div>

                <form @submit.prevent="submitProxyCreate" class="space-y-6 mt-6">
                    <!-- General Error -->
                    <div v-if="form.errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {{ form.errors.general }}
                    </div>

                    <!-- Vehicle Type -->
                    <div>
                        <InputLabel for="vehicle_type" value="Vehicle Type *" />
                        <select
                            id="vehicle_type"
                            v-model="form.vehicle_type"
                            @change="handleDateChange"
                            :class="{'border-red-500': form.errors.vehicle_type}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                        >
                            <option value="">Select a vehicle</option>
                            <option v-for="vehicle in vehicleOptions" :key="vehicle.name" :value="vehicle.name">
                                {{ vehicle.label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.vehicle_type?.[0]" class="mt-2" />
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="date_from" value="Start Date *" />
                            <DateInput
                                id="date_from"
                                v-model="form.date_from"
                                :min="minDate"
                                @change="handleDateChange"
                                :error="form.errors.date_from?.[0]"
                                class="mt-1 block w-full"
                            />
                        </div>
                        <div>
                            <InputLabel for="date_to" value="End Date *" />
                            <DateInput
                                id="date_to"
                                v-model="form.date_to"
                                type="date"
                                :min="form.date_from || minDate"
                                @change="handleDateChange"
                                :error="form.errors.date_to?.[0]"
                                class="mt-1 block w-full"
                            />
                        </div>
                    </div>

                    <!-- Availability Status -->
                    <div v-if="form.date_from && form.date_to && form.vehicle_type" class="p-4 rounded-lg" :class="isAvailable ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
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
                            <TimeInput
                                id="time_from"
                                v-model="form.time_from"
                                @change="handleDateChange"
                                :error="form.errors.time_from?.[0]"
                                class="mt-1 block w-full"
                            />
                        </div>

                        <div>
                            <TimeInput
                                id="time_to"
                                v-model="form.time_to"
                                @change="handleDateChange"
                                :error="form.errors.time_to?.[0]"
                                class="mt-1 block w-full"
                            />
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div>
                        <InputLabel for="purpose" value="Purpose *" />
                        <textarea
                            id="purpose"
                            v-model="form.purpose"
                            placeholder="Describe the purpose of your vehicle rental"
                            :class="{'border-red-500': form.errors.purpose}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                            rows="3"
                        ></textarea>
                        <InputError :message="form.errors.purpose?.[0]" class="mt-2" />
                    </div>

                    <!-- Requested By -->
                    <div>
                        <InputLabel for="requested_by" value="Your Name *" />
                        <TextInput
                            id="requested_by"
                            v-model="form.requested_by"
                            type="text"
                            placeholder="Full name"
                            :error="form.errors.requested_by?.[0]"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.requested_by?.[0]" class="mt-2" />
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <InputLabel for="contact_number" value="Contact Number *" />
                        <TextInput
                            id="contact_number"
                            v-model="form.contact_number"
                            type="tel"
                            placeholder="09XX-XXX-XXXX"
                            :error="form.errors.contact_number?.[0]"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.contact_number?.[0]" class="mt-2" />
                    </div>

                    <!-- Notes -->
                    <div>
                        <InputLabel for="notes" value="Additional Notes (Optional)" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            placeholder="Any additional information"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                            rows="2"
                        ></textarea>
                        <InputError :message="form.errors.notes?.[0]" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 pt-6 border-t">
                        <PrimaryButton :disabled="processing || !isAvailable" class="flex-1">
                            <span v-if="processing" class="flex items-center justify-center gap-2">
                                <loader-icon />
                                Submitting...
                            </span>
                            <span v-else>Submit Rental Request</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
</template>
