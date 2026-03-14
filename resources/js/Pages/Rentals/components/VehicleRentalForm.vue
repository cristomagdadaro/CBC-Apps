<script>
import RentalVehicle from '@/Modules/domain/RentalVehicle';
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import SuccessModal from "@/Components/SuccessModal.vue";
import CalendarModule from "@/Components/CalendarModule.vue";

export default {
    name: 'VehicleRentalForm',
    components: {
        SuccessModal,
        CalendarModule,
    },
    mixins: [ApiMixin, FormLocalMixin],
    props: {
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
            employee_id: null,
            showSuccessModal: false,
            successMessage: '',
            calendarLoading: false,
            calendarEvents: [],
        };
    },
    computed: {
        minDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },
        statusColors() {
            return {
                pending: '#FBBF24',
                approved: '#10B981',
                rejected: '#EF4444',
            };
        },
        statusOptions() {
            return [
                { key: 'pending', label: 'Pending' },
                { key: 'approved', label: 'Approved' },
                { key: 'rejected', label: 'Declined' },
            ];
        },
        vehicleTypeOptions() {
            return this.vehicleOptions.map(option => ({
                key: option.name,
                label: option.label,
                color: '#6B7280',
            }));
        },
        isGuestContext() {
            return !this.$page?.props?.auth?.user?.id;
        },
    },
    methods: {
        routeNameFor(type) {
            const routeMap = {
                index: this.isGuestContext ? 'api.guest.rental.vehicles.index' : 'api.rental.vehicles.index',
                create: this.isGuestContext ? 'api.guest.rental.vehicles.store' : 'api.rental.vehicles.store',
                checkAvailability: this.isGuestContext
                    ? 'api.guest.rental.vehicles.check-availability'
                    : 'api.rental.vehicles.check-availability',
            };

            return routeMap[type];
        },
        async checkAvailability() {
            if (!this.form.vehicle_type || !this.form.date_from || !this.form.date_to) {
                return;
            }

            this.availabilityChecking = true;
            try {
                const response = await this.fetchGetApi(this.routeNameFor('checkAvailability'), {
                    routeParams: {
                        vehicleType: this.form.vehicle_type,
                        dateFrom: this.form.date_from,
                        dateTo: this.form.date_to,
                    },
                });
                this.isAvailable = response.available;
                this.availabilityMessage = response.message;
            } catch (error) {
                console.error('Error checking availability:', error);
                this.availabilityMessage = 'Error checking availability';
            } finally {
                this.availabilityChecking = false;
            }
        },
        handleVehicleTypeChange(value) {
            this.form.vehicle_type = value;
            this.checkAvailability();
        },
        handleDateChange() {
            this.checkAvailability();
        },
        handlePersonnelFound(data) {
            this.form.requested_by = data.fullName;
            this.form.contact_number = data.phone;
        },
        normalizeCalendarEvents(rows = []) {
            return rows.map((rental) => ({
                id: rental.id,
                label: rental.vehicle_type,
                subtitle: rental.requested_by || '',
                type: rental.vehicle_type || 'vehicle',
                status: rental.status || 'pending',
                date_from: rental.date_from,
                date_to: rental.date_to,
            }));
        },
        async loadCalendarEvents() {
            this.calendarLoading = true;

            try {
                const response = await this.fetchGetApi(this.routeNameFor('index'), {
                    statuses: 'pending,approved,rejected',
                });

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
                this.form.errors.general = 'Please select available dates';
                return;
            }

            const data = this.isGuestContext
                ? await this.fetchPostApi(this.routeNameFor('create'), this.form.data())
                : await this.submitCreate();
            
            if (data && data.error || data.status === 422 || data.status === 500) {
                this.form.errors.general = data.message || 'Failed to submit rental request';
                return;
            }

            await this.loadCalendarEvents();
            
            this.successMessage = (data && data.message) ? data.message : 'Rental request submitted successfully';
            this.showSuccessModal = true;
            this.$emit('submitted', data.data ?? data);
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

    <div v-if="form" class="bg-white p-2 rounded-md flex flex-col gap-2 max-w-xl drop-shadow-lg lg:mx-0 my-4 md:mt-0">
        <form @submit.prevent="submitProxyCreate" class="space-y-3 bg-white rounded-lg p-3">
            <div v-if="form.errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ form.errors.general }}
            </div>
            <div v-if="form.date_from && form.date_to && form.vehicle_type" class="px-4 py-2 rounded-md" :class="isAvailable ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex items-center gap-2">
                    <loader-icon v-if="availabilityChecking" class="text-AB" />
                    <span :class="isAvailable ? 'text-green-700' : 'text-red-700'">
                        {{ availabilityMessage || 'Checking availability...' }}
                    </span>
                </div>
            </div>
            <custom-dropdown
                label="Vehicle Type"
                required
                placeholder="Select a vehicle"
                @selectedChange="handleVehicleTypeChange"
                :value="form.vehicle_type"
                :with-all-option="false"
                :options="vehicleOptions.map(v => ({ name: v.name, label: v.label }))"
                :error="form.errors.vehicle_type"
            >
                <template #icon>
                    <caret-down class="h-4 w-4 text-gray-600" />
                </template>
            </custom-dropdown>
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
            <TextArea
                id="purpose"
                v-model="form.purpose"
                label="Purpose"
                required
                placeholder="Describe the purpose of your vehicle rental"
                :class="{'border-red-500': form.errors.purpose}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
            ></TextArea>
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
            <TextArea
                id="notes"
                label="Additional Notes"
                v-model="form.notes"
                placeholder="Any additional information"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"
            ></TextArea>
            <div class="flex gap-4 pt-6 border-t">
                <PrimaryButton :disabled="processing || !isAvailable" class="justify-center flex-1">
                    <span v-if="processing" class="flex items-center justify-center gap-2">
                        <loader-icon />
                        Submitting...
                    </span>
                    <span v-else>Submit Rental Request</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-4 border-t pt-4">
            <h3 class="text-base font-semibold text-gray-900 mb-2">Vehicle Availability Calendar</h3>
            <p class="text-sm text-gray-600 mb-3">Check pending, approved, and declined vehicle requests before submitting.</p>
            <div v-if="calendarLoading" class="text-sm text-gray-500">Loading calendar...</div>
            <calendar-module
                v-else
                title="Vehicle Requests"
                :events="calendarEvents"
                :type-options="vehicleTypeOptions"
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
