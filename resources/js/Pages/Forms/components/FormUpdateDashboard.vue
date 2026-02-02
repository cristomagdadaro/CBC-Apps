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

Chart.register(BarController, PieController, BarElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend);

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
    </div>
</template>
