<script>
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
import Transaction from "@/Modules/domain/Transaction";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import { ArrowUpDown, ArrowDownLeft, ArrowUpRight, AlertTriangle, Layers, MapPin, FolderGit2, TrendingUp, PackageCheck, RefreshCw, Calendar, Box, Users } from "lucide-vue-next";

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
        Box,
        Users,
    },
    mixins: [ApiMixin],
    data() {
        const today = new Date();
        const selectedDate = [today.getFullYear(), String(today.getMonth() + 1).padStart(2, "0"), String(today.getDate()).padStart(2, "0")].join("-");
        const weekDate = new Date(Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()));
        const weekDay = weekDate.getUTCDay() || 7;

        weekDate.setUTCDate(weekDate.getUTCDate() + 4 - weekDay);

        const yearStart = new Date(Date.UTC(weekDate.getUTCFullYear(), 0, 1));
        const selectedWeek = `${weekDate.getUTCFullYear()}-W${String(Math.ceil(((weekDate - yearStart) / 86400000 + 1) / 7)).padStart(2, "0")}`;
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
                    total_transactions: 0,
                },
                top_issued_items: [],
                top_personnel_by_volume: [],
                top_personnel_by_transaction: [],
                recent_transactions: [],
                items_per_category: [],
                items_per_location: [],
                items_per_project_code: [],
                stock_buckets: { empty: 0, low: 0, mid: 0, high: 0 },
            },
            topPersonnelMode: "volume",
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
            return (
                {
                    daily: "Date",
                    weekly: "Week",
                    monthly: "Month",
                    yearly: "Year",
                }[this.scope] || "Period"
            );
        },
        periodInputType() {
            return (
                {
                    daily: "date",
                    weekly: "week",
                    monthly: "month",
                    yearly: "number",
                }[this.scope] || "text"
            );
        },
        periodInputValue() {
            return (
                {
                    daily: this.selectedDate,
                    weekly: this.selectedWeek,
                    monthly: this.selectedMonth,
                    yearly: this.selectedYear,
                }[this.scope] || ""
            );
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
                {
                    label: "Empty Stock (0%)",
                    key: "empty",
                    value: buckets.empty || 0,
                    colorClass: "bg-rose-500",
                    textClass: "text-rose-600 dark:text-rose-400",
                },
                {
                    label: "Low Stock (1-25%)",
                    key: "low",
                    value: buckets.low || 0,
                    colorClass: "bg-amber-500",
                    textClass: "text-amber-600 dark:text-amber-400",
                },
                {
                    label: "Mid Stock (26-75%)",
                    key: "mid",
                    value: buckets.mid || 0,
                    colorClass: "bg-blue-500",
                    textClass: "text-blue-600 dark:text-blue-400",
                },
                {
                    label: "Healthy Stock (>75%)",
                    key: "high",
                    value: buckets.high || 0,
                    colorClass: "bg-lime-500",
                    textClass: "text-lime-600 dark:text-lime-400",
                },
            ];

            const total = values.reduce((sum, item) => sum + item.value, 0) || 1;
            return values.map((item) => ({
                ...item,
                percent: Math.round((item.value / total) * 100),
                width: `${Math.max((item.value / total) * 100, item.value > 0 ? 6 : 0)}%`,
            }));
        },
        maxCategoryTotal() {
            return Math.max(...(this.dashboard?.items_per_category || []).map((i) => i.total), 1);
        },
        maxLocationTotal() {
            return Math.max(...(this.dashboard?.items_per_location || []).map((i) => i.total), 1);
        },
        maxProjectTotal() {
            return Math.max(...(this.dashboard?.items_per_project_code || []).map((i) => i.total), 1);
        },
        maxIssuedQuantity() {
            return Math.max(...(this.dashboard?.top_issued_items || []).map((i) => i.total_quantity), 1);
        },
        topPersonnel() {
            return this.topPersonnelMode === "volume" ? this.dashboard.top_personnel_by_volume : this.dashboard.top_personnel_by_transaction;
        },
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
                        total_transactions: 0,
                    },
                    top_issued_items: payload?.top_issued_items ?? [],
                    top_personnel_by_volume: payload?.top_personnel_by_volume ?? [],
                    top_personnel_by_transaction: payload?.top_personnel_by_transaction ?? [],
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
            return response.map((item) => service.castToModel(item, Transaction));
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
            return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, "0"), String(date.getDate()).padStart(2, "0")].join("-");
        },
        formatWeekForInput(value) {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "";
            const normalized = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const day = normalized.getUTCDay() || 7;
            normalized.setUTCDate(normalized.getUTCDate() + 4 - day);
            const yearStart = new Date(Date.UTC(normalized.getUTCFullYear(), 0, 1));
            const week = Math.ceil(((normalized - yearStart) / 86400000 + 1) / 7);
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
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
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

        <div class="default-container space-y-4 py-4 text-slate-900 sm:space-y-6 sm:py-6 dark:text-slate-100">
            <!-- Header Bar & Scope Selectors -->
            <div class="shadow-xs flex flex-col items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 md:flex-row md:items-center dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5 text-lime-600 dark:text-lime-400" />
                        <h2 class="text-lg font-bold tracking-tight sm:text-xl">Stock Analytics & Movement</h2>
                    </div>
                    <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                        Viewing inventory transaction metrics
                        <span class="font-semibold text-lime-600 dark:text-lime-400">
                            {{ scopeCaption }}
                        </span>
                    </p>
                </div>

                <div class="flex w-full flex-wrap items-center gap-2.5 md:w-auto">
                    <div class="w-full sm:w-52">
                        <custom-dropdown
                            label="Filter Scope"
                            :value="scope"
                            :options="scopeOptions"
                            :withAllOption="false"
                            :show-clear="false"
                            @selectedChange="scope = $event || 'all'"
                            :show-valid-indicator="false" />
                    </div>
                    <div
                        v-if="usesAnchoredPeriod"
                        class="w-full sm:w-44">
                        <label class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">
                            {{ periodInputLabel }}
                        </label>
                        <input
                            class="shadow-xs w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-900 transition-colors focus:border-lime-500 focus:ring-1 focus:ring-lime-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            :type="periodInputType"
                            :value="periodInputValue"
                            min="2000"
                            max="2100"
                            @input="updatePeriodAnchor($event.target.value)" />
                    </div>
                    <button
                        @click="loadDashboard"
                        class="shadow-xs rounded-xl border border-slate-200 bg-slate-50 p-2 text-slate-600 transition-all hover:bg-slate-100 active:scale-95 sm:p-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/80"
                        title="Refresh Analytics">
                        <RefreshCw
                            class="sm:w-4.5 sm:h-4.5 h-4 w-4"
                            :class="{ 'animate-spin': loading }" />
                    </button>
                </div>
            </div>

            <!-- Top Metric Cards Grid -->
            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
                <!-- Total Transactions Card -->
                <div class="shadow-xs group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition-all duration-200 hover:border-lime-500/50 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Movements</span>
                        <div class="rounded-xl bg-lime-500/10 p-2.5 text-lime-600 dark:text-lime-400">
                            <ArrowUpDown class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100">
                            {{ dashboard.totals.total_transactions || dashboard.totals.incoming + dashboard.totals.outgoing }}
                        </p>
                        <p class="mt-1 flex items-center gap-1 text-[0.7rem] text-slate-500 sm:text-xs dark:text-slate-400">
                            <span class="font-medium">Total log records</span>
                        </p>
                    </div>
                </div>

                <!-- Stock In Card -->
                <div class="shadow-xs group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition-all duration-200 hover:border-emerald-500/50 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Stock In (Restock)</span>
                        <div class="rounded-xl bg-emerald-500/10 p-2.5 text-emerald-600 dark:text-emerald-400">
                            <ArrowDownLeft class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-extrabold tracking-tight text-emerald-600 sm:text-3xl dark:text-emerald-400">
                                {{ dashboard.totals.incoming }}
                            </p>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">transactions</span>
                        </div>
                        <p class="mt-1 text-[0.7rem] text-slate-500 sm:text-xs dark:text-slate-400">
                            Total Item Qty Added:
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ dashboard.totals.incoming_quantity || "-" }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Stock Out Card -->
                <div class="shadow-xs group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition-all duration-200 hover:border-rose-500/50 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">Stock Out (Issued)</span>
                        <div class="rounded-xl bg-rose-500/10 p-2.5 text-rose-600 dark:text-rose-400">
                            <ArrowUpRight class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-extrabold tracking-tight text-rose-600 sm:text-3xl dark:text-rose-400">
                                {{ dashboard.totals.outgoing }}
                            </p>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">transactions</span>
                        </div>
                        <p class="mt-1 text-[0.7rem] text-slate-500 sm:text-xs dark:text-slate-400">
                            Total Item Qty Issued:
                            <span class="font-bold text-rose-600 dark:text-rose-400">
                                {{ dashboard.totals.outgoing_quantity || "-" }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Low & Empty Reorder Warning Card -->
                <div class="shadow-xs group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition-all duration-200 hover:border-amber-500/50 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Low / Empty Items</span>
                        <div class="rounded-xl bg-amber-500/10 p-2.5 text-amber-600 dark:text-amber-400">
                            <AlertTriangle class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-extrabold tracking-tight text-amber-600 sm:text-3xl dark:text-amber-400">
                                {{ (dashboard.stock_buckets.empty || 0) + (dashboard.stock_buckets.low || 0) }}
                            </p>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">items</span>
                        </div>
                        <p class="mt-1 text-[0.7rem] text-slate-500 sm:text-xs dark:text-slate-400">
                            Empty:
                            <span class="font-bold text-rose-500">
                                {{ dashboard.stock_buckets.empty || 0 }}
                            </span>
                            | Low:
                            <span class="font-bold text-amber-500">
                                {{ dashboard.stock_buckets.low || 0 }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Stock Buckets & Top Issued Items -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
                <!-- Stock Health Buckets Card -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <PackageCheck class="w-4.5 h-4.5 text-lime-600 dark:text-lime-400" />
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Stock Level Health Matrix</h3>
                        </div>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Inventory Distribution</span>
                    </div>

                    <div class="space-y-3.5">
                        <div
                            v-for="bucket in stockBucketRows"
                            :key="bucket.key"
                            class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-700 dark:text-slate-300">
                                    {{ bucket.label }}
                                </span>
                                <span
                                    class="font-bold"
                                    :class="bucket.textClass">
                                    {{ bucket.value }} items ({{ bucket.percent }}%)
                                </span>
                            </div>
                            <div class="h-3.5 w-full overflow-hidden rounded-full border border-slate-200/60 bg-slate-100 p-0.5 dark:border-slate-700/60 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="bucket.colorClass"
                                    :style="{ width: bucket.width }" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Issued Items Leaderboard -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Box class="w-4.5 h-4.5 text-rose-500" />
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Top Issued Stock Items</h3>
                        </div>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Highest Outgoing Volume</span>
                    </div>

                    <div
                        v-if="dashboard.top_issued_items && dashboard.top_issued_items.length"
                        class="space-y-3">
                        <div
                            v-for="(item, idx) in dashboard.top_issued_items"
                            :key="`top-item-${idx}`"
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200/70 bg-slate-50 p-2.5 dark:border-slate-700/70 dark:bg-slate-800/60">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-300">#{{ idx + 1 }}</span>
                                <div class="min-w-0 leading-tight">
                                    <p class="truncate text-xs font-semibold text-slate-900 sm:text-sm dark:text-slate-100">
                                        {{ item.name }}
                                    </p>
                                    <p
                                        class="truncate text-[0.7rem] text-slate-500 dark:text-slate-400"
                                        v-if="item.brand || item.description">
                                        {{ item.brand }}
                                        {{ item.description ? `(${item.description})` : "" }}
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="text-xs font-extrabold text-rose-600 sm:text-sm dark:text-rose-400">{{ item.total_quantity }} units</span>
                                <p class="text-[0.65rem] text-slate-500 dark:text-slate-400">{{ item.transac_count }} transactions</p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                        No outgoing transactions recorded for this scope period.
                    </div>
                </div>

                <!-- Top Personnel -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Users class="w-4.5 h-4.5 text-blue-500" />
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Top Personnel</h3>
                        </div>
                        
                        <!-- Toggle Button Group -->
                        <div class="flex items-center rounded-lg border border-slate-200 bg-slate-100 p-0.5 dark:border-slate-700 dark:bg-slate-800">
                            <button 
                                @click="topPersonnelMode = 'volume'"
                                :class="['px-2 py-1 text-[0.65rem] sm:text-xs font-semibold rounded-md transition-colors', topPersonnelMode === 'volume' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200']"
                            >
                                Volume
                            </button>
                            <button 
                                @click="topPersonnelMode = 'transaction'"
                                :class="['px-2 py-1 text-[0.65rem] sm:text-xs font-semibold rounded-md transition-colors', topPersonnelMode === 'transaction' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200']"
                            >
                                Transac
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="topPersonnel && topPersonnel.length"
                        class="space-y-3">
                        <div
                            v-for="(person, idx) in topPersonnel"
                            :key="`top-personnel-${idx}`"
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200/70 bg-slate-50 p-2.5 dark:border-slate-700/70 dark:bg-slate-800/60">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-300">#{{ idx + 1 }}</span>
                                <div class="min-w-0 leading-tight">
                                    <p class="truncate text-xs font-semibold text-slate-900 sm:text-sm dark:text-slate-100">
                                        {{ person.name }}
                                    </p>
                                    <p
                                        class="truncate text-[0.7rem] text-slate-500 dark:text-slate-400"
                                        v-if="person.position || person.employee_id">
                                        {{ person.position }} 
                                        <span v-if="person.position && person.employee_id" class="mx-1 opacity-50">&bull;</span>
                                        {{ person.employee_id }}
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="text-xs font-extrabold sm:text-sm" :class="topPersonnelMode === 'volume' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300'">{{ person.total_volume }} units</span>
                                <p class="text-[0.65rem]" :class="topPersonnelMode === 'transaction' ? 'text-blue-600 font-semibold dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'">{{ person.transac_count }} transactions</p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                        No outgoing transactions recorded for this scope period.
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Category, Location & Project Allocation -->
            <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-3">
                <!-- Items per Category -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center gap-2">
                        <Layers class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Category Allocation</h3>
                    </div>
                    <div
                        v-if="dashboard.items_per_category && dashboard.items_per_category.length"
                        class="space-y-2.5">
                        <div
                            v-for="row in dashboard.items_per_category"
                            :key="row.label"
                            class="space-y-1">
                            <div class="flex justify-between text-xs font-medium">
                                <span class="truncate text-slate-700 dark:text-slate-300">
                                    {{ row.label }}
                                </span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ row.total }} items</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-2 rounded-full bg-indigo-500 transition-all"
                                    :style="{
                                        width: `${(row.total / maxCategoryTotal) * 100}%`,
                                    }" />
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        No category data.
                    </div>
                </div>

                <!-- Items per Storage Location -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center gap-2">
                        <MapPin class="h-4 w-4 text-emerald-500" />
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Storage Locations</h3>
                    </div>
                    <div
                        v-if="dashboard.items_per_location && dashboard.items_per_location.length"
                        class="space-y-2.5">
                        <div
                            v-for="row in dashboard.items_per_location"
                            :key="row.label"
                            class="space-y-1">
                            <div class="flex justify-between text-xs font-medium">
                                <span class="truncate text-slate-700 dark:text-slate-300">
                                    {{ row.label }}
                                </span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ row.total }} items</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-2 rounded-full bg-emerald-500 transition-all"
                                    :style="{
                                        width: `${(row.total / maxLocationTotal) * 100}%`,
                                    }" />
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        No location data.
                    </div>
                </div>

                <!-- Items per Project Code -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center gap-2">
                        <FolderGit2 class="h-4 w-4 text-amber-500" />
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Project Codes</h3>
                    </div>
                    <div
                        v-if="dashboard.items_per_project_code && dashboard.items_per_project_code.length"
                        class="space-y-2.5">
                        <div
                            v-for="row in dashboard.items_per_project_code"
                            :key="row.label"
                            class="space-y-1">
                            <div class="flex justify-between text-xs font-medium">
                                <span class="truncate text-slate-700 dark:text-slate-300">
                                    {{ row.label }}
                                </span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">{{ row.total }} items</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-2 rounded-full bg-amber-500 transition-all"
                                    :style="{ width: `${(row.total / maxProjectTotal) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        No project code data.
                    </div>
                </div>
            </div>

            <!-- Recent Movement Log Table -->
            <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">Recent Movement Log Feed</h3>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Last 10 Records</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-[0.68rem] font-semibold uppercase text-slate-500 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-400">
                            <tr>
                                <th class="p-3">Date & Time</th>
                                <th class="p-3">Personnel</th>
                                <th class="p-3">Item Name</th>
                                <th class="p-3">Movement Type</th>
                                <th class="p-3 text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr
                                v-for="row in dashboard.recent_transactions"
                                :key="row.id"
                                class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap p-3 font-medium text-slate-600 dark:text-slate-300">
                                    {{ formatDateTime(row.created_at) }}
                                </td>
                                <td class="whitespace-nowrap p-3 font-semibold text-slate-900 dark:text-slate-100">
                                    {{ row.actor_display_name || "-" }}
                                </td>
                                <td class="p-3 text-slate-900 dark:text-slate-100">
                                    <span class="font-medium">{{ row.item?.name || "-" }}</span>
                                    <span
                                        v-if="row.item?.brand"
                                        class="block text-[0.7rem] text-slate-500 dark:text-slate-400">
                                        {{ row.item.brand }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap p-3">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[0.65rem] font-extrabold uppercase tracking-wider"
                                        :class="row.transac_type === 'incoming' ? 'border border-emerald-300 bg-emerald-100 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'border border-rose-300 bg-rose-100 text-rose-700 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-300'">
                                        <component
                                            :is="row.transac_type === 'incoming' ? 'ArrowDownLeft' : 'ArrowUpRight'"
                                            class="h-3 w-3" />
                                        {{ row.transac_type }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap p-3 text-right font-extrabold text-slate-900 dark:text-slate-100">{{ row.quantity }} {{ row.unit || "" }}</td>
                            </tr>
                            <tr v-if="!dashboard.recent_transactions.length">
                                <td
                                    colspan="5"
                                    class="p-6 text-center text-xs text-slate-500 dark:text-slate-400">
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
