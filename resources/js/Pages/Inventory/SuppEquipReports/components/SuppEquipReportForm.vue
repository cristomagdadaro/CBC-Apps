<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SuppEquipReport from "@/Modules/domain/SuppEquipReport";
import Transaction from "@/Modules/domain/Transaction";
import DateInput from '@/Components/DateInput.vue';

export default {
  components: { DateInput },
    name: 'SuppEquipReportForm',
    mixins: [ApiMixin],
    props: {
        reportTemplates: {
            type: Object,
            required: true,
        },
        barcode: {
            type: String,
            required: false,
        },
        mode: {
            type: String,
            default: 'create', // 'create' or 'update'
            validator: v => ['create', 'update'].includes(v),
        },
    },
    emits: ['saved'],
    data() {
        return {
            transactionModel: null,
            transactionSearch: '',
            transactionResults: [],
            transactionLookupError: null,
            transactionLoading: false,
            selectedTransaction: null,
            successMessage: null,
        };
    },
    computed: {
        templateOptions() {
            return Object.entries(this.reportTemplates || {}).map(([key, template]) => ({
                name: key,
                label: template.label || this.startCase(key),
            }));
        },
        selectedTemplate() {
            if (!this.form) return null;
            return this.reportTemplates?.[this.form.report_type] || null;
        },
        templateFields() {
            return this.selectedTemplate?.fields || {};
        },
        templateDescription() {
            return this.selectedTemplate?.description || 'Attach the necessary details for this report template.';
        },
        hasTemplates() {
            return this.templateOptions.length > 0;
        },
        isUpdateMode() {
            return this.mode === 'update';
        },
        submitLabel() {
            if (this.model.api.processing) {
                return this.isUpdateMode ? 'Updating...' : 'Saving...';
            }

            return this.isUpdateMode ? 'Update Report' : 'Save Report';
        },
    },
    watch: {
        reportTemplates: {
            handler() {
                this.ensureReportType();
                this.initializeFieldState();
            },
            deep: true,
        },
        'form.report_type': {
            handler() {
                this.initializeFieldState();
            },
        },
    },
    beforeMount() {
        this.model = new SuppEquipReport(this.data || {});
        this.setFormAction(this.mode);
        this.transactionModel = new Transaction();
        this.ensureReportType();
        this.initializeFieldState();
        this.syncSelectedTransaction();
    },
    mounted() {
        if (this.isUpdateMode) {
            return;
        }

        if (this.barcode) {
            this.transactionSearch = this.barcode;
            this.searchTransactions();
        }
    },
    methods: {
        ensureReportType() {
            if (!this.form || !this.hasTemplates) return;
            if (!this.form.report_type) {
                this.form.report_type = this.templateOptions[0]?.name;
            }
            if (!this.form.reported_at) {
                this.form.reported_at = new Date().toISOString().slice(0, 10);
            }
        },
        initializeFieldState() {
            if (!this.form) return;
            const defaults = {};
            Object.keys(this.templateFields).forEach((field) => {
                const fieldConfig = this.templateFields[field] || {};
                const existingValue = this.form.report_data?.[field] ?? null;

                if (existingValue !== null && existingValue !== '') {
                    defaults[field] = existingValue;
                    return;
                }

                defaults[field] = fieldConfig.type === 'date'
                    ? new Date().toISOString().split('T')[0]
                    : null;
            });
            this.form.report_data = defaults;
            this.syncReportedBy();
        },
        formatOptionLabel(label) {
            if (!label) return '';
            return label;
        },
        startCase(value) {
            return value
                .split('_')
                .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
                .join(' ');
        },
        async searchTransactions() {
            if (!this.transactionSearch) {
                this.transactionLookupError = 'Enter a barcode, transaction ID, or keywords to search.';
                return;
            }

            this.transactionLookupError = null;
            this.transactionLoading = true;
            this.transactionResults = [];

            try {
                const response = await this.transactionModel.api.getIndex({
                    search: this.transactionSearch,
                    per_page: 5,
                }, this.transactionModel);

                if (Array.isArray(response?.data)) {
                    this.transactionResults = response.data;
                } else if (Array.isArray(response?.data?.data)) {
                    this.transactionResults = response.data.data;
                } else {
                    this.transactionResults = [];
                }

                if (!this.transactionResults.length) {
                    this.transactionLookupError = 'No transactions matched your query.';
                    if (!this.selectedTransaction) {
                        this.form.transaction_id = null;
                    }
                }
            } catch (error) {
                console.error(error);
                this.transactionLookupError = 'Unable to fetch transactions right now.';
            } finally {
                this.transactionLoading = false;
            }
        },
        syncSelectedTransaction() {
            const transaction = this.data?.transaction || null;

            if (!transaction) {
                return;
            }

            const resolvedTransaction = transaction instanceof Transaction
                ? transaction
                : new Transaction(transaction);

            this.selectedTransaction = resolvedTransaction;
            this.form.transaction_id = resolvedTransaction.id || this.data?.transaction_id || null;
            this.transactionSearch = resolvedTransaction.barcode || resolvedTransaction.item?.fullName || resolvedTransaction.id || '';
            this.syncReportedBy();
        },
        syncReportedBy() {
            if (!this.form?.report_data || !Object.prototype.hasOwnProperty.call(this.form.report_data, 'reported_by')) {
                return;
            }

            if (this.form.report_data.reported_by) {
                return;
            }

            const reporter = this.selectedTransaction?.personnel?.fullName;
            this.form.report_data.reported_by = reporter || null;
        },
        selectTransaction(transaction) {
            const resolvedTransaction = transaction instanceof Transaction
                ? transaction
                : new Transaction(transaction || {});

            this.selectedTransaction = resolvedTransaction;
            this.form.transaction_id = resolvedTransaction?.id || null;
            this.syncReportedBy();
            this.transactionResults = [];
            if (resolvedTransaction?.barcode) {
                this.transactionSearch = resolvedTransaction.barcode;
            } else if (resolvedTransaction?.item?.fullName) {
                this.transactionSearch = resolvedTransaction.item.fullName;
            } else {
                this.transactionSearch = resolvedTransaction?.id || '';
            }
            this.transactionLookupError = null;
        },
        clearTransaction() {
            this.selectedTransaction = null;
            this.form.transaction_id = null;
            this.transactionResults = [];
        },
        getFieldPlaceholder(fieldConfig, fallback) {
            return fieldConfig?.placeholder || fallback;
        },
        resolveFieldError(fieldKey) {
            if (!this.form) return null;
            return this.form.errors[fieldKey] || this.form.errors[`report_data.${fieldKey}`];
        },
        setFieldValue(fieldKey, value) {
            if (!this.form?.report_data) return;
            this.form.report_data[fieldKey] = value;
        },
        getFieldValue(fieldKey) {
            return this.form?.report_data?.[fieldKey] ?? null;
        },
        async submitForm() {
            this.successMessage = null;
            let response;
            if (this.mode === 'update') {
                response = await this.submitUpdate(false, 'report_type');
            } else {
                response = await this.submitCreate(false, 'report_type');
            }

            if ((this.mode === 'create' && response?.status === 201) || (this.mode === 'update' && response?.status === 200)) {
                this.successMessage = this.mode === 'update' ? 'Report updated successfully.' : 'Report saved successfully.';
                this.$emit('saved', response?.data?.data || null);
                if (this.mode === 'create') {
                    this.clearTransaction();
                    this.transactionSearch = '';
                    this.initializeFieldState();
                    this.form.notes = null;
                    this.form.reported_at = new Date().toISOString().slice(0, 10);
                } else {
                    this.model = new SuppEquipReport(response?.data?.data || this.data || {});
                    this.setFormAction('update');
                    this.ensureReportType();
                    this.initializeFieldState();
                    this.syncSelectedTransaction();
                }
            }
        },
    },
}
</script>

<template>
    <form v-if="!!form" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-100" @submit.prevent="submitForm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Attach Supplies & Equipment Report</h2>
                <p class="text-sm text-gray-500">Link a structured report to a specific transaction for audit readiness.</p>
            </div>
            <transition-container type="fade">
                <div v-if="successMessage" class="text-sm text-green-700 bg-green-100 px-3 py-1 rounded">
                    {{ successMessage }}
                </div>
            </transition-container>
        </div>

        <div v-if="!hasTemplates" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded">
            Configure at least one template in config/suppequipreportforms.php to start logging reports.
        </div>

        <div v-else class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
                <custom-dropdown
                    :value="form.report_type"
                    :options="templateOptions"
                    :with-all-option="false"
                    label="Report Template"
                    :required="true"
                    :disabled="isUpdateMode"
                    :error="form.errors.report_type"
                    @selectedChange="form.report_type = $event"
                >
                    <template #icon>
                        <filter-icon class="h-4 w-4" />
                    </template>
                </custom-dropdown>
                <date-input
                    label="Reported At"
                    :required="true"
                    type-input="date"
                    :disabled="isUpdateMode"
                    v-model="form.reported_at"
                    :error="resolveFieldError('reported_at')"
                />
            </div>
            <p class="text-xs text-gray-500">{{ templateDescription }}</p>

            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-600">Link Transaction <span class="text-red-500">*</span></label>
                <div v-if="!isUpdateMode" class="flex flex-col md:flex-row gap-3">
                    <text-input
                        class="flex-1"
                        :placeholder="'Scan barcode or paste transaction ID'"
                        v-model="transactionSearch"
                        :error="form.errors.transaction_id || transactionLookupError"
                    />
                    <search-btn type="button" class="px-6" :disabled="model.api.processing || transactionLoading" @click="searchTransactions">
                        <span v-if="!transactionLoading">Lookup</span>
                        <span v-else>Searching</span>
                    </search-btn>
                </div>
                <transition-container type="fade">
                    <div v-if="transactionLoading" class="flex items-center gap-2 text-sm text-gray-500">
                        <loader-icon class="w-4 h-4" />
                        <span>Fetching transactions…</span>
                    </div>
                </transition-container>
                <transition-container type="fade">
                    <div v-if="transactionLookupError" class="text-xs text-red-600">
                        {{ transactionLookupError }}
                    </div>
                </transition-container>

                <div v-if="transactionResults.length && !isUpdateMode" class="grid gap-2">
                    <div
                        v-for="tx in transactionResults"
                        :key="tx.id"
                        class="border rounded px-3 py-2 cursor-pointer hover:border-AB"
                        @click="selectTransaction(tx)"
                    >
                        <p class="text-sm font-semibold text-gray-700">{{ tx.item?.brand || '' }} {{ tx.item?.fullName || 'Unnamed Item' }} by {{ tx?.personnel?.fullName || 'Unassigned' }}</p>
                        <p class="text-xs text-gray-500">Barcode: {{ tx.barcode || 'Not set' }} · {{ tx.transac_type }}</p>
                        <p class="text-xs text-gray-500">Date: {{ tx.created_at || 'Not set' }}</p>
                    </div>
                </div>

                <div v-if="selectedTransaction" class="rounded border border-green-200 bg-green-50 px-3 py-2 flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-green-800">Attached to {{ selectedTransaction.item?.brand || '' }} {{ selectedTransaction.item?.fullName || 'Item' }}</p>
                        <button v-if="!isUpdateMode" type="button" class="text-xs text-red-500" @click="clearTransaction">Clear</button>
                    </div>
                    <p class="text-xs text-green-700">Barcode: {{ selectedTransaction.barcode || 'Not set' }} · {{ selectedTransaction.transac_type || 'Not set' }} · Qty {{ selectedTransaction.quantity || 'Not set' }}</p>
                    <p class="text-xs text-green-700">Remarks: {{ selectedTransaction.remarks || 'n/a' }}</p>
                </div>
            </div>

            <div class="grid gap-4">
                <div
                    v-for="(fieldConfig, fieldKey) in templateFields"
                    :key="fieldKey"
                    class="flex flex-col gap-1"
                >
                    <label class="text-xs text-gray-600 font-semibold">
                        {{ fieldConfig.label || startCase(fieldKey) }}
                        <span v-if="(fieldConfig.rules || '').includes('required')" class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-if="fieldConfig.type === 'textarea'"
                        class="w-full border border-gray-300 rounded-md shadow-sm text-sm focus:border-AB focus:ring-AB"
                        rows="3"
                        :placeholder="getFieldPlaceholder(fieldConfig, 'Enter details')"
                        :value="getFieldValue(fieldKey)"
                        @input="setFieldValue(fieldKey, $event.target.value)"
                    />
                    <custom-dropdown
                        v-else-if="fieldConfig.type === 'select'"
                        :with-all-option="false"
                        :options="fieldConfig.options || []"
                        :value="getFieldValue(fieldKey)"
                        :error="resolveFieldError(fieldKey)"
                        :placeholder="getFieldPlaceholder(fieldConfig, 'Select an option')"
                        @selectedChange="setFieldValue(fieldKey, $event)"
                    >
                        <template #icon>
                            <filter-icon class="h-4 w-4" />
                        </template>
                    </custom-dropdown>
                    <text-input
                        v-else
                        :type-input="fieldConfig.type || 'text'"
                        :placeholder="getFieldPlaceholder(fieldConfig, 'Enter value')"
                        :error="resolveFieldError(fieldKey)"
                        v-model="form.report_data[fieldKey]"
                    />
                    <input-error v-if="resolveFieldError(fieldKey)" :message="resolveFieldError(fieldKey)" />
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-600 font-semibold">Additional Notes</label>
                <textarea
                    class="w-full border border-gray-300 rounded-md shadow-sm text-sm focus:border-AB focus:ring-AB"
                    rows="3"
                    placeholder="Optional context, follow-up actions, or references"
                    v-model="form.notes"
                />
                <input-error v-if="form.errors.notes" :message="form.errors.notes" />
            </div>

            <div class="flex justify-end">
                <submit-btn
                    class="px-6"
                    :disabled="model.api.processing || !form.transaction_id"
                >
                    <span>{{ submitLabel }}</span>
                </submit-btn>
            </div>
        </div>
    </form>
</template>
