<script>
export default {
    name: "CalendarModule",
    props: {
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
    },
    data() {
        return {
            currentDate: this.startDate ? new Date(this.startDate) : new Date(),
            filterType: "all",
            filterStatus: "all",
            weekDays: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            MAX_VISIBLE_EVENTS_PER_DAY: 2,
        };
    },
    computed: {
        normalizedEvents() {
            return (this.events || []).map((event) => ({
                ...event,
                label: event.label || event.title || "(Untitled)",
                subtitle: event.subtitle || event.requested_by || "",
                type: this.normalizeTypeValue(event.type || "general"),
                status: this.normalizeStatusValue(event.status || ""),
            }));
        },
        filteredEvents() {
            let list = [...this.normalizedEvents];

            if (this.showTypeFilter && this.filterType !== "all") {
                const selectedType = this.normalizeTypeValue(this.filterType);
                list = list.filter((event) => event.type === selectedType);
            }

            if (this.showStatusFilter && this.filterStatus !== "all") {
                const selectedStatus = this.normalizeStatusValue(this.filterStatus);
                list = list.filter((event) => event.status === selectedStatus);
            }

            return list;
        },
        daysInMonth() {
            return new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0).getDate();
        },
        firstDayOfMonth() {
            return new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1).getDay();
        },
        calendarDays() {
            const days = [];
            for (let i = 0; i < this.firstDayOfMonth; i += 1) {
                days.push(null);
            }
            for (let i = 1; i <= this.daysInMonth; i += 1) {
                days.push(i);
            }
            return days;
        },
        monthYearLabel() {
            const options = { month: "long", year: "numeric" };
            return this.currentDate.toLocaleDateString("en-US", options);
        },
        legendData() {
            if (this.legendGroups && this.legendGroups.length) {
                return this.legendGroups;
            }

            const groups = [];
            if (this.typeOptions && this.typeOptions.length) {
                groups.push({
                    title: "Types",
                    items: this.typeOptions.map((item) => ({
                        label: item.label,
                        color: item.color || "#6B7280",
                        key: item.key,
                        filterKey: this.normalizeTypeValue(item.key),
                        filterTarget: "type",
                    })),
                });
            }

            if (this.statusColors && Object.keys(this.statusColors).length) {
                groups.push({
                    title: "Status",
                    items: Object.entries(this.statusColors).map(([key, value]) => ({
                        label: key.charAt(0).toUpperCase() + key.slice(1),
                        color: value,
                        key,
                        filterKey: this.normalizeStatusValue(key),
                        filterTarget: "status",
                    })),
                });
            }

            return groups;
        },
        stats() {
            return {
                total: this.normalizedEvents.length,
                visible: this.filteredEvents.length,
            };
        },
    },
    methods: {
        normalizeTypeValue(value) {
            return String(value || "general")
                .trim()
                .toUpperCase();
        },
        normalizeStatusValue(value) {
            return String(value || "")
                .trim()
                .toLowerCase();
        },
        formatLocalDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");

            return `${year}-${month}-${day}`;
        },
        previousMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1);
        },
        nextMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1);
        },
        goToToday() {
            this.currentDate = new Date();
        },
        toDateOnly(value) {
            if (!value) return null;
            if (value instanceof Date) return this.formatLocalDate(value);
            const text = String(value).trim();
            const dateOnlyMatch = text.match(/^(\d{4}-\d{2}-\d{2})/);
            if (dateOnlyMatch) {
                return dateOnlyMatch[1];
            }
            const iso = text.includes("T") ? text : text.replace(" ", "T");
            const parsed = new Date(iso);
            if (!Number.isNaN(parsed.getTime())) {
                return this.formatLocalDate(parsed);
            }
            return text.length >= 10 ? text.slice(0, 10) : null;
        },
        getBookingsForDate(day) {
            if (!day) return [];

            const dateStr = this.formatLocalDate(new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day));

            return this.filteredEvents.filter((event) => {
                const dateFrom = this.toDateOnly(event.date_from || event.start_at || event.started_at);
                const dateTo = this.toDateOnly(event.date_to || event.end_at || event.end_use_at || event.date_from);
                if (!dateFrom || !dateTo) return false;
                return dateStr >= dateFrom && dateStr <= dateTo;
            });
        },
        getVisibleBookingsForDate(day) {
            return this.getBookingsForDate(day).slice(0, this.MAX_VISIBLE_EVENTS_PER_DAY);
        },
        getRemainingBookingsForDate(day) {
            const bookings = this.getBookingsForDate(day);
            return bookings.length > this.MAX_VISIBLE_EVENTS_PER_DAY ? bookings.slice(this.MAX_VISIBLE_EVENTS_PER_DAY) : [];
        },
        getEventColor(event) {
            if (event.color) return event.color;
            const typeMatch = this.typeOptions.find((item) => this.normalizeTypeValue(item.key) === event.type);
            return typeMatch?.color || "#6B7280";
        },
        handleLegendClick(item) {
            if (!item?.filterTarget || !item.filterKey) {
                return;
            }

            if (item.filterTarget === "type" && this.showTypeFilter) {
                this.filterType = item.filterKey;
                return;
            }

            if (item.filterTarget === "status" && this.showStatusFilter) {
                this.filterStatus = item.filterKey;
            }
        },
        handleEventClick(event) {
            if (event.checkoutPage && event.checkoutPageId) {
                const url = route(event.checkoutPage, event.checkoutPageId);
                const target = event.checkoutPageTarget || "_self";
                window.open(url, target);
            } else if (this.$inertia && this.$inertia.visit) {
                // Fallback for testing without proper route
                console.warn("Event has no checkout route configured:", event);
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-lg bg-white p-4 shadow sm:p-6 dark:bg-gray-800">
            <div class="flex flex-col gap-2">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ title }}
                    </h2>
                    <p
                        v-if="subtitle"
                        class="text-sm text-gray-500">
                        {{ subtitle }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex items-center justify-between sm:col-span-2 lg:col-span-1">
                        <button
                            type="button"
                            @click="previousMonth"
                            class="rounded-md bg-gray-100 px-3 py-2 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            ← Previous
                        </button>
                        <h3 class="mx-2 whitespace-nowrap text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ monthYearLabel }}
                        </h3>
                        <button
                            type="button"
                            @click="nextMonth"
                            class="rounded-md bg-gray-100 px-3 py-2 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Next →
                        </button>
                    </div>

                    <div v-if="showTypeFilter && typeOptions.length">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Resource Type</label>
                        <select
                            v-model="filterType"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="all">All Resources</option>
                            <option
                                v-for="option in typeOptions"
                                :key="option.key"
                                :value="option.key">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div v-if="showStatusFilter">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select
                            v-model="filterStatus"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="all">All Statuses</option>
                            <option
                                v-for="option in statusOptions"
                                :key="option.key"
                                :value="option.key">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="showToday"
                        class="flex items-end">
                        <button
                            type="button"
                            @click="goToToday"
                            class="w-full rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                            Today
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                v-for="day in weekDays"
                                :key="day"
                                class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-gray-100">
                                {{ day }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(week, weekIndex) in Math.ceil(calendarDays.length / 7)"
                            :key="weekIndex"
                            class="border-t border-gray-200 dark:border-gray-700">
                            <td
                                v-for="dayIndex in 7"
                                :key="dayIndex"
                                class="h-32 border-r border-gray-200 bg-gray-50 p-2 transition-colors last:border-r-0 hover:bg-gray-100 sm:h-40 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800">
                                <div class="flex h-full flex-col">
                                    <div class="mb-1 font-semibold text-gray-900 dark:text-gray-100">
                                        {{ calendarDays[weekIndex * 7 + dayIndex - 1] || "" }}
                                    </div>
                                    <div class="relative flex-1 space-y-1 overflow-visible">
                                        <div
                                            v-for="booking in getVisibleBookingsForDate(calendarDays[weekIndex * 7 + dayIndex - 1])"
                                            :key="booking.id"
                                            :style="{
                                                backgroundColor: getEventColor(booking) + '20',
                                                borderColor: getEventColor(booking),
                                            }"
                                            class="transform cursor-pointer rounded border-l-2 p-1.5 text-xs transition-opacity hover:scale-105 hover:opacity-80 hover:shadow-md"
                                            :title="booking.subtitle ? booking.label + ' - ' + booking.subtitle : booking.label"
                                            @click="handleEventClick(booking)">
                                            <div class="truncate font-medium text-gray-900 dark:text-gray-100">
                                                {{ booking.label }}
                                            </div>
                                            <div
                                                v-if="booking.subtitle"
                                                class="truncate text-gray-600 dark:text-gray-400">
                                                {{ booking.subtitle }}
                                            </div>
                                            <div
                                                v-if="booking.status"
                                                :style="{
                                                    color: statusColors[booking.status] || '#6B7280',
                                                }"
                                                class="text-xs font-semibold capitalize">
                                                {{ booking.status }}
                                            </div>
                                        </div>

                                        <Dropdown
                                            v-if="getRemainingBookingsForDate(calendarDays[weekIndex * 7 + dayIndex - 1]).length"
                                            align="right"
                                            width="auto"
                                            max-height="16rem">
                                            <template #trigger>
                                                <button
                                                    type="button"
                                                    class="w-full rounded border border-gray-300 px-1.5 py-1 text-left text-xs text-blue-600 hover:bg-gray-100 dark:border-gray-600 dark:text-blue-400 dark:hover:bg-gray-700">
                                                    +{{ getRemainingBookingsForDate(calendarDays[weekIndex * 7 + dayIndex - 1]).length }}
                                                    more
                                                </button>
                                            </template>

                                            <template #content>
                                                <div class="w-72 max-w-[85vw] space-y-1 p-2">
                                                    <div
                                                        v-for="booking in getRemainingBookingsForDate(calendarDays[weekIndex * 7 + dayIndex - 1])"
                                                        :key="`remaining-${booking.id}`"
                                                        :style="{
                                                            backgroundColor: getEventColor(booking) + '20',
                                                            borderColor: getEventColor(booking),
                                                        }"
                                                        class="transform cursor-pointer rounded border-l-2 p-1.5 text-xs transition-opacity hover:scale-105 hover:opacity-80 hover:shadow-md"
                                                        :title="booking.subtitle ? booking.label + ' - ' + booking.subtitle : booking.label"
                                                        @click="handleEventClick(booking)">
                                                        <div class="truncate font-medium text-gray-900 dark:text-gray-100">
                                                            {{ booking.label }}
                                                        </div>
                                                        <div
                                                            v-if="booking.subtitle"
                                                            class="truncate text-gray-600 dark:text-gray-400">
                                                            {{ booking.subtitle }}
                                                        </div>
                                                        <div
                                                            v-if="booking.status"
                                                            :style="{
                                                                color: statusColors[booking.status] || '#6B7280',
                                                            }"
                                                            class="text-xs font-semibold capitalize">
                                                            {{ booking.status }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="showLegend"
            class="rounded-lg bg-white p-4 shadow sm:p-6 dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Legend</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="group in legendData"
                    :key="group.title">
                    <h4 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">
                        {{ group.title }}
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="item in group.items"
                            :key="item.label"
                            class="flex cursor-pointer items-center gap-2"
                            @click="handleLegendClick(item)">
                            <div
                                class="h-4 w-4 rounded"
                                :style="{ backgroundColor: item.color }"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ item.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="showStats">
                    <h4 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">Statistics</h4>
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
