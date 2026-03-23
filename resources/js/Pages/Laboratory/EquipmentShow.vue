<script>
import { useForm, usePage, router } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import LaboratoryPersonnelMixin from "@/Modules/mixins/LaboratoryPersonnelMixin";

export default {
    name: "EquipmentShow",
    mixins: [ApiMixin, DataFormatterMixin, LaboratoryPersonnelMixin],
    props: {
        equipment_id: {
            type: String,
            default: null,
        },
        logger_type: {
            type: String,
            default: "laboratory",
        },
    },
    data() {
        return {
            delayReady: false,
            title:
                this.logger_type === "ict"
                    ? "ICT Equipment Logger"
                    : "Laboratory Equipment Logger",
            subtitle:
                this.logger_type === "ict"
                    ? "Track and manage ICT equipment usage"
                    : "Track and manage laboratory equipment usage",
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
        loggerType() {
            return this.logger_type === "ict" ? "ict" : "laboratory";
        },
        apiRoutePrefix() {
            return `api.${this.loggerType}.equipments`;
        },
        showPageRoute() {
            return `${this.loggerType}.equipments.show`;
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
        statusColor() {
            if (!this.activeLog) return "gray";
            if (this.isOverdue) return "red";
            return "emerald";
        },
        isOverdue() {
            if (!this.activeLog) return false;
            return this.isActiveItemOverdue(this.activeLog);
        },
    },
    methods: {
        async loadEquipmentOptions() {
            try {
                const response = await this.fetchGetApi(
                    `${this.apiRoutePrefix}.index`,
                );
                const payload = response?.data ?? response;
                const list = Array.isArray(payload)
                    ? payload
                    : (payload?.data ?? []);

                this.equipmentOptions = list.map((item) => {
                    const name = item.name || "Equipment";
                    const brand = item.brand ? ` • ${item.brand}` : "";
                    const barcode = item.barcode ? ` • ${item.barcode}` : "";
                    return {
                        id: item.equipment_id || item.id,
                        name: `${name}${brand}${barcode}`,
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
                    `${this.apiRoutePrefix}.active`,
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
            if (!this.equipmentId) return;
            this.loading = true;
            this.notFound = false;
            try {
                const response = await this.fetchGetApi(
                    `${this.apiRoutePrefix}.show`,
                    { routeParams: this.equipmentId },
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
                    "Equipment not found";
                this.notFound = true;
            } finally {
                this.loading = false;
                this.loadActiveEquipments();
            }
        },
        handlePersonnelFound(data) {
            this.personnelPreview = data;
            this.checkInErrors = { ...this.checkInErrors, employee_id: null };
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
            setTimeout(() => (this.isRotating = false), 300);
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
                this.updateEndUseForm.end_use_at = this.formatForDatetimeLocal(
                    this.activeLog.end_use_at,
                );
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
            try {
                await this.fetchPostApi(
                    `${this.apiRoutePrefix}.check-in`,
                    {
                        employee_id: this.checkInForm.employee_id,
                        end_use_at: this.checkInForm.end_use_at,
                        purpose: this.checkInForm.purpose,
                    },
                    { routeParams: this.equipmentId },
                );
                this.messageType = "success";
                this.message = "Equipment checked in successfully";
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
                        base: error?.response?.data?.message || "Check-in failed",
                    };
                }
            }
        },
        async submitCheckOut() {
            this.checkOutErrors = {};
            this.message = null;
            try {
                await this.fetchPostApi(
                    `${this.apiRoutePrefix}.check-out`,
                    {
                        employee_id: this.checkOutForm.employee_id,
                        admin_override: this.checkOutForm.admin_override,
                    },
                    { routeParams: this.equipmentId },
                );
                this.messageType = "success";
                this.message = "Equipment checked out successfully";
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
                        base: error?.response?.data?.message || "Check-out failed",
                    };
                }
            }
        },
        async submitUpdateEndUse() {
            this.updateEndUseErrors = {};
            this.message = null;
            try {
                await this.fetchPostApi(
                    `${this.apiRoutePrefix}.update-end-use`,
                    {
                        employee_id: this.updateEndUseForm.employee_id,
                        end_use_at: this.updateEndUseForm.end_use_at,
                    },
                    { routeParams: this.equipmentId },
                );
                this.messageType = "success";
                this.message = "End time updated successfully";
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
                        base: error?.response?.data?.message || "Update failed",
                    };
                }
            }
        },
        async submitLocationSurvey() {
            this.locationSurveyErrors = {};
            this.message = null;
            try {
                await this.fetchPostApi(
                    `${this.apiRoutePrefix}.report-location`,
                    {
                        employee_id: this.locationSurveyForm.employee_id,
                        location_label: this.locationSurveyForm.location_label,
                    },
                    { routeParams: this.equipmentId },
                );
                this.messageType = "success";
                this.message = "Location updated successfully";
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
                        base: error?.response?.data?.message || "Update failed",
                    };
                }
            }
        },
        formatPersonnelName(personnel) {
            if (!personnel) return "—";
            const parts = [
                personnel.fname,
                personnel.mname,
                personnel.lname,
                personnel.suffix,
            ]
                .filter(Boolean)
                .map((v) => String(v).trim())
                .filter(Boolean);
            return parts.length ? parts.join(" ") : "—";
        },
        formatForDatetimeLocal(value) {
            if (!value) return "";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "";
            const pad = (n) => String(n).padStart(2, "0");
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        },
        isActiveItemOverdue(item) {
            if (!item) return false;
            if (item.status === "overdue") return true;
            if (!item.end_use_at) return false;
            const endAt = new Date(item.end_use_at);
            return !Number.isNaN(endAt.getTime()) && endAt.getTime() < Date.now();
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
        equipmentId(newVal, oldVal) {
            if (newVal === oldVal) return;
            if (!newVal) {
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
        if (this.savedLaboratoryPersonnel?.employee_id) {
            this.showPhilRiceField = true;
            this.checkOutForm.employee_id =
                this.savedLaboratoryPersonnel.employee_id;
            this.updateEndUseForm.employee_id =
                this.savedLaboratoryPersonnel.employee_id;
            this.locationSurveyForm.employee_id =
                this.savedLaboratoryPersonnel.employee_id;
        }
        setTimeout(() => (this.delayReady = true), 200);

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
    
    <!-- Success Modal -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
    >
        <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-sm p-6 bg-white shadow-2xl rounded-2xl">
                <div class="flex flex-col items-center text-center">
                    <div class="p-3 mb-4 rounded-full bg-emerald-100">
                        <LuCheckCircle2 class="w-8 h-8 text-emerald-600" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">Success</h3>
                    <p class="mb-6 text-gray-600">{{ message }}</p>
                    <button
                        @click="showSuccessModal = false"
                        class="w-full px-4 py-2.5 text-sm font-medium text-white transition-colors bg-emerald-600 rounded-xl hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    >
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <GuestFormPage :title="title" :subtitle="subtitle" :delay-ready="delayReady" max-width="max-w-7xl">
        <!-- Loading Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="processing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-3 p-8 bg-white shadow-2xl rounded-2xl">
                    <LuLoader2 class="w-10 h-10 animate-spin text-emerald-600" />
                    <p class="text-sm font-medium text-gray-600">Processing...</p>
                </div>
            </div>
        </Transition>

        <Transition
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
        >
            <div v-show="delayReady" class="grid grid-cols-1 gap-6 p-4 mx-auto lg:grid-cols-12 max-w-7xl">
                
                <!-- Main Content Column -->
                <div class="space-y-6 lg:col-span-7">
                    
                    <!-- Equipment Selection / Details Card -->
                    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                        <!-- Empty State -->
                        <div v-if="!hasEquipment" class="p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2 rounded-lg bg-emerald-100">
                                    <LuScanLine class="w-6 h-6 text-emerald-600" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Select Equipment</h2>
                                    <p class="text-sm text-gray-500">Scan QR code or search manually</p>
                                </div>
                            </div>
                            <SelectSearchField
                                id="equipment_selector"
                                placeholder="Search by name, brand, or barcode..."
                                :options="equipmentOptions"
                                v-model="selectedEquipmentId"
                            />
                            <p class="flex items-center gap-2 mt-3 text-xs text-gray-500">
                                <LuSearch class="w-3.5 h-3.5" />
                                Type to search or scan barcode
                            </p>
                        </div>

                        <!-- Loading State -->
                        <div v-else-if="loading" class="flex items-center justify-center p-12">
                            <LuLoader2 class="w-8 h-8 animate-spin text-emerald-600" />
                        </div>

                        <!-- Not Found State -->
                        <div v-else-if="notFound" class="p-12 text-center">
                            <div class="inline-flex p-4 mb-4 rounded-full bg-red-100">
                                <LuAlertCircle class="w-8 h-8 text-red-600" />
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900">Equipment Not Found</h3>
                            <p class="max-w-xs mx-auto mb-6 text-sm text-gray-500">{{ message }}</p>
                            <Link
                                :href="route(showPageRoute)"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-emerald-600 rounded-lg hover:bg-emerald-700"
                                :class="{ 'opacity-70 pointer-events-none': isNavigating }"
                            >
                                <LuArrowLeft class="w-4 h-4" />
                                Browse All Equipment
                            </Link>
                        </div>

                        <!-- Equipment Details -->
                        <div v-else-if="equipment" class="divide-y divide-gray-100">
                            <!-- Header -->
                            <div class="flex items-start justify-between p-6">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-xl bg-emerald-100">
                                        <LuPackage class="w-6 h-6 text-emerald-600" />
                                    </div>
                                    <div>
                                        <h1 class="text-xl font-bold text-gray-900">{{ equipment.name }}</h1>
                                        <p class="text-sm text-gray-500">{{ equipment.brand || "No brand specified" }}</p>
                                    </div>
                                </div>
                                <button
                                    v-if="!equipment_id"
                                    @click="selectedEquipmentId = null"
                                    class="p-2 text-gray-400 transition-colors rounded-lg hover:bg-gray-100 hover:text-gray-600"
                                >
                                    <LuX class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-2 gap-6 p-6">
                                <div class="space-y-1">
                                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        <LuBarcode class="w-3.5 h-3.5" />
                                        PhilRice Property No.
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">{{ equipment.barcode_prri || "—" }}</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        <LuBarcode class="w-3.5 h-3.5" />
                                        CBC Barcode
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">{{ equipment.barcode || "—" }}</p>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        <LuBuilding class="w-3.5 h-3.5" />
                                        Current Location
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900">{{ currentLocation?.label || "Unknown" }}</p>
                                        <span
                                            v-if="currentLocation?.source === 'temporary'"
                                            class="px-2 py-0.5 text-xs font-medium text-amber-700 bg-amber-100 rounded-full"
                                        >
                                            Temporary
                                        </span>
                                    </div>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Description</label>
                                    <p class="text-sm text-gray-700">{{ equipment.description || "No description" }}</p>
                                </div>
                            </div>

                            <!-- Location Survey -->
                            <div v-if="shouldShowLocationSurvey" class="p-6 bg-amber-50/50 border-t border-amber-100">
                                <div class="flex items-center gap-2 mb-4">
                                    <LuMapPin class="w-4 h-4 text-amber-600" />
                                    <h3 class="text-sm font-semibold text-gray-900">Update Location</h3>
                                </div>
                                <p class="mb-4 text-xs text-gray-600">Report if equipment is in a different location</p>
                                <div class="space-y-3">
                                    <TextInput
                                        id="survey_location_employee_id"
                                        v-model="locationSurveyForm.employee_id"
                                        label="Your ID"
                                        :error="getErrorMessage(locationSurveyErrors.employee_id)"
                                        @keydown.enter.prevent="submitLocationSurvey"
                                    />
                                    <TextInput
                                        id="survey_location_label"
                                        v-model="locationSurveyForm.location_label"
                                        label="Current Location"
                                        :datalist-id="'storage-location-suggestions'"
                                        :datalist-options="storageLocationOptions"
                                        :error="getErrorMessage(locationSurveyErrors.location_label)"
                                        @keydown.enter.prevent="submitLocationSurvey"
                                    />
                                    <div v-if="getErrorMessage(locationSurveyErrors.base)" class="text-sm text-red-600">
                                        {{ getErrorMessage(locationSurveyErrors.base) }}
                                    </div>
                                    <button
                                        type="button"
                                        @click="submitLocationSurvey"
                                        class="w-full px-4 py-2.5 text-sm font-medium text-white transition-colors bg-amber-600 rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                    >
                                        Update Location
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Card -->
                    <div v-if="hasEquipment && !notFound && equipment" class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                        <div class="flex items-center justify-between p-6 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="p-2 rounded-lg"
                                    :class="isOverdue ? 'bg-red-100' : activeLog ? 'bg-emerald-100' : 'bg-gray-100'"
                                >
                                    <LuActivity
                                        class="w-5 h-5"
                                        :class="isOverdue ? 'text-red-600' : activeLog ? 'text-emerald-600' : 'text-gray-600'"
                                    />
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-900">Current Status</h2>
                                    <p class="text-xs text-gray-500">
                                        {{ isOverdue ? 'Overdue' : activeLog ? 'In Use' : 'Available' }}
                                    </p>
                                </div>
                            </div>
                            <button
                                v-if="activeLog"
                                @click="showEstimatedEndUseModal = true"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors bg-emerald-50 rounded-lg hover:bg-emerald-100"
                            >
                                <LuEdit class="w-3.5 h-3.5" />
                                Edit Time
                            </button>
                        </div>

                        <div v-if="activeLog" class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Status</span>
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full"
                                    :class="isOverdue ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'"
                                >
                                    <span
                                        class="w-1.5 h-1.5 rounded-full animate-pulse"
                                        :class="isOverdue ? 'bg-red-500' : 'bg-emerald-500'"
                                    />
                                    {{ isOverdue ? 'Overdue' : activeLog.status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Checked In</span>
                                <span class="flex items-center gap-1.5 text-sm font-medium text-gray-900">
                                    <LuCalendar class="w-4 h-4 text-gray-400" />
                                    {{ formatDateTime(activeLog.started_at) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Expected Return</span>
                                <span
                                    class="flex items-center gap-1.5 text-sm font-medium"
                                    :class="isOverdue ? 'text-red-600' : 'text-gray-900'"
                                >
                                    <LuClock class="w-4 h-4" :class="isOverdue ? 'text-red-400' : 'text-gray-400'" />
                                    {{ formatDateTime(activeLog.end_use_at) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">Current User</span>
                                <span class="flex items-center gap-1.5 text-sm font-medium text-gray-900">
                                    <LuUser class="w-4 h-4 text-gray-400" />
                                    {{ formatPersonnelName(activeLog.personnel) }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center justify-center p-12 text-center">
                            <div class="p-3 mb-3 rounded-full bg-gray-100">
                                <LuPackage class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-sm text-gray-500">No active session</p>
                            <p class="text-xs text-gray-400">Equipment is available for check-in</p>
                        </div>
                    </div>

                    <!-- Check-in Form -->
                    <div v-if="hasEquipment && !notFound && canCheckIn" class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                        <div class="p-6 border-b border-gray-100 bg-emerald-50/30">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-emerald-100">
                                    <LuLogIn class="w-5 h-5 text-emerald-600" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-900">Check In Equipment</h2>
                                    <p class="text-xs text-gray-500">Start a new usage session</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <PersonnelLookup
                                v-model="checkInForm.employee_id"
                                @found="handlePersonnelFound"
                                @error="handlePersonnelError"
                            />
                            
                            <div v-if="personnelPreview" class="flex items-center gap-2 p-3 text-sm text-emerald-700 rounded-lg bg-emerald-50">
                                <LuCheckCircle2 class="w-4 h-4" />
                                <span class="font-medium">{{ personnelPreview.fullName }}</span>
                            </div>

                            <div v-if="getErrorMessage(checkInErrors.employee_id)" class="text-sm text-red-600">
                                {{ getErrorMessage(checkInErrors.employee_id) }}
                            </div>

                            <TextInput
                                id="end_use_at"
                                v-model="checkInForm.end_use_at"
                                label="Expected Return Time"
                                type="datetime-local"
                                :error="getErrorMessage(checkInErrors.end_use_at)"
                                @keydown.enter.prevent="submitCheckIn"
                            />

                            <TextInput
                                id="purpose"
                                v-model="checkInForm.purpose"
                                label="Purpose (optional)"
                                placeholder="What will you use this for?"
                                :datalist-id="'purpose-suggestions'"
                                :datalist-options="purposeSuggestions"
                                :error="getErrorMessage(checkInErrors.purpose)"
                                @keydown.enter.prevent="submitCheckIn"
                            />

                            <div v-if="checkInErrors.base" class="p-3 text-sm text-red-600 rounded-lg bg-red-50">
                                {{ checkInErrors.base }}
                            </div>

                            <button
                                type="button"
                                @click="submitCheckIn"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white transition-all bg-emerald-600 rounded-xl hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                            >
                                <LuLogIn class="w-4 h-4" />
                                Check In Equipment
                            </button>
                        </div>
                    </div>

                    <!-- Check-out Form -->
                    <div v-if="hasEquipment && !notFound && canCheckOut" class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
                        <div class="p-6 border-b border-gray-100 bg-amber-50/30">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-amber-100">
                                        <LuLogOut class="w-5 h-5 text-amber-600" />
                                    </div>
                                    <div>
                                        <h2 class="text-sm font-semibold text-gray-900">Check Out Equipment</h2>
                                        <p class="text-xs text-gray-500">End current usage session</p>
                                    </div>
                                </div>
                                <a
                                    :href="route('suppEquipReports.create.guest', equipment.barcode)"
                                    target="_blank"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors bg-red-50 rounded-lg hover:bg-red-100"
                                >
                                    <LuFlag class="w-3.5 h-3.5" />
                                    Report Issue
                                </a>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <Transition mode="out-in" name="fade-slide">
                                <div v-if="savedLaboratoryPersonnel && showPhilRiceField" key="saved" class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-200">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-emerald-100">
                                            <LuUser class="w-4 h-4 text-emerald-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ savedLaboratoryPersonnel.fullName }}</p>
                                            <p class="text-xs text-gray-500">{{ savedLaboratoryPersonnel.employee_id }}</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="handlePersonnelSwitch"
                                        class="p-2 text-gray-500 transition-colors rounded-lg hover:bg-gray-200"
                                        :class="{ 'animate-spin': isRotating }"
                                    >
                                        <LuRefreshCw class="w-4 h-4" />
                                    </button>
                                </div>

                                <div v-else key="manual" class="space-y-3">
                                    <label class="text-sm font-medium text-gray-700">Enter Your ID</label>
                                    <div class="flex gap-2">
                                        <TextInput
                                            id="checkout_employee_id"
                                            v-model="checkOutForm.employee_id"
                                            placeholder="PhilRice ID"
                                            class="flex-1"
                                            :error="getErrorMessage(checkOutErrors.employee_id)"
                                            @keydown.enter.prevent="submitCheckOut"
                                        />
                                        <button
                                            type="button"
                                            @click="handlePersonnelSwitch"
                                            class="px-3 py-2 text-gray-600 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200"
                                            title="Use saved profile"
                                        >
                                            <LuUser class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </Transition>

                            <div v-if="getErrorMessage(checkOutErrors.base)" class="p-3 text-sm text-red-600 rounded-lg bg-red-50">
                                {{ getErrorMessage(checkOutErrors.base) }}
                            </div>

                            <div v-if="isAdmin" class="flex items-center gap-2 p-3 rounded-lg bg-gray-50">
                                <input
                                    id="admin_override"
                                    v-model="checkOutForm.admin_override"
                                    type="checkbox"
                                    class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                                />
                                <label for="admin_override" class="text-sm text-gray-700">Admin Override</label>
                            </div>

                            <button
                                type="button"
                                @click="submitCheckOut"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white transition-all bg-amber-600 rounded-xl hover:bg-amber-700 hover:shadow-lg hover:shadow-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                            >
                                <LuLogOut class="w-4 h-4" />
                                Check Out Equipment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Active Equipment -->
                <div class="lg:col-span-5">
                    <div class="sticky overflow-hidden bg-white border border-gray-200 shadow-sm top-4 rounded-2xl">
                        <div class="flex items-center justify-between p-5 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-blue-100">
                                    <LuActivity class="w-5 h-5 text-blue-600" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-900">Active Sessions</h2>
                                    <p class="text-xs text-gray-500">{{ filteredActiveEquipments.length }} equipment in use</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="savedLaboratoryPersonnel" class="p-4 border-b border-gray-100 bg-gray-50/50">
                            <button
                                @click="filterActiveByPersonnel = !filterActiveByPersonnel"
                                class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium transition-colors rounded-lg"
                                :class="filterActiveByPersonnel ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                            >
                                <span class="flex items-center gap-2">
                                    <LuUser class="w-4 h-4" />
                                    {{ filterActiveByPersonnel ? "Showing My Equipment" : "Show My Equipment Only" }}
                                </span>
                                <LuChevronRight
                                    class="w-4 h-4 transition-transform"
                                    :class="filterActiveByPersonnel ? 'rotate-90' : ''"
                                />
                            </button>
                        </div>

                        <div v-if="loadingActiveEquipments" class="flex items-center justify-center p-12">
                            <LuLoader2 class="w-6 h-6 animate-spin text-gray-400" />
                        </div>

                        <div v-else-if="filteredActiveEquipments.length === 0" class="flex flex-col items-center justify-center p-12 text-center">
                            <div class="p-3 mb-3 rounded-full bg-gray-100">
                                <LuPackage class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-sm font-medium text-gray-900">No active sessions</p>
                            <p class="text-xs text-gray-500 mt-1">All equipment is currently available</p>
                        </div>

                        <div v-else class="divide-y divide-gray-100 max-h-[calc(100vh-300px)] overflow-y-auto">
                            <Link
                                v-for="item in filteredActiveEquipments"
                                :key="item.id"
                                :href="route(showPageRoute, item.equipment_id)"
                                class="flex items-start gap-3 p-4 transition-colors hover:bg-gray-50"
                                :class="{
                                    'bg-blue-50/50 border-l-4 border-blue-500': equipment?.id === item.equipment_id,
                                    'border-l-4 border-transparent': equipment?.id !== item.equipment_id,
                                }"
                            >
                                <div
                                    class="flex-shrink-0 w-2 h-2 mt-2 rounded-full"
                                    :class="isActiveItemOverdue(item) ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'"
                                />
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ item.equipment?.name }}
                                        </h3>
                                        <span
                                            v-if="isActiveItemOverdue(item)"
                                            class="flex-shrink-0 px-1.5 py-0.5 text-[10px] font-bold text-red-700 uppercase bg-red-100 rounded"
                                        >
                                            Overdue
                                        </span>
                                    </div>
                                    
                                    <p class="text-xs text-gray-500 mt-0.5">{{ item.equipment?.brand }}</p>
                                    
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <LuClock class="w-3.5 h-3.5" />
                                            {{ formatDateTime(item.end_use_at, 'time') }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <LuUser class="w-3.5 h-3.5" />
                                            {{ formatPersonnelName(item.personnel).split(' ')[0] }}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Edit End Time Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showEstimatedEndUseModal && activeLog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="w-full max-w-md p-6 bg-white shadow-2xl rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-emerald-100">
                                <LuTimer class="w-5 h-5 text-emerald-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Extend Usage Time</h3>
                        </div>
                        <button
                            @click="showEstimatedEndUseModal = false; resetUpdateEndUse()"
                            class="p-2 text-gray-400 transition-colors rounded-lg hover:bg-gray-100"
                        >
                            <LuX class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <TextInput
                            id="update_end_use_employee_id"
                            v-model="updateEndUseForm.employee_id"
                            label="Your ID"
                            :error="getErrorMessage(updateEndUseErrors.employee_id)"
                            @keydown.enter.prevent="submitUpdateEndUse"
                        />

                        <TextInput
                            id="update_end_use_at"
                            v-model="updateEndUseForm.end_use_at"
                            label="New End Time"
                            type="datetime-local"
                            :error="getErrorMessage(updateEndUseErrors.end_use_at)"
                            @keydown.enter.prevent="submitUpdateEndUse"
                        />

                        <div v-if="getErrorMessage(updateEndUseErrors.base)" class="p-3 text-sm text-red-600 rounded-lg bg-red-50">
                            {{ getErrorMessage(updateEndUseErrors.base) }}
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="min in [15, 30, 60, 120]"
                                :key="min"
                                type="button"
                                @click="addMinutes(min)"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors bg-emerald-50 rounded-lg hover:bg-emerald-100"
                            >
                                <LuPlus class="w-3 h-3" />
                                {{ min }}m
                            </button>
                            <button
                                type="button"
                                @click="addMinutes(0)"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200"
                            >
                                <LuRefreshCw class="w-3 h-3" />
                                Reset
                            </button>
                        </div>

                        <button
                            type="button"
                            @click="submitUpdateEndUse"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white transition-colors bg-emerald-600 rounded-xl hover:bg-emerald-700"
                        >
                            <LuCheckCircle2 class="w-4 h-4" />
                            Update Time
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </GuestFormPage>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

/* Custom scrollbar for the active list */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 20px;
}
</style>