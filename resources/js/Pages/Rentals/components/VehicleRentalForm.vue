<script>
import RentalVehicle from "@/Modules/domain/RentalVehicle";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import SuccessModal from "@/Components/SuccessModal.vue";
import CalendarModule from "@/Components/CalendarModule.vue";
import { rentalVehicleTripOptions, getTripTypeMeta } from "@/Pages/Rentals/constants/tripWorkflows";

export default {
    name: "VehicleRentalForm",
    components: {
        SuccessModal,
        CalendarModule,
    },
    mixins: [ApiMixin, FormLocalMixin],
    props: {
        vehicleOptions: {
            type: Array,
            default: () => [],
        },
    },
    beforeMount() {
        this.model = new RentalVehicle();
        this.setFormAction("create");
    },
    data() {
        return {
            submitted: false,
            employee_id: null,
            showSuccessModal: false,
            successMessage: "",
            calendarLoading: false,
            calendarEvents: [],
            membersOfPartyRows: [],
            destinationStopInput: "",
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
        tripTypeOptions() {
            return rentalVehicleTripOptions.map((option) => ({
                key: option.name,
                name: option.name,
                label: option.label,
                color: "#6B7280",
            }));
        },
        selectedTripTypeMeta() {
            return getTripTypeMeta(this.form?.trip_type);
        },
        vehicleTypeOptions() {
            return this.vehicleOptions.map((option) => ({
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
                index: "api.guest.rental.vehicles.index",
                create: this.isGuestContext
                    ? "api.guest.rental.vehicles.store"
                    : "api.rental.vehicles.store",
            };

            return routeMap[type];
        },
        handleTripTypeChange(value) {
            this.form.trip_type = value;
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
        syncDestinationStops() {
            this.form.destination_stops = this.destinationStopInput
                .split("\n")
                .map((value) => String(value || "").trim())
                .filter((value) => !!value);
        },
        createEmptyMemberRow() {
            return { name: "" };
        },
        hydrateMembersOfPartyRows() {
            const value = this.form?.members_of_party;

            if (!Array.isArray(value) || !value.length) {
                this.membersOfPartyRows = [];
                return;
            }

            this.membersOfPartyRows = value.map((member) => ({
                name: String(member ?? "").trim(),
            }));
        },
        syncMembersOfPartyPayload() {
            if (!this.form) return;

            this.form.members_of_party = this.membersOfPartyRows
                .map((row) => String(row?.name ?? "").trim())
                .filter((name) => !!name);
        },
        addMemberOfPartyRow() {
            this.membersOfPartyRows.push(this.createEmptyMemberRow());
            this.syncMembersOfPartyPayload();
        },
        removeMemberOfPartyRow(index) {
            if (index < 0 || index >= this.membersOfPartyRows.length) {
                return;
            }

            this.membersOfPartyRows.splice(index, 1);
            this.syncMembersOfPartyPayload();
        },
        memberRowError(index) {
            return this.form?.errors?.[`members_of_party.${index}`] ?? null;
        },
        normalizeCalendarEvents(rows = []) {
            return rows.map((rental) => ({
                id: rental.id,
                label: `${rental.requested_by || "Unknown requester"} (${rental.vehicle_type || "Vehicle pending"})`,
                subtitle: [rental.destination_location, rental.purpose]
                    .filter(Boolean)
                    .join(" - "),
                type: rental.vehicle_type || rental.trip_type || "vehicle",
                status: rental.status || "pending",
                date_from: rental.date_from,
                date_to: rental.date_to,
                checkoutPage: "rental.vehicle.show",
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
            this.syncDestinationStops();
            this.syncMembersOfPartyPayload();

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
            this.$emit("submitted", data.data ?? data);
        },
    },
    mounted() {
        this.hydrateMembersOfPartyRows();
        this.destinationStopInput = Array.isArray(this.form?.destination_stops)
            ? this.form.destination_stops.join("\n")
            : "";
        this.loadCalendarEvents();
    },
};
</script>

<template>
    <SuccessModal :show="showSuccessModal" title="Success!" :message="successMessage"
        @close="showSuccessModal = false" />
    <div class="grid md:grid-cols-4 grid-cols-1 gap-6 mt-3 md:mt-0">
        <div v-if="form" class="bg-white p-2 md:rounded-md flex flex-col gap-2 md:max-w-xl drop-shadow-lg h-fit col-span-3 md:col-span-1">
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="text-amber-800 font-medium text-sm">
                            <span class="font-bold uppercase tracking-wide">Internal Use Only:</span>
                            This form is exclusively for CBC internal use.
                            Please note that submission does not replace the
                            official PhilRice Travel Filing Protocols, which
                            must still be followed.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-2 bg-white border border-gray-200 rounded-lg p-3 shadow-sm">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Important Reminders
                </h2>
                <ul class="text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Ensure all required fields are completed
                            accurately.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500">•</span>
                        <span>Refer to the filled Travel Order (TO) .</span>
                    </li>
                </ul>
            </div>
            <div v-if="form" class="bg-white s p-2 rounded-md flex gap-2">
                <form @submit.prevent="submitProxyCreate" class="space-y-3 bg-white rounded-lg p-3 w-full">
                    <div v-if="form.errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {{ form.errors.general }}
                    </div>
                    <custom-dropdown label="Trip Workflow" required placeholder="Select a trip workflow"
                        @selectedChange="handleTripTypeChange" :value="form.trip_type" :with-all-option="false"
                        :options="tripTypeOptions" :error="form.errors.trip_type">
                        <template #icon>
                            <caret-down class="h-4 w-4 text-gray-600" />
                        </template>
                    </custom-dropdown>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Selected Workflow</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900">{{ selectedTripTypeMeta.label }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ selectedTripTypeMeta.description }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <DateInput id="date_from" label="Start Date" required v-model="form.date_from" :min="minDate"
                            :error="form.errors.date_from" />
                        <DateInput id="date_to" label="End Date" required v-model="form.date_to" type="date"
                            :min="form.date_from || minDate" :error="form.errors.date_to" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <TimeInput id="time_from" label="Start Time" required v-model="form.time_from"
                            :error="form.errors.time_from" class="mt-1 block w-full" />
                        <TimeInput id="time_to" label="End Time" required v-model="form.time_to"
                            :error="form.errors.time_to" class="mt-1 block w-full" />
                    </div>
                    <TextArea id="purpose" v-model="form.purpose" label="Purpose" required
                        placeholder="Describe the purpose of your vehicle rental"
                        :class="{ 'border-red-500': form.errors.purpose }"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"></TextArea>
                    <div>
                        <label class="text-xs text-gray-500 flex items-center justify-between mb-1">
                            Destination Location
                        </label>
                        <div class="grid grid-cols-1 gap-4">
                            <SelectRegion v-model="form.destination_region" :error="form.errors.destination_region"
                                @update:modelValue="handleDestinationRegionChange" />
                            <SelectProvince v-model="form.destination_province" :region="form.destination_region"
                                :disabled="!form.destination_region" :error="form.errors.destination_province"
                                @update:modelValue="handleDestinationProvinceChange" />
                            <SelectCity v-model="form.destination_city" :region="form.destination_region"
                                :province="form.destination_province" :disabled="!form.destination_province"
                                :error="form.errors.destination_city" />
                        </div>
                    </div>
                    <TextInput id="destination_location" label="Specific Address" required
                        v-model="form.destination_location" type="text" placeholder="Specific destination / address"
                        :error="form.errors.destination_location" class="mt-1 block w-full" />
                    <TextArea id="destination_stops" v-model="destinationStopInput" label="Additional Stops"
                        placeholder="One stop per line for shuttle or multi-stop trips" @input="syncDestinationStops"
                        :error="form.errors.destination_stops"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"></TextArea>
                    <div class="rounded-lg border border-gray-200 p-4">
                        <label class="flex items-start gap-3 text-sm text-gray-700">
                            <Checkbox v-model:checked="form.is_shared_ride" name="is_shared_ride" />
                            <span>
                                <span class="block font-medium text-gray-900">Shared/Hitch Ride</span>
                                <span class="block text-xs text-gray-500">Enable this if the trip can be grouped with another approved request.</span>
                            </span>
                        </label>
                    </div>
                    <TextInput v-if="form.is_shared_ride" id="shared_ride_reference" label="Shared/Hitch Ride Reference"
                        v-model="form.shared_ride_reference" type="text" placeholder="Full name of the person you're sharing with"
                        :error="form.errors.shared_ride_reference" class="mt-1 block w-full" />
                    <PersonnelLookup v-model="employee_id" @found="handlePersonnelFound" />
                    <TextInput id="requested_by" label="Your Name" required v-model="form.requested_by" type="text"
                        placeholder="Full name" :error="form.errors.requested_by" class="mt-1 block w-full" />
                    <div class="mt-1 border border-gray-200 rounded-lg p-3 bg-white">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-semibold text-gray-900">Members of the Party (MOP)</label>
                            <button type="button"
                                class="px-2 py-1 text-xs border border-dashed border-gray-400 rounded-md text-gray-700 hover:bg-gray-50"
                                @click="addMemberOfPartyRow">
                                + Add Member
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 mb-2">
                            Add companions for this trip. Leave empty if none.
                        </p>

                        <div v-if="form.errors.members_of_party" class="text-xs text-red-600 mb-2">
                            {{ form.errors.members_of_party }}
                        </div>

                        <div v-if="membersOfPartyRows.length" class="flex flex-col gap-2">
                            <div v-for="(member, index) in membersOfPartyRows" :key="`mop-${index}`"
                                class="flex gap-2 items-start">
                                <div class="flex-1">
                                    <TextInput :id="`members_of_party_${index}`" :label="`Member ${index + 1}`"
                                        v-model="member.name" type="text" placeholder="Enter member full name"
                                        @input="syncMembersOfPartyPayload" class="mt-1 block w-full" />
                                    <p v-if="memberRowError(index)" class="text-xs text-red-600 mt-1">
                                        {{ memberRowError(index) }}
                                    </p>
                                </div>

                                <div class="flex gap-1">
                                    <button type="button"
                                        class="px-2 py-1 text-xs border border-red-300 text-red-600 rounded hover:bg-red-50"
                                        @click="removeMemberOfPartyRow(index)" title="Remove member">
                                        <lu-x class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-xs text-gray-400">
                            No members added.
                        </p>
                    </div>
                    <TextInput id="contact_number" label="Contact Number" required v-model="form.contact_number"
                        type="tel" placeholder="09XX-XXX-XXXX" :error="form.errors.contact_number"
                        class="mt-1 block w-full" />
                    <TextArea id="notes" label="Additional Notes" v-model="form.notes"
                        placeholder="Any additional information"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-AB focus:ring-AB"></TextArea>
                    <div class="flex gap-4 pt-6 border-t">
                        <PrimaryButton :disabled="processing" class="justify-center flex-1">
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
        <div class="bg-white p-4 md:rounded-lg shadow col-span-3 h-fit">
            <h3 class="text-base font-semibold text-gray-900 mb-2">
                Vehicle Availability Calendar
            </h3>
            <p class="text-sm text-gray-600 mb-3">
                Review current request schedules and workflow states before submitting.
            </p>
            <div v-if="calendarLoading" class="text-sm text-gray-500 flex items-center gap-2 justify-center">
                <loader-icon class="w-6 h-6 text-gray-500 animate-spin" />
                Loading booking calendars...
            </div>
            <calendar-module v-else title="Vehicle Requests" :events="calendarEvents" :type-options="vehicleTypeOptions"
                :status-options="statusOptions" :status-colors="statusColors" :show-today="true"
                :show-type-filter="true" :show-status-filter="true" :show-stats="false" />
        </div>
    </div>
</template>
