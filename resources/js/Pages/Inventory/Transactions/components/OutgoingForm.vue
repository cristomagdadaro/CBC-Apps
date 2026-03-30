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
            this.fetchGetApi('api.inventory.transactions.index.public', {'sort': 'created_at', 'order': 'desc', 'filter': 'id', 'filter_by_parent_column': 'item_id', 'filter_by_parent_id': this.data.item_id, 'per_page': 5})
                .then((response) => {
                    this.recentTransactions = response.data;
                });
            this.processing = false;
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
    <div class="grid sm:grid-cols-3 grid-cols-1 sm:p-5 p-3 sm:gap-3">
        <div class="flex flex-col col-span-2 gap-1 bg-white rounded">
            <div  v-if="displayData" class="flex flex-col  justify-between items-center gap-5 py-2 px-1 md:px-4 border-b">
                <div class="flex flex-col leading-tight w-full">
                    <span class="font-bold text-base md:text-lg whitespace-nowrap overflow-ellipsis overflow-hidden">
                        {{ displayData.name }} {{ displayData.description ? `(${displayData.description})` : '' }}
                    </span>
                    <span v-if="displayData.expiration" :class="{
                        'text-red-600 font-semibold': getExpirationStatus(displayData.expiration) === 'expired',
                        'text-orange-600 font-semibold': ['expiring_soon', 'expiring_today'].includes(getExpirationStatus(displayData.expiration)),
                        'text-gray-500': !getExpirationStatus(displayData.expiration)
                    }" class="text-xs">
                        Expiry: {{ formatDate(displayData.expiration) }}
                        <span v-if="getExpirationStatus(displayData.expiration) === 'expired'" class="ml-1">(Expired)</span>
                        <span v-else-if="getExpirationStatus(displayData.expiration) === 'expiring_today'" class="ml-1">(Expires Today)</span>
                        <span v-else-if="getExpirationStatus(displayData.expiration) === 'expiring_soon'" class="ml-1">(Expiring Soon)</span>
                    </span>
                    <span class="text-sm text-gray-500">{{ displayData.brand }}</span>
                    <span class="text-xs text-gray-500 leading-none" :class="{'text-red-600' : !data?.barcode}">{{ data?.barcode || 'Warning! NO BARCODE' }}</span>
                </div>
                <div class="flex sm:gap-4 gap-1 w-full justify-evenly">
                    <div class="flex flex-col leading-none md:leading-relaxed">
                        <span class="text-center text-gray-600">
                            {{ formatNumber(displayData.remaining_quantity) }}
                        </span>
                        <span class="text-xs text-gray-400">
                            Remaining
                        </span>
                    </div>
                    <div class="flex flex-col leading-none md:leading-relaxed">
                        <span class="text-center text-gray-600">
                            {{ formatNumber(displayData.total_outgoing) }}
                        </span>
                        <span class="text-xs text-gray-400">
                            Consumed
                        </span>
                    </div>
                    <div class="flex flex-col leading-none md:leading-relaxed">
                        <span class="text-center text-gray-600">
                            {{ utilizationPercentage }}%
                        </span>
                        <span class="text-xs text-gray-400">
                            Utilization
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:gap-3 gap-1 sm:p-3 p-1 rounded">
                <transaction-report-accordion
                    v-if="isUpdate"
                    class="w-full mb-3"
                    :reports="reportsList"
                />
                <h1 class="text-base md:text-lg font-semibold text-gray-800">{{ isUpdate ? 'Update Outgoing Transaction' : 'Outgoing Transaction' }}</h1>
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
                        label="Personnel"
                        :error="form.errors.personnel_id"
                        @selectedChange="form.personnel_id = $event"
                        class="w-full"
                    >
                        <template #icon>
                            <filter-icon class="h-4 w-4" />
                        </template>
                    </custom-dropdown>

                    <text-input
                        required
                        type-input="number"
                        autocomplete="off"
                        :label="'Quantity by ' + data?.unit"
                        name="quantity"
                        :placeholder="'How many ' + data?.unit + '(s)?'"
                        id="quantity"
                        v-model="form.quantity"
                        :error="form.errors.quantity"
                    />

                    <text-area
                        required
                        type-input="number"
                        autocomplete="off"
                        label="Purpose"
                        name="purpose"
                        id="purpose"
                        v-model="form.remarks"
                        :error="form.errors.remarks"
                    />

                    <div v-if="isUpdate" class="grid grid-cols-2 gap-2">
                        <text-input label="PRRI Barcode" v-model="form.barcode_prri" :error="form.errors.barcode_prri" />
                        <text-input label="PAR No" v-model="form.par_no" :error="form.errors.par_no" />
                        <text-input label="Condition" v-model="form.condition" :error="form.errors.condition" />
                    </div>

                    <div class="flex gap-1 justify-between">
                        <cancel-btn @click="resetOutgoingForm">
                            {{ isUpdate ? 'Cancel' : 'Reset' }}
                        </cancel-btn>
                        <submit-btn :disabled="model.api.processing">
                            <span v-if="model.api.processing">{{ isUpdate ? 'Updating' : 'Saving' }}</span>
                            <span v-else>{{ isUpdate ? 'Update' : 'Save' }}</span>
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
        <!-- Transactions Recent Ativities -->
        <div class="bg-white dark:bg-gray-800 sm:rounded-lg md:px-4 w-full">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Recent Transactions</h3>
            <div class="flex flex-col text-xs gap-1 w-full">
                <div v-if="processing" class="text-center py-3 bg-yellow-200 rounded-lg w-full">
                    <p>
                    Loading ...
                    </p>
                </div>
                <div v-else v-for="transaction in recentTransactions" :key="transaction.id" class="py-2 rounded-md px-2 duration-200 flex flex-col gap-1" :class="transaction.transac_type === 'incoming' ? 'bg-green-200':'bg-red-200'">
                    <div class="flex justify-between items-center leading-tight">
                        <div>   
                            <p>{{ transaction.personnel ? transaction.personnel.fname + ' ' + transaction.personnel.lname : 'Unknown' }}</p>
                            <p>{{ formatDate(transaction.created_at) }}</p>
                        </div>

                        <b class="text-center leading-none">
                            <span v-if="transaction.transac_type === 'incoming'">{{ transaction.quantity }}</span>
                            <span v-else>-{{ transaction.quantity }}</span>
                            {{ transaction.unit }}
                        </b>
                    </div>
                    <div v-if="transaction.remarks" class="border-t pt-1 border-gray-800 leading-none">
                        <span>{{ transaction.remarks }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
