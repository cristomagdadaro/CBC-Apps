<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import AppLayout from "@/Layouts/AppLayout.vue";
import CalendarModule from "@/Components/CalendarModule.vue";
import LaboratoryLogHeaderAction from "@/Pages/Laboratory/components/LaboratoryLogHeaderAction.vue";

export default {
    name: "LaboratoryDashboard",
    components: { AppLayout, CalendarModule, LaboratoryLogHeaderAction },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            dashboard: null,
            loading: false,
            refreshTimer: null,
        };
    },
    computed: {
        activeLogs() {
            return this.dashboard?.active ?? [];
        },
        overdueLogs() {
            return this.dashboard?.overdue ?? [];
        },
        mostUsed() {
            return this.dashboard?.most_used ?? [];
        },
        calendarEvents() {
            const mapLog = (log) => ({
                id: log.id,
                type: "equipment",
                status: log.status,
                date_from: log.started_at,
                date_to: log.end_use_at,
                label: log.equipment?.name || "Equipment",
                subtitle: this.formatPersonnelName(log.personnel),
                color: log.status === "overdue" ? "#EF4444" : "#10B981",
            });

            return [...this.activeLogs.map(mapLog), ...this.overdueLogs.map(mapLog)];
        },
        calendarLegend() {
            return [
                {
                    title: "Status",
                    items: [
                        { label: "Active", color: "#10B981" },
                        { label: "Overdue", color: "#EF4444" },
                    ],
                },
            ];
        },
        heatmap() {
            const rows = Array.from({ length: 7 }, () => Array(24).fill(0));
            (this.dashboard?.heatmap ?? []).forEach((entry) => {
                const dayIndex = Math.max(0, Math.min(6, (entry.day_of_week ?? 1) - 1));
                const hourIndex = Math.max(0, Math.min(23, entry.hour_of_day ?? 0));
                rows[dayIndex][hourIndex] = entry.usage_count ?? 0;
            });
            return rows;
        },
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            try {
                const response = await this.fetchGetApi("api.laboratory.dashboard");
                const payload = response?.data?.data ?? response?.data ?? response;
                this.dashboard = payload?.data ?? payload;
            } finally {
                this.loading = false;
            }
        },
        formatPersonnelName(personnel) {
            if (!personnel) return "-";
            const parts = [personnel.fname, personnel.mname, personnel.lname, personnel.suffix]
                .filter(Boolean)
                .map((value) => String(value).trim())
                .filter(Boolean);
            return parts.length ? parts.join(" ") : "-";
        },
        heatColor(value) {
            if (!value) return "bg-gray-100";
            if (value < 2) return "bg-green-100";
            if (value < 5) return "bg-green-300";
            if (value < 10) return "bg-green-500";
            return "bg-green-700";
        },
        formatDuration(seconds) {
            if (!seconds) return "0h";
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        },
    },
    mounted() {
        this.loadDashboard();
        this.refreshTimer = setInterval(this.loadDashboard, 30000);
    },
    beforeUnmount() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
    },
};
</script>

<template>
    <AppLayout title="Laboratory Logs Dashboard">
         <template #header>
            <LaboratoryLogHeaderAction />
         </template>
        <div class="max-w-6xl mx-auto px-4 py-6">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        
                        <div class="text-xs text-gray-500" v-if="loading">Refreshing...</div>
                    </div>
                    <div class="rounded-md bg-gray-50 border border-gray-200 px-4 py-3 text-xs text-gray-600">
                        Active and overdue panels show current equipment usage; the heatmap aggregates check-ins by day and hour (darker = more logs),
                        and the calendar highlights active/overdue spans across the month.
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="border rounded-lg bg-white p-4 shadow-sm">
                        <h2 class="text-base font-semibold mb-3">Active Equipment</h2>
                        <div v-if="activeLogs.length" class="space-y-2 text-sm">
                            <div v-for="log in activeLogs" :key="log.id" class="flex flex-col gap-1 border-b pb-2">
                                <Link class="font-semibold" :href="route('laboratory.equipments.show', log.equipment?.id)">{{ log.equipment?.name }}</Link>
                                <div class="text-gray-500">Started: {{ formatDateTime(log.started_at) }}</div>
                                <div class="text-gray-500">Ends: {{ formatDateTime(log.end_use_at) }}</div>
                                <div class="text-gray-500">User: {{ formatPersonnelName(log.personnel) }}</div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">No active equipment logs.</div>
                    </div>

                    <div class="border rounded-lg bg-white p-4 shadow-sm">
                        <h2 class="text-base font-semibold mb-3">Overdue Equipment</h2>
                        <div v-if="overdueLogs.length" class="space-y-2 text-sm">
                            <div v-for="log in overdueLogs" :key="log.id" class="flex flex-col gap-1 border-b pb-2">
                                <Link class="font-semibold text-red-600" :href="route('laboratory.equipments.show', log.equipment?.id)">{{ log.equipment?.name }}</Link>
                                <div class="text-gray-500">Expected end: {{ formatDateTime(log.end_use_at) }}</div>
                                <div class="text-gray-500">User: {{ formatPersonnelName(log.personnel) }}</div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">No overdue equipment.</div>
                    </div>
                </div>

                <div class="border rounded-lg bg-white p-4 shadow-sm">
                    <h2 class="text-base font-semibold mb-3">Most-Used Equipment</h2>
                    <div v-if="mostUsed.length" class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div v-for="item in mostUsed" :key="item.equipment_id" class="border rounded-md p-3">
                            <div class="font-semibold">{{ item.equipment_name || item.equipment_id }}</div>
                            <div class="text-gray-500">Usage count: {{ item.usage_count }}</div>
                            <div class="text-gray-500">Total duration: {{ formatDuration(item.total_duration_seconds) }}</div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500">No usage data available.</div>
                </div>

                <div class="border rounded-lg bg-white p-4 shadow-sm">
                    <h2 class="text-base font-semibold mb-3">Usage Heatmap</h2>
                    <div class="overflow-x-auto">
                        <div class="grid gap-1 text-xs" style="grid-template-columns: repeat(25, minmax(0, 1fr));">
                            <div></div>
                            <div v-for="hour in 24" :key="hour" class="text-center text-[0.65rem] text-gray-500">
                                {{ hour - 1 }}
                            </div>
                        </div>
                        <div v-for="(row, dayIndex) in heatmap" :key="dayIndex" class="grid gap-1" style="grid-template-columns: repeat(25, minmax(0, 1fr));">
                            <div class="text-[0.65rem] text-gray-500">
                                {{ ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][dayIndex] }}
                            </div>
                            <div
                                v-for="(cell, hourIndex) in row"
                                :key="hourIndex"
                                :class="['h-4 rounded', heatColor(cell)]"
                                :title="cell + ' logs'"
                            ></div>
                        </div>
                    </div>
                </div>

                <CalendarModule
                    title="Equipment Usage Calendar"
                    subtitle="Active and overdue equipment usage by day."
                    :events="calendarEvents"
                    :type-options="[{ key: 'equipment', label: 'Equipment' }]"
                    :status-options="[{ key: 'active', label: 'Active' }, { key: 'overdue', label: 'Overdue' }]"
                    :status-colors="{ active: '#10B981', overdue: '#EF4444' }"
                    :legend-groups="calendarLegend"
                    :show-type-filter="false"
                />
            </div>
        </div>
    </AppLayout>
</template>
