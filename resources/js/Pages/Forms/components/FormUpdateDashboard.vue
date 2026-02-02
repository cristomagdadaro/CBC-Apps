<script setup>
import { computed, onMounted, ref, watch } from 'vue';
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
import DataFormatterMixin from '@/Modules/mixins/DataFormatterMixin';

Chart.register(BarController, PieController, BarElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend);

const formatDateTime = DataFormatterMixin.methods.formatDateTime;

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            responses_total: 0,
            responses_by_type: {},
            registrations_total: 0,
            participants_total: 0,
            requirements_total: 0,
        }),
    },
    responsesByType: {
        type: Object,
        default: () => ({}),
    },
});

const responseChartCanvas = ref(null);
const totalsChartCanvas = ref(null);

let responseChartInstance = null;
let totalsChartInstance = null;

const labelMap = {
    pre_registration: 'Pre-registration',
    pre_registration_biotech: 'Pre-registration + Quiz Bee',
    registration: 'Registration',
    pre_test: 'Pre-test',
    post_test: 'Post-test',
    feedback: 'Feedback',
};

const responseLabels = computed(() =>
    Object.keys(props.stats?.responses_by_type || {}).map((key) => labelMap[key] || key)
);

const responseValues = computed(() =>
    Object.values(props.stats?.responses_by_type || {})
);

const responseColors = ['#22c55e', '#0ea5e9', '#eab308', '#ef4444', '#a855f7', '#10b981'];
const totalsColors = ['#3b82f6', '#f97316', '#22c55e'];

const responseTableRows = computed(() => {
    const entries = Object.entries(props.stats?.responses_by_type || {});

    return entries.map(([key, value]) => ({
        form_type: key,
        label: labelMap[key] || key,
        count: value ?? 0,
    }));
});

const responseDataGroups = computed(() => {
    const groups = props.responsesByType || {};
    return Object.entries(groups).map(([key, items]) => {
        const itemsArray = Array.isArray(items) ? items : [];
        
        // Extract unique keys from all response_data objects
        const uniqueKeys = new Set();
        itemsArray.forEach(item => {
            if (item.response_data && typeof item.response_data === 'object') {
                Object.keys(item.response_data).forEach(k => uniqueKeys.add(k));
            }
        });
        
        return {
            form_type: key,
            label: labelMap[key] || key,
            items: itemsArray,
            dataColumns: Array.from(uniqueKeys).sort(), // Dynamic columns from response_data
        };
    });
});

const formatValue = (value) => {
    if (value === null || value === undefined || value === '') {
        return '-';
    }
    if (Array.isArray(value)) {
        return value.join(', ');
    }
    return String(value);
};

const escapeCSV = (value) => {
    if (value === null || value === undefined) {
        return '';
    }
    const str = Array.isArray(value) ? value.join(', ') : String(value);
    if (str.includes(',') || str.includes('"') || str.includes('\n')) {
        return `"${str.replace(/"/g, '""')}"`;
    }
    return str;
};

const exportToCSV = (group) => {
    const headers = ['Submitted On', 'Respondent', ...group.dataColumns.map(col => col.replace(/_/g, ' '))];
    const rows = group.items.map(item => [
        item.created_at,
        item.response_data?.name || item.response_data?.full_name || item.response_data?.email || 'N/A',
        ...group.dataColumns.map(col => item.response_data?.[col] ?? ''),
    ]);

    const csvContent = [
        headers.map(escapeCSV).join(','),
        ...rows.map(row => row.map(escapeCSV).join(',')),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `${group.form_type}_responses.csv`);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const destroyCharts = () => {
    if (responseChartInstance) {
        responseChartInstance.destroy();
        responseChartInstance = null;
    }
    if (totalsChartInstance) {
        totalsChartInstance.destroy();
        totalsChartInstance = null;
    }
};

const buildCharts = () => {
    destroyCharts();

    if (responseChartCanvas.value) {
        responseChartInstance = new Chart(responseChartCanvas.value, {
            type: 'pie',
            data: {
                labels: responseLabels.value,
                datasets: [
                    {
                        label: 'Responses by Form Type',
                        data: responseValues.value,
                        backgroundColor: responseColors,
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
                                return responseColors[index] || '#6b7280';
                            },
                        },
                    },
                },
            },
        });
    }

    if (totalsChartCanvas.value) {
        totalsChartInstance = new Chart(totalsChartCanvas.value, {
            type: 'bar',
            data: {
                labels: ['Registrations', 'Participants', 'Responses'],
                datasets: [
                    {
                        label: 'Totals',
                        data: [
                            props.stats?.registrations_total || 0,
                            props.stats?.participants_total || 0,
                            props.stats?.responses_total || 0,
                        ],
                        backgroundColor: totalsColors,
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
                    },
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            color: (ctx) => totalsColors[ctx.index] || '#6b7280',
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
    () => props.stats,
    () => {
        buildCharts();
    },
    { deep: true }
);
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Responses</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.responses_total }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Across all form types</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Registrations</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.registrations_total }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Registered participants</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Participants</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.participants_total }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Unique attendees</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Responses by Form Type</h3>
                <div class="mt-4 h-64">
                    <canvas ref="responseChartCanvas"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Event Totals</h3>
                <div class="mt-4 h-64">
                    <canvas ref="totalsChartCanvas"></canvas>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div
                v-for="group in responseDataGroups"
                :key="group.form_type"
                class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4"
            >
            <div class="flex w-full justify-between items-center">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    <span class="uppercase">{{ group.label }}</span>
                </h3>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-700 dark:text-gray-200">
                        <span class="font-semibold">{{ group.items.length }}</span> response<span v-if="group.items.length > 1">s</span>
                    </div>
                    <button
                        @click="exportToCSV(group)"
                        class="inline-flex items-center gap-2 px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 border-b">
                            <tr>
                                <th class="px-4 py-2">Submitted On</th>
                                <th class="px-4 py-2">Respondent</th>
                                <th
                                    v-for="col in group.dataColumns"
                                    :key="col"
                                    class="px-4 py-2"
                                >
                                    {{ col.replace(/_/g, ' ') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in group.items" :key="item.id" class="border-b">
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ formatDateTime(item.created_at) }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-200">
                                    {{ item.response_data?.name || item.response_data?.full_name || item.response_data?.email || 'N/A' }}
                                </td>
                                <td
                                    v-for="col in group.dataColumns"
                                    :key="col"
                                    class="px-4 py-2 text-gray-700 dark:text-gray-200"
                                >
                                    {{ formatValue(item.response_data?.[col]) }}
                                </td>
                            </tr>
                            <tr v-if="!group.items.length">
                                <td :colspan="2 + group.dataColumns.length" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No responses available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
