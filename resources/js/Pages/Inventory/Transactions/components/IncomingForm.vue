<script>
import QrcodeVue from 'qrcode.vue';
import { createCanvas } from "canvas";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Modules/domain/Transaction";
import JsBarcode from "jsbarcode";
import DtoResponse from "@/Modules/dto/DtoResponse";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import TransactionReportAccordion from "@/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue";
import TransactionComponentAccordion from "@/Pages/Inventory/Transactions/components/TransactionComponentAccordion.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";
import {
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
    AlertCircle
} from 'lucide-vue-next';

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
    },
    mixins: [ApiMixin],
    data() {
        return {
            api: null,
            noModelApi: null,
            barcodeCanvas: null,
            svgText: '',
            showNewItemForm: false,
            rememberFormKey: 'incomingTransactionForm',
        }
    },
    emits: ['showNewItemForm'],
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
            return [
                'transac_type',
                'user_id',
                'project_code',
                'personnel_id',
                'condition',
                'remarks',
                'par_no',
                'parent_barcode',
            ];
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

            await this.fetchGetApi('api.inventory.transactions.genbarcode', { room: room }).then(response => {
                this.form.barcode = response.data.barcode;
                this.renderBarcode();
            });
        },
        async applyCreateDefaults(storage = null) {
            if (!this.form || this.isUpdate) {
                return;
            }

            this.form.transac_type = this.form.transac_type ?? 'incoming';
            this.form.user_id = this.form.user_id ?? this.currentUserId();
            this.form.parent_barcode = this.form.parent_barcode ?? this.getParentReferenceBarcode();

            if (storage) {
                await this.generateBarcode(storage);
                return;
            }

            this.form.barcode = this.form.barcode ?? this.preGenerateBarcode ?? null;

            if (this.form.barcode) {
                this.renderBarcode();
                return;
            }

            this.svgText = '';
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
            if (!this.$page.props?.items)
                return [];
            return this.$page.props.items.map(item => {
                const supplement = item.brand || item.description;

                return {
                    name: item.id,
                    label: item.name + (supplement ? ' (' + supplement + ')' : ''),
                }
            });
        },
        personnels() {
            if (!this.$page.props?.personnels)
                return [];
            return this.$page.props.personnels.map(personnel => {
                const fullName = [
                    personnel.lname,
                    ', ',
                    personnel.fname,
                    personnel.mname,
                    personnel.suffix,
                    ' (',
                    personnel.employee_id,
                    ')'
                ].filter(Boolean).join(' ');

                return {
                    name: personnel.id,
                    label: fullName,
                };
            });
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
            this.$emit('showNewItemForm', this.showNewItemForm);
        },
    },
    watch: {
        'form.barcode': function () {
            this.renderBarcode();
        },
        'form.item_id': function (val) {
            const selectedItem = typeof val === 'object' && val !== null
                ? val
                : (this.$page.props?.items ?? []).find(item => item.id === val);

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
            this.form.total_cost = this.form.quantity && this.form.unit_price
                ? this.form.unit_price * this.form.quantity
                : null;
        },
        'form.quantity': function (val) {
            this.form.total_cost = this.form.unit_price ? this.form.unit_price * val : null;
        },
        'form.unit_price': function (val) {
            this.form.total_cost = this.form.quantity ? val * this.form.quantity : null;
        },
    },
    async mounted() {
        this.model = new Transaction();
        this.setFormAction(this.isUpdate ? 'update' : 'create');

        if (!this.isUpdate) {
            await this.applyCreateDefaults(this.selectedStorage);
            return;
        }

        this.applyParentReference();
        this.renderBarcode();
    },
}
</script>

<template>
    <form v-if="!!form" @submit.prevent="submitForm" class="grid gap-4 w-full" :class="currentFormAction === 'create' ? 'grid-cols-1' : 'grid-cols-2'">
        <!-- Main Form Column -->
        <div class="flex flex-col gap-4 w-full mx-auto p-4 lg:p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl h-fit border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col gap-4 mx-auto w-full h-fit">
                <!-- Header -->
                <div class="flex border-b border-gray-200 dark:border-gray-700  justify-between items-center pb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <Package class="w-5 h-5 text-AA" />
                            <h2 class="font-bold uppercase leading-none text-lg text-gray-900 dark:text-gray-100">
                                {{ isUpdate ? 'Update Incoming Transaction' : 'Incoming Transaction' }}
                            </h2>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ isUpdate ? 'Update the details of this incoming transaction.' : 'Submit details for a new incoming transaction.' }}
                        </p>
                    </div>
                    <!-- Barcode Display -->
                    <div v-if="svgText && selectedStorage" class="flex sm:flex-row flex-col gap-3 w-fit h-16 items-center relative border border-gray-200 dark:border-gray-600">
                        <img id="barcode-image" :src="svgText" alt="Generated barcode" class="w-full h-full max-w-md rounded shadow-sm bg-white" />
                    </div>
                </div>

                <!-- Reports Accordion -->
                <transaction-report-accordion
                    v-if="isUpdate"
                    class="w-full"
                    :reports="attachedReportsList"
                />

                <!-- Item Selection -->
                <div class="flex flex-row gap-2 h-fit">
                    <select-search-field
                        :disabled="isUpdate"
                        required
                        :api-link="'api.inventory.items.options'"
                        :error="form.errors.item_id"
                        label="Item"
                        v-model="form.item_id"
                    />
                    <div v-if="!isUpdate" class="flex items-end">
                        <button
                            v-if="!showNewItemForm"
                            @click.prevent="toggleShowNewItemForm"
                            class="h-fit w-full py-2.5 border border-gray-300 dark:border-gray-600 flex items-center justify-center bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg gap-1.5 text-sm px-3 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                        >
                            <Plus class="h-4 w-4" />
                            <span class="whitespace-nowrap">New Item</span>
                        </button>

                        <button
                            v-else
                            @click.prevent="toggleShowNewItemForm"
                            class="h-fit w-full py-2.5 border border-red-300 dark:border-red-700 flex items-center justify-center bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg gap-1.5 text-sm px-3 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                        >
                            <X class="h-4 w-4" />
                            <span class="whitespace-nowrap">Close</span>
                        </button>
                    </div>
                </div>

                <!-- Project Code -->
                <text-input
                    type="text"
                    label="Project Code"
                    required
                    v-model="form.project_code"
                    :error="form.errors.project_code"
                    :hint="'Enter the project code for this transaction'"
                >
                    <template #icon>
                        <FileText class="w-4 h-4 text-gray-400" />
                    </template>
                </text-input>

                <!-- Personnel -->
                <custom-dropdown
                    searchable
                    :with-all-option="false"
                    :value="form.personnel_id"
                    :options="personnels"
                    placeholder="Select Personnel"
                    label="Accountable Personnel"
                    required
                    :error="form.errors.personnel_id"
                    @selectedChange="form.personnel_id = $event"
                >
                    <template #icon>
                        <User class="w-4 h-4 text-gray-400" />
                    </template>
                </custom-dropdown>

                <!-- Storage Location -->
                <custom-dropdown
                    required
                    :disabled="isUpdate"
                    :with-all-option="false"
                    :value="selectedStorage"
                    :options="storage_locations"
                    placeholder="Select Storage"
                    label="Storage Location"
                    :error="form.errors.barcode"
                    @selectedChange="generateBarcode($event)"
                >
                    <template #icon>
                        <MapPin class="w-4 h-4 text-gray-400" />
                    </template>
                </custom-dropdown>

                <!-- Parent Barcode -->
                <text-input
                    label="Parent Barcode / PRRI Barcode"
                    v-model="form.parent_barcode"
                    :error="form.errors.parent_barcode"
                    placeholder="Optional: link this as a sub-component"
                    :hint="'Used to link this as a sub-component of another transaction'"
                >
                    <template #icon>
                        <GitBranch class="w-4 h-4 text-gray-400" />
                    </template>
                </text-input>

                <!-- Barcodes & PAR -->
                <div class="grid grid-cols-2 gap-3">
                    <text-input label="PRRI Barcode" v-model="form.barcode_prri" :error="form.errors.barcode_prri">
                        <template #icon>
                            <Hash class="w-4 h-4 text-gray-400" />
                        </template>
                    </text-input>
                    <text-input label="PAR No" v-model="form.par_no" :error="form.errors.par_no">
                        <template #icon>
                            <Tag class="w-4 h-4 text-gray-400" />
                        </template>
                    </text-input>
                    <text-input label="Condition" v-model="form.condition" :error="form.errors.condition" class="col-span-2" />
                </div>

                <!-- Quantity & Pricing -->
                <div class="grid grid-cols-2 gap-3">
                    <text-input required type="number" label="Quantity" v-model="form.quantity" :error="form.errors.quantity">
                        <template #icon>
                            <Box class="w-4 h-4 text-gray-400" />
                        </template>
                    </text-input>
                    <text-input type="number" label="Unit Price" v-model="form.unit_price" :error="form.errors.unit_price">
                        <template #icon>
                            <DollarSign class="w-4 h-4 text-gray-400" />
                        </template>
                    </text-input>
                    <text-input required label="Unit" v-model="form.unit" :error="form.errors.unit">
                        <template #icon>
                            <Scale class="w-4 h-4 text-gray-400" />
                        </template>
                    </text-input>
                    <text-input type="number" label="Total Cost" v-model="form.total_cost" :error="form.errors.total_cost" :disabled="true">
                        <template #icon>
                            <DollarSign class="w-4 h-4 text-gray-400" />
                        </template>
                    </text-input>
                </div>

                <!-- Expiration -->
                <date-input type="date" label="Expiration" v-model="form.expiration" :error="form.errors.expiration" />

                <!-- Remarks -->
                <text-area label="PR Details/Remarks" v-model="form.remarks" :error="form.errors.remarks" />
            </div>

            <!-- Actions -->
            <div class="flex gap-3 justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="resetIncomingForm"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                >
                    <RotateCcw class="w-4 h-4" />
                    Reset
                </button>

                <button
                    type="submit"
                    :disabled="model.api.processing"
                    class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-AA hover:bg-AA-dark disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors shadow-sm"
                >
                    <Loader2 v-if="model.api.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4" />
                    <span v-if="model.api.processing">{{ isUpdate ? 'Updating...' : 'Saving...' }}</span>
                    <span v-else>{{ isUpdate ? 'Update' : 'Save' }}</span>
                </button>
            </div>

            <!-- Audit Info -->
            <audit-info-card
                v-if="isUpdate"
                :audit-logs="$page.props.auditLogs"
                :created-at="data?.created_at"
                :updated-at="data?.updated_at"
            />
        </div>

        <!-- Side Panel (Update Mode) -->
        <div v-if="currentFormAction !== 'create'" class="flex flex-col gap-4 shadow-xl sm:rounded-xl sm:p-4 lg:p-6 bg-white dark:bg-gray-800 h-fit border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col gap-4">
                <!-- Workflow Info -->
                <div class="border border-blue-100 dark:border-blue-800 rounded-lg p-4 bg-blue-50/50 dark:bg-blue-900/20">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg flex-shrink-0">
                            <GitBranch class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide text-sm mb-1">
                                Sub-Component Workflow
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                Save each equipment part as its own incoming transaction, then use the parent CBC or PRRI barcode above to link it back to the main equipment record.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Parent Transaction -->
                <div v-if="hasParentTransaction" class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-white dark:bg-gray-700/50 shadow-sm">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-AA/10 dark:bg-AA/20 rounded-lg flex-shrink-0">
                                <ArrowUpRight class="w-5 h-5 text-AA" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Parent Transaction</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    This transaction is linked as a sub-component
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('transactions.show', parentTransaction.id)"
                            class="group flex items-center gap-1.5 text-xs font-medium text-AA hover:text-AA-dark whitespace-nowrap transition-colors"
                        >
                            <span class="underline-offset-2 group-hover:underline">View Parent</span>
                            <ArrowUpRight class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm ml-11">
                        <div class="flex items-center gap-2">
                            <Package class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                            <div class="min-w-0">
                                <span class="text-gray-500 dark:text-gray-400 text-xs block">Item</span>
                                <span class="text-gray-900 dark:text-gray-100 font-medium truncate block">
                                    {{ parentTransaction?.item?.name ?? '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Hash class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 text-xs block">CBC Barcode</span>
                                <span class="font-mono text-gray-700 dark:text-gray-200">
                                    {{ parentTransaction?.barcode ?? '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Hash class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 text-xs block">PRRI Barcode</span>
                                <span class="font-mono text-gray-700 dark:text-gray-200">
                                    {{ parentTransaction?.barcode_prri ?? '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <User class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" />
                            <div class="min-w-0">
                                <span class="text-gray-500 dark:text-gray-400 text-xs block">Accountable</span>
                                <span class="text-gray-700 dark:text-gray-200 truncate block">
                                    {{ parentTransaction?.actor_display_name ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sub-Components Accordion -->
                <transaction-component-accordion
                    :components="attachedComponentsList"
                    title="Sub-Components"
                    :empty-message="'No sub-components linked to this transaction yet.'"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
/* Smooth transitions */
button {
    transition: all 0.2s ease;
}

/* Barcode image styling */
#barcode-image {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
}
</style>
