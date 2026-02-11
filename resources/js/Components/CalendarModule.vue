<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    title: {
        type: String,
        default: "Calendar",
    },
    subtitle: {
        type: String,
        default: "",
    },
    events: {
        type: Array,
        default: () => [],
    },
    typeOptions: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [
            { key: "pending", label: "Pending" },
            { key: "approved", label: "Approved" },
            { key: "rejected", label: "Rejected" },
            { key: "completed", label: "Completed" },
            { key: "cancelled", label: "Cancelled" },
        ],
    },
    statusColors: {
        type: Object,
        default: () => ({}),
    },
    legendGroups: {
        type: Array,
        default: () => [],
    },
    showTypeFilter: {
        type: Boolean,
        default: true,
    },
    showStatusFilter: {
        type: Boolean,
        default: true,
    },
    showLegend: {
        type: Boolean,
        default: true,
    },
    showStats: {
        type: Boolean,
        default: true,
    },
    showToday: {
        type: Boolean,
        default: true,
    },
    startDate: {
        type: [String, Date],
        default: null,
    },
});

const currentDate = ref(props.startDate ? new Date(props.startDate) : new Date());
const filterType = ref("all");
const filterStatus = ref("all");

const weekDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

const normalizedEvents = computed(() =>
    (props.events || []).map((event) => ({
        ...event,
        label: event.label || event.title || "(Untitled)",
        subtitle: event.subtitle || event.requested_by || "",
        type: event.type || "general",
        status: event.status || "",
    }))
);

const filteredEvents = computed(() => {
    let list = [...normalizedEvents.value];

    if (props.showTypeFilter && filterType.value !== "all") {
        list = list.filter((event) => event.type === filterType.value);
    }

    if (props.showStatusFilter && filterStatus.value !== "all") {
        list = list.filter((event) => event.status === filterStatus.value);
    }

    return list;
});

const daysInMonth = computed(() =>
    new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0).getDate()
);

const firstDayOfMonth = computed(() =>
    new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1).getDay()
);

const calendarDays = computed(() => {
    const days = [];
    for (let i = 0; i < firstDayOfMonth.value; i += 1) {
        days.push(null);
    }
    for (let i = 1; i <= daysInMonth.value; i += 1) {
        days.push(i);
    }
    return days;
});

const monthYearLabel = computed(() => {
    const options = { month: "long", year: "numeric" };
    return currentDate.value.toLocaleDateString("en-US", options);
});

const legendData = computed(() => {
    if (props.legendGroups && props.legendGroups.length) {
        return props.legendGroups;
    }

    const groups = [];
    if (props.typeOptions && props.typeOptions.length) {
        groups.push({
            title: "Types",
            items: props.typeOptions.map((item) => ({
                label: item.label,
                color: item.color || "#6B7280",
            })),
        });
    }

    if (props.statusColors && Object.keys(props.statusColors).length) {
        groups.push({
            title: "Status",
            items: Object.entries(props.statusColors).map(([key, value]) => ({
                label: key.charAt(0).toUpperCase() + key.slice(1),
                color: value,
            })),
        });
    }

    return groups;
});

const stats = computed(() => ({
    total: normalizedEvents.value.length,
    visible: filteredEvents.value.length,
}));

const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

const toDateOnly = (value) => {
    if (!value) return null;
    if (value instanceof Date) return value.toISOString().split("T")[0];
    const text = String(value).trim();
    const iso = text.includes("T") ? text : text.replace(" ", "T");
    const parsed = new Date(iso);
    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toISOString().split("T")[0];
    }
    return text.length >= 10 ? text.slice(0, 10) : null;
};

const getBookingsForDate = (day) => {
    if (!day) return [];

    const dateStr = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), day)
        .toISOString()
        .split("T")[0];

    return filteredEvents.value.filter((event) => {
        const dateFrom = toDateOnly(event.date_from || event.start_at || event.started_at);
        const dateTo = toDateOnly(event.date_to || event.end_at || event.end_use_at || event.date_from);
        if (!dateFrom || !dateTo) return false;
        return dateStr >= dateFrom && dateStr <= dateTo;
    });
};

const getEventColor = (event) => {
    if (event.color) return event.color;
    const typeMatch = props.typeOptions.find((item) => item.key === event.type);
    return typeMatch?.color || "#6B7280";
};
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <div class="flex flex-col gap-2">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h2>
                    <p v-if="subtitle" class="text-sm text-gray-500">{{ subtitle }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center justify-between sm:col-span-2 lg:col-span-1">
                        <button
                            type="button"
                            @click="previousMonth"
                            class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300"
                        >
                            ← Previous
                        </button>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap mx-2">
                            {{ monthYearLabel }}
                        </h3>
                        <button
                            type="button"
                            @click="nextMonth"
                            class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300"
                        >
                            Next →
                        </button>
                    </div>

                    <div v-if="showTypeFilter && typeOptions.length">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Resource Type
                        </label>
                        <select
                            v-model="filterType"
                            class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        >
                            <option value="all">All Resources</option>
                            <option v-for="option in typeOptions" :key="option.key" :value="option.key">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div v-if="showStatusFilter">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status
                        </label>
                        <select
                            v-model="filterStatus"
                            class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        >
                            <option value="all">All Statuses</option>
                            <option v-for="option in statusOptions" :key="option.key" :value="option.key">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div v-if="showToday" class="flex items-end">
                        <button
                            type="button"
                            @click="goToToday"
                            class="w-full px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium"
                        >
                            Today
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                v-for="day in weekDays"
                                :key="day"
                                class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-gray-100"
                            >
                                {{ day }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(week, weekIndex) in Math.ceil(calendarDays.length / 7)"
                            :key="weekIndex"
                            class="border-t border-gray-200 dark:border-gray-700"
                        >
                            <td
                                v-for="dayIndex in 7"
                                :key="dayIndex"
                                class="border-r border-gray-200 dark:border-gray-700 last:border-r-0 h-32 sm:h-40 p-2 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            >
                                <div class="h-full flex flex-col">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                        {{ calendarDays[(weekIndex - 1) * 7 + dayIndex - 1] || "" }}
                                    </div>
                                    <div class="flex-1 overflow-y-auto space-y-1">
                                        <div
                                            v-for="booking in getBookingsForDate(calendarDays[(weekIndex - 1) * 7 + dayIndex - 1])"
                                            :key="booking.id"
                                            :style="{
                                                backgroundColor: getEventColor(booking) + '20',
                                                borderColor: getEventColor(booking),
                                            }"
                                            class="text-xs p-1.5 rounded border-l-2 cursor-pointer hover:opacity-80 transition-opacity"
                                            :title="booking.subtitle ? booking.label + ' - ' + booking.subtitle : booking.label"
                                        >
                                            <div class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ booking.label }}
                                            </div>
                                            <div v-if="booking.subtitle" class="text-gray-600 dark:text-gray-400 truncate">
                                                {{ booking.subtitle }}
                                            </div>
                                            <div
                                                v-if="booking.status"
                                                :style="{ color: statusColors[booking.status] || '#6B7280' }"
                                                class="text-xs font-semibold capitalize"
                                            >
                                                {{ booking.status }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showLegend" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Legend</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="group in legendData" :key="group.title">
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ group.title }}</h4>
                    <div class="space-y-2">
                        <div v-for="item in group.items" :key="item.label" class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded" :style="{ backgroundColor: item.color }"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="showStats">
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Statistics</h4>
                    <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                        <div>
                            <span class="font-medium">Total Events:</span>
                            {{ stats.total }}
                        </div>
                        <div>
                            <span class="font-medium">Visible Events:</span>
                            {{ stats.visible }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
table {
    table-layout: fixed;
}

td {
    vertical-align: top;
}

:deep(.transition-colors) {
    transition: background-color 0.2s ease-in-out;
}

:deep(.transition-opacity) {
    transition: opacity 0.2s ease-in-out;
}
</style>
