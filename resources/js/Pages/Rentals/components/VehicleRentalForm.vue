<script>
import RentalVehicle from "@/Modules/domain/RentalVehicle";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import SuccessModal from "@/Components/SuccessModal.vue";
import CalendarModule from "@/Components/CalendarModule.vue";

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
            availableVehicles: [],
            availabilityChecking: false,
            isAvailable: true,
            availabilityMessage: "",
            employee_id: null,
            showSuccessModal: false,
            successMessage: "",
            calendarLoading: false,
            calendarEvents: [],
            membersOfPartyRows: [],
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
    methods: {
        routeNameFor(type) {
            const routeMap = {
                index: this.isGuestContext
                    ? "api.guest.rental.vehicles.index"
                    : "api.rental.vehicles.index",
                create: this.isGuestContext
                    ? "api.guest.rental.vehicles.store"
                    : "api.rental.vehicles.store",
                checkAvailability: this.isGuestContext
                    ? "api.guest.rental.vehicles.check-availability"
                    : "api.rental.vehicles.check-availability",
            };

            return routeMap[type];
        },
        async checkAvailability() {
            if (
                !this.form.vehicle_type ||
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
                            vehicleType: this.form.vehicle_type,
                            dateFrom: this.form.date_from,
                            dateTo: this.form.date_to,
                        },
                    },
                );
                this.isAvailable = response.available;
                this.availabilityMessage = response.message;
            } catch (error) {
                console.error("Error checking availability:", error);
                this.availabilityMessage = "Error checking availability";
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
        moveMemberOfParty(index, direction) {
            const target = index + direction;

            if (target < 0 || target >= this.membersOfPartyRows.length) {
                return;
            }

            const copy = [...this.membersOfPartyRows];
            const current = copy[index];
            copy[index] = copy[target];
            copy[target] = current;
            this.membersOfPartyRows = copy;
            this.syncMembersOfPartyPayload();
        },
        memberRowError(index) {
            return this.form?.errors?.[`members_of_party.${index}`] ?? null;
        },
        normalizeCalendarEvents(rows = []) {
            return rows.map((rental) => ({
                id: rental.id,
                label: rental.vehicle_type,
                subtitle: rental.requested_by || "",
                type: rental.vehicle_type || "vehicle",
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

    <div
        v-if="form"
        class="bg-white p-2 rounded-md flex flex-col gap-2 max-w-xl drop-shadow-lg lg:mx-0 my-4 md:mt-0"
    >
        <div
            class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm"
        >
            <div class="flex items-start gap-3">
                <svg
                    class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
                <div>
                    <p class="text-amber-800 font-medium text-sm">
                        <span class="font-bold uppercase tracking-wide"
                            >Internal Use Only:</span
                        >
                        This form is exclusively for CBC internal use. Please note
                        that submission does not replace the official PhilRice
                        Travel Filing Protocols, which must still be followed.
                    </p>
                </div>
            </div>
        </div>

        <div
            class="mt-2 bg-white border border-gray-200 rounded-lg p-3 shadow-sm"
        >
            <h2
                class="font-semibold text-gray-900 flex items-center gap-2"
            >
                <svg
                    class="w-5 h-5 text-blue-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
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
        <form
            @submit.prevent="submitProxyCreate"
            class="space-y-3 bg-white rounded-lg p-3"
        >
            <div
                v-if="form.errors.general"
                class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"
            >
                {{ form.errors.general }}
            </div>
            <div
                v-if="form.date_from && form.date_to && form.vehicle_type"
                class="px-4 py-2 rounded-md"
                :class="
                    isAvailable
                        ? 'bg-green-50 border border-green-200'
                        : 'bg-red-50 border border-red-200'
                "
            >
                <div class="flex items-center gap-2">
                    <loader-icon v-if="availabilityChecking" class="text-AB" />
                    <span
                        :class="isAvailable ? 'text-green-700' : 'text-red-700'"
                    >
                        {{ availabilityMessage || "Checking availability..." }}
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
                :options="
                    vehicleOptions.map((v) => ({
                        name: v.name,
                        label: v.label,
                    }))
                "
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
                :class="{ 'border-red-500': form.errors.purpose }"
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
            <div class="mt-1 border border-gray-200 rounded-lg p-3 bg-white">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-semibold text-gray-900">Members of the Party (MOP)</label>
                    <button
                        type="button"
                        class="px-2 py-1 text-xs border border-dashed border-gray-400 rounded-md text-gray-700 hover:bg-gray-50"
                        @click="addMemberOfPartyRow"
                    >
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
                    <div
                        v-for="(member, index) in membersOfPartyRows"
                        :key="`mop-${index}`"
                        class="grid grid-cols-12 gap-2 items-start"
                    >
                        <div class="col-span-8">
                            <TextInput
                                :id="`members_of_party_${index}`"
                                :label="`Member ${index + 1}`"
                                v-model="member.name"
                                type="text"
                                placeholder="Enter member full name"
                                @input="syncMembersOfPartyPayload"
                                class="mt-1 block w-full"
                            />
                            <p v-if="memberRowError(index)" class="text-xs text-red-600 mt-1">
                                {{ memberRowError(index) }}
                            </p>
                        </div>

                        <div class="col-span-4 flex gap-1 pt-6">
                            <button
                                type="button"
                                class="px-2 py-1 text-xs border rounded hover:bg-gray-50 disabled:opacity-50"
                                :disabled="index === 0"
                                @click="moveMemberOfParty(index, -1)"
                                title="Move up"
                            >
                                Up
                            </button>
                            <button
                                type="button"
                                class="px-2 py-1 text-xs border rounded hover:bg-gray-50 disabled:opacity-50"
                                :disabled="index === membersOfPartyRows.length - 1"
                                @click="moveMemberOfParty(index, 1)"
                                title="Move down"
                            >
                                Down
                            </button>
                            <button
                                type="button"
                                class="px-2 py-1 text-xs border border-red-300 text-red-600 rounded hover:bg-red-50"
                                @click="removeMemberOfPartyRow(index)"
                                title="Remove member"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                <p v-else class="text-xs text-gray-400">No members added.</p>
            </div>
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
                    <span v-else>Submit Rental Request</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-4 border-t pt-4">
            <h3 class="text-base font-semibold text-gray-900 mb-2">
                Vehicle Availability Calendar
            </h3>
            <p class="text-sm text-gray-600 mb-3">
                Check pending, approved, and declined vehicle requests before
                submitting.
            </p>
            <div v-if="calendarLoading" class="text-sm text-gray-500">
                Loading calendar...
            </div>
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
