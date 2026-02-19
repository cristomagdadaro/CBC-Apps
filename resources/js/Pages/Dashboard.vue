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
            if (this.eventsChartInstance) {
                this.eventsChartInstance.destroy();
                this.eventsChartInstance = null;
            }
            if (this.accessChartInstance) {
                this.accessChartInstance.destroy();
                this.accessChartInstance = null;
            }
            if (this.inventoryChartInstance) {
                this.inventoryChartInstance.destroy();
                this.inventoryChartInstance = null;
            }
            if (this.vehicleChartInstance) {
                this.vehicleChartInstance.destroy();
                this.vehicleChartInstance = null;
            }
            if (this.venueChartInstance) {
                this.venueChartInstance.destroy();
                this.venueChartInstance = null;
            }
            if (this.labChartInstance) {
                this.labChartInstance.destroy();
                this.labChartInstance = null;
            }
        },
        buildCharts() {
            this.destroyCharts();

            const eventsCanvas = this.$refs.eventsChartCanvas;
            const accessCanvas = this.$refs.accessChartCanvas;
            const inventoryCanvas = this.$refs.inventoryChartCanvas;

            if (eventsCanvas) {
                this.eventsChartInstance = new Chart(eventsCanvas, {
                    type: 'pie',
                    data: {
                        labels: ['Active', 'Upcoming', 'Suspended', 'Expired'],
                        datasets: [{
                            label: 'Event Forms',
                            data: [this.stats.events.active, this.stats.events.upcoming, this.stats.events.suspended, this.stats.events.expired],
                            backgroundColor: this.eventColors,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false, position: 'bottom', labels: { usePointStyle: true, color: (ctx) => { const index = ctx.dataIndex ?? 0; return this.eventColors[index] || '#6b7280'; } } },
                            tooltip: { callbacks: { labelColor: (ctx) => { const index = ctx.dataIndex ?? 0; return { borderColor: this.eventColors[index], backgroundColor: this.eventColors[index] }; } } },
                        },
                        scales: {},
                    },
                });
            }

            if (accessCanvas) {
                this.accessChartInstance = new Chart(accessCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Approved', 'Rejected'],
                        datasets: [{
                            label: 'FES Requests',
                            data: [this.stats.access_requests.pending, this.stats.access_requests.approved, this.stats.access_requests.rejected],
                            backgroundColor: this.accessColors,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false, position: 'bottom', labels: { usePointStyle: true, color: (ctx) => { const index = ctx.dataIndex ?? 0; return this.accessColors[index] || '#6b7280'; } } },
                            tooltip: { callbacks: { labelColor: (ctx) => { const index = ctx.dataIndex ?? 0; return { borderColor: this.accessColors[index], backgroundColor: this.accessColors[index] }; } } },
                        },
                        scales: {
                            x: { grid: { display: false, drawBorder: false }, ticks: { color: (ctx) => this.accessColors[ctx.index] || '#6b7280' }, border: { display: false } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { display: false }, border: { display: false } },
                        },
                    },
                });
            }

            if (inventoryCanvas) {
                const buckets = this.stats.inventory.stock_buckets || { empty: 0, low: 0, mid: 0, high: 0 };
                this.inventoryChartInstance = new Chart(inventoryCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Empty', 'Low', 'Mid', 'High'],
                        datasets: [{
                            label: 'Items by Stock Level',
                            data: [buckets.empty, buckets.low, buckets.mid, buckets.high],
                            backgroundColor: this.invColors,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false, position: 'bottom', labels: { usePointStyle: true, color: (ctx) => { const index = ctx.dataIndex ?? 0; return this.invColors[index] || '#6b7280'; } } },
                            tooltip: { callbacks: { labelColor: (ctx) => { const index = ctx.dataIndex ?? 0; return { borderColor: this.invColors[index], backgroundColor: this.invColors[index] }; } } },
                        },
                        scales: {
                            x: { grid: { display: false, drawBorder: false }, ticks: { color: (ctx) => this.invColors[ctx.index] || '#6b7280' }, border: { display: false } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { display: false }, border: { display: false } },
                        },
                    },
                });
            }

            const vehicleCanvas = this.$refs.vehicleChartCanvas;
            if (vehicleCanvas) {
                this.vehicleChartInstance = new Chart(vehicleCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Approved', 'Completed', 'Rejected'],
                        datasets: [{
                            label: 'Vehicle Rentals',
                            data: [this.stats.vehicle_rentals.pending, this.stats.vehicle_rentals.approved, this.stats.vehicle_rentals.completed, this.stats.vehicle_rentals.rejected],
                            backgroundColor: this.rentalColors,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false, position: 'bottom', labels: { usePointStyle: true, color: (ctx) => { const index = ctx.dataIndex ?? 0; return this.rentalColors[index] || '#6b7280'; } } },
                            tooltip: { callbacks: { labelColor: (ctx) => { const index = ctx.dataIndex ?? 0; return { borderColor: this.rentalColors[index], backgroundColor: this.rentalColors[index] }; } } },
                        },
                        scales: {
                            x: { grid: { display: false, drawBorder: false }, ticks: { color: (ctx) => this.rentalColors[ctx.index] || '#6b7280' }, border: { display: false } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { display: false }, border: { display: false } },
                        },
                    },
                });
            }

            const venueCanvas = this.$refs.venueChartCanvas;
            if (venueCanvas) {
                this.venueChartInstance = new Chart(venueCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Approved', 'Completed', 'Rejected'],
                        datasets: [{
                            label: 'Venue Rentals',
                            data: [this.stats.venue_rentals.pending, this.stats.venue_rentals.approved, this.stats.venue_rentals.completed, this.stats.venue_rentals.rejected],
                            backgroundColor: this.rentalColors,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false, position: 'bottom', labels: { usePointStyle: true, color: (ctx) => { const index = ctx.dataIndex ?? 0; return this.rentalColors[index] || '#6b7280'; } } },
                            tooltip: { callbacks: { labelColor: (ctx) => { const index = ctx.dataIndex ?? 0; return { borderColor: this.rentalColors[index], backgroundColor: this.rentalColors[index] }; } } },
                        },
                        scales: {
                            x: { grid: { display: false, drawBorder: false }, ticks: { color: (ctx) => this.rentalColors[ctx.index] || '#6b7280' }, border: { display: false } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { display: false }, border: { display: false } },
                        },
                    },
                });
            }

            const labCanvas = this.$refs.labChartCanvas;
            if (labCanvas) {
                this.labChartInstance = new Chart(labCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Active', 'Overdue', 'Completed'],
                        datasets: [{
                            label: 'Lab Equipment Logs',
                            data: [this.stats.laboratory_equipment.active, this.stats.laboratory_equipment.overdue, this.stats.laboratory_equipment.completed],
                            backgroundColor: this.labColors,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false, position: 'bottom', labels: { usePointStyle: true, color: (ctx) => { const index = ctx.dataIndex ?? 0; return this.labColors[index] || '#6b7280'; } } },
                            tooltip: { callbacks: { labelColor: (ctx) => { const index = ctx.dataIndex ?? 0; return { borderColor: this.labColors[index], backgroundColor: this.labColors[index] }; } } },
                        },
                        scales: {
                            x: { grid: { display: false, drawBorder: false }, ticks: { color: (ctx) => this.labColors[ctx.index] || '#6b7280' }, border: { display: false } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { display: false }, border: { display: false } },
                        },
                    },
                });
            }
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

        <div class="py-6">
            <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary cards -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Event forms summary -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Event Forms</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.events.total }}</p>
                            <dl class="mt-2 grid grid-cols-4 text-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <div :class="'text-[' + eventColors[0] + ']'">
                                    <dt>Active</dt>
                                    <dd class="font-semibold">{{ stats.events.active }}</dd>
                                </div>
                                <div :class="'text-[' + eventColors[1] + ']'">
                                    <dt>Upcoming</dt>
                                    <dd class="font-semibold">{{ stats.events.upcoming }}</dd>
                                </div>
                                <div :class="'text-[' + eventColors[2] + ']'">
                                    <dt>Suspended</dt>
                                    <dd class="font-semibold">{{ stats.events.suspended }}</dd>
                                </div>
                                <div :class="'text-[' + eventColors[3] + ']'">
                                    <dt>Expired</dt>
                                    <dd class="font-semibold">{{ stats.events.expired }}</dd>
                                </div>
                            </dl>
                            <div class="mt-4 h-32">
                                <canvas ref="eventsChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Link :href="route('forms.index')" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                View event forms
                            </Link>
                        </div>
                    </div>

                    <!-- FES requests summary -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">FES Requests</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.access_requests.total }}</p>
                            <dl class="mt-2 grid grid-cols-3 text-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <div :class="'text-[' + accessColors[0] + ']'">
                                    <dt>Pending</dt>
                                    <dd class="font-semibold">{{ stats.access_requests.pending }}</dd>
                                </div>
                                <div :class="'text-[' + accessColors[2] + ']'">
                                    <dt>Approved</dt>
                                    <dd class="font-semibold">{{ stats.access_requests.approved }}</dd>
                                </div>
                                <div :class="'text-[' + accessColors[1] + ']'">
                                    <dt>Rejected</dt>
                                    <dd class="font-semibold">{{ stats.access_requests.rejected }}</dd>
                                </div>
                            </dl>
                            <div class="mt-4 h-32">
                                <canvas ref="accessChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Link :href="route('accessUseRequest.index')" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                View requests
                            </Link>
                        </div>
                    </div>

                    <!-- Inventory summary -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Inventory</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.inventory.items }}</p>
                            <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <div>
                                    <dt>Transactions today</dt>
                                    <dd class="font-semibold">{{ stats.inventory.transactions_today }}</dd>
                                </div>
                                <div :class="'text-[' + invColors[0] + ']'">
                                    <dt>Empty stock</dt>
                                    <dd class="font-semibold">{{ stats.inventory.stock_buckets.empty }}</dd>
                                </div>
                                <div :class="'text-[' + invColors[1] + ']'">
                                    <dt>Low stock (≤ 25%)</dt>
                                    <dd class="font-semibold">{{ stats.inventory.stock_buckets.low }}</dd>
                                </div>
                                <div :class="'text-[' + invColors[2] + ']'">
                                    <dt>Mid stock (25–75%)</dt>
                                    <dd class="font-semibold">{{ stats.inventory.stock_buckets.mid }}</dd>
                                </div>
                                <div :class="'text-[' + invColors[3] + ']'">
                                    <dt>High stock (&gt; 75%)</dt>
                                    <dd class="font-semibold">{{ stats.inventory.stock_buckets.high }}</dd>
                                </div>
                            </dl>
                            <div class="mt-4 h-32">
                                <canvas ref="inventoryChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between text-xs">
                            <Link :href="route('items.index')" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                View items
                            </Link>
                            <Link :href="route('transactions.index')" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                View transactions
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Rentals, Venue Rentals, and Lab Equipment cards -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- Vehicle rentals summary -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Vehicle Rentals</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.vehicle_rentals.total }}</p>
                            <dl class="mt-2 grid grid-cols-4 text-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <div :class="'text-[' + rentalColors[0] + ']'">
                                    <dt>Pending</dt>
                                    <dd class="font-semibold">{{ stats.vehicle_rentals.pending }}</dd>
                                </div>
                                <div :class="'text-[' + rentalColors[1] + ']'">
                                    <dt>Approved</dt>
                                    <dd class="font-semibold">{{ stats.vehicle_rentals.approved }}</dd>
                                </div>
                                <div :class="'text-[' + rentalColors[2] + ']'">
                                    <dt>Completed</dt>
                                    <dd class="font-semibold">{{ stats.vehicle_rentals.completed }}</dd>
                                </div>
                                <div :class="'text-[' + rentalColors[3] + ']'">
                                    <dt>Rejected</dt>
                                    <dd class="font-semibold">{{ stats.vehicle_rentals.rejected }}</dd>
                                </div>
                            </dl>
                            <div class="mt-4 h-32">
                                <canvas ref="vehicleChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Link :href="route('rentals.vehicle.index')" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                View rentals
                            </Link>
                        </div>
                    </div>

                    <!-- Venue rentals summary -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue Rentals</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.venue_rentals.total }}</p>
                            <dl class="mt-2 grid grid-cols-4 text-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <div :class="'text-[' + rentalColors[0] + ']'">
                                    <dt>Pending</dt>
                                    <dd class="font-semibold">{{ stats.venue_rentals.pending }}</dd>
                                </div>
                                <div :class="'text-[' + rentalColors[1] + ']'">
                                    <dt>Approved</dt>
                                    <dd class="font-semibold">{{ stats.venue_rentals.approved }}</dd>
                                </div>
                                <div :class="'text-[' + rentalColors[2] + ']'">
                                    <dt>Completed</dt>
                                    <dd class="font-semibold">{{ stats.venue_rentals.completed }}</dd>
                                </div>
                                <div :class="'text-[' + rentalColors[3] + ']'">
                                    <dt>Rejected</dt>
                                    <dd class="font-semibold">{{ stats.venue_rentals.rejected }}</dd>
                                </div>
                            </dl>
                            <div class="mt-4 h-32">
                                <canvas ref="venueChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Link :href="route('rentals.venue.index')" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                View rentals
                            </Link>
                        </div>
                    </div>

                    <!-- Lab equipment logger summary -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Lab Equipment Logger</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.laboratory_equipment.total }}</p>
                            <dl class="mt-2 grid grid-cols-3 text-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <div :class="'text-[' + labColors[0] + ']'">
                                    <dt>Active</dt>
                                    <dd class="font-semibold">{{ stats.laboratory_equipment.active }}</dd>
                                </div>
                                <div :class="'text-[' + labColors[1] + ']'">
                                    <dt>Overdue</dt>
                                    <dd class="font-semibold">{{ stats.laboratory_equipment.overdue }}</dd>
                                </div>
                                <div :class="'text-[' + labColors[2] + ']'">
                                    <dt>Completed</dt>
                                    <dd class="font-semibold">{{ stats.laboratory_equipment.completed }}</dd>
                                </div>
                            </dl>
                            <div class="mt-4 h-32">
                                <canvas ref="labChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Link :href="route('laboratory.dashboard')" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                View logs
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick navigation panels -->
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Events &amp; Attendance</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link :href="route('forms.create')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Create new event form
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('forms.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Manage event forms
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('forms.scan')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Scan participant attendance
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Access &amp; Use Requests</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link :href="route('accessUseRequest.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Review and approve requests
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('inventory.public.outgoing.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Public outgoing inventory form
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Rentals</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link :href="route('rentals.vehicle.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Vehicle rentals
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('rentals.venue.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Venue rentals
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('rentals.calendar.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Calendar view
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Laboratory</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link :href="route('laboratory.dashboard')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Equipment logs
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('laboratory.dashboard')" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    View dashboard
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Transactions Recent Ativities -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Recent Transactions</h3>
                    <div class="flex flex-col text-sm gap-1">
                        <Link v-for="transaction in $page.props.recentTransactions" :key="transaction.id" :href="route('transactions.show', transaction.id)" class="py-2 rounded-md px-2 hover:opacity-75 duration-200" :class="transaction.transac_type === 'incoming' ? 'bg-green-200':'bg-red-200'">
                            <p>
                                <span class="font-medium">{{ transaction?.item?.name }}</span>
                                <span>{{ transaction.type }}</span>
                                &mdash;
                                <span class="font-medium">{{ transaction?.quantity }}</span>
                                {{ transaction?.unit }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                By
                                <span class="font-medium">
                                    {{ transaction?.personnel ? transaction?.personnel?.fname + ' ' + transaction?.personnel?.lname : 'Unknown' }}
                                </span>
                                on
                                <span>{{ new Date(transaction?.created_at).toLocaleString() }}</span>
                            </p>
                        </Link>
                        <Link :href="route('transactions.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                            View all transactions
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
