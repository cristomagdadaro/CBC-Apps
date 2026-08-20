<script>
import { Link } from '@inertiajs/vue3';
import { markRaw } from 'vue';
import {
    Chart,
    PieController,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Filler,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(
    BarController, PieController, LineController, LineElement, PointElement,
    BarElement, ArcElement, CategoryScale, LinearScale, Filler, Tooltip, Legend,
);

export default {
    name: 'Dashboard',
    components: {
        Link,
    },
    data() {
        return {
            eventColors: ['#22c55e', '#0ea5e9', '#eab308', '#ef4444'],
            accessColors: ['#eab308', '#22c55e', '#ef4444'],
            invColors: ['#6b7280', '#f97316', '#0ea5e9', '#22c55e'],
            rentalColors: ['#eab308', '#22c55e', '#10b981', '#ef4444'],
            labColors: ['#22c55e', '#f59e0b', '#ef4444', '#0ea5e9'],
        };
    },
    computed: {
        stats() {
            return this.$page.props.stats ?? this.defaultStats();
        },
        systemPulse() {
            return this.$page.props.systemPulse ?? {};
        },
        weeklyTrend() {
            return this.$page.props.weeklyTrend ?? [];
        },
        moduleHealth() {
            return this.$page.props.moduleHealth ?? [];
        },
        topActiveEquipment() {
            return this.$page.props.topActiveEquipment ?? [];
        },
        recentSystemActivity() {
            return this.$page.props.recentSystemActivity ?? [];
        },
        recentTransactions() {
            return this.$page.props.recentTransactions ?? [];
        },
        recentEquipmentLogs() {
            return this.$page.props.recentEquipmentLogs ?? [];
        },
        dashboardAccess() {
            return {
                events: false,
                fes: false,
                inventory: false,
                rentals: false,
                laboratory: false,
                equipment_logger: false,
                ...(this.$page.props.dashboardAccess ?? {}),
            };
        },
        hasSummaryCards() {
            return this.dashboardAccess.events
                || this.dashboardAccess.fes
                || this.dashboardAccess.inventory
                || this.dashboardAccess.rentals
                || this.dashboardAccess.laboratory;
        },
        hasQuickActions() {
            return this.dashboardAccess.events
                || this.dashboardAccess.rentals
                || this.dashboardAccess.laboratory;
        },
        hasDashboardContent() {
            return this.hasSummaryCards
                || this.dashboardAccess.inventory
                || this.dashboardAccess.laboratory
                || this.hasQuickActions;
        },
        pulseKpis() {
            const p = this.systemPulse;
            return [
                { label: 'Personnel', value: p.total_personnel ?? 0, icon: 'LuUsers', color: 'violet', gradient: 'from-violet-500 to-purple-600' },
                { label: 'Inventory Items', value: p.total_items ?? 0, icon: 'LuPackage', color: 'sky', gradient: 'from-sky-500 to-blue-600' },
                { label: 'Active Sessions', value: p.active_equipment_logs ?? 0, icon: 'LuFlaskConical', color: 'emerald', gradient: 'from-emerald-500 to-teal-600' },
                { label: 'Today\'s Transactions', value: p.transactions_today ?? 0, icon: 'LuZap', color: 'amber', gradient: 'from-amber-500 to-orange-600' },
            ];
        },
    },
    watch: {
        stats: {
            handler() {
                this.buildModuleCharts();
            },
            deep: true,
            immediate: true,
        },
        weeklyTrend: {
            handler() {
                this.buildTrendChart();
            },
            deep: true,
            immediate: true,
        },
    },
    methods: {
        defaultStats() {
            return {
                events: { total: 0, active: 0, upcoming: 0, suspended: 0, expired: 0 },
                access_requests: { total: 0, pending: 0, approved: 0, rejected: 0 },
                inventory: { items: 0, transactions_today: 0, stock_buckets: { empty: 0, low: 0, mid: 0, high: 0 } },
                vehicle_rentals: { total: 0, pending: 0, approved: 0, completed: 0, rejected: 0 },
                venue_rentals: { total: 0, pending: 0, approved: 0, completed: 0, rejected: 0 },
                laboratory_equipment: { total: 0, active: 0, overdue: 0, completed: 0 },
            };
        },
        destroyCharts() {
            ['trend', 'events', 'access', 'inventory', 'vehicle', 'venue', 'lab'].forEach(key => {
                const instance = this[`${key}ChartInstance`];
                if (instance) {
                    instance.destroy();
                    this[`${key}ChartInstance`] = null;
                }
            });
        },
        buildTrendChart() {
            if (this.trendChartInstance) {
                this.trendChartInstance.destroy();
                this.trendChartInstance = null;
            }
            this.$nextTick(() => {
                const canvas = this.$refs.trendChartCanvas;
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                const trend = this.weeklyTrend;
                const labels = trend.map(d => d.label);

                const makeGradient = (color, opacity) => {
                    const g = ctx.createLinearGradient(0, 0, 0, canvas.height);
                    g.addColorStop(0, `rgba(${color}, ${opacity})`);
                    g.addColorStop(1, `rgba(${color}, 0.02)`);
                    return g;
                };

                if (this.trendChartInstance) {
                    this.trendChartInstance.destroy();
                }

                try {
                        this.trendChartInstance = markRaw(new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Transactions',
                                    data: trend.map(d => d.transactions),
                                    borderColor: '#0ea5e9',
                                    backgroundColor: makeGradient('14, 165, 233', 0.25),
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#0ea5e9',
                                    borderWidth: 2.5,
                                },
                                {
                                    label: 'Equipment',
                                    data: trend.map(d => d.equipment),
                                    borderColor: '#8b5cf6',
                                    backgroundColor: makeGradient('139, 92, 246', 0.2),
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#8b5cf6',
                                    borderWidth: 2.5,
                                },
                                {
                                    label: 'Rentals',
                                    data: trend.map(d => d.rentals),
                                    borderColor: '#f59e0b',
                                    backgroundColor: makeGradient('245, 158, 11', 0.15),
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#f59e0b',
                                    borderWidth: 2.5,
                                },
                                {
                                    label: 'Events',
                                    data: trend.map(d => d.events),
                                    borderColor: '#22c55e',
                                    backgroundColor: makeGradient('34, 197, 94', 0.15),
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#22c55e',
                                    borderWidth: 2.5,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    align: 'end',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 16,
                                        font: { size: 11, weight: '600' },
                                        color: '#94a3b8',
                                    },
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                    padding: 12,
                                    cornerRadius: 10,
                                    titleFont: { size: 12, weight: '600' },
                                    bodyFont: { size: 11 },
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#94a3b8', font: { size: 11, weight: '500' } },
                                    border: { display: false },
                                },
                                y: {
                                    grid: { color: 'rgba(148, 163, 184, 0.08)' },
                                    ticks: { color: '#94a3b8', font: { size: 11 }, stepSize: 1 },
                                    border: { display: false },
                                    beginAtZero: true,
                                },
                            },
                        },
                    }));
                } catch(e) {
                    console.warn(e)
                }
            });
        },
        buildModuleCharts() {
            ['events', 'access', 'inventory', 'vehicle', 'venue', 'lab'].forEach(key => {
                const instance = this[`${key}ChartInstance`];
                if (instance) {
                    instance.destroy();
                    this[`${key}ChartInstance`] = null;
                }
            });

            const chartConfigs = [
                {
                    ref: 'eventsChartCanvas',
                    key: 'eventsChartInstance',
                    type: 'pie',
                    labels: ['Active', 'Upcoming', 'Suspended', 'Expired'],
                    data: [this.stats.events.active, this.stats.events.upcoming, this.stats.events.suspended, this.stats.events.expired],
                    colors: this.eventColors,
                    label: 'Event Forms',
                },
                {
                    ref: 'accessChartCanvas',
                    key: 'accessChartInstance',
                    type: 'bar',
                    labels: ['Pending', 'Approved', 'Rejected'],
                    data: [this.stats.access_requests.pending, this.stats.access_requests.approved, this.stats.access_requests.rejected],
                    colors: this.accessColors,
                    label: 'FES Requests',
                },
                {
                    ref: 'inventoryChartCanvas',
                    key: 'inventoryChartInstance',
                    type: 'bar',
                    labels: ['Empty', 'Low', 'Mid', 'High'],
                    data: [
                        this.stats.inventory.stock_buckets?.empty ?? 0,
                        this.stats.inventory.stock_buckets?.low ?? 0,
                        this.stats.inventory.stock_buckets?.mid ?? 0,
                        this.stats.inventory.stock_buckets?.high ?? 0,
                    ],
                    colors: this.invColors,
                    label: 'Stock Levels',
                },
                {
                    ref: 'vehicleChartCanvas',
                    key: 'vehicleChartInstance',
                    type: 'bar',
                    labels: ['Pending', 'Approved', 'Completed', 'Rejected'],
                    data: [this.stats.vehicle_rentals.pending, this.stats.vehicle_rentals.approved, this.stats.vehicle_rentals.completed, this.stats.vehicle_rentals.rejected],
                    colors: this.rentalColors,
                    label: 'Vehicle Rentals',
                },
                {
                    ref: 'venueChartCanvas',
                    key: 'venueChartInstance',
                    type: 'bar',
                    labels: ['Pending', 'Approved', 'Completed', 'Rejected'],
                    data: [this.stats.venue_rentals.pending, this.stats.venue_rentals.approved, this.stats.venue_rentals.completed, this.stats.venue_rentals.rejected],
                    colors: this.rentalColors,
                    label: 'Venue Rentals',
                },
                {
                    ref: 'labChartCanvas',
                    key: 'labChartInstance',
                    type: 'bar',
                    labels: ['Active', 'Overdue', 'Completed'],
                    data: [this.stats.laboratory_equipment.active, this.stats.laboratory_equipment.overdue, this.stats.laboratory_equipment.completed],
                    colors: this.labColors,
                    label: 'Lab Equipment',
                },
            ];

            this.$nextTick(() => {
                chartConfigs.forEach(config => {
                    const canvas = this.$refs[config.ref];
                    if (!canvas) return;

                    const options = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    labelColor: (ctx) => ({
                                        borderColor: config.colors[ctx.dataIndex],
                                        backgroundColor: config.colors[ctx.dataIndex],
                                    }),
                                },
                            },
                        },
                        scales: config.type === 'pie' ? {} : {
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: { color: '#6b7280', font: { size: 11 } },
                                border: { display: false },
                            },
                            y: {
                                grid: { display: false, drawBorder: false },
                                ticks: { display: false },
                                border: { display: false },
                            },
                        },
                    };

                    if (this[config.key]) {
                        this[config.key].destroy();
                    }

                    try {
                        this[config.key] = markRaw(new Chart(canvas, {
                            type: config.type,
                            data: {
                                labels: config.labels,
                                datasets: [{
                                    label: config.label,
                                    data: config.data,
                                    backgroundColor: config.colors,
                                    borderWidth: 0,
                                    borderRadius: config.type === 'bar' ? 4 : 0,
                                }],
                            },
                            options,
                        }));
                    } catch(e) {
                        console.warn(e)
                    }
                });
            });
        },
        formatPersonnelName(personnel) {
            if (!personnel) return 'Unknown';
            const parts = [personnel.fname, personnel.mname, personnel.lname, personnel.suffix]
                .filter(Boolean).map((value) => String(value).trim()).filter(Boolean);
            return parts.length ? parts.join(' ') : 'Unknown';
        },
        formatDateTime(value) {
            if (!value) return 'N/A';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return 'N/A';
            return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },
        timeAgo(timestamp) {
            if (!timestamp) return '';
            const now = new Date();
            const then = new Date(timestamp);
            const diffMs = now - then;
            const diffMin = Math.floor(diffMs / 60000);
            if (diffMin < 1) return 'just now';
            if (diffMin < 60) return `${diffMin}m ago`;
            const diffHr = Math.floor(diffMin / 60);
            if (diffHr < 24) return `${diffHr}h ago`;
            const diffDay = Math.floor(diffHr / 24);
            return `${diffDay}d ago`;
        },
        elapsedTime(startedAt) {
            if (!startedAt) return '—';
            const now = new Date();
            const start = new Date(startedAt);
            const diffMs = now - start;
            const hours = Math.floor(diffMs / 3600000);
            const minutes = Math.floor((diffMs % 3600000) / 60000);
            return hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
        },
        equipmentStatusBadge(status) {
            const normalized = String(status || '').toLowerCase();
            if (normalized === 'overdue') return { label: 'Overdue', className: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' };
            if (normalized === 'completed') return { label: 'Completed', className: 'bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-300' };
            return { label: 'Active', className: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' };
        },
        equipmentShowRoute(log) {
            const categoryId = Number(log?.equipment?.category_id ?? 0);
            const equipmentId = log?.equipment?.id ?? log?.equipment_id;
            if (!equipmentId) return '#';
            const routeName = categoryId === 4 ? 'ict.equipments.show' : 'laboratory.equipments.show';
            return route(routeName, equipmentId);
        },
        moduleHealthTotal(mod) {
            return (mod.active ?? 0) + (mod.pending ?? 0) + (mod.completed ?? 0) + (mod.overdue ?? 0);
        },
        moduleBarWidth(mod, key) {
            const total = this.moduleHealthTotal(mod);
            if (total === 0) return '0%';
            return ((mod[key] ?? 0) / total * 100).toFixed(1) + '%';
        },
        activityColorClass(color) {
            const map = {
                emerald: 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30',
                rose: 'border-rose-500 bg-rose-50 dark:bg-rose-950/30',
                amber: 'border-amber-500 bg-amber-50 dark:bg-amber-950/30',
                indigo: 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30',
                slate: 'border-slate-400 bg-slate-50 dark:bg-slate-800/30',
            };
            return map[color] || map.slate;
        },
        activityModuleBadgeClass(color) {
            const map = {
                emerald: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                rose: 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                amber: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                indigo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                slate: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
            };
            return map[color] || map.slate;
        },
    },
    mounted() {
        this.buildModuleCharts();
        this.buildTrendChart();
    },
    beforeUnmount() {
        this.destroyCharts();
    },
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <ActionHeaderLayout title="Dashboard" subtitle="System-wide overview and quick access to key sections." :route-link="route('dashboard')" />
        </template>

        <div class="py-3 sm:py-6">
            <div class="px-2.5 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

                <!-- Empty State -->
                <div
                    v-if="!hasDashboardContent"
                    class="rounded-xl border border-dashed border-gray-300 bg-white p-4 sm:p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <LuShield class="mx-auto h-8 w-8 sm:h-10 sm:w-10 text-gray-400 dark:text-slate-500" />
                    <h3 class="mt-3 sm:mt-4 text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                        No dashboard modules are available right now
                    </h3>
                    <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-gray-500 dark:text-slate-400 leading-relaxed">
                        This dashboard now follows Module Access Controls and your assigned permissions. If you expected more sections here, verify the deployment-access settings and your current role permissions.
                    </p>
                </div>

                <!-- ═══════ SYSTEM PULSE KPI BANNER ═══════ -->
                <div v-if="hasDashboardContent" class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(kpi, idx) in pulseKpis"
                        :key="idx"
                        class="relative overflow-hidden rounded-2xl border border-white/20 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group"
                    >
                        <!-- Gradient accent bar -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r" :class="kpi.gradient"></div>

                        <div class="p-3.5 sm:p-5">
                            <div class="flex items-center justify-between">
                                <div
                                    class="p-2 sm:p-2.5 rounded-xl border transition-transform group-hover:scale-110"
                                    :class="{
                                        'bg-violet-500/10 dark:bg-violet-400/15 border-violet-500/20': kpi.color === 'violet',
                                        'bg-sky-500/10 dark:bg-sky-400/15 border-sky-500/20': kpi.color === 'sky',
                                        'bg-emerald-500/10 dark:bg-emerald-400/15 border-emerald-500/20': kpi.color === 'emerald',
                                        'bg-amber-500/10 dark:bg-amber-400/15 border-amber-500/20': kpi.color === 'amber',
                                    }"
                                >
                                    <component
                                        :is="kpi.icon"
                                        class="w-4 h-4 sm:w-5 sm:h-5"
                                        :class="{
                                            'text-violet-600 dark:text-violet-400': kpi.color === 'violet',
                                            'text-sky-600 dark:text-sky-400': kpi.color === 'sky',
                                            'text-emerald-600 dark:text-emerald-400': kpi.color === 'emerald',
                                            'text-amber-600 dark:text-amber-400': kpi.color === 'amber',
                                        }"
                                    />
                                </div>
                            </div>
                            <p class="mt-3 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                                {{ kpi.value.toLocaleString() }}
                            </p>
                            <p class="mt-0.5 text-[0.7rem] sm:text-xs font-medium text-slate-500 dark:text-slate-400">{{ kpi.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- ═══════ 7-DAY ACTIVITY TREND ═══════ -->
                <div v-if="hasDashboardContent && weeklyTrend.length" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 bg-sky-500/10 dark:bg-sky-400/15 rounded-lg border border-sky-500/20">
                                <LuTrendingUp class="w-4 h-4 text-sky-600 dark:text-sky-400" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">7-Day Activity Trend</h3>
                                <p class="text-[0.65rem] sm:text-xs text-slate-500 dark:text-slate-400">Cross-module activity over the past week</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 sm:p-5">
                        <div class="h-48 sm:h-64">
                            <canvas ref="trendChartCanvas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- ═══════ MODULE HEALTH SUMMARY ═══════ -->
                <div v-if="hasDashboardContent && moduleHealth.length" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 dark:border-slate-800 flex items-center gap-2.5">
                        <div class="p-2 bg-indigo-500/10 dark:bg-indigo-400/15 rounded-lg border border-indigo-500/20">
                            <LuBarChart3 class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">Module Health Overview</h3>
                            <p class="text-[0.65rem] sm:text-xs text-slate-500 dark:text-slate-400">Status distribution across all modules</p>
                        </div>
                    </div>

                    <div class="p-3 sm:p-5 space-y-3 sm:space-y-4">
                        <!-- Legend -->
                        <div class="flex items-center gap-4 text-[0.65rem] sm:text-xs font-medium text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Active</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span> Pending</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-slate-400"></span> Completed</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Overdue/Rejected</span>
                        </div>

                        <div v-for="mod in moduleHealth" :key="mod.module" class="group">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <Link
                                    :href="route(mod.route)"
                                    class="w-28 sm:w-36 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate shrink-0"
                                >
                                    {{ mod.module }}
                                </Link>
                                <div class="flex-1 h-5 sm:h-6 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex">
                                    <div
                                        v-if="mod.active"
                                        class="bg-emerald-500 h-full transition-all duration-500"
                                        :style="{ width: moduleBarWidth(mod, 'active') }"
                                        :title="`Active: ${mod.active}`"
                                    ></div>
                                    <div
                                        v-if="mod.pending"
                                        class="bg-amber-500 h-full transition-all duration-500"
                                        :style="{ width: moduleBarWidth(mod, 'pending') }"
                                        :title="`Pending: ${mod.pending}`"
                                    ></div>
                                    <div
                                        v-if="mod.completed"
                                        class="bg-slate-400 dark:bg-slate-500 h-full transition-all duration-500"
                                        :style="{ width: moduleBarWidth(mod, 'completed') }"
                                        :title="`Completed: ${mod.completed}`"
                                    ></div>
                                    <div
                                        v-if="mod.overdue"
                                        class="bg-rose-500 h-full transition-all duration-500"
                                        :style="{ width: moduleBarWidth(mod, 'overdue') }"
                                        :title="`Overdue: ${mod.overdue}`"
                                    ></div>
                                </div>
                                <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 w-10 text-right shrink-0 tabular-nums">
                                    {{ moduleHealthTotal(mod) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══════ SUMMARY STATS GRID (existing cards, preserved) ═══════ -->
                <div v-if="hasSummaryCards" class="grid gap-3.5 sm:gap-4.5 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">

                    <!-- Event Forms Card -->
                    <div v-if="dashboardAccess.events" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-green-500/10 dark:bg-green-400/15 rounded-xl border border-green-500/20">
                                        <LuCalendar class="w-5 h-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Event Forms</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.events.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('forms.index')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>
                            <div class="mt-3.5 sm:mt-4 flex items-center justify-between text-[0.7rem] sm:text-xs font-medium">
                                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500"></span><span class="text-slate-600 dark:text-slate-300">{{ stats.events.active }} Active</span></div>
                                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="text-slate-600 dark:text-slate-300">{{ stats.events.upcoming }} Upcoming</span></div>
                                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span><span class="text-slate-600 dark:text-slate-300">{{ stats.events.suspended }} Suspended</span></div>
                            </div>
                            <div class="mt-3 sm:mt-4 h-24 sm:h-28"><canvas ref="eventsChartCanvas"></canvas></div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('forms.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">View all events <LuChevronRight class="w-4 h-4" /></Link>
                        </div>
                    </div>

                    <!-- FES Requests Card -->
                    <div v-if="dashboardAccess.fes" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-amber-500/10 dark:bg-amber-400/15 rounded-xl border border-amber-500/20">
                                        <LuShield class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">FES Requests</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.access_requests.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('accessUseRequest.index')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>
                            <div class="mt-3.5 sm:mt-4 flex items-center justify-between text-[0.7rem] sm:text-xs font-medium">
                                <div class="flex items-center gap-1"><LuClock class="w-3.5 h-3.5 text-amber-500 shrink-0" /><span class="text-slate-600 dark:text-slate-300">{{ stats.access_requests.pending }} Pending</span></div>
                                <div class="flex items-center gap-1"><LuCheckCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0" /><span class="text-slate-600 dark:text-slate-300">{{ stats.access_requests.approved }} Approved</span></div>
                                <div class="flex items-center gap-1"><LuXCircle class="w-3.5 h-3.5 text-rose-500 shrink-0" /><span class="text-slate-600 dark:text-slate-300">{{ stats.access_requests.rejected }} Rejected</span></div>
                            </div>
                            <div class="mt-3 sm:mt-4 h-24 sm:h-28"><canvas ref="accessChartCanvas"></canvas></div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('accessUseRequest.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">Review requests <LuChevronRight class="w-4 h-4" /></Link>
                        </div>
                    </div>

                    <!-- Inventory Card -->
                    <div v-if="dashboardAccess.inventory" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-blue-500/10 dark:bg-blue-400/15 rounded-xl border border-blue-500/20">
                                        <LuPackage class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Inventory Items</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.inventory.items }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Link :href="route('items.index')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                        <LuArrowRight class="w-5 h-5" />
                                    </Link>
                                </div>
                            </div>
                            <div class="mt-3.5 sm:mt-4 grid grid-cols-2 gap-1.5 text-[0.7rem] sm:text-xs font-medium">
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-slate-100 dark:bg-slate-800/60 rounded-md"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span><span class="text-slate-600 dark:text-slate-300">{{ stats.inventory.stock_buckets?.empty ?? 0 }} Empty</span></div>
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-orange-50 dark:bg-orange-950/40 rounded-md"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span><span class="text-orange-700 dark:text-orange-300">{{ stats.inventory.stock_buckets?.low ?? 0 }} Low</span></div>
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-md"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span><span class="text-blue-700 dark:text-blue-300">{{ stats.inventory.stock_buckets?.mid ?? 0 }} Mid</span></div>
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-emerald-50 dark:bg-emerald-950/40 rounded-md"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span class="text-emerald-700 dark:text-emerald-300">{{ stats.inventory.stock_buckets?.high ?? 0 }} High</span></div>
                            </div>
                            <div class="mt-3 sm:mt-4 h-24 sm:h-28"><canvas ref="inventoryChartCanvas"></canvas></div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800 flex justify-between">
                            <Link :href="route('items.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">View items <LuChevronRight class="w-4 h-4" /></Link>
                            <Link :href="route('transactions.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors"><LuArrowLeftRight class="w-4 h-4" /> Transactions</Link>
                        </div>
                    </div>

                    <!-- Vehicle Rentals Card -->
                    <div v-if="dashboardAccess.rentals" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-amber-500/10 dark:bg-amber-400/15 rounded-xl border border-amber-500/20"><LuCar class="w-5 h-5 text-amber-600 dark:text-amber-400" /></div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Vehicle Rentals</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.vehicle_rentals.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('rentals.vehicle.index')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"><LuArrowRight class="w-5 h-5" /></Link>
                            </div>
                            <div class="mt-3.5 sm:mt-4 grid grid-cols-4 gap-1 text-center text-[0.65rem] sm:text-xs font-medium">
                                <div class="p-1 rounded-md bg-amber-50 dark:bg-amber-950/40"><p class="font-bold text-amber-700 dark:text-amber-300">{{ stats.vehicle_rentals.pending }}</p><p class="text-amber-600/80 dark:text-amber-400/80">Pending</p></div>
                                <div class="p-1 rounded-md bg-green-50 dark:bg-green-950/40"><p class="font-bold text-green-700 dark:text-green-300">{{ stats.vehicle_rentals.approved }}</p><p class="text-green-600/80 dark:text-green-400/80">Approved</p></div>
                                <div class="p-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40"><p class="font-bold text-emerald-700 dark:text-emerald-300">{{ stats.vehicle_rentals.completed }}</p><p class="text-emerald-600/80 dark:text-emerald-400/80">Done</p></div>
                                <div class="p-1 rounded-md bg-rose-50 dark:bg-rose-950/40"><p class="font-bold text-rose-700 dark:text-rose-300">{{ stats.vehicle_rentals.rejected }}</p><p class="text-rose-600/80 dark:text-rose-400/80">Rejected</p></div>
                            </div>
                            <div class="mt-3 sm:mt-4 h-24 sm:h-28"><canvas ref="vehicleChartCanvas"></canvas></div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('rentals.vehicle.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">Manage bookings <LuChevronRight class="w-4 h-4" /></Link>
                        </div>
                    </div>

                    <!-- Venue Rentals Card -->
                    <div v-if="dashboardAccess.rentals" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-purple-500/10 dark:bg-purple-400/15 rounded-xl border border-purple-500/20"><LuBuilding class="w-5 h-5 text-purple-600 dark:text-purple-400" /></div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Venue Rentals</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.venue_rentals.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('rentals.venue.index')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"><LuArrowRight class="w-5 h-5" /></Link>
                            </div>
                            <div class="mt-3.5 sm:mt-4 grid grid-cols-4 gap-1 text-center text-[0.65rem] sm:text-xs font-medium">
                                <div class="p-1 rounded-md bg-amber-50 dark:bg-amber-950/40"><p class="font-bold text-amber-700 dark:text-amber-300">{{ stats.venue_rentals.pending }}</p><p class="text-amber-600/80 dark:text-amber-400/80">Pending</p></div>
                                <div class="p-1 rounded-md bg-green-50 dark:bg-green-950/40"><p class="font-bold text-green-700 dark:text-green-300">{{ stats.venue_rentals.approved }}</p><p class="text-green-600/80 dark:text-green-400/80">Approved</p></div>
                                <div class="p-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40"><p class="font-bold text-emerald-700 dark:text-emerald-300">{{ stats.venue_rentals.completed }}</p><p class="text-emerald-600/80 dark:text-emerald-400/80">Done</p></div>
                                <div class="p-1 rounded-md bg-rose-50 dark:bg-rose-950/40"><p class="font-bold text-rose-700 dark:text-rose-300">{{ stats.venue_rentals.rejected }}</p><p class="text-rose-600/80 dark:text-rose-400/80">Rejected</p></div>
                            </div>
                            <div class="mt-3 sm:mt-4 h-24 sm:h-28"><canvas ref="venueChartCanvas"></canvas></div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('rentals.venue.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">Manage venues <LuChevronRight class="w-4 h-4" /></Link>
                        </div>
                    </div>

                    <!-- Lab Equipment Card -->
                    <div v-if="dashboardAccess.laboratory" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-indigo-500/10 dark:bg-indigo-400/15 rounded-xl border border-indigo-500/20"><LuMicroscope class="w-5 h-5 text-indigo-600 dark:text-indigo-400" /></div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Lab Equipment</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.laboratory_equipment.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('equipment-logger.dashboard')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"><LuArrowRight class="w-5 h-5" /></Link>
                            </div>
                            <div class="mt-3.5 sm:mt-4 flex items-center justify-around text-[0.7rem] sm:text-xs font-medium">
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="flex items-center gap-1 px-2.5 py-1 bg-green-50 dark:bg-green-950/40 rounded-full"><LuActivity class="w-3 h-3 text-green-500" /><span class="font-bold text-green-700 dark:text-green-300">{{ stats.laboratory_equipment.active }}</span></div>
                                    <span class="text-slate-500 dark:text-slate-400 mt-0.5">Active</span>
                                </div>
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="flex items-center gap-1 px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 rounded-full"><LuAlertTriangle class="w-3 h-3 text-amber-500" /><span class="font-bold text-amber-700 dark:text-amber-300">{{ stats.laboratory_equipment.overdue }}</span></div>
                                    <span class="text-slate-500 dark:text-slate-400 mt-0.5">Overdue</span>
                                </div>
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="flex items-center gap-1 px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-full"><LuCheckCircle class="w-3 h-3 text-blue-500" /><span class="font-bold text-blue-700 dark:text-blue-300">{{ stats.laboratory_equipment.completed }}</span></div>
                                    <span class="text-slate-500 dark:text-slate-400 mt-0.5">Done</span>
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-4 h-24 sm:h-28"><canvas ref="labChartCanvas"></canvas></div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('equipment-logger.dashboard')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">View logs <LuChevronRight class="w-4 h-4" /></Link>
                        </div>
                    </div>
                </div>

                <!-- ═══════ QUICK ACTIONS ═══════ -->
                <div v-if="hasQuickActions" class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
                    <Link v-if="dashboardAccess.events" :href="route('forms.create')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-blue-500/40 dark:hover:border-blue-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-blue-500/10 dark:bg-blue-400/15 rounded-xl border border-blue-500/20 group-hover:scale-105 transition-transform"><LuCalendarPlus class="w-5 h-5 text-blue-600 dark:text-blue-400" /></div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Create Event</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">New event form</p>
                    </Link>

                    <Link v-if="dashboardAccess.events" :href="route('forms.scan')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-emerald-500/40 dark:hover:border-emerald-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-emerald-500/10 dark:bg-emerald-400/15 rounded-xl border border-emerald-500/20 group-hover:scale-105 transition-transform"><LuQrCode class="w-5 h-5 text-emerald-600 dark:text-emerald-400" /></div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Scan QR</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Check attendance</p>
                    </Link>

                    <Link v-if="dashboardAccess.rentals" :href="route('rentals.vehicle.index')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-amber-500/40 dark:hover:border-amber-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-amber-500/10 dark:bg-amber-400/15 rounded-xl border border-amber-500/20 group-hover:scale-105 transition-transform"><LuClipboardList class="w-5 h-5 text-amber-600 dark:text-amber-400" /></div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Bookings</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Rentals & venues</p>
                    </Link>

                    <Link v-if="dashboardAccess.laboratory" :href="route('equipment-logger.dashboard')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-indigo-500/40 dark:hover:border-indigo-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-indigo-500/10 dark:bg-indigo-400/15 rounded-xl border border-indigo-500/20 group-hover:scale-105 transition-transform"><LuFlaskConical class="w-5 h-5 text-indigo-600 dark:text-indigo-400" /></div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Laboratory</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Equipment logs</p>
                    </Link>
                </div>

                <!-- ═══════ BOTTOM GRID: Activity Timeline + Top Active Equipment ═══════ -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">

                    <!-- Unified Recent Activity Timeline -->
                    <div v-if="recentSystemActivity.length" class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 bg-emerald-500/10 dark:bg-emerald-400/15 rounded-lg border border-emerald-500/20">
                                    <LuActivity class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">Recent System Activity</h3>
                                    <p class="text-[0.65rem] sm:text-xs text-slate-500 dark:text-slate-400">Latest actions across all modules</p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-slate-800">
                            <div
                                v-for="(activity, idx) in recentSystemActivity"
                                :key="idx"
                                class="flex items-start gap-3 px-4 sm:px-6 py-3 sm:py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors border-l-[3px]"
                                :class="activityColorClass(activity.color)"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="inline-flex px-2 py-0.5 rounded-full text-[0.6rem] sm:text-[0.65rem] font-bold uppercase"
                                            :class="activityModuleBadgeClass(activity.color)"
                                        >
                                            {{ activity.module }}
                                        </span>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">
                                            {{ activity.title }}
                                        </p>
                                    </div>
                                    <p v-if="activity.subtitle" class="mt-0.5 text-[0.65rem] sm:text-xs text-slate-500 dark:text-slate-400 truncate">
                                        {{ activity.subtitle }}
                                    </p>
                                </div>
                                <span class="text-[0.6rem] sm:text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap shrink-0 tabular-nums">
                                    {{ timeAgo(activity.timestamp) }}
                                </span>
                            </div>

                            <div v-if="!recentSystemActivity.length" class="px-5 py-8 text-center text-xs sm:text-sm text-slate-400 dark:text-slate-500">
                                <LuActivity class="w-8 h-8 mx-auto mb-2 opacity-20" />
                                <p>No recent activity</p>
                            </div>
                        </div>
                    </div>

                    <!-- Top Active Equipment -->
                    <div v-if="dashboardAccess.laboratory" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                        <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-gray-100 dark:border-slate-800 flex items-center gap-2.5">
                            <div class="p-2 bg-rose-500/10 dark:bg-rose-400/15 rounded-lg border border-rose-500/20">
                                <LuTimer class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">Active Sessions</h3>
                                <p class="text-[0.65rem] sm:text-xs text-slate-500 dark:text-slate-400">Currently checked-out equipment</p>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-slate-800">
                            <div
                                v-for="eq in topActiveEquipment"
                                :key="eq.id"
                                class="px-4 sm:px-5 py-3 sm:py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">
                                            {{ eq.equipment?.name || 'Equipment' }}
                                        </p>
                                        <p class="text-[0.65rem] sm:text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                            {{ formatPersonnelName(eq.personnel) }}
                                        </p>
                                        <p v-if="eq.location_label" class="text-[0.6rem] text-slate-400 dark:text-slate-500 truncate">
                                            {{ eq.location_label }}
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span
                                            class="inline-flex px-2 py-0.5 rounded-full text-[0.6rem] sm:text-[0.65rem] font-bold uppercase"
                                            :class="eq.status === 'overdue' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                        >
                                            {{ elapsedTime(eq.started_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!topActiveEquipment.length" class="px-5 py-8 text-center text-xs sm:text-sm text-slate-400 dark:text-slate-500">
                                <LuFlaskConical class="w-8 h-8 mx-auto mb-2 opacity-20" />
                                <p>No active sessions</p>
                            </div>
                        </div>

                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('equipment-logger.dashboard')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                View all sessions <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Fallback: If no lab access but activity exists, make timeline full width -->
                    <div v-if="!dashboardAccess.laboratory && !recentSystemActivity.length" class="lg:col-span-3 text-center py-6 text-xs text-slate-400 dark:text-slate-500">
                        <!-- Empty spacer -->
                    </div>
                </div>

                <!-- ═══════ LEGACY: Recent Transactions + Equipment Logs (kept as fallback) ═══════ -->
                <div v-if="(dashboardAccess.inventory || dashboardAccess.laboratory) && !recentSystemActivity.length" class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Recent Transactions Section -->
                    <div v-if="dashboardAccess.inventory" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200/80 dark:border-slate-800 overflow-hidden">
                        <div class="px-3.5 sm:px-5 py-3 sm:py-4 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <LuActivity class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                                <h3 class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">Recent Transactions</h3>
                            </div>
                            <Link :href="route('transactions.index')" class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">View all</Link>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-slate-800">
                            <Link v-for="transaction in recentTransactions" :key="transaction.id" :href="route('transactions.show', transaction.id)" class="flex items-center justify-between px-3.5 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                                    <div class="p-2 rounded-full shrink-0 hidden sm:block" :class="transaction.transac_type === 'incoming' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'">
                                        <LuArrowDownLeft v-if="transaction.transac_type === 'incoming'" class="w-4 h-4" />
                                        <LuArrowUpRight v-else class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                                            {{ transaction?.item?.name ?? 'Unknown Item' }}
                                            <span class="text-[0.65rem] font-normal uppercase px-1.5 py-0.5 rounded-full ml-1.5 inline-block" :class="transaction.transac_type === 'incoming' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'">{{ transaction.transac_type }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5 truncate">{{ transaction?.quantity }} {{ transaction?.unit }} by <span class="font-medium text-gray-700 dark:text-slate-300">{{ transaction?.personnel ? `${transaction.personnel.fname} ${transaction.personnel.lname}` : 'Unknown' }}</span></p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <p class="text-[0.7rem] sm:text-xs text-gray-500 dark:text-slate-400">{{ new Date(transaction?.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</p>
                                </div>
                            </Link>
                            <div v-if="!recentTransactions?.length" class="px-5 py-6 text-center text-xs sm:text-sm text-gray-500 dark:text-slate-400">
                                <LuPackage class="w-10 h-10 mx-auto mb-2 opacity-20" /><p>No recent transactions</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Equipment Logs Section -->
                    <div v-if="dashboardAccess.laboratory" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200/80 dark:border-slate-800 overflow-hidden">
                        <div class="px-3.5 sm:px-5 py-3 sm:py-4 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <LuMicroscope class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                                <h3 class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">Recent Equipment Logs</h3>
                            </div>
                            <Link :href="route('equipment-logger.dashboard')" class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">View all</Link>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-slate-800">
                            <a v-for="log in recentEquipmentLogs" :key="log.id" class="flex items-center justify-between gap-3 px-3.5 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors" :href="equipmentShowRoute(log)" target="_blank">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="font-medium text-xs sm:text-sm text-gray-900 dark:text-white truncate">{{ log?.equipment?.name ?? 'Unknown Equipment' }}</p>
                                        <span class="text-[0.65rem] uppercase px-1.5 py-0.5 rounded-full inline-block" :class="equipmentStatusBadge(log.status).className">{{ equipmentStatusBadge(log.status).label }}</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-slate-400 truncate">{{ formatPersonnelName(log?.personnel) }}</p>
                                </div>
                                <div class="text-right shrink-0 text-[0.65rem] sm:text-xs text-gray-500 dark:text-slate-400">
                                    <p class="flex flex-col">
                                        <span>Started {{ formatDateTime(log?.started_at) }}</span>
                                        <span v-if="log?.actual_end_at" class="mt-0.5 font-medium text-emerald-600 dark:text-emerald-400">Ended {{ formatDateTime(log.actual_end_at) }}</span>
                                        <span v-else-if="log?.end_use_at" class="mt-0.5 font-medium text-amber-600 dark:text-amber-400">Due {{ formatDateTime(log.end_use_at) }}</span>
                                    </p>
                                </div>
                            </a>
                            <div v-if="!recentEquipmentLogs?.length" class="px-5 py-6 text-center text-xs sm:text-sm text-gray-500 dark:text-slate-400">
                                <LuMicroscope class="w-10 h-10 mx-auto mb-2 opacity-20" /><p>No recent equipment logs</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
