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
            <div class="sm:px-6 lg:px-8 space-y-6">
                
                <!-- Summary Stats Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    
                    <!-- Event Forms Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        <LuCalendar class="w-5 h-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Event Forms</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ stats.events.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('forms.index')" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>
                            
                            <div class="mt-4 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="text-gray-600 dark:text-gray-300">{{ stats.events.active }} Active</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span class="text-gray-600 dark:text-gray-300">{{ stats.events.upcoming }} Upcoming</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    <span class="text-gray-600 dark:text-gray-300">{{ stats.events.suspended }} Suspended</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 h-28">
                                <canvas ref="eventsChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                            <Link :href="route('forms.index')" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                View all events
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- FES Requests Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                        <LuShield class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">FES Requests</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ stats.access_requests.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('accessUseRequest.index')" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>
                            
                            <div class="mt-4 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <LuClock class="w-3.5 h-3.5 text-yellow-500" />
                                    <span class="text-gray-600 dark:text-gray-300">{{ stats.access_requests.pending }} Pending</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <LuCheckCircle class="w-3.5 h-3.5 text-green-500" />
                                    <span class="text-gray-600 dark:text-gray-300">{{ stats.access_requests.approved }} Approved</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <LuXCircle class="w-3.5 h-3.5 text-red-500" />
                                    <span class="text-gray-600 dark:text-gray-300">{{ stats.access_requests.rejected }} Rejected</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 h-28">
                                <canvas ref="accessChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                            <Link :href="route('accessUseRequest.index')" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                Review requests
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Inventory Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <LuPackage class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Inventory Items</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ stats.inventory.items }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Link :href="route('items.index')" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <LuArrowRight class="w-5 h-5" />
                                    </Link>
                                </div>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center gap-1.5 px-2 py-1.5 bg-gray-50 dark:bg-gray-700/50 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                    <span class="text-gray-600 dark:text-gray-300">{{ stats.inventory.stock_buckets?.empty ?? 0 }} Empty</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-2 py-1.5 bg-orange-50 dark:bg-orange-900/20 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                    <span class="text-orange-700 dark:text-orange-300">{{ stats.inventory.stock_buckets?.low ?? 0 }} Low</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-2 py-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span class="text-blue-700 dark:text-blue-300">{{ stats.inventory.stock_buckets?.mid ?? 0 }} Mid</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-2 py-1.5 bg-green-50 dark:bg-green-900/20 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    <span class="text-green-700 dark:text-green-300">{{ stats.inventory.stock_buckets?.high ?? 0 }} High</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 h-28">
                                <canvas ref="inventoryChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                            <Link :href="route('items.index')" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                View items
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                            <Link :href="route('transactions.index')" class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                                <LuArrowLeftRight class="w-4 h-4" />
                                Transactions
                            </Link>
                        </div>
                    </div>

                    <!-- Vehicle Rentals Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                                        <LuCar class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Vehicle Rentals</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ stats.vehicle_rentals.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('rental.bookings.guest')" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-4 gap-1 text-center text-xs">
                                <div class="p-1.5 rounded-md bg-yellow-50 dark:bg-yellow-900/20">
                                    <p class="font-semibold text-yellow-700 dark:text-yellow-300">{{ stats.vehicle_rentals.pending }}</p>
                                    <p class="text-yellow-600/70 dark:text-yellow-400/70">Pending</p>
                                </div>
                                <div class="p-1.5 rounded-md bg-green-50 dark:bg-green-900/20">
                                    <p class="font-semibold text-green-700 dark:text-green-300">{{ stats.vehicle_rentals.approved }}</p>
                                    <p class="text-green-600/70 dark:text-green-400/70">Approved</p>
                                </div>
                                <div class="p-1.5 rounded-md bg-emerald-50 dark:bg-emerald-900/20">
                                    <p class="font-semibold text-emerald-700 dark:text-emerald-300">{{ stats.vehicle_rentals.completed }}</p>
                                    <p class="text-emerald-600/70 dark:text-emerald-400/70">Done</p>
                                </div>
                                <div class="p-1.5 rounded-md bg-red-50 dark:bg-red-900/20">
                                    <p class="font-semibold text-red-700 dark:text-red-300">{{ stats.vehicle_rentals.rejected }}</p>
                                    <p class="text-red-600/70 dark:text-red-400/70">Rejected</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 h-28">
                                <canvas ref="vehicleChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                            <Link :href="route('rental.bookings.guest')" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                Manage bookings
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Venue Rentals Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                        <LuBuilding class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue Rentals</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ stats.venue_rentals.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('rental.bookings.guest')" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-4 gap-1 text-center text-xs">
                                <div class="p-1.5 rounded-md bg-yellow-50 dark:bg-yellow-900/20">
                                    <p class="font-semibold text-yellow-700 dark:text-yellow-300">{{ stats.venue_rentals.pending }}</p>
                                    <p class="text-yellow-600/70 dark:text-yellow-400/70">Pending</p>
                                </div>
                                <div class="p-1.5 rounded-md bg-green-50 dark:bg-green-900/20">
                                    <p class="font-semibold text-green-700 dark:text-green-300">{{ stats.venue_rentals.approved }}</p>
                                    <p class="text-green-600/70 dark:text-green-400/70">Approved</p>
                                </div>
                                <div class="p-1.5 rounded-md bg-emerald-50 dark:bg-emerald-900/20">
                                    <p class="font-semibold text-emerald-700 dark:text-emerald-300">{{ stats.venue_rentals.completed }}</p>
                                    <p class="text-emerald-600/70 dark:text-emerald-400/70">Done</p>
                                </div>
                                <div class="p-1.5 rounded-md bg-red-50 dark:bg-red-900/20">
                                    <p class="font-semibold text-red-700 dark:text-red-300">{{ stats.venue_rentals.rejected }}</p>
                                    <p class="text-red-600/70 dark:text-red-400/70">Rejected</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 h-28">
                                <canvas ref="venueChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                            <Link :href="route('rental.bookings.guest')" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                Manage venues
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- Lab Equipment Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                        <LuMicroscope class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lab Equipment</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ stats.laboratory_equipment.total }}</p>
                                    </div>
                                </div>
                                <Link :href="route('laboratory.dashboard')" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <LuArrowRight class="w-5 h-5" />
                                </Link>
                            </div>
                            
                            <div class="mt-4 flex items-center justify-around text-xs">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 rounded-full">
                                        <LuActivity class="w-3.5 h-3.5 text-green-500" />
                                        <span class="font-medium text-green-700 dark:text-green-300">{{ stats.laboratory_equipment.active }}</span>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400">Active</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 dark:bg-amber-900/20 rounded-full">
                                        <LuAlertTriangle class="w-3.5 h-3.5 text-amber-500" />
                                        <span class="font-medium text-amber-700 dark:text-amber-300">{{ stats.laboratory_equipment.overdue }}</span>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400">Overdue</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-full">
                                        <LuCheckCircle class="w-3.5 h-3.5 text-blue-500" />
                                        <span class="font-medium text-blue-700 dark:text-blue-300">{{ stats.laboratory_equipment.completed }}</span>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400">Done</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 h-28">
                                <canvas ref="labChartCanvas"></canvas>
                            </div>
                        </div>
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                            <Link :href="route('laboratory.dashboard')" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                View logs
                                <LuChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Link :href="route('forms.create')" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-800 transition-all duration-200">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors">
                                <LuCalendarPlus class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-semibold text-gray-900 dark:text-white">Create Event</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">New event form</p>
                    </Link>

                    <Link :href="route('forms.scan')" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-green-200 dark:hover:border-green-800 transition-all duration-200">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-green-50 dark:bg-green-900/20 rounded-lg group-hover:bg-green-100 dark:group-hover:bg-green-900/30 transition-colors">
                                <LuQrCode class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-semibold text-gray-900 dark:text-white">Scan QR</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check attendance</p>
                    </Link>

                    <Link :href="route('rental.bookings.guest')" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-amber-200 dark:hover:border-amber-800 transition-all duration-200">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-amber-50 dark:bg-amber-900/20 rounded-lg group-hover:bg-amber-100 dark:group-hover:bg-amber-900/30 transition-colors">
                                <LuClipboardList class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-gray-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-semibold text-gray-900 dark:text-white">Bookings</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rentals & venues</p>
                    </Link>

                    <Link :href="route('laboratory.dashboard')" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-800 transition-all duration-200">
                        <div class="flex items-start justify-between">
                            <div class="p-2.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors">
                                <LuFlaskConical class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <LuArrowUpRight class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" />
                        </div>
                        <h3 class="mt-3 font-semibold text-gray-900 dark:text-white">Laboratory</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Equipment logs</p>
                    </Link>
                </div>

                <!-- Recent Activity Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <LuActivity class="w-5 h-5 text-gray-400" />
                            <h3 class="font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
                        </div>
                        <Link :href="route('transactions.index')" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                            View all
                        </Link>
                    </div>
                    
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <Link 
                            v-for="transaction in $page.props.recentTransactions" 
                            :key="transaction.id" 
                            :href="route('transactions.show', transaction.id)" 
                            class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                        >
                            <div class="flex items-center gap-4">
                                <div 
                                    class="p-2 rounded-full"
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
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ transaction?.item?.name ?? 'Unknown Item' }}
                                        <span 
                                            class="text-xs font-normal px-2 py-0.5 rounded-full ml-2"
                                            :class="transaction.transac_type === 'incoming' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'"
                                        >
                                            {{ transaction.transac_type }}
                                        </span>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ transaction?.quantity }} {{ transaction?.unit }} by 
                                        <span class="font-medium text-gray-700 dark:text-gray-300">
                                            {{ transaction?.personnel ? `${transaction.personnel.fname} ${transaction.personnel.lname}` : 'Unknown' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ new Date(transaction?.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ new Date(transaction?.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}
                                </p>
                            </div>
                        </Link>
                        
                        <div v-if="!$page.props.recentTransactions?.length" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                            <LuPackage class="w-12 h-12 mx-auto mb-3 opacity-20" />
                            <p>No recent transactions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>