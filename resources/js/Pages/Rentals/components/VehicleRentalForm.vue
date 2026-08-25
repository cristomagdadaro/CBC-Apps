<script>
import RentalVehicle from "@/Modules/domain/RentalVehicle";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import SuccessModal from "@/Components/SuccessModal.vue";
import CalendarModule from "@/Components/CalendarModule.vue";
import {
    rentalVehicleTripOptions,
    getTripTypeMeta,
} from "@/Pages/Rentals/constants/tripWorkflows";
import {
    Car,
    CalendarDays,
    Loader2,
    AlertTriangle,
    Info,
    X,
    ChevronDown,
    CheckCircle2,
} from "lucide-vue-next";

export default {
    name: "VehicleRentalForm",
    components: {
        SuccessModal,
        CalendarModule,
        Car,
        CalendarDays,
        Loader2,
        AlertTriangle,
        Info,
        X,
        ChevronDown,
        CheckCircle2,
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
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
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
        tripTypeOptions() {
            return rentalVehicleTripOptions.map((option) => ({
                key: option.name,
                name: option.name,
                label: option.label,
                color: "#64748B",
            }));
        },
        selectedTripTypeMeta() {
            return getTripTypeMeta(this.form?.trip_type);
        },
        vehicleTypeOptions() {
            return this.vehicleOptions.map((option) => ({
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
                label: `${rental.vehicle_type || "Vehicle"} booking`,
                subtitle: [rental.trip_type, rental.status]
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

    <div class="grid lg:grid-cols-4 gap-6 mt-3 md:mt-0">
        <!-- Left Column: Form -->
        <div
            data-guide="rental-form-shell"
            v-if="form"
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/60 dark:border-slate-800 p-6 rounded-2xl shadow-sm h-fit w-full lg:col-span-1"
        >
            <div
                class="flex items-center gap-3 pb-5 mb-5 border-b border-slate-100 dark:border-slate-800/60"
            >
                <div
                    class="p-2.5 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl shadow-sm shrink-0"
                >
                    <Car class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
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
                        Vehicle Request
                    </h2>
                </div>
            </div>

            <!-- Informational Alerts -->
            <div class="space-y-3 mb-6">
                <div
                    class="bg-amber-50/80 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 p-4 rounded-xl shadow-xs"
                >
                    <div class="flex items-start gap-3">
                        <AlertTriangle
                            class="w-5 h-5 text-amber-600 dark:text-amber-500 mt-0.5 shrink-0"
                        />
                        <div>
                            <p
                                class="text-amber-800 dark:text-amber-400 font-medium text-xs leading-relaxed"
                            >
                                <span
                                    class="font-bold uppercase tracking-wider block mb-1"
                                    >Internal Use Only:</span
                                >
                                This form is exclusively for CBC internal use.
                                Please note that submission does not replace the
                                official PhilRice Travel Filing Protocols, which
                                must still be followed.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-indigo-50/80 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 rounded-xl p-4 shadow-xs"
                >
                    <h2
                        class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 flex items-center gap-2 mb-2"
                    >
                        <Info
                            class="w-4 h-4 text-indigo-600 dark:text-indigo-400"
                        />
                        Important Reminders
                    </h2>
                    <ul
                        class="text-[0.7rem] font-medium text-indigo-800 dark:text-indigo-400 space-y-1.5 ml-6 list-disc"
                    >
                        <li>
                            Ensure all required fields are completed accurately.
                        </li>
                        <li>Refer to the filled Travel Order (TO).</li>
                    </ul>
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

                <!-- Trip Workflow -->
                <div class="space-y-3">
                    <custom-dropdown
                        label="Trip Workflow"
                        required
                        placeholder="Select a trip workflow"
                        @selectedChange="handleTripTypeChange"
                        :value="form.trip_type"
                        :with-all-option="false"
                        :options="tripTypeOptions"
                        :error="form.errors.trip_type"
                        class="w-full"
                    >
                        <template #icon>
                            <ChevronDown
                                class="h-4 w-4 text-slate-400 dark:text-slate-500"
                            />
                        </template>
                    </custom-dropdown>

                    <div
                        v-if="selectedTripTypeMeta"
                        class="rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/30 p-4 shadow-xs"
                    >
                        <p
                            class="text-[0.65rem] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"
                        >
                            Selected Workflow
                        </p>
                        <p
                            class="mt-1 text-sm font-bold text-slate-900 dark:text-white"
                        >
                            {{ selectedTripTypeMeta.label }}
                        </p>
                        <p
                            class="mt-1 text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed"
                        >
                            {{ selectedTripTypeMeta.description }}
                        </p>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <DateInput
                        id="date_from"
                        label="Start Date"
                        required
                        v-model="form.date_from"
                        :min="minDate"
                        :error="form.errors.date_from"
                        class="block w-full"
                    />
                    <DateInput
                        id="date_to"
                        label="End Date"
                        required
                        v-model="form.date_to"
                        type="date"
                        :min="form.date_from || minDate"
                        :error="form.errors.date_to"
                        class="block w-full"
                    />
                </div>

                <!-- Time Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <TimeInput
                        id="time_from"
                        label="Start Time"
                        required
                        v-model="form.time_from"
                        :error="form.errors.time_from"
                        class="block w-full"
                    />
                    <TimeInput
                        id="time_to"
                        label="End Time"
                        required
                        v-model="form.time_to"
                        :error="form.errors.time_to"
                        class="block w-full"
                    />
                </div>

                <!-- Purpose -->
                <TextArea
                    id="purpose"
                    v-model="form.purpose"
                    label="Purpose"
                    required
                    placeholder="Describe the purpose of your vehicle rental"
                    :error="form.errors.purpose"
                    class="block w-full"
                ></TextArea>

                <!-- Destination Location -->
                <div>
                    <label
                        class="block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2"
                    >
                        Destination Location
                    </label>
                    <div class="grid grid-cols-1 gap-4">
                        <SelectRegion
                            v-model="form.destination_region"
                            :error="form.errors.destination_region"
                            @update:modelValue="handleDestinationRegionChange"
                            class="block w-full"
                        />
                        <SelectProvince
                            v-model="form.destination_province"
                            :region="form.destination_region"
                            :disabled="!form.destination_region"
                            :error="form.errors.destination_province"
                            @update:modelValue="handleDestinationProvinceChange"
                            class="block w-full"
                        />
                        <SelectCity
                            v-model="form.destination_city"
                            :region="form.destination_region"
                            :province="form.destination_province"
                            :disabled="!form.destination_province"
                            :error="form.errors.destination_city"
                            class="block w-full"
                        />
                    </div>
                </div>

                <TextInput
                    id="destination_location"
                    label="Specific Address"
                    required
                    v-model="form.destination_location"
                    type="text"
                    placeholder="Specific destination / address"
                    :error="form.errors.destination_location"
                    class="block w-full"
                />

                <TextArea
                    id="destination_stops"
                    v-model="destinationStopInput"
                    label="Additional Stops"
                    placeholder="One stop per line for shuttle or multi-stop trips"
                    @input="syncDestinationStops"
                    :error="form.errors.destination_stops"
                    class="block w-full"
                ></TextArea>

                <!-- Shared Ride Checkbox -->
                <div
                    class="rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/30 p-4 shadow-xs"
                >
                    <Checkbox
                        v-model:checked="form.is_shared_ride"
                        name="is_shared_ride"
                        label="Shared/Hitch Ride"
                    />
                    <span
                        class="block text-xs font-medium text-slate-500 dark:text-slate-400 mt-1.5 pl-6 leading-relaxed"
                    >
                        Enable this if the trip can be grouped with another
                        approved request.
                    </span>
                </div>

                <TextInput
                    v-if="form.is_shared_ride"
                    id="shared_ride_reference"
                    label="Shared/Hitch Ride Reference"
                    v-model="form.shared_ride_reference"
                    type="text"
                    placeholder="Full name of the person you're sharing with"
                    :error="form.errors.shared_ride_reference"
                    class="block w-full"
                />

                <!-- Requestor Details -->
                <div class="space-y-5">
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
                        class="block w-full"
                    />
                </div>

                <TextInput
                    id="organization"
                    label="Division / Organization"
                    required
                    v-model="form.organization"
                    type="text"
                    placeholder="e.g. Crop Biotechnology Center"
                    :error="form.errors.organization"
                    class="block w-full"
                />

                <div
                    class="rounded-xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/30 p-5 shadow-xs space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <label
                            class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400"
                            >Members of the Party (MOP)</label
                        >
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-[0.65rem] font-bold uppercase tracking-wider border border-dashed border-indigo-300 dark:border-indigo-500/50 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors"
                            @click="addMemberOfPartyRow"
                        >
                            + Add Member
                        </button>
                    </div>

                    <p
                        class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed"
                    >
                        Add companions for this trip. Leave empty if none.
                    </p>

                    <div
                        v-if="form.errors.members_of_party"
                        class="text-xs font-semibold text-rose-600 dark:text-rose-400"
                    >
                        {{ form.errors.members_of_party }}
                    </div>

                    <div
                        v-if="membersOfPartyRows.length"
                        class="flex flex-col gap-3"
                    >
                        <div
                            v-for="(member, index) in membersOfPartyRows"
                            :key="`mop-${index}`"
                            class="flex gap-3 items-start"
                        >
                            <div class="flex-1">
                                <TextInput
                                    :id="`members_of_party_${index}`"
                                    :label="`Member ${index + 1}`"
                                    v-model="member.name"
                                    type="text"
                                    placeholder="Enter member full name"
                                    @input="syncMembersOfPartyPayload"
                                    class="block w-full"
                                />
                                <p
                                    v-if="memberRowError(index)"
                                    class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1"
                                >
                                    {{ memberRowError(index) }}
                                </p>
                            </div>

                            <div class="flex gap-1 pt-[1.65rem]">
                                <button
                                    type="button"
                                    class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-colors border border-transparent hover:border-rose-200 dark:hover:border-rose-500/30"
                                    @click="removeMemberOfPartyRow(index)"
                                    title="Remove member"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <TextInput
                    id="contact_number"
                    label="Contact Number"
                    required
                    v-model="form.contact_number"
                    type="tel"
                    placeholder="09XX-XXX-XXXX"
                    :error="form.errors.contact_number"
                    class="block w-full"
                />

                <TextArea
                    id="notes"
                    label="Additional Notes"
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
                        :disabled="processing"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3.5 text-sm shadow-sm transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none"
                    >
                        <Loader2
                            v-if="processing"
                            class="animate-spin w-4 h-4"
                        />
                        <span>{{
                            processing
                                ? "Submitting..."
                                : "Submit Vehicle Request"
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
                        Vehicle Availability Calendar
                    </h3>
                    <p
                        class="text-xs font-medium text-slate-500 dark:text-slate-400"
                    >
                        Review current request schedules and workflow states
                        before submitting.
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
                title="Vehicle Requests"
                :events="calendarEvents"
                :type-options="vehicleTypeOptions"
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
