<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SuppEquipReport from "@/Modules/domain/SuppEquipReport";
import Transaction from "@/Modules/domain/Transaction";
import DateInput from "@/Components/DateInput.vue";

export default {
    components: { DateInput },
    name: "SuppEquipReportForm",
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
            default: "create", // 'create' or 'update'
            validator: (v) => ["create", "update"].includes(v),
        },
    },
    emits: ["saved"],
    data() {
        return {
            transactionModel: null,
            transactionSearch: "",
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
            return this.selectedTemplate?.description || "Attach the necessary details for this report template.";
        },
        hasTemplates() {
            return this.templateOptions.length > 0;
        },
        isUpdateMode() {
            return this.mode === "update";
        },
        submitLabel() {
            if (this.model.api.processing) {
                return this.isUpdateMode ? "Updating..." : "Saving...";
            }

            return this.isUpdateMode ? "Update Report" : "Save Report";
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
        "form.report_type": {
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

                if (existingValue !== null && existingValue !== "") {
                    defaults[field] = existingValue;
                    return;
                }

                defaults[field] = fieldConfig.type === "date" ? new Date().toISOString().split("T")[0] : null;
            });
            this.form.report_data = defaults;
            this.syncReportedBy();
        },
        formatOptionLabel(label) {
            if (!label) return "";
            return label;
        },
        startCase(value) {
            return value
                .split("_")
                .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
                .join(" ");
        },
        async searchTransactions() {
            if (!this.transactionSearch) {
                this.transactionLookupError = "Enter a barcode, transaction ID, or keywords to search.";
                return;
            }

            this.transactionLookupError = null;
            this.transactionLoading = true;
            this.transactionResults = [];

            try {
                const response = await this.transactionModel.api.getIndex(
                    {
                        search: this.transactionSearch,
                        per_page: 5,
                    },
                    this.transactionModel,
                );

                if (Array.isArray(response?.data)) {
                    this.transactionResults = response.data;
                } else if (Array.isArray(response?.data?.data)) {
                    this.transactionResults = response.data.data;
                } else {
                    this.transactionResults = [];
                }

                if (!this.transactionResults.length) {
                    this.transactionLookupError = "No transactions matched your query.";
                    if (!this.selectedTransaction) {
                        this.form.transaction_id = null;
                    }
                }
            } catch (error) {
                console.error(error);
                this.transactionLookupError = "Unable to fetch transactions right now.";
            } finally {
                this.transactionLoading = false;
            }
        },
        syncSelectedTransaction() {
            const transaction = this.data?.transaction || null;

            if (!transaction) {
                return;
            }

            const resolvedTransaction = transaction instanceof Transaction ? transaction : new Transaction(transaction);

            this.selectedTransaction = resolvedTransaction;
            this.form.transaction_id = resolvedTransaction.id || this.data?.transaction_id || null;
            this.transactionSearch = resolvedTransaction.barcode || resolvedTransaction.item?.fullName || resolvedTransaction.id || "";
            this.syncReportedBy();
        },
        syncReportedBy() {
            if (!this.form?.report_data || !Object.prototype.hasOwnProperty.call(this.form.report_data, "reported_by")) {
                return;
            }

            if (this.form.report_data.reported_by) {
                return;
            }

            const reporter = this.selectedTransaction?.personnel?.fullName;
            this.form.report_data.reported_by = reporter || null;
        },
        selectTransaction(transaction) {
            const resolvedTransaction = transaction instanceof Transaction ? transaction : new Transaction(transaction || {});

            this.selectedTransaction = resolvedTransaction;
            this.form.transaction_id = resolvedTransaction?.id || null;
            this.syncReportedBy();
            this.transactionResults = [];
            if (resolvedTransaction?.barcode) {
                this.transactionSearch = resolvedTransaction.barcode;
            } else if (resolvedTransaction?.item?.fullName) {
                this.transactionSearch = resolvedTransaction.item.fullName;
            } else {
                this.transactionSearch = resolvedTransaction?.id || "";
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
            if (this.mode === "update") {
                response = await this.submitUpdate(false, "report_type");
            } else {
                response = await this.submitCreate(false, "report_type");
            }

            if ((this.mode === "create" && response?.status === 201) || (this.mode === "update" && response?.status === 200)) {
                this.successMessage = this.mode === "update" ? "Report updated successfully." : "Report saved successfully.";
                this.$emit("saved", response?.data?.data || null);
                if (this.mode === "create") {
                    this.clearTransaction();
                    this.transactionSearch = "";
                    this.initializeFieldState();
                    this.form.notes = null;
                    this.form.reported_at = new Date().toISOString().slice(0, 10);
                } else {
                    this.model = new SuppEquipReport(response?.data?.data || this.data || {});
                    this.setFormAction("update");
                    this.ensureReportType();
                    this.initializeFieldState();
                    this.syncSelectedTransaction();
                }
            }
        },
    },
};
</script>

<template>
    <form
        v-if="!!form"
        class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-lg shadow-2xl rounded-2xl p-6 sm:p-10 border border-gray-100 dark:border-slate-800 mx-auto w-full max-w-4xl"
        @submit.prevent="submitForm">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Attach Supplies & Equipment Report</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Link a structured report to a specific transaction for audit readiness.</p>
            </div>
            <transition-container type="fade">
                <div
                    v-if="successMessage"
                    class="text-sm text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 rounded">
                    {{ successMessage }}
                </div>
            </transition-container>
        </div>

        <div
            v-if="!hasTemplates"
            class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-3 py-2 rounded">
            Configure at least one template in config/suppequipreportforms.php to start logging reports.
        </div>

        <div
            v-else
            class="space-y-8">
            <div class="grid md:grid-cols-2 gap-6">
                <custom-dropdown
                    :value="form.report_type"
                    :options="templateOptions"
                    :with-all-option="false"
                    label="Report Template"
                    :required="true"
                    :disabled="isUpdateMode"
                    :error="form.errors.report_type"
                    @selectedChange="form.report_type = $event">
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
                    :error="resolveFieldError('reported_at')" />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ templateDescription }}</p>

            <div class="space-y-4">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Link Transaction
                    <span class="text-red-500 dark:text-red-400">*</span>
                </label>
                <div
                    v-if="!isUpdateMode"
                    class="flex flex-col md:flex-row gap-3">
                    <text-input
                        class="flex-1 rounded-xl shadow-sm"
                        :placeholder="'Scan barcode or paste transaction ID'"
                        v-model="transactionSearch"
                        :error="form.errors.transaction_id || transactionLookupError" />
                    <search-btn
                        type="button"
                        class="px-8 py-2.5 rounded-xl shadow-md hover:-translate-y-0.5 transition-all duration-300"
                        :disabled="model.api.processing || transactionLoading"
                        @click="searchTransactions">
                        <span v-if="!transactionLoading">Lookup Transaction</span>
                        <span v-else>Searching...</span>
                    </search-btn>
                </div>
                <transition-container type="fade">
                    <div
                        v-if="transactionLoading"
                        class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <loader-icon class="w-4 h-4" />
                        <span>Fetching transactions…</span>
                    </div>
                </transition-container>
                <transition-container type="fade">
                    <div
                        v-if="transactionLookupError"
                        class="text-xs text-red-600 dark:text-red-400">
                        {{ transactionLookupError }}
                    </div>
                </transition-container>

                <div
                    v-if="transactionResults.length && !isUpdateMode"
                    class="grid gap-2">
                    <div
                        v-for="tx in transactionResults"
                        :key="tx.id"
                        class="border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2 cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-400 transition-colors bg-white/50 dark:bg-slate-800/50"
                        @click="selectTransaction(tx)">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ tx.item?.brand || "" }} {{ tx.item?.fullName || "Unnamed Item" }} by
                            {{ tx?.personnel?.fullName || "Unassigned" }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Barcode: {{ tx.barcode || "Not set" }} · {{ tx.transac_type }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Date: {{ tx.created_at || "Not set" }}</p>
                    </div>
                </div>

                <div
                    v-if="selectedTransaction"
                    class="rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-900/20 px-3 py-2 flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-400">
                            Attached to {{ selectedTransaction.item?.brand || "" }}
                            {{ selectedTransaction.item?.fullName || "Item" }}
                        </p>
                        <button
                            v-if="!isUpdateMode"
                            type="button"
                            class="text-xs text-red-500 dark:text-red-400 hover:underline"
                            @click="clearTransaction">
                            Clear
                        </button>
                    </div>
                    <p class="text-xs text-emerald-700 dark:text-emerald-500">
                        Barcode: {{ selectedTransaction.barcode || "Not set" }} · {{ selectedTransaction.transac_type || "Not set" }} · Qty
                        {{ selectedTransaction.quantity || "Not set" }}
                    </p>
                    <p class="text-xs text-emerald-700 dark:text-emerald-500">Remarks: {{ selectedTransaction.remarks || "n/a" }}</p>
                </div>
            </div>

            <div class="grid gap-6">
                <div
                    v-for="(fieldConfig, fieldKey) in templateFields"
                    :key="fieldKey"
                    class="flex flex-col gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300 font-semibold">
                        {{ fieldConfig.label || startCase(fieldKey) }}
                        <span
                            v-if="(fieldConfig.rules || '').includes('required')"
                            class="text-red-500 dark:text-red-400">
                            *
                        </span>
                    </label>
                    <textarea
                        v-if="fieldConfig.type === 'textarea'"
                        class="w-full border border-gray-300 dark:border-slate-700 rounded-xl shadow-sm text-sm p-3 focus:border-emerald-500 focus:ring-emerald-500 transition-shadow bg-white/50 dark:bg-slate-800/50 dark:text-gray-100"
                        rows="3"
                        :placeholder="getFieldPlaceholder(fieldConfig, 'Enter details')"
                        :value="getFieldValue(fieldKey)"
                        @input="setFieldValue(fieldKey, $event.target.value)" />
                    <custom-dropdown
                        v-else-if="fieldConfig.type === 'select'"
                        :with-all-option="false"
                        :options="fieldConfig.options || []"
                        :value="getFieldValue(fieldKey)"
                        :error="resolveFieldError(fieldKey)"
                        :placeholder="getFieldPlaceholder(fieldConfig, 'Select an option')"
                        @selectedChange="setFieldValue(fieldKey, $event)">
                        <template #icon>
                            <filter-icon class="h-4 w-4" />
                        </template>
                    </custom-dropdown>
                    <text-input
                        v-else
                        :type-input="fieldConfig.type || 'text'"
                        :placeholder="getFieldPlaceholder(fieldConfig, 'Enter value')"
                        :error="resolveFieldError(fieldKey)"
                        v-model="form.report_data[fieldKey]" />
                    <input-error
                        v-if="resolveFieldError(fieldKey)"
                        :message="resolveFieldError(fieldKey)" />
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-700 dark:text-gray-300 font-semibold mb-2 block">Additional Notes</label>
                <textarea
                    class="w-full border border-gray-300 dark:border-slate-700 rounded-xl shadow-sm text-sm p-3 focus:border-emerald-500 focus:ring-emerald-500 transition-shadow bg-white/50 dark:bg-slate-800/50 dark:text-gray-100"
                    rows="3"
                    placeholder="Optional context, follow-up actions, or references"
                    v-model="form.notes" />
                <input-error
                    v-if="form.errors.notes"
                    :message="form.errors.notes" />
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-slate-800">
                <div>
                    <a
                        v-if="isUpdateMode"
                        :href="route('suppEquipReports.pdf', form.id)"
                        target="_blank"
                        class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 font-bold shadow-sm hover:bg-gray-200 dark:hover:bg-slate-700 transition-all hover:-translate-y-0.5 duration-300 gap-2">
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Report
                    </a>
                </div>
                <submit-btn
                    class="px-8 py-2.5 rounded-xl bg-AB text-white font-bold shadow-md hover:bg-AB/90 transition-all hover:-translate-y-0.5 duration-300"
                    :disabled="model.api.processing || !form.transaction_id">
                    <span>{{ submitLabel }}</span>
                </submit-btn>
            </div>
        </div>
    </form>
</template>
