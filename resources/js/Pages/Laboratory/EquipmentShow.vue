<script>
import { useForm, usePage, router } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import LaboratoryPersonnelMixin from "@/Modules/mixins/LaboratoryPersonnelMixin";
import EditIcon from "@/Components/Icons/EditIcon.vue";
import DialogModal from "@/Components/DialogModal.vue";

export default {
    components: { EditIcon, DialogModal },
    name: "EquipmentShow",
    mixins: [ApiMixin, DataFormatterMixin, LaboratoryPersonnelMixin],
    props: {
        equipment_id: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            delayReady: false,
            title: "Equipment Logger",
            subtitle: "Scan-based tracking for equipment usage",
            selectedEquipmentId: this.equipment_id,
            equipmentOptions: [],
            equipment: null,
            activeLog: null,
            allowedActions: [],
            maxEndUseHours: 24,
            purposeSuggestions: [],
            currentLocation: null,
            storageLocationOptions: [],
            loading: false,
            loadingActiveEquipments: false,
            activeEquipments: [],
            message: null,
            messageType: "success",
            showSuccessModal: false,
            personnelPreview: null,
            checkInErrors: {},
            checkOutErrors: {},
            updateEndUseErrors: {},
            locationSurveyErrors: {},
            checkInForm: useForm({
                employee_id: "",
                end_use_at: "",
                purpose: "",
            }),
            checkOutForm: useForm({
                employee_id: "",
                admin_override: false,
            }),
            updateEndUseForm: useForm({
                employee_id: "",
                end_use_at: "",
            }),
            locationSurveyForm: useForm({
                employee_id: "",
                location_label: "",
            }),
            showPhilRiceField: false,
            isRotating: false,
            filterActiveByPersonnel: false,
            notFound: false,
            isNavigating: false,
            unsubscribeRouterEvents: null,
            showEstimatedEndUseModal: false,
        };
    },
    computed: {
        equipmentId() {
            return this.selectedEquipmentId || this.equipment_id || null;
        },
        hasEquipment() {
            return !!this.equipmentId;
        },
        canCheckIn() {
            return this.allowedActions.includes("check-in");
        },
        canCheckOut() {
            return this.allowedActions.includes("check-out");
        },
        isAdmin() {
            const page = usePage();
            return page.props.auth?.user?.is_admin ?? false;
        },
        shouldShowLocationSurvey() {
            return this.currentLocation?.source !== "temporary";
        },
        filteredActiveEquipments() {
            if (
                !this.filterActiveByPersonnel ||
                !this.savedLaboratoryPersonnel?.employee_id
            ) {
                return this.activeEquipments;
            }
            return this.activeEquipments.filter(
                (item) =>
                    item.personnel?.employee_id ===
                    this.savedLaboratoryPersonnel.employee_id,
            );
        },
    },
    methods: {
        async loadEquipmentOptions() {
            try {
                const response = await this.fetchGetApi(
                    "api.laboratory.equipments.index",
                );
                const payload = response?.data ?? response;
                const list = Array.isArray(payload)
                    ? payload
                    : (payload?.data ?? []);

                this.equipmentOptions = list.map((item) => {
                    const name = item.name || "Equipment";
                    const brand = item.brand ? ` (${item.brand})` : "";
                    const description = item.description
                        ? `  ${item.description}`
                        : "";
                    const prri = item.barcode ? ` | ${item.barcode}` : "";
                    return {
                        id: item.equipment_id || item.id,
                        name: `${name}${brand}${prri}`,
                    };
                });
            } catch (error) {
                this.equipmentOptions = [];
            }
        },
        async loadActiveEquipments() {
            this.loadingActiveEquipments = true;
            try {
                const response = await this.fetchGetApi(
                    "api.laboratory.equipments.active",
                );
                const payload = response?.data ?? response;
                this.activeEquipments = Array.isArray(payload)
                    ? payload
                    : (payload?.data ?? []);
            } catch (error) {
                this.activeEquipments = [];
            } finally {
                this.loadingActiveEquipments = false;
            }
        },
        async loadEquipment() {
            if (!this.equipmentId) {
                return;
            }
            this.loading = true;
            this.notFound = false;
            try {
                const response = await this.fetchGetApi(
                    "api.laboratory.equipments.show",
                    {
                        routeParams: this.equipmentId,
                    },
                );
                const details = response?.data ?? response;

                this.equipment = details?.equipment ?? null;
                this.activeLog = details?.active_log ?? null;
                this.allowedActions = details?.allowed_actions ?? [];
                this.purposeSuggestions = details?.purpose_suggestions ?? [];
                this.currentLocation = details?.current_location ?? null;
                this.storageLocationOptions =
                    details?.storage_location_options ?? [];
                this.maxEndUseHours = details?.max_end_use_hours ?? 24;

                if (this.activeLog?.end_use_at) {
                    this.updateEndUseForm.end_use_at =
                        this.formatForDatetimeLocal(this.activeLog.end_use_at);
                }

                if (
                    this.savedLaboratoryPersonnel?.employee_id &&
                    !this.updateEndUseForm.employee_id
                ) {
                    this.updateEndUseForm.employee_id =
                        this.savedLaboratoryPersonnel.employee_id;
                }

                if (!this.locationSurveyForm.location_label) {
                    this.locationSurveyForm.location_label =
                        this.currentLocation?.label || "";
                }

                if (
                    this.savedLaboratoryPersonnel?.employee_id &&
                    !this.locationSurveyForm.employee_id
                ) {
                    this.locationSurveyForm.employee_id =
                        this.savedLaboratoryPersonnel.employee_id;
                }
            } catch (error) {
                this.messageType = "error";
                this.message =
                    error?.response?.data?.message ||
                    "Unable to load equipment details.";
                this.notFound = true;
            } finally {
                this.loading = false;
                this.loadActiveEquipments();
            }
        },
        handlePersonnelFound(data) {
            this.personnelPreview = data;
            this.checkInErrors = { ...this.checkInErrors, employee_id: null };
            // Save to localStorage for future check-outs
            this.saveLaboratoryPersonnel({
                employee_id: this.checkInForm.employee_id,
                fullName: data.fullName,
                fname: data.fname,
                mname: data.mname,
                lname: data.lname,
                suffix: data.suffix,
            });
        },
        handlePersonnelSwitch() {
            this.isRotating = true;

            setTimeout(() => {
                this.isRotating = false;
            }, 300);

            this.searchDifferentPersonnel();
        },
        handlePersonnelError(error) {
            this.checkInErrors = {
                ...this.checkInErrors,
                [error.field]: error.message,
            };
        },
        searchDifferentPersonnel() {
            this.checkOutErrors = {};
            this.showPhilRiceField = !this.showPhilRiceField;

            if (
                this.showPhilRiceField &&
                this.savedLaboratoryPersonnel?.employee_id
            ) {
                this.checkOutForm.employee_id =
                    this.savedLaboratoryPersonnel.employee_id;
                return;
            }

            this.checkOutForm.employee_id = "";
        },
        resetCheckIn() {
            this.checkInForm.reset();
            this.checkInErrors = {};
            this.personnelPreview = null;
        },
        resetCheckOut() {
            this.checkOutForm.reset();
            this.checkOutErrors = {};
        },
        resetUpdateEndUse() {
            this.updateEndUseErrors = {};

            if (this.activeLog?.end_use_at) {
                this.updateEndUseForm.end_use_at = this.formatForDatetimeLocal(
                    this.activeLog.end_use_at,
                );
            }

            if (this.savedLaboratoryPersonnel?.employee_id) {
                this.updateEndUseForm.employee_id =
                    this.savedLaboratoryPersonnel.employee_id;
            } else {
                this.updateEndUseForm.employee_id = "";
            }
        },
        resetLocationSurvey() {
            this.locationSurveyErrors = {};
            this.locationSurveyForm.location_label =
                this.currentLocation?.label || "";

            if (this.savedLaboratoryPersonnel?.employee_id) {
                this.locationSurveyForm.employee_id =
                    this.savedLaboratoryPersonnel.employee_id;
            }
        },
        addMinutes(minutes) {
            if (minutes === 0) {
                if (!this.activeLog?.end_use_at) return;

                this.updateEndUseForm.end_use_at =
                    this.formatForDatetimeLocal(this.activeLog.end_use_at);
                return;
            }

            let baseTime = this.updateEndUseForm.end_use_at
                ? new Date(this.updateEndUseForm.end_use_at)
                : new Date();

            baseTime.setMinutes(baseTime.getMinutes() + minutes);

            this.updateEndUseForm.end_use_at =
                this.formatForDatetimeLocal(baseTime);
        },
        async submitCheckIn() {
            this.checkInErrors = {};
            this.message = null;

            const payload = {
                employee_id: this.checkInForm.employee_id,
                end_use_at: this.checkInForm.end_use_at,
                purpose: this.checkInForm.purpose,
            };

            try {
                await this.fetchPostApi(
                    "api.laboratory.equipments.check-in",
                    payload,
                    {
                        routeParams: this.equipmentId,
                    },
                );
                this.messageType = "success";
                this.message = "Check-in recorded successfully.";
                this.showSuccessModal = true;
                this.resetCheckIn();
                await this.loadEquipment();
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.checkInErrors = error.response.data.errors || {
                        base: error.response.data.message,
                    };
                } else {
                    this.checkInErrors = {
                        base:
                            error?.response?.data?.message ||
                            "Check-in failed.",
                    };
                }
            }
        },
        async submitCheckOut() {
            this.checkOutErrors = {};
            this.message = null;

            const payload = {
                employee_id: this.checkOutForm.employee_id,
                admin_override: this.checkOutForm.admin_override,
            };

            try {
                await this.fetchPostApi(
                    "api.laboratory.equipments.check-out",
                    payload,
                    {
                        routeParams: this.equipmentId,
                    },
                );
                this.messageType = "success";
                this.message = "Check-out recorded successfully.";
                this.showSuccessModal = true;
                this.resetCheckOut();
                await this.loadEquipment();
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.checkOutErrors = error.response.data.errors || {
                        base: error.response.data.message,
                    };
                } else {
                    this.checkOutErrors = {
                        base:
                            error?.response?.data?.message ||
                            "Check-out failed.",
                    };
                }
            }
        },
        async submitUpdateEndUse() {
            this.updateEndUseErrors = {};
            this.message = null;

            const payload = {
                employee_id: this.updateEndUseForm.employee_id,
                end_use_at: this.updateEndUseForm.end_use_at,
            };

            try {
                await this.fetchPostApi(
                    "api.laboratory.equipments.update-end-use",
                    payload,
                    {
                        routeParams: this.equipmentId,
                    },
                );
                this.messageType = "success";
                this.message = "Estimated end of use updated successfully.";
                this.showSuccessModal = true;
                await this.loadEquipment();
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.updateEndUseErrors = error.response.data.errors || {
                        base: error.response.data.message,
                    };
                } else {
                    this.updateEndUseErrors = {
                        base:
                            error?.response?.data?.message ||
                            "Failed to update estimated end of use.",
                    };
                }
            }
        },
        async submitLocationSurvey() {
            this.locationSurveyErrors = {};
            this.message = null;

            const payload = {
                employee_id: this.locationSurveyForm.employee_id,
                location_label: this.locationSurveyForm.location_label,
            };

            try {
                await this.fetchPostApi(
                    "api.laboratory.equipments.report-location",
                    payload,
                    {
                        routeParams: this.equipmentId,
                    },
                );

                this.messageType = "success";
                this.message = "Temporary location saved successfully.";
                this.showSuccessModal = true;
                await this.loadEquipment();
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.locationSurveyErrors = error.response.data.errors || {
                        base: error.response.data.message,
                    };
                } else {
                    this.locationSurveyErrors = {
                        base:
                            error?.response?.data?.message ||
                            "Failed to save temporary location.",
                    };
                }
            }
        },
        formatPersonnelName(personnel) {
            if (!personnel) return "-";
            const parts = [
                personnel.fname,
                personnel.mname,
                personnel.lname,
                personnel.suffix,
            ]
                .filter(Boolean)
                .map((value) => String(value).trim())
                .filter(Boolean);
            return parts.length ? parts.join(" ") : "-";
        },
        formatForDatetimeLocal(value) {
            if (!value) return "";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "";

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            const hours = String(date.getHours()).padStart(2, "0");
            const minutes = String(date.getMinutes()).padStart(2, "0");

            return `${year}-${month}-${day}T${hours}:${minutes}`;
        },
        getErrorMessage(error) {
            if (!error) return null;
            return typeof error === "string"
                ? error
                : Array.isArray(error)
                  ? error[0]
                  : error;
        },
    },
    watch: {
        equipmentId(newValue, oldValue) {
            if (newValue === oldValue) return;
            if (!newValue) {
                this.equipment = null;
                this.activeLog = null;
                this.allowedActions = [];
                return;
            }
            this.loadEquipment();
        },
    },
    mounted() {
        if (this.equipmentId) {
            this.loadEquipment();
        } else {
            this.loadEquipmentOptions();
        }
        this.loadLaboratoryPersonnel();
        this.loadActiveEquipments();
        // Auto-fill check-out form with saved personnel
        if (this.savedLaboratoryPersonnel?.employee_id) {
            this.showPhilRiceField = true;
            this.checkOutForm.employee_id =
                this.savedLaboratoryPersonnel.employee_id;
            this.updateEndUseForm.employee_id =
                this.savedLaboratoryPersonnel.employee_id;
            this.locationSurveyForm.employee_id =
                this.savedLaboratoryPersonnel.employee_id;
        }
        setTimeout(() => {
            this.delayReady = true;
        }, 200);

        // Setup Inertia router events for navigation loading
        const unsubscribeStart = router.on(
            "start",
            () => (this.isNavigating = true),
        );
        const unsubscribeFinish = router.on(
            "finish",
            () => (this.isNavigating = false),
        );

        this.unsubscribeRouterEvents = () => {
            unsubscribeStart();
            unsubscribeFinish();
        };
    },

    beforeUnmount() {
        if (this.unsubscribeRouterEvents) {
            this.unsubscribeRouterEvents();
        }
    },
};
</script>

<template>
    <Head :title="title" />
    <SuccessModal
        :show="showSuccessModal"
        :title="message"
        @close="showSuccessModal = false"
    />
    <GuestFormPage
        :title="title"
        :subtitle="subtitle"
        :delay-ready="delayReady"
    >
        <!-- Loading Overlay -->
        <transition name="fade">
            <div
                v-if="processing"
                class="fixed top-0 left-0 w-full h-full z-[60] flex items-center justify-center bg-black bg-opacity-30"
            >
                <div
                    class="flex flex-col items-center gap-3 p-6 bg-white rounded-lg shadow-lg"
                >
                    <div
                        class="w-10 h-10 border-4 border-gray-300 rounded-full animate-spin border-t-AB"
                    ></div>
                    <p class="text-sm font-medium text-gray-600">
                        Processing request...
                    </p>
                </div>
            </div>
        </transition>

        <transition-container
            v-show="delayReady"
            :duration="1000"
            type="slide-bottom"
        >
            <div
                class="grid grid-cols-1 md:grid-cols-3 w-full h-full max-w-6xl gap-4 p-2 mx-auto bg-gray-100 md:rounded-md md:h-fit"
            >
                <div
                    class="flex flex-col flex-1 gap-4 col-span-2"
                    :class="{ 'my-auto': notFound }"
                >
                    <div
                        class="flex flex-col gap-2 p-4 bg-white border rounded-lg shadow-sm"
                    >
                        <div v-if="!hasEquipment" class="mt-3">
                            <SelectSearchField
                                id="equipment_selector"
                                label="Select equipment"
                                placeholder="Search by name, ID, brand, or barcode"
                                :options="equipmentOptions"
                                v-model="selectedEquipmentId"
                            />
                            <p class="mt-2 text-xs text-gray-500">
                                Scan the QR code if available, or search and
                                select equipment from the list.
                            </p>
                        </div>
                        <div v-else-if="loading" class="text-sm text-gray-500">
                            Loading equipment details...
                        </div>
                        <div
                            v-else-if="notFound"
                            class="flex flex-col items-center justify-center py-8"
                        >
                            <div class="text-center">
                                <error-icon
                                    class="w-12 h-12 mx-auto mb-3 text-red-500"
                                />
                                <h3
                                    class="mb-2 text-lg font-semibold text-gray-800"
                                >
                                    Equipment Not Found
                                </h3>
                                <p class="mb-4 text-sm text-gray-500">
                                    {{
                                        message ||
                                        "The equipment you are looking for could not be found."
                                    }}
                                </p>
                                <Link
                                    :href="route('laboratory.equipments.show')"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm text-white transition-opacity rounded bg-AB hover:bg-AB-dark"
                                    :class="
                                        isNavigating
                                            ? 'opacity-70 pointer-events-none'
                                            : ''
                                    "
                                >
                                    <span
                                        v-if="isNavigating"
                                        class="inline-flex"
                                    >
                                        <loader-icon
                                            class="flex-shrink-0 w-4 h-4 animate-spin"
                                        />
                                    </span>
                                    <span>Browse all equipment</span>
                                </Link>
                            </div>
                        </div>

                        <div
                            v-else-if="equipment"
                            class="grid grid-cols-1 gap-5 md:grid-cols-2"
                        >
                            <div
                                class="flex justify-between col-span-2 pb-1 leading-none"
                            >
                                <h1 class="text-xl font-bold uppercase">
                                    {{ equipment.name }}
                                </h1>
                                <button
                                    v-if="!equipment_id"
                                    @click="selectedEquipmentId = null"
                                >
                                    <close-icon class="w-6 h-6 text-red-600" />
                                </button>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-500 uppercase"
                                    >Brand</span
                                >
                                <span class="font-semibold">{{
                                    equipment.brand || "-"
                                }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-500 uppercase"
                                    >Description</span
                                >
                                <span class="font-semibold">{{
                                    equipment.description || "-"
                                }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-500 uppercase"
                                    >PhilRice Property No.</span
                                >
                                <span class="font-semibold">{{
                                    equipment.barcode_prri || "-"
                                }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-500 uppercase"
                                    >CBC Barcode</span
                                >
                                <span class="font-semibold">{{
                                    equipment.barcode || "-"
                                }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-500 uppercase"
                                    >Current Location</span
                                >
                                <span class="font-semibold">{{
                                    currentLocation?.label || "Unknown Location"
                                }}</span>
                            </div>
                            <div
                                v-if="shouldShowLocationSurvey"
                                class="flex flex-col gap-2 md:col-span-2"
                            >
                               <div>
                                 <span class="font-bold text-gray-800 uppercase">
                                    Temporary Location Survey
                                </span>
                                 <p class="text-sm text-gray-500">
                                    Report the current location if different from
                                    the known location. This helps us keep track
                                    of equipment whereabouts.
                                </p>
                               </div>
                                <TextInput
                                    id="survey_location_employee_id"
                                    v-model="locationSurveyForm.employee_id"
                                    label="PhilRice ID (Location Survey)"
                                    :error="
                                        getErrorMessage(
                                            locationSurveyErrors.employee_id,
                                        )
                                    "
                                    @keydown.enter.prevent="submitLocationSurvey"
                                    required
                                />
                                <TextInput
                                    id="survey_location_label"
                                    v-model="locationSurveyForm.location_label"
                                    label="Temporary Current Location"
                                    :datalist-id="'storage-location-suggestions'"
                                    :datalist-options="storageLocationOptions"
                                    :error="
                                        getErrorMessage(
                                            locationSurveyErrors.location_label,
                                        )
                                    "
                                    @keydown.enter.prevent="submitLocationSurvey"
                                    required
                                />
                                <div
                                    v-if="getErrorMessage(locationSurveyErrors.base)"
                                    class="text-sm text-red-600"
                                >
                                    {{ getErrorMessage(locationSurveyErrors.base) }}
                                </div>
                                <button
                                    type="button"
                                    class="w-full px-4 py-2 text-sm text-white rounded bg-AB hover:bg-AB-dark"
                                    @click="submitLocationSurvey"
                                >
                                    Save Temporary Location
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="hasEquipment && !notFound"
                        class="grid grid-cols-1 gap-4"
                    >
                        <div class="p-4 bg-white border rounded-lg shadow-sm">
                            <div class="flex justify-between items-center">
                                <h2
                                    class="mb-2 text-base font-semibold uppercase"
                                >
                                    Current Status
                                </h2>
                                <button
                                    v-if="activeLog"
                                    @click.prevent="
                                        showEstimatedEndUseModal =
                                            !showEstimatedEndUseModal
                                    "
                                    title="Edit Estimated time of use"
                                >
                                    <edit-icon
                                        class="w-4 h-4 text-yellow-500"
                                    />
                                </button>
                            </div>
                            <div
                                v-if="activeLog"
                                class="flex flex-col gap-1 text-sm"
                            >
                                <div class="flex justify-between gap-1">
                                    <span class="text-gray-500">Status</span>
                                    <span
                                        class="font-semibold uppercase flex items-center gap-2"
                                    >
                                        <div
                                            class="p-1 shadow-md bg-lime-500 animate-pulse rounded-full w-2 h-2"
                                        ></div>
                                        {{ activeLog.status }}</span
                                    >
                                </div>
                                <div class="flex justify-between gap-1">
                                    <span class="text-gray-500"
                                        >Checked in at</span
                                    >
                                    <span>{{
                                        formatDateTime(activeLog.started_at)
                                    }}</span>
                                </div>
                                <div class="flex justify-between gap-1">
                                    <span class="text-gray-500"
                                        >Expected end</span
                                    >
                                    <span>{{
                                        formatDateTime(activeLog.end_use_at)
                                    }}</span>
                                </div>
                                <div class="flex justify-between gap-1">
                                    <span class="text-gray-500">User</span>
                                    <span>{{
                                        formatPersonnelName(activeLog.personnel)
                                    }}</span>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500">
                                No active user for this equipment
                            </div>

                            <DialogModal
                                :show="showEstimatedEndUseModal && !!activeLog"
                                @close="
                                    resetUpdateEndUse;
                                    showEstimatedEndUseModal = false;
                                "
                            >
                                <template #title>
                                    Update Estimated End of Use
                                </template>
                                <template #content>
                                    <div
                                        class="pt-3 mt-3 border-t border-gray-100"
                                    >
                                        <div class="flex flex-col gap-2">
                                            <TextInput
                                                id="update_end_use_employee_id"
                                                v-model="
                                                    updateEndUseForm.employee_id
                                                "
                                                label="PhilRice ID"
                                                :error="
                                                    getErrorMessage(
                                                        updateEndUseErrors.employee_id,
                                                    )
                                                "
                                                @keydown.enter.prevent="
                                                    submitUpdateEndUse
                                                "
                                                required
                                            />
                                            <TextInput
                                                id="update_end_use_at"
                                                v-model="
                                                    updateEndUseForm.end_use_at
                                                "
                                                label="New Estimated End of Use"
                                                type="datetime-local"
                                                :error="
                                                    getErrorMessage(
                                                        updateEndUseErrors.end_use_at,
                                                    )
                                                "
                                                @keydown.enter.prevent="
                                                    submitUpdateEndUse
                                                "
                                                required
                                            />
                                            <div
                                                v-if="
                                                    getErrorMessage(
                                                        updateEndUseErrors.base,
                                                    )
                                                "
                                                class="text-sm text-red-600"
                                            >
                                                {{
                                                    getErrorMessage(
                                                        updateEndUseErrors.base,
                                                    )
                                                }}
                                            </div>
                                            <div
                                                class="flex flex-wrap gap-2 mt-2"
                                            >
                                                <button
                                                    type="button"
                                                    class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded-md"
                                                    @click="addMinutes(15)"
                                                >
                                                    +15 min
                                                </button>

                                                <button
                                                    type="button"
                                                    class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded-md"
                                                    @click="addMinutes(30)"
                                                >
                                                    +30 min
                                                </button>

                                                <button
                                                    type="button"
                                                    class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded-md"
                                                    @click="addMinutes(60)"
                                                >
                                                    +60 min
                                                </button>

                                                <button
                                                    type="button"
                                                    class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded-md"
                                                    @click="addMinutes(120)"
                                                >
                                                    +120 min
                                                </button>
                                                <button
                                                    type="button"
                                                    class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded-md"
                                                    @click="addMinutes(0)"
                                                >
                                                    Reset to default
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #footer>
                                    <button
                                        type="button"
                                        class="w-full px-4 py-2 text-sm text-white rounded bg-AB hover:bg-AB-dark"
                                        @click="submitUpdateEndUse"
                                    >
                                        Update End of Use
                                    </button>
                                </template>
                            </DialogModal>
                        </div>
                    </div>

                    <div
                        v-if="hasEquipment && !notFound && canCheckIn"
                        class="p-4 bg-white border rounded-lg shadow-sm flex flex-col gap-2"
                    >
                        <div class="grid grid-cols-1 gap-2 mb-3">
                            <h2 class="text-base font-bold uppercase">
                                Check-in Equipment
                            </h2>
                        </div>

                        <div class="flex flex-col gap-1">
                            <PersonnelLookup
                                v-model="checkInForm.employee_id"
                                @found="handlePersonnelFound"
                                @error="handlePersonnelError"
                            />
                            <div
                                v-if="personnelPreview"
                                class="w-full text-xs text-center text-AC"
                            >
                                Hi! {{ personnelPreview.fullName }}
                            </div>
                            <div
                                v-if="
                                    getErrorMessage(checkInErrors.employee_id)
                                "
                                class="w-full text-xs text-center text-red-600"
                            >
                                {{ getErrorMessage(checkInErrors.employee_id) }}
                            </div>
                        </div>

                        <TextInput
                            id="end_use_at"
                            v-model="checkInForm.end_use_at"
                            label="Estimated End of Use"
                            type="datetime-local"
                            :error="getErrorMessage(checkInErrors.end_use_at)"
                            @keydown.enter.prevent="submitCheckIn"
                            required
                        />
                        <TextInput
                            id="purpose"
                            v-model="checkInForm.purpose"
                            label="Purpose (optional)"
                            :error="getErrorMessage(checkInErrors.purpose)"
                            :datalist-id="'purpose-suggestions'"
                            :datalist-options="purposeSuggestions"
                            @keydown.enter.prevent="submitCheckIn"
                        />

                        <div
                            v-if="checkInErrors.base"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ checkInErrors.base }}
                        </div>

                        <button
                            type="button"
                            class="w-full px-4 py-2 mt-3 text-sm text-white rounded bg-AB hover:bg-AB-dark"
                            @click="submitCheckIn"
                        >
                            Check In Equipment
                        </button>
                    </div>

                    <div
                        v-if="hasEquipment && !notFound && canCheckOut"
                        class="flex flex-col justify-end gap-2 p-4 bg-white border rounded-lg shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between gap-5 px-2"
                        >
                            <h2 class="text-base font-bold uppercase w-fit">
                                Check-out Equipment
                            </h2>
                            <a
                                :href="
                                    route(
                                        'suppEquipReports.create.guest',
                                        equipment.barcode,
                                    )
                                "
                                target="_blank"
                                title="Report an issue with this equipment"
                                rel="noopener noreferrer"
                                class="flex flex-row items-center gap-1 p-1 px-2 text-xs text-red-600 rounded-full w-fit"
                            >
                                <flag-icon class="w-3 h-5" />
                                Report
                            </a>
                        </div>

                        <transition-container :duration="100" type="slide-left">
                            <div
                                v-if="
                                    savedLaboratoryPersonnel &&
                                    showPhilRiceField
                                "
                                key="saved-personnel"
                                class="flex items-center justify-between gap-2 px-2 py-3"
                            >
                                <div class="text-gray-600">
                                    As:
                                    <span class="font-semibold">
                                        {{ savedLaboratoryPersonnel.fullName }}
                                        (
                                        {{
                                            savedLaboratoryPersonnel.employee_id
                                        }}
                                        )
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    title="Switch Personnel"
                                    class="px-2 py-1 duration-200 bg-gray-200 rounded h-fit active:scale-90"
                                    @click="handlePersonnelSwitch"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        fill="currentColor"
                                        :class="[
                                            'transition-transform duration-300',
                                            isRotating ? 'rotate-[360deg]' : '',
                                        ]"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M1.146 8.354a.5.5 0 0 1 0-.708l2-2a.5.5 0 1 1 .708.708L2.707 7.5h10.586l-1.147-1.146a.5.5 0 0 1 .708-.708l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L13.293 8.5H2.707l1.147 1.146a.5.5 0 0 1-.708.708z"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <div
                                v-else
                                key="manual-personnel"
                                class="flex gap-0.5 flex-col"
                            >
                                <label
                                    for="checkout_employee_id"
                                    class="px-3 text-xs text-gray-500"
                                    >Enter your PhilRice ID</label
                                >
                                <div class="flex items-center gap-2 px-2">
                                    <div class="flex-1">
                                        <TextInput
                                            id="checkout_employee_id"
                                            v-model="checkOutForm.employee_id"
                                            :error="
                                                getErrorMessage(
                                                    checkOutErrors.employee_id,
                                                )
                                            "
                                            @keydown.enter.prevent="
                                                submitCheckOut
                                            "
                                        />
                                    </div>

                                    <button
                                        type="button"
                                        title="Switch Personnel"
                                        class="px-2 py-1 duration-200 bg-gray-200 rounded h-fit active:scale-90"
                                        @click="handlePersonnelSwitch"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="20"
                                            height="20"
                                            fill="currentColor"
                                            :class="[
                                                'transition-transform duration-300',
                                                isRotating
                                                    ? 'rotate-[360deg]'
                                                    : '',
                                            ]"
                                            viewBox="0 0 16 16"
                                        >
                                            <path
                                                d="M1.146 8.354a.5.5 0 0 1 0-.708l2-2a.5.5 0 1 1 .708.708L2.707 7.5h10.586l-1.147-1.146a.5.5 0 0 1 .708-.708l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L13.293 8.5H2.707l1.147 1.146a.5.5 0 0 1-.708.708z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </transition-container>

                        <div
                            v-if="getErrorMessage(checkOutErrors.base)"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ getErrorMessage(checkOutErrors.base) }}
                        </div>

                        <div class="flex flex-col gap-1">
                            <div
                                v-if="isAdmin"
                                class="flex items-center justify-end gap-2 px-2 w-fit"
                            >
                                <input
                                    id="admin_override"
                                    v-model="checkOutForm.admin_override"
                                    type="checkbox"
                                    class="rounded-full"
                                />
                                <label
                                    for="admin_override"
                                    class="text-xs leading-none"
                                    >Admin Override</label
                                >
                            </div>
                            <button
                                type="button"
                                class="w-full px-4 py-2 text-sm text-white rounded bg-AB hover:bg-AA"
                                @click="submitCheckOut"
                            >
                                Check Out Equipment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Active Equipments Sidebar -->
                <div class="flex flex-col w-full gap-4">
                    <div
                        class="relative h-full p-4 bg-white border rounded-lg shadow-sm"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-bold uppercase">
                                Currently Active
                            </h2>
                            <span
                                class="px-2 py-1 text-xs font-semibold text-white rounded-full bg-AB"
                                >{{ filteredActiveEquipments.length }}</span
                            >
                        </div>
                        <button
                            v-if="savedLaboratoryPersonnel"
                            @click="
                                filterActiveByPersonnel =
                                    !filterActiveByPersonnel
                            "
                            :class="[
                                'w-full mb-3 px-3 py-1.5 rounded text-xs font-semibold transition-colors',
                                filterActiveByPersonnel
                                    ? 'bg-AB text-white'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                            ]"
                        >
                            {{
                                filterActiveByPersonnel
                                    ? "My Equipment"
                                    : "All Equipment"
                            }}
                        </button>
                        <div
                            v-if="loadingActiveEquipments"
                            class="py-4 text-sm text-center text-gray-500"
                        >
                            Loading...
                        </div>
                        <div
                            v-else-if="filteredActiveEquipments.length === 0"
                            class="py-4 text-sm text-center text-gray-500"
                        >
                            No active equipments
                        </div>
                        <div
                            v-else
                            class="flex flex-col gap-2 max-h-[29rem] h-fit overflow-y-auto overflow-x-hidden"
                        >
                            <Link
                                v-for="item in filteredActiveEquipments"
                                :key="item.id"
                                class="relative flex items-start justify-between gap-2 p-3 leading-tight transition-colors border-l-4 rounded cursor-default border-AB bg-gray-50 hover:bg-gray-200"
                                :class="{
                                    'opacity-70 pointer-events-none':
                                        isNavigating,
                                    'pointer-events-none bg-gray-500 left-2 border-AA':
                                        equipment?.id === item.equipment_id,
                                }"
                                :href="
                                    route(
                                        'laboratory.equipments.show',
                                        item.equipment_id,
                                    )
                                "
                            >
                                <div
                                    class="flex-1 min-w-0"
                                    :class="
                                        equipment?.id === item.equipment_id
                                            ? 'text-white'
                                            : 'text-gray-600'
                                    "
                                >
                                    <h3 class="text-sm font-semibold truncate">
                                        {{ item.equipment?.name }}
                                        {{ "(" + item.equipment?.brand + ")" }}
                                    </h3>
                                    <p class="text-xs truncate">
                                        Checked in at
                                        <b>{{
                                            formatDateTime(item.started_at)
                                        }}</b>
                                    </p>
                                    <p class="text-xs truncate">
                                        Expected end at
                                        <b>{{
                                            formatDateTime(item.end_use_at)
                                        }}</b>
                                    </p>
                                    <div class="space-y-1 text-xs">
                                        <div v-if="item.personnel">
                                            <span>User:</span>
                                            <b>{{
                                                formatPersonnelName(
                                                    item.personnel,
                                                )
                                            }}</b>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </transition-container>
    </GuestFormPage>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}
</style>
