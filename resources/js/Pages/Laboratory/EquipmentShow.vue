<script>
import { useForm, router } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import LaboratoryPersonnelMixin from "@/Modules/mixins/LaboratoryPersonnelMixin";
import { LuQrCode, LuMapPin, LuEdit } from "@/Components/Icons";
import { SingleSelectTagify } from "@/Components";

export default {
    name: "EquipmentShow",
    components: {
        LuQrCode,
        LuMapPin,
        LuEdit,
        SingleSelectTagify,
    },
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
            activeLogs: [],
            allowedActions: [],
            maxEndUseHours: 24,
            purposeSuggestions: [],
            currentLocation: null,
            storageLocationOptions: [],
            loading: false,
            isLoading: false,
            loadingActiveEquipments: false,
            activeEquipments: [],
            activeEquipmentsRequest: null,
            message: null,
            messageType: "success",
            notFoundTitle: "Equipment Not Found",
            showSuccessModal: false,
            personnelPreview: null,
            profileRequiresUpdate: false,
            emailRequired: false,
            checkInErrors: {},
            checkOutErrors: {},
            updateEndUseErrors: {},
            locationSurveyErrors: {},
            personnelProfileErrors: {},
            emailCaptureErrors: {},
            checkInForm: useForm({
                employee_id: "",
                end_use_at: "",
                purpose: "",
            }),
            personnelProfileForm: useForm({
                employee_id: "",
                fname: "",
                mname: "",
                lname: "",
                suffix: "",
                position: "",
                phone: "",
                address: "",
                email: "",
            }),
            emailCaptureForm: useForm({
                employee_id: "",
                email: "",
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
            showLocationSurveyModal: false,
            showEmailCaptureModal: false,
            showFinalizeLocationModal: false,
            lastResolvedPersonnelId: null,
        };
    },
    computed: {
        equipmentIdFromUrl() {
            if (typeof window === "undefined") {
                return null;
            }

            const segments = window.location.pathname
                .split("/")
                .map((segment) => segment.trim())
                .filter(Boolean);

            if (segments.length < 3) {
                return null;
            }

            const [scope, resource, ...rest] = segments;
            if (
                (scope !== "laboratory" && scope !== "ict") ||
                resource !== "equipments" ||
                rest.length === 0
            ) {
                return null;
            }

            const identifier = rest.join("/");
            return identifier ? decodeURIComponent(identifier) : null;
        },
        equipmentId() {
            return this.selectedEquipmentId || this.equipment_id || this.equipmentIdFromUrl || null;
        },
        loggerType() {
            return this.logger_type === "ict" ? "ict" : "laboratory";
        },
        apiRoutePrefix() {
            return `api.${this.loggerType}.equipments`;
        },
        apiGuestBasePath() {
            return this.loggerType === "ict"
                ? "/api/guest/ict/equipments"
                : "/api/guest/lab/equipments";
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
            return this.$isAdminUser;
        },
        authenticatedPersonnel() {
            const user = this.$currentUser;
            if (!user?.employee_id) {
                return null;
            }

            return {
                employee_id: user.employee_id,
                fullName: user.name || user.employee_id,
                fname: user.name || "",
                mname: "",
                lname: "",
                suffix: "",
                email: user.email || "",
                has_email: !!user.email,
                profile_requires_update: false,
            };
        },
        currentLaboratoryPersonnel() {
            return this.savedLaboratoryPersonnel || this.authenticatedPersonnel;
        },
        statusCardPersonnel() {
            return this.activeLog?.personnel || this.currentLaboratoryPersonnel;
        },
        canEditActiveLog() {
            return !!this.activeLog;
        },
        canReportLocation() {
            return true;
        },
        shouldShowLocationSurvey() {
            return !this.currentLocation || this.currentLocation.label === "Unknown Location" || this.currentLocation.label === "Unknown" || this.currentLocation.label === "UNKNOWN LOCATION";
        },
        filteredActiveEquipments() {
            if (
                !this.filterActiveByPersonnel ||
                !this.currentLaboratoryPersonnel?.employee_id
            ) {
                return this.activeEquipments;
            }
            return this.activeEquipments.filter(
                (item) =>
                    item.personnel?.employee_id ===
                    this.currentLaboratoryPersonnel.employee_id,
            );
        },
        statusColor() {
            if (!this.activeLogs || this.activeLogs.length === 0) return "gray";
            if (this.isOverdue) return "red";
            return "emerald";
        },
        isOverdue() {
            if (!this.activeLogs || this.activeLogs.length === 0) return false;
            return this.activeLogs.some(log => this.isActiveItemOverdue(log));
        },
    },
    methods: {
        equipmentApiPath(identifier = null, action = null) {
            const encodedIdentifier = identifier
                ? encodeURIComponent(String(identifier))
                : null;

            if (!encodedIdentifier) {
                return this.apiGuestBasePath;
            }

            return action
                ? `${this.apiGuestBasePath}/${encodedIdentifier}/${action}`
                : `${this.apiGuestBasePath}/${encodedIdentifier}`;
        },
        async loadEquipmentOptions() {
            try {
                let response;
                try {
                    response = await this.fetchGetApi(`${this.apiRoutePrefix}.index`);
                } catch (error) {
                    response = await window.axios.get(this.equipmentApiPath());
                }
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
            if (this.activeEquipmentsRequest) {
                return this.activeEquipmentsRequest;
            }

            this.loadingActiveEquipments = true;
            const request = (async () => {
                try {
                    let response;
                    try {
                        response = await this.fetchGetApi(
                            `${this.apiRoutePrefix}.active`,
                        );
                    } catch (error) {
                        response = await window.axios.get(`${this.apiGuestBasePath}/active`);
                    }
                    const payload = response?.data ?? response;
                    this.activeEquipments = Array.isArray(payload)
                        ? payload
                        : (payload?.data ?? []);
                } catch (error) {
                    this.activeEquipments = [];
                } finally {
                    this.loadingActiveEquipments = false;
                    this.activeEquipmentsRequest = null;
                }
            })();

            this.activeEquipmentsRequest = request;
            return request;
        },
        async loadEquipment() {
            if (!this.equipmentId) return;
            this.loading = true;
            this.notFound = false;
            this.notFoundTitle = "Equipment Not Found";
            try {
                let response;
                try {
                    response = await this.fetchGetApi(
                        `${this.apiRoutePrefix}.show`,
                        { routeParams: this.equipmentId },
                    );
                } catch (error) {
                    response = await window.axios.get(this.equipmentApiPath(this.equipmentId));
                }
                const details = response?.data ?? response;

                this.equipment = details?.equipment ?? null;
                this.activeLogs = details?.active_logs ?? [];
                
                if (this.currentLaboratoryPersonnel?.employee_id) {
                    this.activeLog = this.activeLogs.find(log => log.personnel?.employee_id === this.currentLaboratoryPersonnel.employee_id) || null;
                } else {
                    this.activeLog = null;
                }
                
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
                    this.currentLaboratoryPersonnel?.employee_id &&
                    !this.updateEndUseForm.employee_id
                ) {
                    this.updateEndUseForm.employee_id =
                        this.currentLaboratoryPersonnel.employee_id;
                }

                if (!this.locationSurveyForm.location_label) {
                    this.locationSurveyForm.location_label =
                        this.currentLocation?.label || "";
                }

                if (
                    this.currentLaboratoryPersonnel?.employee_id &&
                    !this.locationSurveyForm.employee_id
                ) {
                    this.locationSurveyForm.employee_id =
                        this.currentLaboratoryPersonnel.employee_id;
                }

                if (this.shouldShowLocationSurvey) {
                    this.openLocationSurveyModal();
                } else {
                    this.showLocationSurveyModal = false;
                }
            } catch (error) {
                this.equipment = null;
                this.activeLog = null;
                this.allowedActions = [];
                this.currentLocation = null;
                this.storageLocationOptions = [];
                this.purposeSuggestions = [];
                this.notFoundTitle = error?.response?.status === 404
                    ? "Equipment Not Found"
                    : "Equipment can't be used";
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
            this.lastResolvedPersonnelId = data.employee_id || this.checkInForm.employee_id || null;
            this.profileRequiresUpdate = !!data.profile_requires_update;
            this.emailRequired = !data.profile_requires_update && !data.has_email;
            this.showEmailCaptureModal = this.emailRequired;
            this.checkInErrors = { ...this.checkInErrors, employee_id: null };
            this.personnelProfileErrors = {};
            this.personnelProfileForm.employee_id = data.employee_id || this.checkInForm.employee_id;
            this.personnelProfileForm.fname = data.fname || "";
            this.personnelProfileForm.mname = data.mname || "";
            this.personnelProfileForm.lname = data.lname || "";
            this.personnelProfileForm.suffix = data.suffix || "";
            this.personnelProfileForm.position = data.position || "";
            this.personnelProfileForm.phone = data.phone || "";
            this.personnelProfileForm.address = data.address || "";
            this.personnelProfileForm.email = data.email || "";
            this.emailCaptureForm.employee_id = data.employee_id || this.checkInForm.employee_id;
            this.emailCaptureForm.email = data.email || "";
            if (this.emailRequired) {
                this.emailCaptureErrors = {};
            }
            this.saveLaboratoryPersonnel({
                employee_id: data.employee_id || this.checkInForm.employee_id,
                fullName: data.fullName,
                fname: data.fname,
                mname: data.mname,
                lname: data.lname,
                suffix: data.suffix,
                position: data.position,
                phone: data.phone,
                address: data.address,
                email: data.email,
                has_email: data.has_email,
                profile_requires_update: data.profile_requires_update,
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
            this.profileRequiresUpdate = false;
            this.emailRequired = false;
            this.showEmailCaptureModal = false;
        },
        resetPersonnelLookupState() {
            this.personnelPreview = null;
            this.profileRequiresUpdate = false;
            this.emailRequired = false;
            this.personnelProfileErrors = {};
            this.emailCaptureErrors = {};
            this.showEmailCaptureModal = false;
            this.lastResolvedPersonnelId = null;
        },
        async ensurePersonnelResolved(force = false) {
            const employeeId = (this.checkInForm.employee_id || "").trim();

            if (!employeeId) {
                this.checkInErrors = {
                    ...this.checkInErrors,
                    employee_id: "PhilRice ID or CBC ID is required",
                };
                return false;
            }

            if (!force && this.lastResolvedPersonnelId === employeeId && this.personnelPreview) {
                return true;
            }

            const payload = await this.$refs.personnelLookup?.searchPersonnel?.();
            return !!payload;
        },
        searchDifferentPersonnel() {
            this.checkOutErrors = {};
            this.showPhilRiceField = !this.showPhilRiceField;
            if (
                this.showPhilRiceField &&
                this.currentLaboratoryPersonnel?.employee_id
            ) {
                this.checkOutForm.employee_id =
                    this.currentLaboratoryPersonnel.employee_id;
                return;
            }
            this.checkOutForm.employee_id = "";
        },
        resetCheckIn() {
            this.checkInForm.reset();
            this.checkInErrors = {};
            if (this.currentLaboratoryPersonnel?.employee_id) {
                this.checkInForm.employee_id = this.currentLaboratoryPersonnel.employee_id;
            }
            this.resetPersonnelLookupState();
            this.personnelProfileForm.reset();
            this.emailCaptureForm.reset();
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
            if (this.currentLaboratoryPersonnel?.employee_id) {
                this.updateEndUseForm.employee_id =
                    this.currentLaboratoryPersonnel.employee_id;
            } else {
                this.updateEndUseForm.employee_id = "";
            }
        },
        resetLocationSurvey() {
            this.locationSurveyErrors = {};
            this.locationSurveyForm.location_label =
                this.currentLocation?.label || "";
            if (this.currentLaboratoryPersonnel?.employee_id) {
                this.locationSurveyForm.employee_id =
                    this.currentLaboratoryPersonnel.employee_id;
            } else {
                this.locationSurveyForm.employee_id = "";
            }
        },
        syncCurrentPersonnelContext() {
            if (!this.currentLaboratoryPersonnel?.employee_id) {
                return;
            }

            this.checkInForm.employee_id =
                this.checkInForm.employee_id || this.currentLaboratoryPersonnel.employee_id;
            this.showPhilRiceField = true;
            this.checkOutForm.employee_id =
                this.currentLaboratoryPersonnel.employee_id;
            this.updateEndUseForm.employee_id =
                this.currentLaboratoryPersonnel.employee_id;
            this.locationSurveyForm.employee_id =
                this.currentLaboratoryPersonnel.employee_id;

            if (
                !this.savedLaboratoryPersonnel?.employee_id &&
                this.authenticatedPersonnel?.employee_id
            ) {
                this.saveLaboratoryPersonnel(this.authenticatedPersonnel);
            }
        },
        openLocationSurveyModal() {
            if (!this.canReportLocation) return;
            this.resetLocationSurvey();
            this.showLocationSurveyModal = true;
        },
        closeLocationSurveyModal() {
            this.showLocationSurveyModal = false;
            this.resetLocationSurvey();
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
        async finalizeLocation() {
            this.isLoading = true;
            try {
                // apiRoutePrefix could be "api.laboratory.equipments" but the route we added is "api.equipment-logger.equipments.finalize-location"
                await axios.post(this.route("api.equipment-logger.equipments.finalize-location", this.equipmentId));
                this.messageType = "success";
                this.message = "Location finalized successfully";
                this.showSuccessModal = true;
                this.showFinalizeLocationModal = false;
                await this.loadEquipment();
            } catch (error) {
                this.messageType = "error";
                this.message = error?.response?.data?.message || "Failed to finalize location";
                this.showSuccessModal = true; // Use the same modal to show errors as the UI does
            } finally {
                this.isLoading = false;
            }
        },

        async submitCheckIn() {
            this.checkInErrors = {};
            this.message = null;
            const resolved = await this.ensurePersonnelResolved();
            if (!resolved) {
                return;
            }
            if (this.profileRequiresUpdate) {
                this.checkInErrors = {
                    base: "Please update your personnel information before checking in equipment.",
                };
                return;
            }
            if (this.emailRequired) {
                this.checkInErrors = {
                    base: "Please provide your email before checking in equipment.",
                };
                this.showEmailCaptureModal = true;
                return;
            }
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
                    if (
                        String(error?.response?.data?.message || "")
                            .toLowerCase()
                            .includes("provide your email")
                    ) {
                        this.showEmailCaptureModal = true;
                        this.emailRequired = true;
                    }
                }
            }
        },
        async submitPersonnelProfileUpdate() {
            this.personnelProfileErrors = {};
            this.message = null;

            try {
                const response = await this.fetchPostApi(
                    "api.inventory.personnels.initialize-profile.guest",
                    this.personnelProfileForm.data(),
                );
                const payload = response?.data ?? response ?? {};
                const record = payload?.data ?? {};

                this.profileRequiresUpdate = false;
                this.personnelPreview = {
                    ...this.personnelPreview,
                    ...record,
                    employee_id: this.personnelProfileForm.employee_id,
                    profile_requires_update: false,
                };
                this.emailRequired = !record?.has_email;
                this.saveLaboratoryPersonnel({
                    employee_id: this.personnelProfileForm.employee_id,
                    fullName: this.personnelPreview.fullName,
                    fname: this.personnelPreview.fname,
                    mname: this.personnelPreview.mname,
                    lname: this.personnelPreview.lname,
                    suffix: this.personnelPreview.suffix,
                    position: this.personnelPreview.position,
                    phone: this.personnelPreview.phone,
                    address: this.personnelPreview.address,
                    email: this.personnelPreview.email,
                    has_email: record?.has_email,
                    profile_requires_update: false,
                });
                this.messageType = "success";
                this.message = payload?.message || "Personnel information updated successfully";
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.personnelProfileErrors = error.response.data.errors || {
                        base: error.response.data.message,
                    };
                } else {
                    this.personnelProfileErrors = {
                        base: error?.response?.data?.message || "Unable to update personnel information.",
                    };
                }
            }
        },
        async submitEmailCapture() {
            this.emailCaptureErrors = {};
            this.message = null;

            try {
                const response = await this.fetchPutApi(
                    "api.inventory.personnels.email.guest",
                    null,
                    this.emailCaptureForm.data(),
                );
                const payload = response?.data ?? response ?? {};
                const record = payload?.data ?? {};

                this.emailRequired = !record?.has_email;
                this.showEmailCaptureModal = false;
                this.personnelPreview = {
                    ...this.personnelPreview,
                    email: record?.email || this.emailCaptureForm.email,
                    has_email: record?.has_email !== false,
                };
                this.saveLaboratoryPersonnel({
                    ...(this.currentLaboratoryPersonnel || {}),
                    ...(this.personnelPreview || {}),
                    employee_id: this.emailCaptureForm.employee_id,
                    email: record?.email || this.emailCaptureForm.email,
                    has_email: record?.has_email !== false,
                    profile_requires_update: false,
                });
                this.messageType = "success";
                this.message = payload?.message || "Email updated successfully.";
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.emailCaptureErrors = error.response.data.errors || {
                        base: error.response.data.message,
                    };
                } else {
                    this.emailCaptureErrors = {
                        base: error?.response?.data?.message || "Unable to update email.",
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
                this.showLocationSurveyModal = false;
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
                this.showLocationSurveyModal = false;
                return;
            }
            this.loadEquipment();
        },
        "checkInForm.employee_id"(newValue, oldValue) {
            if ((newValue || "").trim() === (oldValue || "").trim()) {
                return;
            }

            if ((newValue || "").trim() !== (this.lastResolvedPersonnelId || "").trim()) {
                this.resetPersonnelLookupState();
            }
        },
        "currentLaboratoryPersonnel.employee_id"(newVal) {
            if (this.activeLogs) {
                this.activeLog = this.activeLogs.find(log => log.personnel?.employee_id === newVal) || null;
            }
        }
    },
        mounted() {
            if (!this.selectedEquipmentId && this.equipmentIdFromUrl) {
                this.selectedEquipmentId = this.equipmentIdFromUrl;
            }

        this.loadLaboratoryPersonnel();
        this.syncCurrentPersonnelContext();

        if (this.equipmentId) {
            this.loadEquipment();
        } else {
            this.loadEquipmentOptions();
            this.loadActiveEquipments();
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
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
        <div v-if="showSuccessModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-sm p-4 bg-white shadow-2xl rounded-2xl">
                <div class="flex flex-col items-center text-center">
                    <div class="p-3 mb-4 rounded-full bg-emerald-100">
                        <LuCheckCircle2 class="w-8 h-8 text-emerald-600" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">Success</h3>
                    <p class="mb-6 text-gray-600">{{ message }}</p>
                    <button @click="showSuccessModal = false"
                        class="w-full px-4 py-2.5 text-sm font-medium text-white transition-colors bg-emerald-600 rounded-xl hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <GuestFormPage :title="title" :subtitle="subtitle" :delay-ready="delayReady" guide-key="equipment-logger-guest" max-width="max-w-7xl">
        <!-- Loading Overlay -->
        <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="processing"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-3 p-8 bg-white shadow-2xl rounded-2xl">
                    <LuLoader2 class="w-10 h-10 animate-spin text-emerald-600" />
                    <p class="text-sm font-medium text-gray-600">Processing...</p>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition-all duration-500 ease-out" enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0">
            <div v-show="delayReady" class="grid grid-cols-1 gap-0 md:gap-4 lg:gap-4 lg:grid-cols-12">

                <!-- Main Content Column -->
                <div class="space-y-1 md:space-y-4 lg:space-y-6 lg:col-span-7">

                    <!-- Equipment Selection / Details Card -->
                    <div class="bg-white border border-gray-200 shadow-sm md:rounded-xl overflow-visible">
                        <!-- Empty State -->
                        <div v-if="!hasEquipment" class="p-4 flex flex-col gap-2">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-emerald-100">
                                    <LuScanLine class="w-6 h-6 text-emerald-600" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Select Equipment</h2>
                                    <p class="text-sm text-gray-500">Scan QR code or search manually</p>
                                </div>
                            </div>
                            <SelectSearchField id="equipment_selector" placeholder="Search by name, brand, or barcode..." :options="equipmentOptions" v-model="selectedEquipmentId" />
                            <p class="flex items-center gap-2 text-xs text-gray-500">
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
                            <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ notFoundTitle }}</h3>
                            <p class="max-w-xs mx-auto mb-6 text-sm text-gray-500">{{ message }}</p>
                            <Link :href="route(showPageRoute)"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-emerald-600 rounded-lg hover:bg-emerald-700"
                                :class="{ 'opacity-70 pointer-events-none': isNavigating }">
                            <LuArrowLeft class="w-4 h-4" />
                            Browse All Equipment
                            </Link>
                        </div>

                        <!-- Equipment Details -->
                        <div v-else-if="equipment" data-guide="equipment-summary" class="divide-y divide-gray-100">
                            <!-- Header -->
                            <div class="flex items-start justify-between p-4 relative">
                                <div class="flex items-center gap-4 w-full">
                                    <div class="p-3 rounded-xl bg-emerald-100">
                                        <LuMicroscope class="w-6 h-6 text-emerald-600" />
                                    </div>
                                    <div class="flex flex-col w-full">
                                        <h1 class="text-xl font-bold text-gray-900">{{ equipment.name }}</h1>
                                        <div class="text-sm text-gray-500 flex justify-between md:flex-row flex-col md:items-center">
                                            <p class="font-semibold">{{ equipment?.brand || "—" }}</p>
                                            <p class="flex items-center gap-1.5" title="PhilRice Property No."><LuBarcode class="w-3.5 h-3.5 text-gray-800 " />{{ equipment?.barcode_prri || "—" }}</p>
                                            <p class="flex items-center gap-1.5" title="DA-CBC Equipment No."><LuQrCode class="w-3.5 h-3.5 text-gray-800 " />{{ equipment?.barcode || "—" }}</p>
                                            <div class="flex items-center gap-2">
                                                <label class="flex items-center gap-1.5 text-xs text-gray-500 uppercase tracking-wide">
                                                    <LuMapPin class="w-3.5 h-3.5 text-gray-800 " />
                                                    <p :class="{'!text-amber-700 rounded-full' : currentLocation?.source === 'temporary'}">{{ currentLocation?.label || "Unknown" }}</p>
                                                </label>
                                                <button v-if="canReportLocation" type="button" @click="openLocationSurveyModal"
                                                    title="Edit Location"
                                                    class="text-xs font-medium text-amber-700 transition-colors hover:text-amber-800">
                                                    <LuEdit class="w-3.5 h-3.5"/> 
                                                </button>
                                                <button v-if="isAdmin && currentLocation?.source === 'temporary'" type="button" @click="showFinalizeLocationModal = true"
                                                    title="Finalize Location"
                                                    class="text-xs font-medium text-emerald-700 transition-colors hover:text-emerald-800 ml-1 underline">
                                                    Finalize
                                                </button>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                <button v-if="!equipment_id" @click="selectedEquipmentId = null"
                                    class="p-2 text-gray-400 transition-colors rounded-lg hover:bg-gray-100 hover:text-gray-600 absolute top-2 right-2">
                                    <LuX class="w-5 h-5" />
                                </button>
                            </div>

                            <DialogModal :show="showLocationSurveyModal" max-width="md" @close="closeLocationSurveyModal">
                                <template #title>
                                    <div class="flex items-center gap-2 mb-4 py-2">
                                        <LuMapPin class="w-4 h-4 text-amber-600" />
                                        <h3 class="text-sm font-semibold text-gray-900">Update Location</h3>
                                    </div>
                                </template>
                                <template #content>
                                    <!-- Location Survey -->
                                    <div>
                                        <p class="mb-4 text-gray-600">Kindly provide the current location of the equipment.
                                            location</p>
                                        <div class="space-y-3">
                                            <TextInput v-if="!$page.props.auth.user" id="survey_location_employee_id" required
                                                v-model="locationSurveyForm.employee_id" label="Your ID"
                                                :error="getErrorMessage(locationSurveyErrors.employee_id)"
                                                @keydown.enter.prevent="submitLocationSurvey" />
                                            <div v-else class="text-sm font-medium text-emerald-700 bg-emerald-50 p-2 rounded">
                                                ID: {{ $page.props.auth.user.employee_id || 'Authenticated User' }}
                                            </div>
                                            <SingleSelectTagify id="survey_location_label" required
                                                v-model="locationSurveyForm.location_label" label="Current Location"
                                                :whitelist="storageLocationOptions"
                                                :error="getErrorMessage(locationSurveyErrors.location_label)"
                                                @keydown.enter.prevent="submitLocationSurvey" />
                                            <p class="text-xs text-gray-500 mt-1">Input a custom room if not available in the options.</p>
                                            <div v-if="getErrorMessage(locationSurveyErrors.base)"
                                                class="text-sm text-red-600">
                                                {{ getErrorMessage(locationSurveyErrors.base) }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #footer>
                                    <button type="button" @click="submitLocationSurvey"
                                        class="w-full px-4 py-2.5 text-sm font-medium text-white transition-colors bg-amber-600 rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                        Update Location
                                    </button>
                                </template>
                            </DialogModal>

                            <DialogModal :show="showFinalizeLocationModal" max-width="md" @close="showFinalizeLocationModal = false">
                                <template #title>
                                    <div class="flex items-center gap-2 mb-4 py-2">
                                        <LuMapPin class="w-4 h-4 text-emerald-600" />
                                        <h3 class="text-sm font-semibold text-gray-900">Finalize Location</h3>
                                    </div>
                                </template>
                                <template #content>
                                    <div class="bg-emerald-50/50 border-t border-emerald-100 py-4 px-2">
                                        <p class="text-gray-600">Are you sure you want to finalize this reported location? This will permanently update the equipment barcode.</p>
                                    </div>
                                </template>
                                <template #footer>
                                    <div class="flex gap-2 justify-end w-full">
                                        <button type="button" @click="showFinalizeLocationModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200">
                                            Cancel
                                        </button>
                                        <button type="button" @click="finalizeLocation" :disabled="isLoading" class="px-4 py-2.5 text-sm font-medium text-white transition-colors bg-emerald-600 rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                            Confirm Finalize
                                        </button>
                                    </div>
                                </template>
                            </DialogModal>
                        </div>
                    </div>

                    <!-- Status Card -->
                    <div v-if="hasEquipment && !notFound && equipment && activeLogs && activeLogs.length > 0" data-guide="equipment-status"
                        class="overflow-hidden bg-white border border-gray-200 shadow-sm md:rounded-xl">
                        <div class="flex items-center justify-between p-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg" :class="isOverdue ? 'bg-red-100' : 'bg-emerald-100'">
                                    <LuActivity class="w-5 h-5" :class="isOverdue ? 'text-red-600' : 'text-emerald-600'" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span  class="inline-flex items-center gap-1.5 uppercase font-semibold rounded-full"
                                            :class="isOverdue ? 'text-red-700' : 'text-emerald-700'">
                                            {{ isOverdue ? 'Overdue' : 'In Use' }}
                                        </span>
                                    </div>
                                    <h2 class="text-xs text-gray-900 leading-none mt-1">Current Status</h2>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-3">
                                <div v-if="(equipment?.simultaneous_users || 1) > 1" class="text-right">
                                    <div class="flex items-end justify-end gap-2">
                                        <span  class="inline-flex items-center gap-1.5 uppercase font-semibold rounded-full" :class="isOverdue ? 'text-red-700' : 'text-emerald-700'">
                                            {{ equipment.simultaneous_users - activeLogs.length }} / {{ equipment.simultaneous_users }}
                                            <LuUsers class="w-5 h-5" :class="isOverdue ? 'text-red-700' : 'text-emerald-700'" />
                                        </span>
                                    </div>
                                    <h2 class="text-xs text-gray-900 leading-none mt-1">Slots Remaining</h2>
                                </div>
                            
                                <button v-if="canEditActiveLog" @click="showEstimatedEndUseModal = true"
                                    class="flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors bg-emerald-50 rounded-lg hover:bg-emerald-100 whitespace-nowrap">
                                    <LuEdit class="w-3.5 h-3.5" />
                                    Edit Time
                                </button>
                            </div>
                        </div>

                        <div v-if="activeLogs && activeLogs.length > 0" class="divide-y divide-gray-100">
                            <div v-for="log in activeLogs" :key="log.id" class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3" :class="{'bg-emerald-50/30': log.personnel?.employee_id === currentLaboratoryPersonnel?.employee_id}">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 rounded-full bg-gray-100 text-gray-500">
                                        <LuUser class="w-4 h-4 text-emerald-700" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ formatPersonnelName(log.personnel) }}</div>
                                        <div v-if="log.personnel?.position || log.personnel?.affiliation || log.personnel?.course_program" class="flex flex-wrap items-center gap-1.5 mt-0.5 text-[0.65rem] text-gray-500 uppercase tracking-wider">
                                            <span v-if="log.personnel?.position">{{ log.personnel.position }}</span>
                                            <span v-if="log.personnel?.position && log.personnel?.affiliation" class="text-gray-300 text-[0.45rem]">●</span>
                                            <span v-if="log.personnel?.affiliation">{{ log.personnel.affiliation }}</span>
                                            <span v-if="(log.personnel?.position || log.personnel?.affiliation) && log.personnel?.course_program && ['student', 'ojt'].includes((log.personnel?.registration_type || '').toLowerCase())" class="text-gray-300 text-[0.45rem]">●</span>
                                            <span v-if="log.personnel?.course_program && ['student', 'ojt'].includes((log.personnel?.registration_type || '').toLowerCase())">{{ log.personnel.course_program }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col md:items-end gap-1 md:gap-1.5">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                        Started: <span class="font-medium text-gray-900">{{ formatDateTime(log.started_at) }}</span>
                                        <LuCalendar class="w-3.5 h-3.5 text-gray-400" />
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs" :class="isActiveItemOverdue(log) ? 'text-red-600' : 'text-gray-900'">
                                        Expected End: <span class="font-medium text-gray-900">{{ formatDateTime(log.end_use_at) }}</span>
                                        <LuClock class="w-3.5 h-3.5" :class="isActiveItemOverdue(log) ? 'text-red-400' : 'text-gray-400'" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in Form -->
                    <div v-if="hasEquipment && !notFound && canCheckIn" data-guide="equipment-actions"
                        class="overflow-hidden bg-white border border-gray-200 shadow-sm md:rounded-xl">
                        <div class="p-4 border-b border-gray-100 bg-emerald-50/30">
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

                        <div class="px-4 pb-4 space-y-4">
                            <PersonnelLookup v-if="!$page.props.auth.user" ref="personnelLookup" v-model="checkInForm.employee_id" @found="handlePersonnelFound" required
                                @error="handlePersonnelError" />
                            <div v-else class="flex items-center gap-2 p-3 text-sm text-emerald-700 rounded-lg bg-emerald-50 mb-2">
                                <LuCheckCircle2 class="w-4 h-4" />
                                <span class="font-medium">User ID: {{ $page.props.auth.user.employee_id || 'Linked Account' }}</span>
                            </div>
                            <div v-if="personnelPreview"
                                class="flex items-center gap-2 p-3 text-sm text-emerald-700 rounded-lg bg-emerald-50">
                                <LuCheckCircle2 class="w-4 h-4" />
                                <span class="font-medium">{{ personnelPreview.fullName }}</span>
                            </div>

                            <div
                                v-if="profileRequiresUpdate"
                                class="space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-4"
                            >
                                <div>
                                    <p class="text-sm font-semibold text-amber-900">
                                        Update your personnel information first
                                    </p>
                                    <p class="mt-1 text-sm text-amber-800">
                                        This employee record is marked as a fresh profile. Please complete the contact details below before checking in equipment.
                                    </p>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <TextInput v-model="personnelProfileForm.fname" label="First Name" required
                                        :error="getErrorMessage(personnelProfileErrors.fname)" />
                                    <TextInput v-model="personnelProfileForm.mname" label="Middle Name"
                                        :error="getErrorMessage(personnelProfileErrors.mname)" />
                                    <TextInput v-model="personnelProfileForm.lname" label="Last Name" required
                                        :error="getErrorMessage(personnelProfileErrors.lname)" />
                                    <TextInput v-model="personnelProfileForm.suffix" label="Suffix"
                                        :error="getErrorMessage(personnelProfileErrors.suffix)" />
                                    <TextInput v-model="personnelProfileForm.position" label="Position" required
                                        :error="getErrorMessage(personnelProfileErrors.position)" />
                                    <TextInput v-model="personnelProfileForm.phone" label="Phone" required
                                        :error="getErrorMessage(personnelProfileErrors.phone)" />
                                    <TextInput v-model="personnelProfileForm.email" label="Email (optional)"
                                        :error="getErrorMessage(personnelProfileErrors.email)" />
                                    <TextInput v-model="personnelProfileForm.address" label="Address" required
                                        :error="getErrorMessage(personnelProfileErrors.address)" />
                                </div>
                                <div v-if="getErrorMessage(personnelProfileErrors.base)" class="text-sm text-red-600">
                                    {{ getErrorMessage(personnelProfileErrors.base) }}
                                </div>
                                <button type="button" @click="submitPersonnelProfileUpdate"
                                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-amber-700">
                                    <LuSave class="w-4 h-4" />
                                    Save Personnel Information
                                </button>
                            </div>

                            <div v-if="getErrorMessage(checkInErrors.employee_id)" class="text-sm text-red-600">
                                {{ getErrorMessage(checkInErrors.employee_id) }}
                            </div>

                            <TextInput id="end_use_at" v-model="checkInForm.end_use_at" label="Estimated End of Use" required
                                type="datetime-local" :error="getErrorMessage(checkInErrors.end_use_at)"
                                @keydown.enter.prevent="submitCheckIn" />

                            <TextInput id="purpose" v-model="checkInForm.purpose" label="Purpose" required
                                placeholder="What will you use this for?" :datalist-id="'purpose-suggestions'"
                                :datalist-options="purposeSuggestions" :error="getErrorMessage(checkInErrors.purpose)"
                                @keydown.enter.prevent="submitCheckIn" />

                            <div v-if="checkInErrors.base" class="p-3 text-sm text-red-600 rounded-lg bg-red-50">
                                {{ checkInErrors.base }}
                            </div>

                            <button type="button" @click="submitCheckIn"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white transition-all bg-emerald-600 rounded-xl hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                <LuLogIn class="w-4 h-4" />
                                Check In Equipment
                            </button>
                        </div>
                    </div>

                    <!-- Check-out Form -->
                    <div v-if="hasEquipment && !notFound && canCheckOut" data-guide="equipment-actions"
                        class="overflow-hidden bg-white border border-gray-200 shadow-sm md:rounded-xl">
                        <div class="p-4 border-b border-gray-100 bg-amber-50/30">
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
                                <a :href="route('suppEquipReports.create.guest', equipment.barcode)" target="_blank"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors bg-red-50 rounded-lg hover:bg-red-100">
                                    <LuFlag class="w-3.5 h-3.5" />
                                    Report Issue
                                </a>
                            </div>
                        </div>

                        <div class="px-4 pb-4 space-y-4">
                            <Transition mode="out-in" name="fade-slide">
                                <div v-if="currentLaboratoryPersonnel && showPhilRiceField" key="saved"
                                    class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-200">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-emerald-100">
                                            <LuUser class="w-4 h-4 text-emerald-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{
                                                currentLaboratoryPersonnel.fullName }}</p>
                                            <p class="text-xs text-gray-500">{{ currentLaboratoryPersonnel.employee_id }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="handlePersonnelSwitch"
                                        class="p-2 text-gray-500 transition-colors rounded-lg hover:bg-gray-200"
                                        :class="{ 'animate-spin': isRotating }">
                                        <LuRefreshCw class="w-4 h-4" />
                                    </button>
                                </div>

                                <div v-else key="manual" class="space-y-3">
                                    <div class="flex gap-2">
                                        <TextInput v-if="!$page.props.auth.user" id="checkout_employee_id" v-model="checkOutForm.employee_id" label="Enter Your ID" required
                                            placeholder="PhilRice ID" class="flex-1"
                                            :error="getErrorMessage(checkOutErrors.employee_id)"
                                            @keydown.enter.prevent="submitCheckOut" />
                                        <div v-else class="flex-1 text-sm font-medium text-amber-700 bg-amber-50 p-2 rounded">
                                            ID: {{ $page.props.auth.user.employee_id || 'Authenticated User' }}
                                        </div>
                                        <button type="button" @click="handlePersonnelSwitch"
                                            class="px-3 py-2 text-gray-600 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200"
                                            title="Use saved profile">
                                            <LuUser class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </Transition>

                            <div v-if="getErrorMessage(checkOutErrors.base)"
                                class="p-3 text-sm text-red-600 rounded-lg bg-red-50">
                                {{ getErrorMessage(checkOutErrors.base) }}
                            </div>

                            <div v-if="isAdmin" class="flex items-center gap-2 p-3 rounded-lg bg-gray-50">
                                <input id="admin_override" v-model="checkOutForm.admin_override" type="checkbox"
                                    class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" />
                                <label for="admin_override" class="text-sm text-gray-700">Admin Override</label>
                            </div>

                            <button type="button" @click="submitCheckOut"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white transition-all bg-amber-600 rounded-xl hover:bg-amber-700 hover:shadow-lg hover:shadow-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                <LuLogOut class="w-4 h-4" />
                                Check Out Equipment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Active Equipment -->
                <div class="lg:col-span-5 mt-1 sm:mt-0">
                    <div data-guide="equipment-active" class="sticky overflow-hidden bg-white border border-gray-200 shadow-sm top-4 md:rounded-xl">
                        <div class="flex items-center justify-between p-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-blue-100">
                                    <LuAlertCircle class="w-5 h-5 text-blue-600" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-900">Active Sessions</h2>
                                    <p class="text-xs text-gray-500">{{ filteredActiveEquipments.length }} equipment in use</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="currentLaboratoryPersonnel" class="space-y-3 p-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-emerald-100 p-2">
                                        <LuUser class="h-4 w-4 text-emerald-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ currentLaboratoryPersonnel.fullName }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ currentLaboratoryPersonnel.employee_id }}
                                        </p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                    Current User
                                </span>
                            </div>
                            <button @click="filterActiveByPersonnel = !filterActiveByPersonnel"
                                class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium transition-colors rounded-lg"
                                :class="filterActiveByPersonnel ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'">
                                <span class="flex items-center gap-2">
                                    <LuUser class="w-4 h-4" />
                                    {{ filterActiveByPersonnel ? "Showing My Equipment" : "Show My Equipment Only" }}
                                </span>
                                <LuChevronRight class="w-4 h-4 transition-transform"
                                    :class="filterActiveByPersonnel ? 'rotate-90' : ''" />
                            </button>
                        </div>

                        <div v-if="loadingActiveEquipments" class="flex items-center justify-center p-12">
                            <LuLoader2 class="w-6 h-6 animate-spin text-gray-400" />
                        </div>

                        <div v-else-if="filteredActiveEquipments.length === 0"
                            class="flex flex-col items-center justify-center p-12 text-center">
                            <div class="p-3 mb-3 rounded-full bg-gray-100">
                                <LuPackage class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-sm font-medium text-gray-900">No active sessions</p>
                            <p class="text-xs text-gray-500 mt-1">All equipment is currently available</p>
                        </div>

                        <div v-else class="divide-y divide-gray-100 max-h-[calc(100vh-300px)] overflow-y-auto">
                            <Link v-for="item in filteredActiveEquipments" :key="item.id"
                                :href="route(showPageRoute, item.equipment_id)"
                                class="flex items-start gap-3 p-4 transition-colors hover:bg-gray-50" :class="{
                                    'bg-blue-50/50 border-l-4 border-blue-500': equipment?.id === item.equipment_id,
                                    'border-l-4 border-transparent': equipment?.id !== item.equipment_id,
                                }">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full"
                                :class="isActiveItemOverdue(item) ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'" />

                            <div class="flex w-full">
                                <div class="flex flex-col w-full gap-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ item.equipment?.name }}
                                        </h3>
                                        
                                    </div>
                                    <p class="text-xs text-gray-500">{{ item.equipment?.brand }}</p>
                                </div>
                                <div class="flex flex-col items-end text-xs gap-1 text-gray-500 whitespace-nowrap">
                                    <span class="flex items-center gap-1" :class="{'flex-shrink-0 text-xs text-red-700 rounded':isActiveItemOverdue(item)}">
                                        <span v-if="isActiveItemOverdue(item)" class="uppercase font-bold"> (Overdue) </span>
                                        {{ formatDateTime(item.end_use_at) }}
                                        <LuClock class="w-3.5 h-3.5" />
                                    </span>
                                    <span class="flex items-center gap-1">
                                        {{ formatPersonnelName(item.personnel) }}
                                        <LuUser class="w-3.5 h-3.5" />
                                    </span>
                                </div>
                            </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showEmailCaptureModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="w-full max-w-md p-5 bg-white shadow-2xl rounded-2xl">
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Email Required</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                We need your email so the system can send overdue equipment reminders.
                            </p>
                        </div>
                        <button @click="showEmailCaptureModal = false"
                            class="p-2 text-gray-400 transition-colors rounded-lg hover:bg-gray-100">
                            <LuX class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <TextInput v-model="emailCaptureForm.email" label="Email Address" type="email" required
                            :error="getErrorMessage(emailCaptureErrors.email)"
                            @keydown.enter.prevent="submitEmailCapture" />

                        <div v-if="getErrorMessage(emailCaptureErrors.base)" class="p-3 text-sm text-red-600 rounded-lg bg-red-50">
                            {{ getErrorMessage(emailCaptureErrors.base) }}
                        </div>

                        <button type="button" @click="submitEmailCapture"
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-emerald-700">
                            <LuSave class="w-4 h-4" />
                            Save Email
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Edit End Time Modal -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showEstimatedEndUseModal && canEditActiveLog"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="w-full max-w-md p-4 bg-white shadow-2xl rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-emerald-100">
                                <LuTimer class="w-5 h-5 text-emerald-600" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Extend Usage Time</h3>
                        </div>
                        <button @click="showEstimatedEndUseModal = false; resetUpdateEndUse()"
                            class="p-2 text-gray-400 transition-colors rounded-lg hover:bg-gray-100">
                            <LuX class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <TextInput v-if="!$page.props.auth.user" id="update_end_use_employee_id" v-model="updateEndUseForm.employee_id"
                            label="Your ID" :error="getErrorMessage(updateEndUseErrors.employee_id)"
                            required @keydown.enter.prevent="submitUpdateEndUse" />
                        <div v-else class="text-sm font-medium text-emerald-700 bg-emerald-50 p-2 rounded">
                            ID: {{ $page.props.auth.user.employee_id || 'Authenticated User' }}
                        </div>

                        <TextInput id="update_end_use_at" v-model="updateEndUseForm.end_use_at" label="New End Time"
                            type="datetime-local" :error="getErrorMessage(updateEndUseErrors.end_use_at)"
                            @keydown.enter.prevent="submitUpdateEndUse" />

                        <div v-if="getErrorMessage(updateEndUseErrors.base)"
                            class="p-3 text-sm text-red-600 rounded-lg bg-red-50">
                            {{ getErrorMessage(updateEndUseErrors.base) }}
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button v-for="min in [15, 30, 60, 120]" :key="min" type="button" @click="addMinutes(min)"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors bg-emerald-50 rounded-lg hover:bg-emerald-100">
                                <LuPlus class="w-3 h-3" />
                                {{ min }}m
                            </button>
                            <button type="button" @click="addMinutes(0)"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200">
                                <LuRefreshCw class="w-3 h-3" />
                                Reset
                            </button>
                        </div>

                        <button type="button" @click="submitUpdateEndUse"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white transition-colors bg-emerald-600 rounded-xl hover:bg-emerald-700">
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
