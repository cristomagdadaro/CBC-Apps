<script>
import Transaction from "@/Modules/domain/Transaction";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DtoError from "@/Modules/dto/DtoError";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import TransactionReportAccordion from "@/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";

export default {
    name: "OutgoingForm",
    components: {
        TransactionReportAccordion,
        AuditInfoCard,
    },
    props: {
        personnels: {
            type: Array,
            default: () => [],
        },
        data: {
            type: Object,
            default: null,
        },
        summary: {
            type: Object,
            default: null,
        },
        attachedReports: {
            type: Array,
            default: () => [],
        },
        mode: {
            type: String,
            default: 'create',
        },
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            employee_id: '',
            recentTransactions: [],
            processing: false,
        }
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction(this.isUpdate ? 'update' : 'create');
    },
    computed: {
        isUpdate() {
            return this.mode === 'update';
        },
        isAuthenticated() {
            return (this.$page.props.auth && this.$page.props.auth.user);
        },
        isPublic() {
            return !this.isAuthenticated;
        },
        displayData() {
            return this.isUpdate ? (this.summary ?? this.data ?? {}) : (this.data ?? {});
        },
        reportsList() {
            return Array.isArray(this.attachedReports) ? this.attachedReports : [];
        },
        utilizationPercentage() {
            const totalIngoing = Number(this.displayData?.total_ingoing ?? 0);
            const remaining = Number(this.displayData?.remaining_quantity ?? 0);

            if (totalIngoing <= 0) {
                return '0.00';
            }

            return (100 - ((remaining / totalIngoing) * 100)).toFixed(2);
        },
    },
    methods: {
        formatNumber(value){
            if (value === null || value === undefined) return '0';
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        formatDate(value) {
            if (!value) return 'N/A';
            const normalized = typeof value === 'string' && !value.includes('T') ? value.replace(' ', 'T') : value;
            const date = new Date(normalized);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString();
        },
        async proxySubmit() {
            if (this.isPublic) {
                //this.form.employee_id = this.employee_id;
                this.form.user_id = null;
                this.form.personnel_id = null;
            }

            const temp = this.isUpdate
                ? await this.submitUpdate()
                : await this.submitCreate();

            if (temp instanceof DtoResponse)
                this.$emit('submitted');
            if (temp instanceof DtoError)
                this.$emit('error');
        },
        resetOutgoingForm() {
            if (this.isUpdate) {
                this.resetField(this.data);
                return;
            }

            this.resetForm(['barcode','item_id','transac_type','unit']);
        },
        getRecentTransactions() {
            this.processing = true;
            const barcode = this.data?.barcode;
            const filterCol = barcode ? 'barcode' : 'item_id';
            const filterVal = barcode ? barcode : this.data?.item_id;

            if (!filterVal) {
                this.processing = false;
                return;
            }

            this.fetchGetApi('api.inventory.transactions.index.public', {
                'sort': 'created_at',
                'order': 'desc',
                'filter_by_parent_column': filterCol,
                'filter_by_parent_id': filterVal,
                'per_page': 5
            })
                .then((response) => {
                    this.recentTransactions = response?.data || [];
                })
                .finally(() => {
                    this.processing = false;
                });
        },
    },
    mounted() {
        this.form.barcode = this.data?.barcode;
        this.form.name = this.data?.name;
        this.form.brand = this.data?.brand;
        this.form.unit = this.data?.unit;
        this.form.item_id = this.data?.item_id;

        // For logged-in users, set user_id so we know who inserted the record
        if (this.isAuthenticated) {
            this.form.user_id = this.$page.props?.auth?.user?.id;
        }

        if (!this.form.transac_type) {
            this.form.transac_type = 'outgoing';
        }

        this.getRecentTransactions();
    },
    watch: {
        'form.quantity': {
            handler(newVal) {
                if (newVal > Number(this.displayData?.remaining_quantity))
                    this.form['errors'].quantity = 'Exceeds maximum quantity';
                else
                    this.form['errors'].quantity = null;
            }
        }
    }
}
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 sm:p-5 gap-4 text-slate-900 dark:text-slate-100">
        <div class="flex flex-col col-span-1 sm:col-span-2 gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div v-if="displayData" class="flex flex-col gap-3 pb-4 border-b border-slate-200 dark:border-slate-800">
                <!-- Item Details Header (Full Width) -->
                <div class="flex flex-col leading-tight w-full space-y-1">
                    <h2 class="font-extrabold text-base sm:text-lg text-slate-900 dark:text-slate-100 break-words">
                        {{ displayData.name }}
                        <span v-if="displayData.description" class="text-slate-500 font-normal text-xs sm:text-sm">({{ displayData.description }})</span>
                    </h2>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs pt-0.5">
                        <span v-if="displayData.expiration" :class="{
                            'text-rose-600 dark:text-rose-400 font-bold': getExpirationStatus(displayData.expiration) === 'expired',
                            'text-amber-600 dark:text-amber-400 font-bold': ['expiring_soon', 'expiring_today'].includes(getExpirationStatus(displayData.expiration)),
                            'text-slate-500 dark:text-slate-400': !getExpirationStatus(displayData.expiration)
                        }" class="font-medium">
                            Expiry: {{ formatDate(displayData.expiration) }}
                            <span v-if="getExpirationStatus(displayData.expiration) === 'expired'">(Expired)</span>
                            <span v-else-if="getExpirationStatus(displayData.expiration) === 'expiring_today'">(Expires Today)</span>
                            <span v-else-if="getExpirationStatus(displayData.expiration) === 'expiring_soon'">(Expiring Soon)</span>
                        </span>
                        <span v-if="displayData.brand" class="text-slate-500 dark:text-slate-400 font-medium">Brand: {{ displayData.brand }}</span>
                        <span class="font-mono font-semibold" :class="data?.barcode ? 'text-slate-600 dark:text-slate-300' : 'text-rose-500 font-bold'">Barcode: {{ data?.barcode || 'NO BARCODE' }}</span>
                    </div>
                </div>

                <!-- Stats Grid Placed Cleanly UNDER Item Details -->
                <div class="grid grid-cols-3 gap-2 w-full bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200 dark:border-slate-700/80">
                    <div class="flex flex-col text-center justify-center p-1">
                        <span class="text-base sm:text-xl font-black text-slate-900 dark:text-slate-100">
                            {{ formatNumber(displayData.remaining_quantity) }}
                        </span>
                        <span class="text-[0.65rem] uppercase font-extrabold tracking-wider text-slate-500 dark:text-slate-400">
                            Remaining
                        </span>
                    </div>
                    <div class="flex flex-col text-center justify-center p-1 border-x border-slate-200 dark:border-slate-700/60">
                        <span class="text-base sm:text-xl font-black text-slate-900 dark:text-slate-100">
                            {{ formatNumber(displayData.total_outgoing) }}
                        </span>
                        <span class="text-[0.65rem] uppercase font-extrabold tracking-wider text-slate-500 dark:text-slate-400">
                            Consumed
                        </span>
                    </div>
                    <div class="flex flex-col text-center justify-center p-1">
                        <span class="text-base sm:text-xl font-black text-lime-600 dark:text-lime-400">
                            {{ utilizationPercentage }}%
                        </span>
                        <span class="text-[0.65rem] uppercase font-extrabold tracking-wider text-slate-500 dark:text-slate-400">
                            Utilization
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-3 pt-2">
                <transaction-report-accordion
                    v-if="isUpdate"
                    class="w-full mb-3"
                    :reports="reportsList"
                />
                <h1 class="text-sm sm:text-base font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">{{ isUpdate ? 'Update Outgoing Transaction' : 'Checkout' }}</h1>
                <form @submit.prevent="proxySubmit" class="flex flex-col gap-3">
                    <!-- Public access: ask for Employee ID only -->
                    <text-input
                        v-if="isPublic"
                        required
                        label="PhilRice ID"
                        placeholder="XX-XXXX"
                        name="employee_id"
                        id="employee_id"
                        v-model="form.employee_id"
                        :error="form.errors.employee_id"
                    />

                    <!-- Logged-in users: allow choosing Personnel (consumer) -->
                    <custom-dropdown
                        v-if="isAuthenticated"
                        required
                        searchable
                        :with-all-option="false"
                        :value="form.personnel_id"
                        :options="personnels"
                        placeholder="Select Personnel"
                        label="Accountable Personnel"
                        :error="form.errors.personnel_id"
                        @selectedChange="form.personnel_id = $event"
                        class="w-full"
                    />

                    <text-input
                        required
                        type-input="number"
                        autocomplete="off"
                        :label="'Quantity by ' + (data?.unit || 'unit')"
                        name="quantity"
                        :placeholder="'How many ' + (data?.unit || 'unit') + '(s)?'"
                        id="quantity"
                        v-model="form.quantity"
                        :error="form.errors.quantity"
                    />

                    <text-area
                        required
                        autocomplete="off"
                        label="Purpose / Remarks"
                        name="purpose"
                        id="purpose"
                        v-model="form.remarks"
                        :error="form.errors.remarks"
                    />

                    <div v-if="isUpdate" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <text-input label="PRRI Barcode" v-model="form.barcode_prri" :error="form.errors.barcode_prri" />
                        <text-input label="PAR No" v-model="form.par_no" :error="form.errors.par_no" />
                        <text-input label="Condition" v-model="form.condition" :error="form.errors.condition" />
                    </div>

                    <div class="flex gap-2 justify-between pt-2">
                        <cancel-btn @click="resetOutgoingForm">
                            {{ isUpdate ? 'Cancel' : 'Reset' }}
                        </cancel-btn>
                        <submit-btn :disabled="model.api.processing">
                            <span v-if="model.api.processing">{{ isUpdate ? 'Updating...' : 'Saving...' }}</span>
                            <span v-else>{{ isUpdate ? 'Update' : 'Checkout' }}</span>
                        </submit-btn>
                    </div>
                    <audit-info-card
                        v-if="isUpdate"
                        :audit-logs="$page.props.auditLogs"
                        :created-at="data?.created_at"
                        :updated-at="data?.updated_at"
                    />
                </form>
            </div>
        </div>
        <!-- Recent Activities Feed -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 h-fit shadow-xs">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-3">Recent Transactions</h3>
            <div class="flex flex-col text-xs gap-2 w-full">
                <div v-if="processing" class="text-center py-4 bg-slate-100 dark:bg-slate-800 rounded-xl text-slate-500 font-semibold">
                    Loading transactions...
                </div>
                <div v-else v-for="transaction in recentTransactions" :key="transaction.id" class="p-3 rounded-xl border flex flex-col gap-1.5 transition-colors"
                    :class="transaction.transac_type === 'incoming'
                        ? 'bg-emerald-50/60 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-900/60'
                        : 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/60'"
                >
                    <div class="flex justify-between items-center leading-tight">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ transaction.actor_display_name || 'Unknown' }}</p>
                            <p class="text-[0.68rem] text-slate-500 dark:text-slate-400 mt-0.5">{{ formatDate(transaction.created_at) }}</p>
                        </div>

                        <span class="font-extrabold text-xs px-2 py-1 rounded-lg"
                            :class="transaction.transac_type === 'incoming' ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/50' : 'text-rose-700 dark:text-rose-300 bg-rose-100 dark:bg-rose-900/50'"
                        >
                            {{ transaction.transac_type === 'incoming' ? '+' : '-' }}{{ transaction.quantity }} {{ transaction.unit || '' }}
                        </span>
                    </div>
                    <div v-if="transaction.remarks" class="border-t pt-1.5 border-slate-200/60 dark:border-slate-800 text-[0.7rem] text-slate-600 dark:text-slate-300">
                        <span>{{ transaction.remarks }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
