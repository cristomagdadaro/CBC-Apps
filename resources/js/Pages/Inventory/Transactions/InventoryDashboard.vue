<script>
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
import Transaction from "@/Modules/domain/Transaction";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import {
    ArrowUpDown,
    ArrowDownLeft,
    ArrowUpRight,
    AlertTriangle,
    Layers,
    MapPin,
    FolderGit2,
    TrendingUp,
    PackageCheck,
    RefreshCw,
    Calendar,
    Box
} from "lucide-vue-next";

export default {
    name: "InventoryDashboard",
    components: {
        TransactionHeaderAction,
        ArrowUpDown,
        ArrowDownLeft,
        ArrowUpRight,
        AlertTriangle,
        Layers,
        MapPin,
        FolderGit2,
        TrendingUp,
        PackageCheck,
        RefreshCw,
        Calendar,
        Box
    },
    mixins: [ApiMixin],
    data() {
        const today = new Date();
        const selectedDate = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, "0"),
            String(today.getDate()).padStart(2, "0"),
        ].join("-");
        const weekDate = new Date(Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()));
        const weekDay = weekDate.getUTCDay() || 7;

        weekDate.setUTCDate(weekDate.getUTCDate() + 4 - weekDay);

        const yearStart = new Date(Date.UTC(weekDate.getUTCFullYear(), 0, 1));
        const selectedWeek = `${weekDate.getUTCFullYear()}-W${String(Math.ceil((((weekDate - yearStart) / 86400000) + 1) / 7)).padStart(2, "0")}`;
        const selectedMonth = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}`;

        return {
            loading: false,
            scope: "all",
            selectedDate,
            selectedWeek,
            selectedMonth,
            selectedYear: String(today.getFullYear()),
            scopeOptions: [
                { name: "all", label: "All Time (Entire Database)" },
                { name: "day", label: "Last 24 Hours" },
                { name: "daily", label: "Specific Day" },
                { name: "week", label: "Last 7 Days" },
                { name: "weekly", label: "Specific Week" },
                { name: "month", label: "Last 30 Days" },
                { name: "monthly", label: "Specific Month" },
                { name: "year", label: "Last 365 Days" },
                { name: "yearly", label: "Specific Year" },
            ],
            dashboard: {
                range: { start: null, end: null },
                totals: {
                    incoming: 0,
                    outgoing: 0,
                    incoming_count: 0,
                    outgoing_count: 0,
                    incoming_quantity: 0,
                    outgoing_quantity: 0,
                    total_transactions: 0
                },
                top_issued_items: [],
                recent_transactions: [],
                items_per_category: [],
                items_per_location: [],
                items_per_project_code: [],
                stock_buckets: { empty: 0, low: 0, mid: 0, high: 0 },
            },
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
        };
    },
    computed: {
        dashboardParams() {
            const params = { scope: this.scope };

            if (this.scope === "daily") {
                params.date = this.selectedDate;
            }

            if (this.scope === "weekly") {
                params.week = this.selectedWeek;
            }

            if (this.scope === "monthly") {
                params.month = this.selectedMonth;
            }

            if (this.scope === "yearly") {
                params.year = this.selectedYear;
            }

            return params;
        },
        usesAnchoredPeriod() {
            return ["daily", "weekly", "monthly", "yearly"].includes(this.scope);
        },
        periodInputLabel() {
            return {
                daily: "Date",
                weekly: "Week",
                monthly: "Month",
                yearly: "Year",
            }[this.scope] || "Period";
        },
        periodInputType() {
            return {
                daily: "date",
                weekly: "week",
                monthly: "month",
                yearly: "number",
            }[this.scope] || "text";
        },
        periodInputValue() {
            return {
                daily: this.selectedDate,
                weekly: this.selectedWeek,
                monthly: this.selectedMonth,
                yearly: this.selectedYear,
            }[this.scope] || "";
        },
        scopeCaption() {
            const captions = {
                all: "across all time (entire database)",
                day: "Last 24 hours",
                daily: this.selectedDate ? `${this.formatDisplayDate(this.selectedDate)}` : "Selected day",
                week: "Last 7 days",
                weekly: this.selectedWeek ? `Week ${this.selectedWeek}` : "Selected week",
                month: "Last 30 days",
                monthly: this.selectedMonth ? `${this.formatDisplayMonth(this.selectedMonth)}` : "Selected month",
                year: "Last 365 days",
                yearly: this.selectedYear ? `${this.selectedYear}` : "Selected year",
            };

            return captions[this.scope] || "Current month";
        },
        stockBucketRows() {
            const buckets = this.dashboard?.stock_buckets || {};
            const values = [
                { label: "Empty Stock (0%)", key: "empty", value: buckets.empty || 0, colorClass: "bg-rose-500", textClass: "text-rose-600 dark:text-rose-400" },
                { label: "Low Stock (1-25%)", key: "low", value: buckets.low || 0, colorClass: "bg-amber-500", textClass: "text-amber-600 dark:text-amber-400" },
                { label: "Mid Stock (26-75%)", key: "mid", value: buckets.mid || 0, colorClass: "bg-blue-500", textClass: "text-blue-600 dark:text-blue-400" },
                { label: "Healthy Stock (>75%)", key: "high", value: buckets.high || 0, colorClass: "bg-lime-500", textClass: "text-lime-600 dark:text-lime-400" },
            ];

            const total = values.reduce((sum, item) => sum + item.value, 0) || 1;
            return values.map((item) => ({
                ...item,
                percent: Math.round((item.value / total) * 100),
                width: `${Math.max((item.value / total) * 100, item.value > 0 ? 6 : 0)}%`,
            }));
        },
        maxCategoryTotal() {
            return Math.max(...(this.dashboard?.items_per_category || []).map(i => i.total), 1);
        },
        maxLocationTotal() {
            return Math.max(...(this.dashboard?.items_per_location || []).map(i => i.total), 1);
        },
        maxProjectTotal() {
            return Math.max(...(this.dashboard?.items_per_project_code || []).map(i => i.total), 1);
        },
        maxIssuedQuantity() {
            return Math.max(...(this.dashboard?.top_issued_items || []).map(i => i.total_quantity), 1);
        }
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            try {
                const response = await this.fetchGetApi("api.inventory.transactions.dashboard", {
                    ...this.dashboardParams,
                });
                const payload = response?.data?.data ?? response?.data ?? {};
                this.dashboard = {
                    range: payload?.range ?? { start: null, end: null },
                    totals: payload?.totals ?? {
                        incoming: 0,
                        outgoing: 0,
                        incoming_count: 0,
                        outgoing_count: 0,
                        incoming_quantity: 0,
                        outgoing_quantity: 0,
                        total_transactions: 0
                    },
                    top_issued_items: payload?.top_issued_items ?? [],
                    recent_transactions: this.convertToTransaction(payload?.recent_transactions) ?? [],
                    items_per_category: payload?.items_per_category ?? [],
                    items_per_location: payload?.items_per_location ?? [],
                    items_per_project_code: payload?.items_per_project_code ?? [],
                    stock_buckets: payload?.stock_buckets ?? {
                        empty: 0,
                        low: 0,
                        mid: 0,
                        high: 0,
                    },
                };
            } finally {
                this.loading = false;
            }
        },
        convertToTransaction(response = []) {
            const service = new ConcreteApiService();
            return response.map((item) =>
                service.castToModel(item, Transaction)
            );
        },
        updatePeriodAnchor(value) {
            if (this.scope === "daily") this.selectedDate = value;
            if (this.scope === "weekly") this.selectedWeek = value;
            if (this.scope === "monthly") this.selectedMonth = value;
            if (this.scope === "yearly") this.selectedYear = value;
        },
        formatDateForInput(value) {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "";
            return [
                date.getFullYear(),
                String(date.getMonth() + 1).padStart(2, "0"),
                String(date.getDate()).padStart(2, "0"),
            ].join("-");
        },
        formatWeekForInput(value) {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "";
            const normalized = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const day = normalized.getUTCDay() || 7;
            normalized.setUTCDate(normalized.getUTCDate() + 4 - day);
            const yearStart = new Date(Date.UTC(normalized.getUTCFullYear(), 0, 1));
            const week = Math.ceil((((normalized - yearStart) / 86400000) + 1) / 7);
            return `${normalized.getUTCFullYear()}-W${String(week).padStart(2, "0")}`;
        },
        formatMonthForInput(value) {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "";
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
        },
        formatDisplayDate(value) {
            if (!value) return "the selected day";
            const date = new Date(`${value}T00:00:00`);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleDateString(undefined, {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        },
        formatDisplayMonth(value) {
            if (!value) return "the selected month";
            const date = new Date(`${value}-01T00:00:00`);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleDateString(undefined, {
                year: "numeric",
                month: "long",
            });
        },
        formatDateTime(value) {
            if (!value) return "-";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString(undefined, {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }
            this.realtimeCleanup = null;
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }
            this.realtimeRefreshTimer = setTimeout(() => {
                this.loadDashboard();
            }, 400);
        },
        configureRealtime() {
            this.cleanupRealtime();
            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "private",
                    channel: "inventory.transactions",
                    event: "inventory.transaction.changed",
                    feature: "inventory",
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ]);
        },
    },
    watch: {
        dashboardParams: {
            deep: true,
            handler() {
                this.loadDashboard();
            },
        },
        scope(nextScope, previousScope) {
            if (nextScope === previousScope) return;
            if (nextScope === "daily" && !this.selectedDate) this.selectedDate = this.formatDateForInput(new Date());
            if (nextScope === "weekly" && !this.selectedWeek) this.selectedWeek = this.formatWeekForInput(new Date());
            if (nextScope === "monthly" && !this.selectedMonth) this.selectedMonth = this.formatMonthForInput(new Date());
            if (nextScope === "yearly" && !this.selectedYear) this.selectedYear = String(new Date().getFullYear());
        },
    },
    mounted() {
        this.loadDashboard();
        this.configureRealtime();
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) clearTimeout(this.realtimeRefreshTimer);
        this.cleanupRealtime();
    },
};
</script>

<template>
    <Head title="Inventory Dashboard" />

    <AppLayout>
        <template #header>
            <TransactionHeaderAction />
        </template>

        <div class="default-container py-4 sm:py-6 space-y-4 sm:space-y-6 text-slate-900 dark:text-slate-100">
            <!-- Header Bar & Scope Selectors -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-5 rounded-2xl shadow-xs">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <TrendingUp class="w-5 h-5 text-lime-600 dark:text-lime-400" />
                        <h2 class="text-lg sm:text-xl font-bold tracking-tight">Stock Analytics & Movement</h2>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Viewing inventory transaction metrics <span class="font-semibold text-lime-600 dark:text-lime-400">{{ scopeCaption }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                    <div class="w-full sm:w-52">
                        <custom-dropdown
                            label="Filter Scope"
                            :value="scope"
                            :options="scopeOptions"
                            :withAllOption="false"
                            :show-clear="false"
                            @selectedChange="scope = $event || 'all'"
                        />
                    </div>
                    <div v-if="usesAnchoredPeriod" class="w-full sm:w-44">
                        <label class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">{{ periodInputLabel }}</label>
                        <input
                            class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:border-lime-500 focus:ring-1 focus:ring-lime-500 shadow-xs transition-colors"
                            :type="periodInputType"
                            :value="periodInputValue"
                            min="2000"
                            max="2100"
                            @input="updatePeriodAnchor($event.target.value)"
                        />
                    </div>
                    <button
                        @click="loadDashboard"
                        class="p-2 sm:p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 text-slate-600 dark:text-slate-300 transition-all active:scale-95 shadow-xs"
                        title="Refresh Analytics"
                    >
                        <RefreshCw class="w-4 h-4 sm:w-4.5 sm:h-4.5" :class="{ 'animate-spin': loading }" />
                    </button>
                </div>
            </div>

            <!-- Top Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                <!-- Total Transactions Card -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group hover:border-lime-500/50 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Movements</span>
                        <div class="p-2.5 rounded-xl bg-lime-500/10 text-lime-600 dark:text-lime-400">
                            <ArrowUpDown class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                            {{ dashboard.totals.total_transactions || (dashboard.totals.incoming + dashboard.totals.outgoing) }}
                        </p>
                        <p class="text-[0.7rem] sm:text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                            <span class="font-medium">Total log records</span>
                        </p>
                    </div>
                </div>

                <!-- Stock In Card -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group hover:border-emerald-500/50 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Stock In (Restock)</span>
                        <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <ArrowDownLeft class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">
                                {{ dashboard.totals.incoming }}
                            </p>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">transactions</span>
                        </div>
                        <p class="text-[0.7rem] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Total Item Qty Added: <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ dashboard.totals.incoming_quantity || '-' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Stock Out Card -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group hover:border-rose-500/50 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">Stock Out (Issued)</span>
                        <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <ArrowUpRight class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-rose-600 dark:text-rose-400">
                                {{ dashboard.totals.outgoing }}
                            </p>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">transactions</span>
                        </div>
                        <p class="text-[0.7rem] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Total Item Qty Issued: <span class="font-bold text-rose-600 dark:text-rose-400">{{ dashboard.totals.outgoing_quantity || '-' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Low & Empty Reorder Warning Card -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs relative overflow-hidden group hover:border-amber-500/50 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Low / Empty Items</span>
                        <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <AlertTriangle class="w-5 h-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">
                                {{ (dashboard.stock_buckets.empty || 0) + (dashboard.stock_buckets.low || 0) }}
                            </p>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">items</span>
                        </div>
                        <p class="text-[0.7rem] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Empty: <span class="font-bold text-rose-500">{{ dashboard.stock_buckets.empty || 0 }}</span> | Low: <span class="font-bold text-amber-500">{{ dashboard.stock_buckets.low || 0 }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Stock Buckets & Top Issued Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Stock Health Buckets Card -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <PackageCheck class="w-4.5 h-4.5 text-lime-600 dark:text-lime-400" />
                            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Stock Level Health Matrix</h3>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Inventory Distribution</span>
                    </div>

                    <div class="space-y-3.5">
                        <div
                            v-for="bucket in stockBucketRows"
                            :key="bucket.key"
                            class="space-y-1"
                        >
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ bucket.label }}</span>
                                <span class="font-bold" :class="bucket.textClass">{{ bucket.value }} items ({{ bucket.percent }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3.5 overflow-hidden p-0.5 border border-slate-200/60 dark:border-slate-700/60">
                                <div class="h-full rounded-full transition-all duration-500" :class="bucket.colorClass" :style="{ width: bucket.width }" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Issued Items Leaderboard -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <Box class="w-4.5 h-4.5 text-rose-500" />
                            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Top Issued Stock Items</h3>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Highest Outgoing Volume</span>
                    </div>

                    <div v-if="dashboard.top_issued_items && dashboard.top_issued_items.length" class="space-y-3">
                        <div
                            v-for="(item, idx) in dashboard.top_issued_items"
                            :key="`top-item-${idx}`"
                            class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/70 flex items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="flex items-center justify-center w-6 h-6 rounded-lg text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 shrink-0">
                                    #{{ idx + 1 }}
                                </span>
                                <div class="min-w-0 leading-tight">
                                    <p class="text-xs sm:text-sm font-semibold truncate text-slate-900 dark:text-slate-100">{{ item.name }}</p>
                                    <p class="text-[0.7rem] text-slate-500 dark:text-slate-400 truncate" v-if="item.brand || item.description">
                                        {{ item.brand }} {{ item.description ? `(${item.description})` : '' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-xs sm:text-sm font-extrabold text-rose-600 dark:text-rose-400">{{ item.total_quantity }} units</span>
                                <p class="text-[0.65rem] text-slate-500 dark:text-slate-400">{{ item.transac_count }} transactions</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                        No outgoing transactions recorded for this scope period.
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Category, Location & Project Allocation -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                <!-- Items per Category -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
                    <div class="flex items-center gap-2 mb-4">
                        <Layers class="w-4 h-4 text-indigo-500" />
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Category Allocation</h3>
                    </div>
                    <div v-if="dashboard.items_per_category && dashboard.items_per_category.length" class="space-y-2.5">
                        <div v-for="row in dashboard.items_per_category" :key="row.label" class="space-y-1">
                            <div class="flex justify-between text-xs font-medium">
                                <span class="truncate text-slate-700 dark:text-slate-300">{{ row.label }}</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ row.total }} items</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-500 h-2 rounded-full transition-all" :style="{ width: `${(row.total / maxCategoryTotal) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center">No category data.</div>
                </div>

                <!-- Items per Storage Location -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
                    <div class="flex items-center gap-2 mb-4">
                        <MapPin class="w-4 h-4 text-emerald-500" />
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Storage Locations</h3>
                    </div>
                    <div v-if="dashboard.items_per_location && dashboard.items_per_location.length" class="space-y-2.5">
                        <div v-for="row in dashboard.items_per_location" :key="row.label" class="space-y-1">
                            <div class="flex justify-between text-xs font-medium">
                                <span class="truncate text-slate-700 dark:text-slate-300">{{ row.label }}</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ row.total }} items</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-500 h-2 rounded-full transition-all" :style="{ width: `${(row.total / maxLocationTotal) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center">No location data.</div>
                </div>

                <!-- Items per Project Code -->
                <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
                    <div class="flex items-center gap-2 mb-4">
                        <FolderGit2 class="w-4 h-4 text-amber-500" />
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Project Codes</h3>
                    </div>
                    <div v-if="dashboard.items_per_project_code && dashboard.items_per_project_code.length" class="space-y-2.5">
                        <div v-for="row in dashboard.items_per_project_code" :key="row.label" class="space-y-1">
                            <div class="flex justify-between text-xs font-medium">
                                <span class="truncate text-slate-700 dark:text-slate-300">{{ row.label }}</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">{{ row.total }} items</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-amber-500 h-2 rounded-full transition-all" :style="{ width: `${(row.total / maxProjectTotal) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center">No project code data.</div>
                </div>
            </div>

            <!-- Recent Movement Log Table -->
            <div class="p-4 sm:p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Recent Movement Log Feed</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Last 10 Records</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs sm:text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 uppercase text-[0.68rem] font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="p-3">Date & Time</th>
                                <th class="p-3">Personnel</th>
                                <th class="p-3">Item Name</th>
                                <th class="p-3">Movement Type</th>
                                <th class="p-3 text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="row in dashboard.recent_transactions" :key="row.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">{{ formatDateTime(row.created_at) }}</td>
                                <td class="p-3 whitespace-nowrap font-semibold text-slate-900 dark:text-slate-100">{{ row.actor_display_name || '-' }}</td>
                                <td class="p-3 text-slate-900 dark:text-slate-100">
                                    <span class="font-medium">{{ row.item?.name || '-' }}</span>
                                    <span v-if="row.item?.brand" class="block text-[0.7rem] text-slate-500 dark:text-slate-400">{{ row.item.brand }}</span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[0.65rem] font-extrabold uppercase tracking-wider inline-flex items-center gap-1"
                                        :class="row.transac_type === 'incoming'
                                            ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'
                                            : 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800'"
                                    >
                                        <component :is="row.transac_type === 'incoming' ? 'ArrowDownLeft' : 'ArrowUpRight'" class="w-3 h-3" />
                                        {{ row.transac_type }}
                                    </span>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap font-extrabold text-slate-900 dark:text-slate-100">
                                    {{ row.quantity }} {{ row.unit || '' }}
                                </td>
                            </tr>
                            <tr v-if="!dashboard.recent_transactions.length">
                                <td colspan="5" class="p-6 text-center text-xs text-slate-500 dark:text-slate-400">
                                    No transactions recorded for the selected scope.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
