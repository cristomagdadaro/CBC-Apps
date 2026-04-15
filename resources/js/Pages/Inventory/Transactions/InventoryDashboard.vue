<script>
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
import Transaction from "@/Modules/domain/Transaction";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

export default {
    name: "InventoryDashboard",
    components: { TransactionHeaderAction },
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
            scope: "monthly",
            selectedDate,
            selectedWeek,
            selectedMonth,
            selectedYear: String(today.getFullYear()),
            scopeOptions: [
                { name: "day", label: "Day (Last 24 Hours)" },
                { name: "daily", label: "Daily (Selected Day)" },
                { name: "week", label: "Week (Last 168 Hours)" },
                { name: "weekly", label: "Weekly (Selected Week)" },
                { name: "month", label: "Month (Last 1 Month)" },
                { name: "monthly", label: "Monthly (Selected Month)" },
                { name: "year", label: "Year (Last 365 Days)" },
                { name: "yearly", label: "Yearly (Selected Year)" },
            ],
            dashboard: {
                range: { start: null, end: null },
                totals: { incoming: 0, outgoing: 0 },
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
                day: "within the last 24 hours",
                daily: this.selectedDate ? `within ${this.formatDisplayDate(this.selectedDate)}` : "within the selected day",
                week: "within the last 168 hours",
                weekly: this.selectedWeek ? `within week ${this.selectedWeek}` : "within the selected week",
                month: "within the last 1 month",
                monthly: this.selectedMonth ? `within ${this.formatDisplayMonth(this.selectedMonth)}` : "within the selected month",
                year: "within the last 365 days",
                yearly: this.selectedYear ? `within ${this.selectedYear}` : "within the selected year",
            };

            return captions[this.scope] || "within the current month";
        },
        stockBucketRows() {
            const buckets = this.dashboard?.stock_buckets || {};
            const values = [
                { label: "Empty", key: "empty", value: buckets.empty || 0 },
                { label: "Low", key: "low", value: buckets.low || 0 },
                { label: "Mid", key: "mid", value: buckets.mid || 0 },
                { label: "High", key: "high", value: buckets.high || 0 },
            ];

            const max = Math.max(...values.map((item) => item.value), 1);
            return values.map((item) => ({
                ...item,
                width: `${Math.max((item.value / max) * 100, item.value > 0 ? 8 : 0)}%`,
            }));
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
                    totals: payload?.totals ?? { incoming: 0, outgoing: 0 },
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
            if (this.scope === "daily") {
                this.selectedDate = value;
            }

            if (this.scope === "weekly") {
                this.selectedWeek = value;
            }

            if (this.scope === "monthly") {
                this.selectedMonth = value;
            }

            if (this.scope === "yearly") {
                this.selectedYear = value;
            }
        },
        formatDateForInput(value) {
            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return "";
            }

            return [
                date.getFullYear(),
                String(date.getMonth() + 1).padStart(2, "0"),
                String(date.getDate()).padStart(2, "0"),
            ].join("-");
        },
        formatWeekForInput(value) {
            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return "";
            }

            const normalized = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const day = normalized.getUTCDay() || 7;

            normalized.setUTCDate(normalized.getUTCDate() + 4 - day);

            const yearStart = new Date(Date.UTC(normalized.getUTCFullYear(), 0, 1));
            const week = Math.ceil((((normalized - yearStart) / 86400000) + 1) / 7);

            return `${normalized.getUTCFullYear()}-W${String(week).padStart(2, "0")}`;
        },
        formatMonthForInput(value) {
            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return "";
            }

            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
        },
        formatDisplayDate(value) {
            if (!value) return "the selected day";

            const date = new Date(`${value}T00:00:00`);

            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleDateString(undefined, {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        },
        formatDisplayMonth(value) {
            if (!value) return "the selected month";

            const date = new Date(`${value}-01T00:00:00`);

            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleDateString(undefined, {
                year: "numeric",
                month: "long",
            });
        },
        formatDateTime(value) {
            if (!value) return "-";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString();
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
            if (nextScope === previousScope) {
                return;
            }

            if (nextScope === "daily" && !this.selectedDate) {
                this.selectedDate = this.formatDateForInput(new Date());
            }

            if (nextScope === "weekly" && !this.selectedWeek) {
                this.selectedWeek = this.formatWeekForInput(new Date());
            }

            if (nextScope === "monthly" && !this.selectedMonth) {
                this.selectedMonth = this.formatMonthForInput(new Date());
            }

            if (nextScope === "yearly" && !this.selectedYear) {
                this.selectedYear = String(new Date().getFullYear());
            }
        },
    },
    mounted() {
        this.loadDashboard();
        this.configureRealtime();
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

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

        <div class="default-container py-4 space-y-4">
            <div class="flex flex-wrap justify-between items-end gap-3">
                <h2 class="text-xl font-bold">Inventory Dashboard</h2>
                <div class="flex flex-wrap items-end gap-3">
                    <div class="w-56">
                        <custom-dropdown
                            label="Period"
                            :value="scope"
                            :options="scopeOptions"
                            :withAllOption="false"
                            :show-clear="false"
                            @selectedChange="scope = $event || 'monthly'"
                        />
                    </div>
                    <div v-if="usesAnchoredPeriod" class="w-48">
                        <label class="mb-1 block text-xs font-medium text-gray-500">{{ periodInputLabel }}</label>
                        <input
                            class="border w-full dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB rounded-md shadow-sm"
                            :type="periodInputType"
                            :value="periodInputValue"
                            min="2000"
                            max="2100"
                            @input="updatePeriodAnchor($event.target.value)"
                        />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <div class="flex flex-col leading-tight">
                        <p class="text-sm text-gray-700 uppercase font-semibold">Total Incoming</p>
                        <span class="text-gray-500 font-normal text-xs">{{ scopeCaption }}</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ dashboard.totals.incoming }}</p>
                </div>
                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <div class="flex flex-col leading-tight">
                        <p class="text-sm text-gray-700 uppercase font-semibold">Total Outgoing</p>
                        <span class="text-gray-500 font-normal text-xs">{{ scopeCaption }}</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-red-600">{{ dashboard.totals.outgoing }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <h3 class="text-sm font-semibold uppercase mb-3">Stock Buckets</h3>
                    <div class="space-y-2">
                        <div
                            v-for="bucket in stockBucketRows"
                            :key="bucket.key"
                            class="grid grid-cols-[90px_1fr_50px] gap-2 items-center"
                        >
                            <span class="text-xs text-gray-600">{{ bucket.label }}</span>
                            <div class="w-full bg-gray-100 rounded h-4 overflow-hidden">
                                <div class="h-4 bg-AB" :style="{ width: bucket.width }" />
                            </div>
                            <span class="text-xs text-right font-semibold">{{ bucket.value }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <h3 class="text-sm font-semibold uppercase mb-3">Recent Transactions</h3>
                    <div class="max-h-72 overflow-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="pb-2">Date</th>
                                    <th class="pb-2">Personnel</th>
                                    <th class="pb-2">Item</th>
                                    <th class="pb-2">Type</th>
                                    <th class="pb-2 text-right">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in dashboard.recent_transactions" :key="row.id" class="border-t">
                                    <td class="py-2">{{ formatDateTime(row.created_at) }}</td>
                                    <td class="py-2">{{ row.actor_display_name || '-' }}</td>
                                    <td class="py-2">{{ row.item?.name || '-' }}</td>
                                    <td class="py-2 uppercase">{{ row.transac_type }}</td>
                                    <td class="py-2 text-right">{{ row.quantity }}</td>
                                </tr>
                                <tr v-if="!dashboard.recent_transactions.length">
                                    <td colspan="5" class="py-3 text-center text-gray-500">No transactions found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <h3 class="text-sm font-semibold uppercase mb-3">Items per Category</h3>
                    <ul class="space-y-1 text-sm">
                        <li v-for="row in dashboard.items_per_category" :key="row.label" class="flex justify-between gap-2">
                            <span>{{ row.label }}</span>
                            <span class="font-semibold">{{ row.total }}</span>
                        </li>
                        <li v-if="!dashboard.items_per_category.length" class="text-gray-500 text-xs">No data.</li>
                    </ul>
                </div>

                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <h3 class="text-sm font-semibold uppercase mb-3">Items per Location</h3>
                    <ul class="space-y-1 text-sm">
                        <li v-for="row in dashboard.items_per_location" :key="row.label" class="flex justify-between gap-2">
                            <span>{{ row.label }}</span>
                            <span class="font-semibold">{{ row.total }}</span>
                        </li>
                        <li v-if="!dashboard.items_per_location.length" class="text-gray-500 text-xs">No data.</li>
                    </ul>
                </div>

                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <h3 class="text-sm font-semibold uppercase mb-3">Items per Project Code</h3>
                    <ul class="space-y-1 text-sm">
                        <li v-for="row in dashboard.items_per_project_code" :key="row.label" class="flex justify-between gap-2">
                            <span>{{ row.label }}</span>
                            <span class="font-semibold">{{ row.total }}</span>
                        </li>
                        <li v-if="!dashboard.items_per_project_code.length" class="text-gray-500 text-xs">No data.</li>
                    </ul>
                </div>
            </div>

            <div v-if="loading" class="text-xs text-gray-500">Loading dashboard data...</div>
        </div>
    </AppLayout>
</template>
