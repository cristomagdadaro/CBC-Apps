<script>
import Transaction from "@/Modules/domain/Transaction";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DtoError from "@/Modules/dto/DtoError";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import TransactionReportAccordion from "@/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue";
import AuditInfoCard from "@/Components/AuditInfoCard.vue";
import LuBarcode from "@/Components/Icons/LuBarcode.vue";
import LuXCircle from "@/Components/Icons/LuXCircle.vue";
import { ChevronDown, ChevronUp } from "lucide-vue-next";

export default {
    name: "OutgoingForm",
    components: {
        TransactionReportAccordion,
        AuditInfoCard,
        LuBarcode,
        LuXCircle,
        ChevronDown,
        ChevronUp,
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
            default: "create",
        },
        isGuest: {
            type: Boolean,
            default: false,
        },
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            employee_id: "",
            recentTransactions: [],
            processing: false,
            isTransactionsCollapsed: true, // Default to true (collapsed) on mobile
        };
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction(this.isUpdate ? "update" : "create");
    },
    computed: {
        isUpdate() {
            return this.mode === "update";
        },
        isAuthenticated() {
            return this.$page.props.auth && this.$page.props.auth.user;
        },
        isPublic() {
            return this.isGuest || !this.isAuthenticated;
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
                return "0.00";
            }

            return (100 - (remaining / totalIngoing) * 100).toFixed(2);
        },
        totalIngoing() {
            return Number(this.displayData?.total_ingoing ?? 0);
        },
        remainingQuantity() {
            return Number(this.displayData?.remaining_quantity ?? 0);
        },
        consumedQuantity() {
            return Number(this.displayData?.total_outgoing ?? 0);
        },
        remainingRatio() {
            if (this.totalIngoing <= 0) return 0;
            const ratio = (this.remainingQuantity / this.totalIngoing) * 100;
            return Math.min(100, Math.max(0, ratio));
        },
        stockHealthStatus() {
            if (this.remainingQuantity <= 0) {
                return {
                    label: "Out of Stock",
                    colorClass: "bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-900/50",
                    barClass: "bg-rose-500",
                };
            }
            if (this.remainingRatio <= 25) {
                return {
                    label: "Low Stock",
                    colorClass: "bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-900/50",
                    barClass: "bg-amber-500",
                };
            }
            return {
                label: "In Stock",
                colorClass: "bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/50",
                barClass: "bg-lime-500",
            };
        },
    },
    methods: {
        formatNumber(value) {
            if (value === null || value === undefined) return "0";
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        async proxySubmit() {
            if (this.isPublic && !this.isAuthenticated) {
                //this.form.employee_id = this.employee_id;
                this.form.user_id = null;
                this.form.personnel_id = null;
            } else if (this.isGuest && this.isAuthenticated) {
                this.form.employee_id = this.$page.props.auth.user.employee_id;
            }

            const temp = this.isUpdate ? await this.submitUpdate() : await this.submitCreate();

            if (temp instanceof DtoResponse) this.$emit("submitted");
            if (temp instanceof DtoError) this.$emit("error");
        },
        resetOutgoingForm() {
            if (this.isUpdate) {
                this.resetField(this.data);
                return;
            }

            this.resetForm(["barcode", "item_id", "transac_type", "unit"]);
        },
        getRecentTransactions() {
            this.processing = true;
            const barcode = this.data?.barcode;
            const filterCol = barcode ? "barcode" : "item_id";
            const filterVal = barcode ? barcode : this.data?.item_id;

            if (!filterVal) {
                this.processing = false;
                return;
            }

            this.fetchGetApi("api.inventory.transactions.index.public", {
                sort: "created_at",
                order: "desc",
                filter_by_parent_column: filterCol,
                filter_by_parent_id: filterVal,
                per_page: 5,
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
            this.form.transac_type = "outgoing";
        }

        this.getRecentTransactions();
    },
    watch: {
        "form.quantity": {
            handler(newVal) {
                if (newVal > Number(this.displayData?.remaining_quantity)) this.form["errors"].quantity = "Exceeds maximum quantity";
                else this.form["errors"].quantity = null;
            },
        },
    },
};
</script>

<template>
    <div class="grid grid-cols-1 gap-1 bg-transparent sm:grid-cols-5 md:gap-2 dark:text-slate-100">
        <div class="shadow-xs col-span-1 flex flex-col gap-4 rounded-t-2xl border-slate-200 bg-white p-4 sm:col-span-3 sm:p-5 md:rounded-2xl dark:border dark:border-slate-800 dark:bg-slate-900">
            <div
                v-if="displayData"
                class="flex flex-col gap-3">
                <!-- Item Details Header (Full Width) -->
                <div class="flex w-full flex-col space-y-1 leading-tight">
                    <h2 class="flex flex-col border-b pb-2">
                        <div class="text-lg font-bold leading-none text-slate-900 dark:text-slate-100">
                            {{ displayData.name }}
                            <span
                                v-if="displayData.description"
                                class="font-normal">
                                ({{ displayData.description }})
                            </span>
                        </div>
                        <span
                            v-if="displayData.brand"
                            class="text-sm text-slate-700 dark:text-slate-300">
                            {{ displayData.brand }}
                        </span>
                    </h2>

                    <div class="items-left flex w-full flex-row justify-between gap-x-4 pt-0.5 text-xs md:text-sm">
                        <span class="flex items-center gap-1 leading-none">
                            <lu-barcode class="h-auto w-5" />
                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                {{ data?.barcode || "NO BARCODE" }}
                            </span>
                        </span>
                        <span
                            v-if="displayData.expiration"
                            class="flex items-center gap-1 leading-none"
                            :class="{
                                'text-rose-600 dark:text-rose-400': getExpirationStatus(displayData.expiration) === 'expired',
                                'text-amber-600 dark:text-amber-400': ['expiring_soon', 'expiring_today'].includes(getExpirationStatus(displayData.expiration)),
                                'text-green-600 dark:text-green-400': !getExpirationStatus(displayData.expiration),
                            }">
                            <LuXCircle class="h-auto w-5" />
                            Expiry
                            {{ formatDate(displayData.expiration) }}
                        </span>
                    </div>
                </div>

                <!-- Clean 3-Column Stats Card -->
                <div class="grid w-full grid-cols-3 gap-2 rounded-xl bg-slate-50/60 p-4 dark:bg-slate-800/40">
                    <div class="flex flex-col justify-center p-1 text-center">
                        <span class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ formatNumber(displayData.remaining_quantity) }}
                        </span>
                        <span class="mt-1 text-[0.68rem] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">REMAINING</span>
                    </div>
                    <div class="flex flex-col justify-center p-1 text-center">
                        <span class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ formatNumber(displayData.total_outgoing) }}
                        </span>
                        <span class="mt-1 text-[0.68rem] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">CONSUMED</span>
                    </div>
                    <div class="flex flex-col justify-center p-1 text-center">
                        <span class="text-xl font-bold text-lime-600 dark:text-lime-400">{{ utilizationPercentage }}%</span>
                        <span class="mt-1 text-[0.68rem] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">UTILIZATION</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-3 pt-2">
                <transaction-report-accordion
                    v-if="isUpdate"
                    class="mb-3 w-full"
                    :reports="reportsList" />
                <h1 class="text-sm font-bold uppercase tracking-wider text-slate-800 sm:text-base dark:text-slate-200">
                    {{ isUpdate ? "Update Outgoing Transaction" : "Checkout Form" }}
                </h1>
                <form
                    @submit.prevent="proxySubmit"
                    class="flex flex-col gap-3">
                    <!-- Public access: ask for Employee ID only -->
                    <text-input
                        v-if="isPublic && !isAuthenticated"
                        required
                        label="PhilRice ID"
                        placeholder="XX-XXXX"
                        name="employee_id"
                        id="employee_id"
                        v-model="form.employee_id"
                        :error="form.errors.employee_id" />

                    <div
                        v-else-if="isGuest && isAuthenticated"
                        class="rounded bg-emerald-50 p-2 text-sm font-medium text-emerald-700">
                        ID: {{ $page.props.auth.user.employee_id || "Authenticated User" }}
                    </div>

                    <!-- Logged-in users: allow choosing Personnel (consumer) -->
                    <custom-dropdown
                        v-if="!isGuest && isAuthenticated"
                        required
                        searchable
                        :with-all-option="false"
                        :value="form.personnel_id"
                        :options="personnels"
                        placeholder="Select Personnel"
                        label="Accountable Personnel"
                        :error="form.errors.personnel_id"
                        @selectedChange="form.personnel_id = $event"
                        :show-valid-indicator="false"
                        class="w-full" />

                    <text-input
                        required
                        type-input="number"
                        autocomplete="off"
                        :label="'Quantity by ' + (data?.unit || 'unit')"
                        name="quantity"
                        :placeholder="'How many ' + (data?.unit || 'unit') + '(s)?'"
                        id="quantity"
                        v-model="form.quantity"
                        :error="form.errors.quantity" />

                    <text-area
                        required
                        autocomplete="off"
                        label="Purpose / Remarks"
                        name="purpose"
                        id="purpose"
                        v-model="form.remarks"
                        :error="form.errors.remarks" />

                    <div
                        v-if="isUpdate"
                        class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <text-input
                            label="PRRI Barcode"
                            v-model="form.barcode_prri"
                            :error="form.errors.barcode_prri" />
                        <text-input
                            label="PAR No"
                            v-model="form.par_no"
                            :error="form.errors.par_no" />
                        <text-input
                            label="Condition"
                            v-model="form.condition"
                            :error="form.errors.condition" />
                    </div>

                    <div class="flex justify-between gap-2 pt-2">
                        <div class="flex items-center gap-3">
                            <cancel-btn
                                type="button"
                                @click.prevent="$emit('close')">
                                Cancel
                            </cancel-btn>
                            <button
                                type="button"
                                @click="resetOutgoingForm"
                                class="text-xs font-semibold text-slate-400 transition-colors hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">
                                Reset
                            </button>
                        </div>
                        <submit-btn :disabled="model.api.processing">
                            <span v-if="model.api.processing">
                                {{ isUpdate ? "Updating..." : "Saving..." }}
                            </span>
                            <span v-else>{{ isUpdate ? "Update" : "Checkout" }}</span>
                        </submit-btn>
                    </div>
                    <audit-info-card
                        v-if="isUpdate"
                        :audit-logs="$page.props.auditLogs"
                        :created-at="data?.created_at"
                        :updated-at="data?.updated_at" />
                </form>
            </div>
        </div>
        <!-- Recent Activities Feed -->
        <div class="shadow-xs h-fit rounded-b-2xl border-slate-200 bg-white sm:col-span-2 md:rounded-2xl dark:border dark:border-slate-800 dark:bg-slate-900">
            <!-- Header with Mobile Toggle -->
            <div
                class="flex cursor-pointer items-center justify-between p-4 sm:cursor-default sm:p-5 sm:!pl-0"
                @click="isTransactionsCollapsed = !isTransactionsCollapsed">
                <!-- Wrapped heading with an icon -->
                <div class="flex items-center gap-2">
                    <LuHistory class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Recent Transactions</h3>
                </div>

                <button
                    type="button"
                    class="text-slate-400 hover:text-slate-600 focus:outline-none sm:hidden dark:text-slate-500 dark:hover:text-slate-300">
                    <LuChevronUpIcon
                        class="h-5 w-5 duration-200"
                        :class="{ 'rotate-180': isTransactionsCollapsed }" />
                </button>
            </div>

            <!-- Content Wrapper (Hidden on mobile if collapsed) -->
            <div
                :class="isTransactionsCollapsed ? 'hidden sm:block' : 'block'"
                class="px-4 pb-4 sm:p-0 sm:pb-5 sm:pr-5">
                <div class="flex w-full flex-col gap-2 text-xs">
                    <div
                        v-if="processing"
                        class="rounded-xl bg-slate-100 py-4 text-center font-semibold text-slate-500 dark:bg-slate-800">
                        Loading transactions...
                    </div>
                    <div
                        v-else
                        v-for="transaction in recentTransactions"
                        :key="transaction.id"
                        class="flex flex-col gap-1.5 rounded-xl border p-3 transition-colors"
                        :class="transaction.transac_type === 'incoming' ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/60 dark:bg-emerald-950/30' : 'border-rose-200 bg-rose-50/60 dark:border-rose-900/60 dark:bg-rose-950/30'">
                        <div class="flex flex-col items-start justify-between leading-tight">
                            <p class="font-bold text-slate-900 dark:text-slate-100">
                                {{ transaction.actor_display_name || "Unknown" }}
                            </p>
                            <div class="flex w-full items-center justify-between">
                                <p class="mt-0.5 text-[0.68rem] text-slate-500 dark:text-slate-400">
                                    {{ transaction.created_at }}
                                </p>
                                <span
                                    class="whitespace-nowrap rounded-lg px-2 py-1 text-right text-xs font-bold"
                                    :class="transaction.transac_type === 'incoming' ? 'text-emerald-700 dark:text-emerald-300' : 'ark:text-rose-300 text-rose-700'">
                                    {{ transaction.transac_type === "incoming" ? "+" : "-" }}{{ transaction.quantity }} {{ transaction.unit || "" }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-if="transaction.remarks"
                            class="border-t border-slate-200/60 pt-1.5 text-[0.7rem] text-slate-600 dark:border-slate-800 dark:text-slate-300">
                            <span>{{ transaction.remarks }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
