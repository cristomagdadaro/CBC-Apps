<script>
import QrcodeVue from "qrcode.vue";
import { createCanvas } from "canvas";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Modules/domain/Transaction";
import JsBarcode from "jsbarcode";
import DtoResponse from "@/Modules/dto/DtoResponse";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import TransactionReportAccordion from "@/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue";
import TransactionComponentAccordion from "@/Pages/Inventory/Transactions/components/TransactionComponentAccordion.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";
import { Plus, X, Printer, RotateCcw, Save, Loader2, Package, GitBranch, ArrowUpRight, Filter, Calendar, Hash, User, FileText, DollarSign, Scale, Box, Tag, MapPin, AlertCircle, Info } from "lucide-vue-next";

export default {
    name: "IncomingForm",
    props: {
        attachedReports: {
            type: Array,
            default: () => [],
        },
        attachedComponents: {
            type: Array,
            default: () => [],
        },
        listConditions: {
            type: Array,
            default: () => [],
        },
        parentTransaction: {
            type: Object,
            default: null,
        },
    },
    components: {
        TransactionHeaderAction,
        TransactionReportAccordion,
        TransactionComponentAccordion,
        AuditInfoCard,
        QrcodeVue,
        Plus,
        X,
        Printer,
        RotateCcw,
        Save,
        Loader2,
        Package,
        GitBranch,
        ArrowUpRight,
        Filter,
        Calendar,
        Hash,
        User,
        FileText,
        DollarSign,
        Scale,
        Box,
        Tag,
        MapPin,
        AlertCircle,
        Info,
    },
    mixins: [ApiMixin],
    data() {
        return {
            api: null,
            noModelApi: null,
            barcodeCanvas: null,
            svgText: "",
            showNewItemForm: false,
            rememberFormKey: "incomingTransactionForm",
        };
    },
    emits: ["showNewItemForm"],
    methods: {
        async submitForm() {
            if (this.isUpdate) {
                await this.submitUpdate();
                return;
            }

            const selectedStorage = this.selectedStorage;
            const response = await this.submitCreate(false, this.getCreateRetainedFields());

            await this.handleCreateSuccess(response, selectedStorage);
        },
        getCreateRetainedFields() {
            return ["transac_type", "user_id", "project_code", "equipment_logger_mode", "personnel_id", "condition", "remarks", "par_no", "po_no", "pr_no", "serial_no", "parent_barcode"];
        },
        currentUserId() {
            return this.$page.props?.auth?.user?.id ?? null;
        },
        getParentReferenceBarcode() {
            return this.parentTransaction?.barcode_prri ?? this.parentTransaction?.barcode ?? null;
        },
        applyParentReference() {
            if (!this.form) {
                return;
            }

            this.form.parent_barcode = this.getParentReferenceBarcode();
        },
        async generateBarcode(room) {
            if (!room) {
                return;
            }

            await this.fetchGetApi("api.inventory.transactions.genbarcode", { room: room }).then((response) => {
                this.form.barcode = response.data.barcode;
                this.renderBarcode();
            });
        },
        async applyCreateDefaults(storage = null) {
            if (!this.form || this.isUpdate) {
                return;
            }

            this.form.transac_type = this.form.transac_type ?? "incoming";
            this.form.user_id = this.form.user_id ?? this.currentUserId();
            this.form.parent_barcode = this.form.parent_barcode ?? this.getParentReferenceBarcode();
            this.form.equipment_logger_mode = this.form.equipment_logger_mode ?? this.defaultEquipmentLoggerMode;

            if (storage) {
                await this.generateBarcode(storage);
                return;
            }

            this.form.barcode = this.form.barcode ?? this.preGenerateBarcode ?? null;

            if (this.form.barcode) {
                this.renderBarcode();
                return;
            }

            this.svgText = "";
        },
        async handleCreateSuccess(response, storage = null) {
            if (!(response instanceof DtoResponse)) {
                return;
            }

            await this.applyCreateDefaults(storage);
        },
        async resetIncomingForm() {
            if (this.isUpdate) {
                this.resetField(this.model.updateFields(this.data));
                this.applyParentReference();
                this.renderBarcode();
                return;
            }

            const selectedStorage = this.selectedStorage;

            this.resetField(this.model.createFields());
            await this.applyCreateDefaults(selectedStorage);
        },
        renderBarcode() {
            const canvas = createCanvas(256, 256);
            JsBarcode(canvas, this.form.barcode, {
                displayValue: true,
                fontSize: 20,
                textMargin: 5,
                width: 2,
                height: 60,
            });
            this.svgText = canvas.toDataURL();
        },
    },
    computed: {
        isUpdate() {
            return !!this.data?.id;
        },
        items() {
            if (!this.$page.props?.items) return [];
            return this.$page.props.items.map((item) => {
                const supplement = item.brand || item.description;

                return {
                    name: item.id,
                    label: item.name + (supplement ? " (" + supplement + ")" : ""),
                };
            });
        },
        personnels() {
            if (!this.$page.props?.personnels) return [];
            return this.$page.props.personnels.map((personnel) => {
                const fullName = [personnel.lname, ", ", personnel.fname, personnel.mname, personnel.suffix, " (", personnel.employee_id, ")"].filter(Boolean).join(" ");

                return {
                    name: personnel.id,
                    label: fullName,
                };
            });
        },
        projectCodeSuggestions() {
            const projectCodes = this.$page.props?.projectCodes;

            if (!Array.isArray(projectCodes)) {
                return [];
            }

            return [
                ...new Set(
                    projectCodes
                        .map((projectCode) => {
                            if (typeof projectCode === "string") {
                                return projectCode.trim();
                            }

                            return String(projectCode?.label ?? projectCode?.name ?? projectCode?.value ?? "").trim();
                        })
                        .filter(Boolean),
                ),
            ];
        },
        equipmentLoggerModeOptions() {
            const options = this.$page.props?.equipment_logger_mode_options;

            if (!Array.isArray(options)) {
                return [];
            }

            return options
                .map((option) => ({
                    name: option?.name ?? option?.value ?? null,
                    label: option?.label ?? option?.name ?? option?.value ?? null,
                }))
                .filter((option) => option.name && option.label);
        },
        defaultEquipmentLoggerMode() {
            return this.$page.props?.equipment_logger_mode_default ?? this.equipmentLoggerModeOptions[0]?.name ?? null;
        },
        defaultCondition() {
            return this.form.condition ?? this.listConditions[0]?.name ?? null;
        },
        selectedEquipmentLoggerModeOption() {
            return this.equipmentLoggerModeOptions.find((option) => option.name === this.form?.equipment_logger_mode) ?? null;
        },
        equipmentLoggerModeHelpText() {
            if (!this.selectedEquipmentLoggerModeOption) {
                return "Choose how this incoming stock can participate in equipment or shared-use logger workflows.";
            }

            const mode = this.selectedEquipmentLoggerModeOption.name;
            let explanation = "";

            if (mode === "borrowable") {
                explanation = "The equipment will be visible in the logger and can be checked out/in by personnel.";
            } else if (mode === "tracked_only") {
                explanation = "The equipment will be visible in the logger for location tracking only, but cannot be checked out.";
            } else if (mode === "excluded") {
                explanation = "The equipment will be entirely excluded and hidden from the equipment logger.";
            } else {
                explanation = `${this.selectedEquipmentLoggerModeOption.label} applies to this incoming stock record and any downstream logger visibility tied to it.`;
            }

            return explanation;
        },
        preGenerateBarcode() {
            return this.$page.props.barcode;
        },
        selectedStorage() {
            if (this.form?.barcode && this.form.barcode.length >= 6) {
                return this.form.barcode.substring(4, 6);
            }

            return null;
        },
        selectCondition() {
            return this.form?.condition ?? this.defaultCondition;
        },
        attachedReportsList() {
            return Array.isArray(this.attachedReports) ? this.attachedReports : [];
        },
        attachedComponentsList() {
            return Array.isArray(this.attachedComponents) ? this.attachedComponents : [];
        },
        hasParentTransaction() {
            return !!this.parentTransaction?.id;
        },
        toggleShowNewItemForm() {
            this.showNewItemForm = !this.showNewItemForm;
            this.$emit("showNewItemForm", this.showNewItemForm);
        },
    },
    watch: {
        "form.barcode": function () {
            this.renderBarcode();
        },
        "form.item_id": function (val) {
            const selectedItem = typeof val === "object" && val !== null ? val : (this.$page.props?.items ?? []).find((item) => item.id === val);

            if (!selectedItem) {
                if (!val) {
                    this.form.unit_price = null;
                    this.form.unit = null;
                    this.form.total_cost = null;
                }
                return;
            }

            this.form.unit_price = selectedItem.unit_price ?? null;
            this.form.unit = selectedItem.unit ?? this.form.unit;
            this.form.total_cost = this.form.quantity && this.form.unit_price ? this.form.unit_price * this.form.quantity : null;
        },
        "form.quantity": function (val) {
            this.form.total_cost = this.form.unit_price ? this.form.unit_price * val : null;
        },
        "form.unit_price": function (val) {
            this.form.total_cost = this.form.quantity ? val * this.form.quantity : null;
        },
    },
    async mounted() {
        this.model = new Transaction();
        this.setFormAction(this.isUpdate ? "update" : "create");

        if (!this.isUpdate) {
            await this.applyCreateDefaults(this.selectedStorage);
            return;
        }

        this.applyParentReference();
        this.renderBarcode();
    },
};
</script>

<template>
    <form
        v-if="!!form"
        @submit.prevent="submitForm"
        class="grid w-full gap-6"
        :class="currentFormAction === 'create' ? 'grid-cols-1' : 'grid-cols-1 lg:grid-cols-[1fr,320px] xl:grid-cols-[1fr,360px]'">
        <!-- Main Form Column -->
        <div class="mx-auto flex h-fit w-full flex-col overflow-hidden rounded-2xl border border-slate-200/60 bg-white/90 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/90 dark:ring-white/5">
            <!-- Header Section -->
            <div class="flex flex-col items-start justify-between gap-4 border-b border-slate-100 bg-slate-50/50 px-6 py-5 sm:flex-row sm:items-center dark:border-slate-800/60 dark:bg-slate-800/20">
                <div>
                    <div class="mb-1.5 flex items-center gap-2.5">
                        <div class="shrink-0 rounded-lg bg-indigo-50 p-1.5 dark:bg-indigo-500/10">
                            <Package class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h2 class="text-lg font-black uppercase leading-none tracking-tight text-slate-900 dark:text-white">
                            {{ isUpdate ? "Update Transaction" : "Incoming Transaction" }}
                        </h2>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                        {{ isUpdate ? "Update the details of this incoming transaction." : "Submit details for a new incoming transaction." }}
                    </p>
                </div>

                <!-- Barcode Display -->
                <div
                    v-if="svgText && selectedStorage"
                    class="flex min-h-[4rem] shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-950">
                    <img
                        id="barcode-image"
                        :src="svgText"
                        alt="Generated barcode"
                        class="h-12 w-auto rounded bg-white object-contain mix-blend-multiply dark:mix-blend-normal" />
                </div>
            </div>

            <!-- Form Content Body -->
            <div class="space-y-6 p-6">
                <!-- Reports Accordion (Only visible on Update) -->
                <transaction-report-accordion
                    v-if="isUpdate"
                    class="w-full"
                    :reports="attachedReportsList" />

                <!-- Core Information Card -->
                <div class="space-y-5 rounded-2xl border border-slate-100 bg-slate-50/50 p-5 dark:border-slate-700/60 dark:bg-slate-800/30">
                    <div class="flex w-full flex-col items-end gap-3 sm:flex-row">
                        <div class="w-full flex-1">
                            <select-search-field
                                :disabled="isUpdate"
                                required
                                :api-link="'api.inventory.items.options'"
                                :error="form.errors.item_id"
                                label="Catalog Item"
                                v-model="form.item_id" />
                        </div>
                        <div
                            v-if="!isUpdate"
                            class="w-full shrink-0 sm:w-auto">
                            <button
                                v-if="!showNewItemForm"
                                @click.prevent="toggleShowNewItemForm"
                                class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-indigo-400 hover:text-indigo-600 active:scale-95 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:text-indigo-400">
                                <Plus class="h-4 w-4" />
                                New Item
                            </button>
                            <button
                                v-else
                                @click.prevent="toggleShowNewItemForm"
                                class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-600 shadow-sm transition-all hover:bg-rose-100 active:scale-95 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">
                                <X class="h-4 w-4" />
                                Close Panel
                            </button>
                        </div>
                    </div>

                    <text-input
                        type="text"
                        label="Project Code"
                        required
                        v-model="form.project_code"
                        datalist-id="incoming-project-code-options"
                        :datalist-options="projectCodeSuggestions"
                        :error="form.errors.project_code"
                        hint="Enter the project code assigned to this transaction">
                        <template #icon>
                            <FileText class="h-4 w-4 text-slate-400" />
                        </template>
                    </text-input>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <custom-dropdown
                            searchable
                            :with-all-option="false"
                            :value="form.personnel_id"
                            :options="personnels"
                            placeholder="Select Personnel"
                            label="Accountable Personnel"
                            required
                            :error="form.errors.personnel_id"
                            @selectedChange="form.personnel_id = $event">
                            <template #icon>
                                <User class="h-4 w-4 text-slate-400" />
                            </template>
                        </custom-dropdown>

                        <custom-dropdown
                            required
                            :disabled="isUpdate"
                            :with-all-option="false"
                            :value="selectedStorage"
                            :options="storage_locations"
                            placeholder="Select Storage"
                            label="Storage Location"
                            :error="form.errors.barcode"
                            @selectedChange="generateBarcode($event)">
                            <template #icon>
                                <MapPin class="h-4 w-4 text-slate-400" />
                            </template>
                        </custom-dropdown>
                    </div>
                </div>

                <!-- Identification & Barcodes Card -->
                <div class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/50 p-5 dark:border-slate-700/60 dark:bg-slate-800/30">
                    <text-input
                        label="Parent Barcode / Link"
                        v-model="form.parent_barcode"
                        :error="form.errors.parent_barcode"
                        placeholder="Optional: link this as a sub-component"
                        hint="Used to link this as a sub-component of another transaction">
                        <template #icon>
                            <GitBranch class="h-4 w-4 text-slate-400" />
                        </template>
                    </text-input>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <text-input
                            label="PRRI QR/Barcode"
                            v-model="form.barcode_prri"
                            :error="form.errors.barcode_prri">
                            <template #icon><Hash class="h-4 w-4 text-slate-400" /></template>
                        </text-input>

                        <text-input
                            label="PAR No."
                            v-model="form.par_no"
                            :error="form.errors.par_no">
                            <template #icon><Tag class="h-4 w-4 text-slate-400" /></template>
                        </text-input>

                        <custom-dropdown
                            required
                            :disabled="isUpdate"
                            :with-all-option="false"
                            :value="selectCondition"
                            :options="listConditions"
                            placeholder="Current Condition"
                            label="Condition"
                            :error="form.errors.condition"
                            @selectedChange="form.condition = $event">
                            <template #icon><Info class="h-4 w-4 text-slate-400" /></template>
                        </custom-dropdown>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <text-input
                            label="PO No."
                            v-model="form.po_no"
                            :error="form.errors.po_no">
                            <template #icon><FileText class="h-4 w-4 text-slate-400" /></template>
                        </text-input>

                        <text-input
                            label="PR No."
                            v-model="form.pr_no"
                            :error="form.errors.pr_no">
                            <template #icon><FileText class="h-4 w-4 text-slate-400" /></template>
                        </text-input>

                        <text-input
                            label="Serial No."
                            v-model="form.serial_no"
                            :error="form.errors.serial_no">
                            <template #icon><Hash class="h-4 w-4 text-slate-400" /></template>
                        </text-input>
                    </div>
                </div>

                <!-- Pricing & Details Card -->
                <div class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/50 p-5 dark:border-slate-700/60 dark:bg-slate-800/30">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <text-input
                            required
                            type="number"
                            label="Quantity"
                            v-model="form.quantity"
                            :error="form.errors.quantity">
                            <template #icon><Box class="h-4 w-4 text-slate-400" /></template>
                        </text-input>

                        <text-input
                            required
                            label="Unit"
                            v-model="form.unit"
                            :error="form.errors.unit">
                            <template #icon><Scale class="h-4 w-4 text-slate-400" /></template>
                        </text-input>

                        <text-input
                            type="number"
                            label="Unit Price"
                            v-model="form.unit_price"
                            :error="form.errors.unit_price">
                            <template #icon><DollarSign class="h-4 w-4 text-slate-400" /></template>
                        </text-input>

                        <text-input
                            type="number"
                            label="Total Cost"
                            v-model="form.total_cost"
                            :error="form.errors.total_cost"
                            :disabled="true">
                            <template #icon><DollarSign class="h-4 w-4 text-slate-400" /></template>
                        </text-input>
                    </div>

                    <div class="grid grid-cols-1 gap-4 pt-2">
                        <date-input
                            type="date"
                            label="Expiration Date"
                            v-model="form.expiration"
                            :error="form.errors.expiration" />
                        <text-area
                            label="PR Details / Remarks"
                            v-model="form.remarks"
                            :error="form.errors.remarks"
                            :rows="3" />
                    </div>

                    <div class="mt-2 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <custom-dropdown
                            required
                            :with-all-option="false"
                            :value="form.equipment_logger_mode"
                            :options="equipmentLoggerModeOptions"
                            placeholder="Select logger availability"
                            label="Equipment Logger Availability"
                            :error="form.errors.equipment_logger_mode"
                            @selectedChange="form.equipment_logger_mode = $event">
                            <template #icon>
                                <AlertCircle class="h-4 w-4 text-indigo-500" />
                            </template>
                        </custom-dropdown>
                        <p class="mt-2.5 text-xs font-semibold leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ equipmentLoggerModeHelpText }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Footer Actions -->
            <div class="sticky bottom-0 z-10 flex items-center justify-between gap-4 border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/50">
                <button
                    type="button"
                    @click="resetIncomingForm"
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    <RotateCcw class="h-4 w-4" />
                    Reset
                </button>

                <button
                    type="submit"
                    :disabled="model.api.processing"
                    class="flex items-center gap-2 rounded-xl bg-indigo-600 px-8 py-2.5 text-sm font-black text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-600/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                    <Loader2
                        v-if="model.api.processing"
                        class="h-4 w-4 animate-spin" />
                    <Save
                        v-else
                        class="h-4 w-4" />
                    <span v-if="model.api.processing">
                        {{ isUpdate ? "Updating..." : "Saving..." }}
                    </span>
                    <span v-else>{{ isUpdate ? "Update Transaction" : "Save Transaction" }}</span>
                </button>
            </div>

            <audit-info-card
                v-if="isUpdate"
                class="rounded-none rounded-b-2xl border-t-0"
                :audit-logs="$page.props.auditLogs"
                :created-at="data?.created_at"
                :updated-at="data?.updated_at" />
        </div>

        <!-- Side Panel (Update Mode Info) -->
        <div
            v-if="currentFormAction !== 'create'"
            class="flex flex-col gap-6">
            <div class="space-y-6 rounded-2xl border border-slate-200/60 bg-white/90 p-5 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl sm:p-6 dark:border-slate-800 dark:bg-slate-900/90 dark:ring-white/5">
                <!-- Workflow Info -->
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/80 p-4 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/10">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 rounded-lg border border-indigo-100 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <GitBranch class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div class="min-w-0 flex-1 pt-0.5">
                            <h3 class="mb-1.5 text-xs font-bold uppercase text-indigo-900 dark:text-indigo-300">Sub-Component Workflow</h3>
                            <p class="text-xs font-medium leading-relaxed text-indigo-700 dark:text-indigo-400/80">Save each equipment part as its own incoming transaction, then use the parent CBC or PRRI barcode above to link it back to the main equipment record.</p>
                        </div>
                    </div>
                </div>

                <!-- Parent Transaction Card -->
                <div
                    v-if="hasParentTransaction"
                    class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/50 p-5 dark:border-slate-700 dark:bg-slate-800/30">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 rounded-lg bg-slate-200 p-1.5 dark:bg-slate-700">
                                <ArrowUpRight class="h-4 w-4 text-slate-600 dark:text-slate-400" />
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200">Parent Transaction</h3>
                                <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">Linked as sub-component</p>
                            </div>
                        </div>
                        <Link
                            :href="route('transactions.show', parentTransaction.id)"
                            class="group inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.65rem] font-bold uppercase text-indigo-600 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-indigo-400 dark:hover:bg-slate-800">
                            <span>View</span>
                            <ArrowUpRight class="h-3 w-3 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-3 rounded-lg border border-slate-100 bg-white p-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-start gap-2.5">
                            <Package class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                            <div class="min-w-0">
                                <span class="mb-0.5 block text-[0.65rem] font-bold uppercase text-slate-500">Item</span>
                                <span class="block truncate font-semibold text-slate-800 dark:text-slate-200">
                                    {{ parentTransaction?.item?.name ?? "—" }}
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-2 dark:border-slate-800">
                            <div class="flex items-start gap-2">
                                <Hash class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" />
                                <div>
                                    <span class="mb-0.5 block text-[0.65rem] font-bold uppercase text-slate-500">CBC Barcode</span>
                                    <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                        {{ parentTransaction?.barcode ?? "—" }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <Hash class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" />
                                <div>
                                    <span class="mb-0.5 block text-[0.65rem] font-bold uppercase text-slate-500">PRRI Barcode</span>
                                    <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                        {{ parentTransaction?.barcode_prri ?? "—" }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub-Components Accordion -->
            <div
                v-if="currentFormAction !== 'create'"
                class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/90 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:ring-white/5">
                <transaction-component-accordion
                    :components="attachedComponentsList"
                    title="Sub-Components"
                    empty-message="No sub-components linked to this transaction yet." />
            </div>
        </div>
    </form>
</template>

<style scoped>
/* Smooth transitions */
button,
select {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Barcode image styling */
#barcode-image {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
}
</style>
