<script>
import axios from "axios";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
import Transaction from "@/Modules/domain/Transaction";

export default {
    name: "InventoryDashboard",
    components: { TransactionHeaderAction },
    mixins: [ApiMixin],
    data() {
        return {
            loading: false,
            scope: "month",
            scopeOptions: [
                { name: "day", label: "Day" },
                { name: "week", label: "Week" },
                { name: "month", label: "Month" },
                { name: "year", label: "Year" },
            ],
            dashboard: {
                totals: { incoming: 0, outgoing: 0 },
                recent_transactions: [],
                items_per_category: [],
                items_per_location: [],
                items_per_project_code: [],
                stock_buckets: { empty: 0, low: 0, mid: 0, high: 0 },
            },
        };
    },
    computed: {
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
                const response = await this.fetchGetApi("api.inventory.transactions.dashboard", {params: { scope: this.scope }});
                const payload = response?.data?.data ?? response?.data ?? {};
                this.dashboard = {
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
        convertToTransaction(response) {
            const service = new ConcreteApiService();

            return response.map(item => 
                service.castToModel(item, Transaction)
            );
        },
        formatDateTime(value) {
            if (!value) return "-";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString();
        },
    },
    watch: {
        scope() {
            this.loadDashboard();
        },
    },
    mounted() {
        this.loadDashboard();
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
            <div class="flex justify-between items-end gap-3">
                <h2 class="text-xl font-bold">Inventory Dashboard</h2>
                <div class="w-40">
                    <custom-dropdown
                        label="Period"
                        :value="scope"
                        :options="scopeOptions"
                        :withAllOption="false"
                        :show-clear="false"
                        @selectedChange="scope = $event || 'month'"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <div class="flex flex-col leading-tight">
                        <p class="text-sm text-g7ray-700 uppercase font-semibold">Total Incoming</p>
                        <span class="text-gray-500 lowercase font-normal text-xs">{{ 'within a ' + scope }}</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ dashboard.totals.incoming }}</p>
                </div>
                <div class="p-4 bg-white border rounded-lg shadow-sm">
                    <div class="flex flex-col leading-tight">
                        <p class="text-sm text-gray-700 uppercase font-semibold">Total Outgoing</p>
                        <span class="text-gray-500 lowercase font-normal text-xs">{{ 'within a ' + scope }}</span>
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
                                    <td class="py-2">{{ row.personnel.fullName || '-' }}</td>
                                    <td class="py-2">{{ row.item?.name || '-' }}</td>
                                    <td class="py-2 uppercase">{{ row.transac_type }}</td>
                                    <td class="py-2 text-right">{{ row.quantity }}</td>
                                </tr>
                                <tr v-if="!dashboard.recent_transactions.length">
                                    <td colspan="4" class="py-3 text-center text-gray-500">No transactions found.</td>
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
