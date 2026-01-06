<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { onMounted, watch, ref } from 'vue';
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

const page = usePage();
const stats = page.props.stats || {
    events: { total: 0, active: 0, upcoming: 0, suspended: 0 , expired: 0 },
    access_requests: { total: 0, pending: 0, approved: 0, rejected: 0 },
    inventory: {
        items: 0,
        transactions_today: 0,
        stock_buckets: { empty: 0, low: 0, mid: 0, high: 0 },
    },
};

const eventsChartCanvas = ref(null);
const accessChartCanvas = ref(null);
const inventoryChartCanvas = ref(null);

let eventsChartInstance = null;
let accessChartInstance = null;
let inventoryChartInstance = null;

const eventColors = ['#22c55e', '#0ea5e9', '#eab308', '#ef4444'];
const accessColors = ['#eab308', '#22c55e', '#ef4444'];
const invColors = ['#6b7280', '#f97316', '#0ea5e9', '#22c55e'];

const destroyCharts = () => {
    if (eventsChartInstance) {
        eventsChartInstance.destroy();
        eventsChartInstance = null;
    }
    if (accessChartInstance) {
        accessChartInstance.destroy();
        accessChartInstance = null;
    }
    if (inventoryChartInstance) {
        inventoryChartInstance.destroy();
        inventoryChartInstance = null;
    }
};

const buildCharts = () => {
    destroyCharts();

    if (eventsChartCanvas.value) {

        eventsChartInstance = new Chart(eventsChartCanvas.value, {
            type: 'pie',
            data: {
                labels: ['Active', 'Upcoming', 'Suspended', 'Expired'],
                datasets: [
                    {
                        label: 'Event Forms',
                        data: [
                            stats.events.active,
                            stats.events.upcoming,
                            stats.events.suspended,
                            stats.events.expired,
                        ],
                        backgroundColor: eventColors,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            color: (ctx) => {
                                const index = ctx.dataIndex ?? 0;
                                return eventColors[index] || '#6b7280';
                            },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            labelColor: (ctx) => {
                                const index = ctx.dataIndex ?? 0;
                                return { borderColor: eventColors[index], backgroundColor: eventColors[index] };
                            },
                        },
                    },
                },
                scales: {}, // no x/y axes for pie chart
            },
        });
    }

    if (accessChartCanvas.value) {

        accessChartInstance = new Chart(accessChartCanvas.value, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [
                    {
                        label: 'Access & Use Requests',
                        data: [
                            stats.access_requests.pending,
                            stats.access_requests.approved,
                            stats.access_requests.rejected,
                        ],
                        backgroundColor: accessColors,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            color: (ctx) => {
                                const index = ctx.dataIndex ?? 0;
                                return accessColors[index] || '#6b7280';
                            },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            labelColor: (ctx) => {
                                const index = ctx.dataIndex ?? 0;
                                return { borderColor: accessColors[index], backgroundColor: accessColors[index] };
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            color: (ctx) => accessColors[ctx.index] || '#6b7280',
                        },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false, drawBorder: false },
                        ticks: { display: false },
                        border: { display: false },
                    },
                },
            },
        });
    }

    if (inventoryChartCanvas.value) {
        const buckets = stats.inventory.stock_buckets || { empty: 0, low: 0, mid: 0, high: 0 };

        inventoryChartInstance = new Chart(inventoryChartCanvas.value, {
            type: 'bar',
            data: {
                labels: ['Empty', 'Low', 'Mid', 'High'],
                datasets: [
                    {
                        label: 'Items by Stock Level',
                        data: [
                            buckets.empty,
                            buckets.low,
                            buckets.mid,
                            buckets.high,
                        ],
                        backgroundColor: invColors,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            color: (ctx) => {
                                const index = ctx.dataIndex ?? 0;
                                return invColors[index] || '#6b7280';
                            },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            labelColor: (ctx) => {
                                const index = ctx.dataIndex ?? 0;
                                return { borderColor: invColors[index], backgroundColor: invColors[index] };
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            color: (ctx) => invColors[ctx.index] || '#6b7280',
                        },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false, drawBorder: false },
                        ticks: { display: false },
                        border: { display: false },
                    },
                },
            },
        });
    }
};

onMounted(() => {
    buildCharts();
});

watch(
    () => page.props.stats,
    () => {
        buildCharts();
    }
);
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
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

                    <!-- Access & Use requests summary -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Access &amp; Use Requests</h3>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.access_requests.total }}</p>
                            <dl class="mt-2 grid grid-cols-3 text-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <div :class="'text-[' + accessColors[0] + ']'">
                                    <dt>Pending</dt>
                                    <dd class="font-semibold">{{ stats.access_requests.pending }}</dd>
                                </div>
                                <div :class="'text-[' + accessColors[1] + ']'">
                                    <dt>Rejected</dt>
                                    <dd class="font-semibold">{{ stats.access_requests.rejected }}</dd>
                                </div>
                                <div :class="'text-[' + accessColors[2] + ']'">
                                    <dt>Approved</dt>
                                    <dd class="font-semibold">{{ stats.access_requests.approved }}</dd>
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

                <!-- Quick navigation panels -->
                <div class="grid gap-4 md:grid-cols-2">
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
                </div>

                <!-- Transactions Recent Ativities -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Recent Transactions</h3>
                    <div class="flex flex-col text-sm gap-1">
                        <div v-for="transaction in $page.props.recentTransactions" :key="transaction.id" class="py-2 rounded-md px-2" :class="transaction.transac_type === 'incoming' ? 'bg-green-200':'bg-red-200'">
                            <p>
                                <span class="font-medium">{{ transaction.item.name }}</span>
                                <span>{{ transaction.type }}</span>
                                &mdash;
                                <span class="font-medium">{{ transaction.quantity }}</span>
                                {{ transaction.unit }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                By
                                <span class="font-medium">
                                    {{ transaction.personnel ? transaction.personnel.fname + ' ' + transaction.personnel.lname : 'Unknown' }}
                                </span>
                                on
                                <span>{{ new Date(transaction.created_at).toLocaleString() }}</span>
                            </p>
                        </div>
                        <Link :href="route('transactions.index')" class="text-blue-600 dark:text-blue-400 hover:underline">
                            View all transactions
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
