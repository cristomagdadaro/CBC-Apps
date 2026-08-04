<script>
import { Link } from '@inertiajs/vue3';
import {
    Chart,
    PieController,
    BarController,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(BarController, PieController, BarElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend);

export default {
    name: 'Dashboard',
    components: { Link },
    data() {
        return {
            eventsChartInstance: null,
            accessChartInstance: null,
            inventoryChartInstance: null,
            vehicleChartInstance: null,
            venueChartInstance: null,
            labChartInstance: null,
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
    },
    watch: {
        stats: {
            handler() {
                this.buildCharts();
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
            ['events', 'access', 'inventory', 'vehicle', 'venue', 'lab'].forEach(key => {
                const instance = this[`${key}ChartInstance`];
                if (instance) {
                    instance.destroy();
                    this[`${key}ChartInstance`] = null;
                }
            });
        },
        buildCharts() {
            this.destroyCharts();

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

                this[config.key] = new Chart(canvas, {
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
                });
            });
        },
        formatPersonnelName(personnel) {
            if (!personnel) return 'Unknown';

            const parts = [
                personnel.fname,
                personnel.mname,
                personnel.lname,
                personnel.suffix,
            ]
                .filter(Boolean)
                .map((value) => String(value).trim())
                .filter(Boolean);

            return parts.length ? parts.join(' ') : 'Unknown';
        },
        formatDateTime(value) {
            if (!value) return 'N/A';

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return 'N/A';
            }

            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
        equipmentStatusBadge(status) {
            const normalized = String(status || '').toLowerCase();

            if (normalized === 'overdue') {
                return {
                    label: 'Overdue',
                    className: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                };
            }

            if (normalized === 'completed') {
                return {
                    label: 'Completed',
                    className: 'bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-300',
                };
            }

            return {
                label: 'Active',
                className: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
            };
        },
        equipmentShowRoute(log) {
            const categoryId = Number(log?.equipment?.category_id ?? 0);
            const equipmentId = log?.equipment?.id ?? log?.equipment_id;

            if (!equipmentId) {
                return '#';
            }

            const routeName = categoryId === 4
                ? 'ict.equipments.show'
                : 'laboratory.equipments.show';

            return route(routeName, equipmentId);
        },
    },
    mounted() {
        this.buildCharts();
    },
    beforeUnmount() {
        this.destroyCharts();
    },
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <ActionHeaderLayout title="Dashboard" subtitle="Overview of system statistics and quick access to key sections." :route-link="route('dashboard')" />
        </template>

        <div class="py-3 sm:py-6">
            <div class="px-2.5 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
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

                <!-- Summary Stats Grid -->
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
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ stats.events.active }} Active</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ stats.events.upcoming }} Upcoming</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ stats.events.suspended }} Suspended</span>
                                </div>
                            </div>

                            <div class="mt-3 sm:mt-4 h-24 sm:h-28">
                                <canvas ref="eventsChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('forms.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                View all events
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
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
                                <div class="flex items-center gap-1">
                                    <LuClock class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                    <span class="text-slate-600 dark:text-slate-300">{{ stats.access_requests.pending }} Pending</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <LuCheckCircle class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                                    <span class="text-slate-600 dark:text-slate-300">{{ stats.access_requests.approved }} Approved</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <LuXCircle class="w-3.5 h-3.5 text-rose-500 shrink-0" />
                                    <span class="text-slate-600 dark:text-slate-300">{{ stats.access_requests.rejected }} Rejected</span>
                                </div>
                            </div>

                            <div class="mt-3 sm:mt-4 h-24 sm:h-28">
                                <canvas ref="accessChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('accessUseRequest.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                Review requests
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
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
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-slate-100 dark:bg-slate-800/60 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ stats.inventory.stock_buckets?.empty ?? 0 }} Empty</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-orange-50 dark:bg-orange-950/40 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                    <span class="text-orange-700 dark:text-orange-300">{{ stats.inventory.stock_buckets?.low ?? 0 }} Low</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span class="text-blue-700 dark:text-blue-300">{{ stats.inventory.stock_buckets?.mid ?? 0 }} Mid</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-emerald-50 dark:bg-emerald-950/40 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-emerald-700 dark:text-emerald-300">{{ stats.inventory.stock_buckets?.high ?? 0 }} High</span>
                                </div>
                            </div>

                            <div class="mt-3 sm:mt-4 h-24 sm:h-28">
                                <canvas ref="inventoryChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800 flex justify-between">
                            <Link :href="route('items.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                View items
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                            <Link :href="route('transactions.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors">
                                <LuArrowLeftRight class="w-4 h-4" />
                                Transactions
                            </Link>
                        </div>
                    </div>

                    <!-- Vehicle Rentals Card -->
                    <div v-if="dashboardAccess.rentals" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-amber-500/10 dark:bg-amber-400/15 rounded-xl border border-amber-500/20">
                                        <LuCar class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Vehicle Rentals</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.vehicle_rentals.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('rentals.vehicle.index')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>

                            <div class="mt-3.5 sm:mt-4 grid grid-cols-4 gap-1 text-center text-[0.65rem] sm:text-xs font-medium">
                                <div class="p-1 rounded-md bg-amber-50 dark:bg-amber-950/40">
                                    <p class="font-bold text-amber-700 dark:text-amber-300">{{ stats.vehicle_rentals.pending }}</p>
                                    <p class="text-amber-600/80 dark:text-amber-400/80">Pending</p>
                                </div>
                                <div class="p-1 rounded-md bg-green-50 dark:bg-green-950/40">
                                    <p class="font-bold text-green-700 dark:text-green-300">{{ stats.vehicle_rentals.approved }}</p>
                                    <p class="text-green-600/80 dark:text-green-400/80">Approved</p>
                                </div>
                                <div class="p-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40">
                                    <p class="font-bold text-emerald-700 dark:text-emerald-300">{{ stats.vehicle_rentals.completed }}</p>
                                    <p class="text-emerald-600/80 dark:text-emerald-400/80">Done</p>
                                </div>
                                <div class="p-1 rounded-md bg-rose-50 dark:bg-rose-950/40">
                                    <p class="font-bold text-rose-700 dark:text-rose-300">{{ stats.vehicle_rentals.rejected }}</p>
                                    <p class="text-rose-600/80 dark:text-rose-400/80">Rejected</p>
                                </div>
                            </div>

                            <div class="mt-3 sm:mt-4 h-24 sm:h-28">
                                <canvas ref="vehicleChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('rentals.vehicle.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                Manage bookings
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Venue Rentals Card -->
                    <div v-if="dashboardAccess.rentals" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-purple-500/10 dark:bg-purple-400/15 rounded-xl border border-purple-500/20">
                                        <LuBuilding class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Venue Rentals</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.venue_rentals.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('rentals.venue.index')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>

                            <div class="mt-3.5 sm:mt-4 grid grid-cols-4 gap-1 text-center text-[0.65rem] sm:text-xs font-medium">
                                <div class="p-1 rounded-md bg-amber-50 dark:bg-amber-950/40">
                                    <p class="font-bold text-amber-700 dark:text-amber-300">{{ stats.venue_rentals.pending }}</p>
                                    <p class="text-amber-600/80 dark:text-amber-400/80">Pending</p>
                                </div>
                                <div class="p-1 rounded-md bg-green-50 dark:bg-green-950/40">
                                    <p class="font-bold text-green-700 dark:text-green-300">{{ stats.venue_rentals.approved }}</p>
                                    <p class="text-green-600/80 dark:text-green-400/80">Approved</p>
                                </div>
                                <div class="p-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40">
                                    <p class="font-bold text-emerald-700 dark:text-emerald-300">{{ stats.venue_rentals.completed }}</p>
                                    <p class="text-emerald-600/80 dark:text-emerald-400/80">Done</p>
                                </div>
                                <div class="p-1 rounded-md bg-rose-50 dark:bg-rose-950/40">
                                    <p class="font-bold text-rose-700 dark:text-rose-300">{{ stats.venue_rentals.rejected }}</p>
                                    <p class="text-rose-600/80 dark:text-rose-400/80">Rejected</p>
                                </div>
                            </div>

                            <div class="mt-3 sm:mt-4 h-24 sm:h-28">
                                <canvas ref="venueChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('rentals.venue.index')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                Manage venues
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Lab Equipment Card -->
                    <div v-if="dashboardAccess.laboratory" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-indigo-500/10 dark:bg-indigo-400/15 rounded-xl border border-indigo-500/20">
                                        <LuMicroscope class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Lab Equipment</p>
                                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-0.5 tracking-tight">{{ stats.laboratory_equipment.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('equipment-logger.dashboard')" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>

                            <div class="mt-3.5 sm:mt-4 flex items-center justify-around text-[0.7rem] sm:text-xs font-medium">
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="flex items-center gap-1 px-2.5 py-1 bg-green-50 dark:bg-green-950/40 rounded-full">
                                        <LuActivity class="w-3 h-3 text-green-500" />
                                        <span class="font-bold text-green-700 dark:text-green-300">{{ stats.laboratory_equipment.active }}</span>
                                    </div>
                                    <span class="text-slate-500 dark:text-slate-400 mt-0.5">Active</span>
                                </div>
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="flex items-center gap-1 px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 rounded-full">
                                        <LuAlertTriangle class="w-3 h-3 text-amber-500" />
                                        <span class="font-bold text-amber-700 dark:text-amber-300">{{ stats.laboratory_equipment.overdue }}</span>
                                    </div>
                                    <span class="text-slate-500 dark:text-slate-400 mt-0.5">Overdue</span>
                                </div>
                                <div class="flex flex-col items-center gap-0.5">
                                    <div class="flex items-center gap-1 px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-full">
                                        <LuCheckCircle class="w-3 h-3 text-blue-500" />
                                        <span class="font-bold text-blue-700 dark:text-blue-300">{{ stats.laboratory_equipment.completed }}</span>
                                    </div>
                                    <span class="text-slate-500 dark:text-slate-400 mt-0.5">Done</span>
                                </div>
                            </div>

                            <div class="mt-3 sm:mt-4 h-24 sm:h-28">
                                <canvas ref="labChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-800">
                            <Link :href="route('equipment-logger.dashboard')" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                View logs
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div v-if="hasQuickActions" class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
                    <Link v-if="dashboardAccess.events" :href="route('forms.create')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-blue-500/40 dark:hover:border-blue-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-blue-500/10 dark:bg-blue-400/15 rounded-xl border border-blue-500/20 group-hover:scale-105 transition-transform">
                                <LuCalendarPlus class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Create Event</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">New event form</p>
                    </Link>

                    <Link v-if="dashboardAccess.events" :href="route('forms.scan')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-emerald-500/40 dark:hover:border-emerald-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-emerald-500/10 dark:bg-emerald-400/15 rounded-xl border border-emerald-500/20 group-hover:scale-105 transition-transform">
                                <LuQrCode class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Scan QR</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Check attendance</p>
                    </Link>

                    <Link v-if="dashboardAccess.rentals" :href="route('rentals.vehicle.index')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-amber-500/40 dark:hover:border-amber-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-amber-500/10 dark:bg-amber-400/15 rounded-xl border border-amber-500/20 group-hover:scale-105 transition-transform">
                                <LuClipboardList class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Bookings</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Rentals & venues</p>
                    </Link>

                    <Link v-if="dashboardAccess.laboratory" :href="route('equipment-logger.dashboard')" class="group bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-800 p-4 sm:p-5 hover:shadow-md hover:border-indigo-500/40 dark:hover:border-indigo-400/40 hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-indigo-500/10 dark:bg-indigo-400/15 rounded-xl border border-indigo-500/20 group-hover:scale-105 transition-transform">
                                <LuFlaskConical class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-bold text-sm sm:text-base text-slate-900 dark:text-white">Laboratory</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Equipment logs</p>
                    </Link>
                </div>

                <!-- Recent Activity Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Recent Transactions Section -->
                    <div v-if="dashboardAccess.inventory" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200/80 dark:border-slate-800 overflow-hidden">
                        <div class="px-3.5 sm:px-5 py-3 sm:py-4 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <LuActivity class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                                <h3 class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">Recent Transactions</h3>
                            </div>
                            <Link :href="route('transactions.index')" class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                                View all
                            </Link>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-slate-800">
                            <Link
                                v-for="transaction in recentTransactions"
                                :key="transaction.id"
                                :href="route('transactions.show', transaction.id)"
                                class="flex items-center justify-between px-3.5 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group"
                            >
                                <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                                    <div
                                        class="p-2 rounded-full shrink-0 hidden sm:block"
                                        :class="transaction.transac_type === 'incoming' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'"
                                    >
                                        <LuArrowDownLeft
                                            v-if="transaction.transac_type === 'incoming'"
                                            class="w-4 h-4"
                                        />
                                        <LuArrowUpRight
                                            v-else
                                            class="w-4 h-4"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                                            {{ transaction?.item?.name ?? 'Unknown Item' }}
                                            <span
                                                class="text-[0.65rem] font-normal uppercase px-1.5 py-0.5 rounded-full ml-1.5 inline-block"
                                                :class="transaction.transac_type === 'incoming' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'"
                                            >
                                                {{ transaction.transac_type }}
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5 truncate">
                                            {{ transaction?.quantity }} {{ transaction?.unit }} by
                                            <span class="font-medium text-gray-700 dark:text-slate-300">
                                                {{ transaction?.personnel ? `${transaction.personnel.fname} ${transaction.personnel.lname}` : 'Unknown' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <p class="text-[0.7rem] sm:text-xs text-gray-500 dark:text-slate-400">
                                        {{ new Date(transaction?.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
                                    </p>
                                    <p class="text-[0.65rem] text-gray-400 dark:text-slate-500 mt-0.5">
                                        {{ new Date(transaction?.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>
                            </Link>

                            <div v-if="!recentTransactions?.length" class="px-5 py-6 text-center text-xs sm:text-sm text-gray-500 dark:text-slate-400">
                                <LuPackage class="w-10 h-10 mx-auto mb-2 opacity-20" />
                                <p>No recent transactions</p>
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
                            <Link :href="route('equipment-logger.dashboard')" class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                                View all
                            </Link>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-slate-800">
                            <a
                                v-for="log in recentEquipmentLogs"
                                :key="log.id"
                                class="flex items-center justify-between gap-3 px-3.5 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors"
                                :href="equipmentShowRoute(log)"
                                target="_blank"
                            >
                                <div
                                    class="p-2 rounded-full shrink-0 hidden sm:block"
                                    :class="equipmentStatusBadge(log.status).className"
                                >
                                    <LuTimer
                                        v-if="log.status === 'active'"
                                        class="w-4 h-4"
                                    />
                                    <LuCircleCheckIcon
                                        v-else-if="log.status === 'completed'"
                                        class="w-4 h-4"
                                    />
                                    <LuTriangleAlertIcon
                                        v-else
                                        class="w-4 h-4"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="font-medium text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                                            {{ log?.equipment?.name ?? 'Unknown Equipment' }}
                                        </p>
                                        <span
                                            class="text-[0.65rem] uppercase px-1.5 py-0.5 rounded-full inline-block"
                                            :class="equipmentStatusBadge(log.status).className"
                                        >
                                            {{ equipmentStatusBadge(log.status).label }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-slate-400 truncate">
                                        {{ formatPersonnelName(log?.personnel) }}
                                        <span v-if="log?.purpose"> - {{ log.purpose }}</span>
                                    </p>
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
                                <LuMicroscope class="w-10 h-10 mx-auto mb-2 opacity-20" />
                                <p>No recent equipment logs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
