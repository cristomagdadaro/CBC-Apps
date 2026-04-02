<script>
import axios from 'axios';
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
} from 'chart.js';
import CalendarModule from '@/Components/CalendarModule.vue';
import LaboratoryEquipmentLog from '@/Modules/domain/LaboratoryEquipmentLog';
import { subscribeToRealtimeChannels } from '@/Modules/realtime/subscriptions';
import LaboratoryLogHeaderAction from '@/Pages/Laboratory/components/LaboratoryLogHeaderAction.vue';
import ApiMixin from '@/Modules/mixins/ApiMixin';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

export default {
    name: 'LaboratoryDashboard',
    components: {
        CalendarModule,
        LaboratoryLogHeaderAction,
    },
    mixins: [ApiMixin],
    data() {
        return {
            dashboard: null,
            loading: false,
            refreshTimer: null,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
            activeTab: 'stats',
            tabs: [
                { key: 'stats', label: 'Statistics' },
                { key: 'calendar', label: 'Calendar' },
                { key: 'logs', label: 'Logs' },
            ],
            dayLabels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            heatLegend: [
                { label: '0 logs', className: 'bg-gray-100' },
                { label: '1 log', className: 'bg-green-100' },
                { label: '2-4 logs', className: 'bg-green-300' },
                { label: '5-9 logs', className: 'bg-green-500' },
                { label: '10+ logs', className: 'bg-green-700' },
            ],
            mostUsedChartInstance: null,
        };
    },
    computed: {
        activeLogs() {
            return this.dashboard?.active ?? [];
        },
        overdueLogs() {
            return this.dashboard?.overdue ?? [];
        },
        completedLogs() {
            return this.dashboard?.completed ?? [];
        },
        mostUsed() {
            return this.dashboard?.most_used ?? [];
        },
        activeCount() {
            return this.activeLogs.length;
        },
        overdueCount() {
            return this.overdueLogs.length;
        },
        currentInUseCount() {
            return this.activeCount + this.overdueCount;
        },
        groupedActiveLogs() {
            return this.groupLogsByLocation(this.activeLogs);
        },
        groupedOverdueLogs() {
            return this.groupLogsByLocation(this.overdueLogs);
        },
        calendarEvents() {
            const mapLog = (log) => ({
                id: log.id,
                type: 'equipment',
                status: log.status,
                date_from: log.started_at,
                date_to: log.actual_end_at || log.end_use_at,
                label: log.equipment?.name || 'Equipment',
                subtitle: this.formatPersonnelName(log.personnel),
                color: log.status === 'overdue' ? '#EF4444' : log.status === 'completed' ? '#9CA3AF' : '#10B981',
                checkoutPage: 'laboratory.equipments.show',
                checkoutPageId: log.id,
                checkoutPageTarget: '_blank',
            });

            return [
                ...this.activeLogs.map(mapLog),
                ...this.overdueLogs.map(mapLog),
                ...this.completedLogs.map(mapLog),
            ];
        },
        calendarLegend() {
            return [
                {
                    title: 'Status',
                    items: [
                        { label: 'Active', color: '#10B981' },
                        { label: 'Overdue', color: '#EF4444' },
                        { label: 'Completed', color: '#9CA3AF' },
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
        LaboratoryEquipmentLog() {
            return LaboratoryEquipmentLog;
        },
        LaboratoryLogsDataTable() {
            return LaboratoryLogsDataTable;
        },
    },
    methods: {
        groupLogsByLocation(logs) {
            const grouped = logs.reduce((accumulator, log) => {
                const key = log.location_label || 'Unknown Location';

                if (!accumulator[key]) {
                    accumulator[key] = [];
                }

                accumulator[key].push(log);
                return accumulator;
            }, {});

            return Object.entries(grouped)
                .map(([location, items]) => ({ location, items }))
                .sort((a, b) => a.location.localeCompare(b.location));
        },
        formatPersonnelName(personnel) {
            if (!personnel) {
                return '-';
            }

            const parts = [personnel.fname, personnel.mname, personnel.lname, personnel.suffix]
                .filter(Boolean)
                .map((value) => String(value).trim())
                .filter(Boolean);

            return parts.length ? parts.join(' ') : '-';
        },
        formatDateTime(value) {
            if (!value) {
                return '-';
            }

            const parsed = new Date(value);
            if (Number.isNaN(parsed.getTime())) {
                return value;
            }

            return parsed.toLocaleString();
        },
        heatColor(value) {
            if (!value) return 'bg-gray-100';
            if (value < 2) return 'bg-green-100';
            if (value < 5) return 'bg-green-300';
            if (value < 10) return 'bg-green-500';
            return 'bg-green-700';
        },
        destroyCharts() {
            if (this.mostUsedChartInstance) {
                this.mostUsedChartInstance.destroy();
                this.mostUsedChartInstance = null;
            }
        },
        buildMostUsedChart() {
            this.destroyCharts();

            if (!this.$refs.mostUsedChartCanvas || !this.mostUsed.length) {
                return;
            }

            try {
                const canvas = this.$refs.mostUsedChartCanvas;
                if (!canvas) return;

                // Ensure canvas has valid dimensions
                if (!canvas.offsetWidth || !canvas.offsetHeight) {
                    return;
                }

                // Get context and verify it exists
                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                const labels = this.mostUsed.map((item) => item.equipment_name || item.equipment_id);
                const usageCounts = this.mostUsed.map((item) => item.usage_count ?? 0);
                const durationHours = this.mostUsed.map((item) => {
                    const seconds = item.total_duration_seconds ?? 0;
                    return Number((seconds / 3600).toFixed(2));
                });

                this.mostUsedChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Usage Count',
                                data: usageCounts,
                                borderColor: '#0EA5E9',
                                backgroundColor: 'rgba(14, 165, 233, 0.2)',
                                borderWidth: 2,
                            },
                            {
                                label: 'Total Hours',
                                data: durationHours,
                                borderColor: '#22C55E',
                                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                                borderWidth: 2,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 750,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false, drawBorder: false },
                                border: { display: false },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 20,
                                },
                            },
                            y: {
                                grid: { display: false, drawBorder: false },
                                border: { display: false },
                                beginAtZero: true,
                            },
                        },
                    },
                });
            } catch (error) {
                console.error('Error building chart:', error);
            }
        },
        async loadDashboard() {
            this.loading = true;

            try {
                const response = await axios.get(route('api.laboratory.dashboard'));
                const payload = response?.data?.data ?? response?.data ?? response;
                this.dashboard = payload?.data ?? payload;
            } finally {
                this.loading = false;
            }
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === 'function') {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        configureRealtime() {
            this.cleanupRealtime();

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: 'private',
                    channel: 'laboratory.logs',
                    event: 'equipment.log.changed',
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ]);
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                this.loadDashboard();
            }, 400);
        },
    },
    watch: {
        mostUsed() {
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.buildMostUsedChart();
                });
            });
        },
        activeTab(newValue) {
            if (newValue === 'stats') {
                this.$nextTick(() => {
                    requestAnimationFrame(() => {
                        setTimeout(() => {
                            this.buildMostUsedChart();
                        }, 100);
                    });
                });
            }
        },
    },
    async mounted() {
        await this.loadDashboard();
        this.configureRealtime();
        //this.refreshTimer = setInterval(this.loadDashboard, 30000);

        this.$nextTick(() => {
            requestAnimationFrame(() => {
                setTimeout(() => {
                    this.buildMostUsedChart();
                }, 200);
            });
        });
    },
    beforeUnmount() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }

        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
        this.destroyCharts();
    },
};
</script>

<template>
    <AppLayout title="Laboratory Logs Dashboard">
        <template #header>
            <LaboratoryLogHeaderAction />
        </template>

        <div class="space-y-6">
                <div class="flex justify-end" v-if="loading">
                    <span class="text-xs text-gray-500">Refreshing...</span>
                </div>

                <TabNavigation v-model="activeTab" :tabs="tabs" />

                <div v-show="activeTab === 'stats'" class="space-y-6 px-5">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current In Use</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ currentInUseCount }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500">Active + overdue equipment logs</p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                            <Dropdown align="right" width="auto" max-height="24rem">
                                <template #trigger>
                                    <button type="button" class="w-full text-left">
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active
                                            Equipment</h3>
                                        <p class="mt-2 text-3xl font-semibold text-green-600">{{ activeCount }}</p>
                                        <p class="mt-2 text-xs text-gray-500">Click to view details</p>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="w-[32rem] max-w-[85vw] p-2 text-sm">
                                        <div v-if="groupedActiveLogs.length" class="space-y-2">
                                            <div v-for="group in groupedActiveLogs" :key="group.location"
                                                class="border rounded-md p-2">
                                                <div class="font-semibold text-gray-700 px-2 py-1">{{ group.location }}
                                                    ({{ group.items.length }})</div>
                                                <DropdownOption v-for="log in group.items" :key="log.id"
                                                    class="!px-2 !py-2 !whitespace-normal">
                                                    <a class="font-semibold text-blue-600 hover:underline"
                                                        target="_blank"
                                                        :href="route('laboratory.equipments.show', log.equipment?.id)">
                                                        {{ log.equipment?.name || 'Equipment' }}
                                                    </a>
                                                    <div class="text-gray-500 text-xs">Barcode: {{ log.equipment_barcode
                                                        || '-' }}</div>
                                                    <div class="text-gray-500 text-xs">Started: {{
                                                        formatDateTime(log.started_at) }}</div>
                                                    <div class="text-gray-500 text-xs">Ends: {{
                                                        formatDateTime(log.end_use_at) }}</div>
                                                    <div class="text-gray-500 text-xs">User: {{
                                                        formatPersonnelName(log.personnel) }}</div>
                                                </DropdownOption>
                                            </div>
                                        </div>
                                        <div v-else class="text-gray-500 p-2">No active equipment logs.</div>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>

                        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                            <Dropdown align="right" width="auto" max-height="24rem">
                                <template #trigger>
                                    <button type="button" class="w-full text-left">
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue
                                            Equipment</h3>
                                        <p class="mt-2 text-3xl font-semibold text-red-600">{{ overdueCount }}</p>
                                        <p class="mt-2 text-xs text-gray-500">Click to view details</p>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="w-[32rem] max-w-[85vw] p-2 text-sm">
                                        <div v-if="groupedOverdueLogs.length" class="space-y-2">
                                            <div v-for="group in groupedOverdueLogs" :key="group.location"
                                                class="border rounded-md p-2">
                                                <div class="font-semibold text-gray-700 px-2 py-1">{{ group.location }}
                                                    ({{ group.items.length }})</div>
                                                <DropdownOption v-for="log in group.items" :key="log.id"
                                                    class="!px-2 !py-2 !whitespace-normal">
                                                    <a class="font-semibold text-red-600 hover:underline"
                                                        target="_blank"
                                                        :href="route('laboratory.equipments.show', log.equipment?.id)">
                                                        {{ log.equipment?.name || 'Equipment' }}
                                                    </a>
                                                    <div class="text-gray-500 text-xs">Barcode: {{ log.equipment_barcode
                                                        || '-' }}</div>
                                                    <div class="text-gray-500 text-xs">Expected end: {{
                                                        formatDateTime(log.end_use_at) }}</div>
                                                    <div class="text-gray-500 text-xs">User: {{
                                                        formatPersonnelName(log.personnel) }}</div>
                                                </DropdownOption>
                                            </div>
                                        </div>
                                        <div v-else class="text-gray-500 p-2">No overdue equipment.</div>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                        <div class="flex justify-between">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Usage Heatmap</h3>

                            <div class="mb-3 flex gap-2 items-center">
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">LEGEND</h3>
                                <div class="flex flex-wrap gap-3 text-xs text-gray-600">
                                    <div v-for="legend in heatLegend" :key="legend.label"
                                        class="flex items-center gap-2">
                                        <span :class="['inline-block h-3 w-6 rounded', legend.className]"></span>
                                        <span>{{ legend.label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <div class="grid gap-1 text-xs" style="grid-template-columns: repeat(25, minmax(0, 1fr));">
                                <div class="text-[0.65rem] text-gray-500 font-semibold">HOURS</div>
                                <div v-for="hour in 24" :key="hour" class="text-center text-[0.65rem] text-gray-500">
                                    {{ hour - 1 }}
                                </div>
                            </div>
                            <div v-for="(row, dayIndex) in heatmap" :key="dayIndex" class="grid gap-1"
                                style="grid-template-columns: repeat(25, minmax(0, 1fr));">
                                <div class="text-[0.65rem] text-gray-500">
                                    {{ dayLabels[dayIndex] }}
                                </div>
                                <div v-for="(cell, hourIndex) in row" :key="hourIndex"
                                    :class="['h-4 rounded-md mb-0.5', heatColor(cell)]" :title="cell + ' logs'"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Top 5 Most-Used Equipment</h3>
                        <div v-if="mostUsed.length" class="h-72 w-full">
                            <canvas ref="mostUsedChartCanvas" :key="mostUsed.length" width="400" height="300"
                                style="max-height: 100%; max-width: 100%;"></canvas>
                        </div>
                        <div v-else class="text-sm text-gray-500">No usage data available.</div>
                    </div>
                </div>

                <div v-show="activeTab === 'calendar'" class="space-y-6 px-5">
                    <CalendarModule title="Equipment Usage Calendar"
                        subtitle="Active, overdue, and completed equipment usage by day." :events="calendarEvents"
                        :type-options="[{ key: 'equipment', label: 'Equipment' }]"
                        :status-options="[{ key: 'active', label: 'Active' }, { key: 'overdue', label: 'Overdue' }, { key: 'completed', label: 'Completed' }]"
                        :status-colors="{ active: '#10B981', overdue: '#EF4444', completed: '#9CA3AF' }" :legend-groups="calendarLegend"
                        :show-type-filter="false" />
                </div>
                <div v-show="activeTab === 'logs'">
                    <CRCMDatatable :base-model="LaboratoryEquipmentLog" :can-view="true" :can-create="false" :can-update="true" :can-delete="false">
                        <!-- Custom Cell Rendering -->
                        <template #cell-status="{ value }">
                            <span class="px-4 py-1.5 rounded-full text-xs font-medium leading-none uppercase" :class="{
                                'bg-yellow-300 text-yellow-700': value === 'active',
                                'bg-red-300 text-red-700': value === 'overdue',
                                'bg-green-300 text-green-700': value === 'completed',
                            }">
                                {{ value === 'active' ? 'Active' : value === 'overdue' ? 'Overdue' : value === 'completed' ? 'Completed' : value }}
                            </span>
                        </template>
                    </CRCMDatatable>
                </div>
            </div>
    </AppLayout>
</template>
