<script setup>
import { ref, computed } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LoaderIcon from '@/Components/Icons/LoaderIcon.vue';

defineProps({
    title: {
        type: String,
        default: 'Vehicle Rental Form'
    }
});

defineEmits(['submitted']);

const form = ref({
    vehicle_type: '',
    date_from: '',
    date_to: '',
    time_from: '08:00',
    time_to: '17:00',
    purpose: '',
    requested_by: '',
    contact_number: '',
    notes: '',
});

const errors = ref({});
const loading = ref(false);
const submitted = ref(false);
const availableVehicles = ref([]);
const availabilityChecking = ref(false);

const vehicleOptions = [
    { name: 'innova', label: 'Innova' },
    { name: 'pickup', label: 'Pickup Truck' },
    { name: 'van', label: 'Van' },
    { name: 'suv', label: 'SUV' }
];

const isAvailable = ref(true);
const availabilityMessage = ref('');

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const checkAvailability = async () => {
    if (!form.value.vehicle_type || !form.value.date_from || !form.value.date_to) {
        return;
    }

    availabilityChecking.value = true;
    try {
        const response = await fetch(`/api/rental-vehicles/check-availability/${form.value.vehicle_type}/${form.value.date_from}/${form.value.date_to}`);
        const data = await response.json();
        isAvailable.value = data.available;
        availabilityMessage.value = data.available
            ? 'Vehicle is available for your selected dates'
            : 'Vehicle is not available for your selected dates';
    } catch (error) {
        availabilityMessage.value = 'Error checking availability';
    } finally {
        availabilityChecking.value = false;
    }
};

const handleDateChange = () => {
    checkAvailability();
};

const submitForm = async () => {
    if (!isAvailable.value) {
        errors.value.general = 'Please select available dates';
        return;
    }

    loading.value = true;
    submitted.value = true;
    errors.value = {};

    try {
        const response = await fetch('/api/rental-vehicles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.value)
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                errors.value = data.errors || {};
            } else if (response.status === 409) {
                errors.value.general = data.message;
            } else {
                errors.value.general = data.message || 'An error occurred';
            }
            return;
        }

        // Success
        submitted.value = false;
        form.value = {
            vehicle_type: '',
            date_from: '',
            date_to: '',
            time_from: '08:00',
            time_to: '17:00',
            purpose: '',
            requested_by: '',
            contact_number: '',
            notes: '',
        };
        isAvailable.value = true;
        availabilityMessage.value = '';
    } catch (error) {
        errors.value.general = 'Failed to submit form. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ title }}</h1>
                <p class="text-gray-600 mb-6">Book a vehicle for your transportation needs</p>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- General Error -->
                    <div v-if="errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {{ errors.general }}
                    </div>

                    <!-- Vehicle Type -->
                    <div>
                        <InputLabel for="vehicle_type" value="Vehicle Type *" />
                        <select
                            id="vehicle_type"
                            v-model="form.vehicle_type"
                            @change="handleDateChange"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                        >
                            <option value="">Select a vehicle</option>
                            <option v-for="vehicle in vehicleOptions" :key="vehicle.name" :value="vehicle.name">
                                {{ vehicle.label }}
                            </option>
                        </select>
                        <InputError :message="errors.vehicle_type?.[0]" class="mt-2" />
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
                                class="mt-1 block w-full"
                            />
                            <InputError :message="errors.date_to?.[0]" class="mt-2" />
                        </div>
                    </div>

                    <!-- Availability Status -->
                    <div v-if="form.date_from && form.date_to && form.vehicle_type" class="p-4 rounded-lg" :class="isAvailable ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                        <div class="flex items-center gap-2">
                            <LoaderIcon v-if="availabilityChecking" class="text-AB" />
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
                                class="mt-1 block w-full"
                            />
                            <InputError :message="errors.time_to?.[0]" class="mt-2" />
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div>
                        <InputLabel for="purpose" value="Purpose *" />
                        <textarea
                            id="purpose"
                            v-model="form.purpose"
                            placeholder="Describe the purpose of your vehicle rental"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                            rows="3"
                        ></textarea>
                        <InputError :message="errors.purpose?.[0]" class="mt-2" />
                    </div>

                    <!-- Requested By -->
                    <div>
                        <InputLabel for="requested_by" value="Your Name *" />
                        <TextInput
                            id="requested_by"
                            v-model="form.requested_by"
                            type="text"
                            placeholder="Full name"
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
                            placeholder="Any additional information"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
                            rows="2"
                        ></textarea>
                        <InputError :message="errors.notes?.[0]" class="mt-2" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 pt-4">
                        <PrimaryButton :disabled="loading || !isAvailable" class="flex-1">
                            <span v-if="loading" class="flex items-center gap-2">
                                <LoaderIcon />
                                Submitting...
                            </span>
                            <span v-else>Submit Rental Request</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
