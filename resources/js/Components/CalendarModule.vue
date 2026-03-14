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
                type: event.type || "general",
                status: event.status || "",
            }));
        },
        filteredEvents() {
            let list = [...this.normalizedEvents];

            if (this.showTypeFilter && this.filterType !== "all") {
                list = list.filter((event) => event.type === this.filterType);
            }

            if (this.showStatusFilter && this.filterStatus !== "all") {
                list = list.filter(
                    (event) => event.status === this.filterStatus,
                );
            }

            return list;
        },
        daysInMonth() {
            return new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() + 1,
                0,
            ).getDate();
        },
        firstDayOfMonth() {
            return new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth(),
                1,
            ).getDay();
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
                    })),
                });
            }

            if (this.statusColors && Object.keys(this.statusColors).length) {
                groups.push({
                    title: "Status",
                    items: Object.entries(this.statusColors).map(
                        ([key, value]) => ({
                            label: key.charAt(0).toUpperCase() + key.slice(1),
                            color: value,
                            key: key,
                        }),
                    ),
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
        previousMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() - 1,
            );
        },
        nextMonth() {
            this.currentDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() + 1,
            );
        },
        goToToday() {
            this.currentDate = new Date();
        },
        isToday(day) {
            const today = new Date();
            return (
                day === today.getDate() &&
                this.currentDate.getMonth() === today.getMonth() &&
                this.currentDate.getFullYear() === today.getFullYear()
            );
        },
        toDateOnly(value) {
            if (!value) return null;
            if (value instanceof Date) return value.toISOString().split("T")[0];
            const text = String(value).trim();
            const iso = text.includes("T") ? text : text.replace(" ", "T");
            const parsed = new Date(iso);
            if (!Number.isNaN(parsed.getTime())) {
                return parsed.toISOString().split("T")[0];
            }
            return text.length >= 10 ? text.slice(0, 10) : null;
        },
        getBookingsForDate(day) {
            if (!day) return [];

            const dateStr = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth(),
                day,
            )
                .toISOString()
                .split("T")[0];

            return this.filteredEvents.filter((event) => {
                const dateFrom = this.toDateOnly(
                    event.date_from || event.start_at || event.started_at,
                );
                const dateTo = this.toDateOnly(
                    event.date_to ||
                        event.end_at ||
                        event.end_use_at ||
                        event.date_from,
                );
                if (!dateFrom || !dateTo) return false;
                return dateStr >= dateFrom && dateStr <= dateTo;
            });
        },
        getVisibleBookingsForDate(day) {
            return this.getBookingsForDate(day).slice(
                0,
                this.MAX_VISIBLE_EVENTS_PER_DAY,
            );
        },
        getRemainingBookingsForDate(day) {
            const bookings = this.getBookingsForDate(day);
            return bookings.length > this.MAX_VISIBLE_EVENTS_PER_DAY
                ? bookings.slice(this.MAX_VISIBLE_EVENTS_PER_DAY)
                : [];
        },
        getEventColor(event) {
            if (event.color) return event.color;
            const typeMatch = this.typeOptions.find(
                (item) => item.key === event.type,
            );
            return typeMatch?.color || "#6B7280";
        },
        handleEventClick(event) {
            if (event.checkoutPage && event.checkoutPageId) {
                const url = route(event.checkoutPage, event.checkoutPageId);
                const target = event.checkoutPageTarget || "_self";
                window.open(url, target);
            } else if (this.$inertia && this.$inertia.visit) {
                console.warn("No Route configured:", event);
            }
        },
        getDropdownAlignment(dayIndex) {
            // If day is in first 2 columns (Sun, Mon or first 2 days), align right
            // Otherwise align left to prevent right-edge cutoff
            const col = dayIndex % 7;
            return col < 2 ? "right" : "left";
        },
    },
    watch: {
        startDate: {
            handler(value) {
                if (!value) {
                    return;
                }

                const parsed = new Date(value);
                if (!Number.isNaN(parsed.getTime())) {
                    this.currentDate = parsed;
                }
            },
        },
    },
};
</script>

<template>
    <div class="flex flex-col xl:flex-row gap-6">
        <!-- Sidebar: Filters & Legend -->
        <aside class="w-full xl:w-80 flex-shrink-0 space-y-4">
            <!-- Header Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="p-2.5 bg-primary-100 dark:bg-primary-900/30 rounded-xl"
                    >
                        <svg
                            class="w-6 h-6 text-primary-600 dark:text-primary-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <div>
                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            {{ title }}
                        </h2>
                        <p
                            v-if="subtitle"
                            class="text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{ subtitle }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 space-y-4"
            >
                <h3
                    class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                        />
                    </svg>
                    Filters
                </h3>

                <div
                    v-if="showTypeFilter && typeOptions.length"
                    class="space-y-1.5"
                >
                    <label
                        class="text-xs font-medium text-slate-600 dark:text-slate-400"
                    >
                        Resource Type
                    </label>
                    <div class="relative">
                        <select
                            v-model="filterType"
                            class="w-full pl-9 pr-8 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all appearance-none cursor-pointer"
                        >
                            <option value="all">All Resources</option>
                            <option
                                v-for="option in typeOptions"
                                :key="option.key"
                                :value="option.key"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <svg
                            class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>
                        <svg
                            class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </div>
                </div>

                <div v-if="showStatusFilter" class="space-y-1.5">
                    <label
                        class="text-xs font-medium text-slate-600 dark:text-slate-400"
                    >
                        Status
                    </label>
                    <div class="relative">
                        <select
                            v-model="filterStatus"
                            class="w-full pl-9 pr-8 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all appearance-none cursor-pointer"
                        >
                            <option value="all">All Statuses</option>
                            <option
                                v-for="option in statusOptions"
                                :key="option.key"
                                :value="option.key"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <svg
                            class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <svg
                            class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </div>
                </div>

                <button
                    v-if="showToday"
                    type="button"
                    @click="goToToday"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 dark:bg-primary-600 dark:hover:bg-primary-500 text-gray-500 font-medium transition-all shadow-md hover:shadow-lg active:scale-[0.98]"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                    Jump to Today
                </button>
            </div>

            <!-- Legend -->
            <div
                v-if="showLegend"
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 space-y-4"
            >
                <h3
                    class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
                        />
                    </svg>
                    Legend
                </h3>

                <div class="space-y-3">
                    <div v-for="group in legendData" :key="group.title">
                        <h4
                            class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2"
                        >
                            {{ group.title }}
                        </h4>
                        <div class="space-y-1">
                            <div
                                v-for="item in group.items"
                                :key="item.label"
                                class="flex items-center gap-2.5 p-1.5 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer group"
                                @click="
                                    item.key ? (filterType = item.key) : null
                                "
                            >
                                <div
                                    class="w-2.5 h-2.5 rounded-full ring-2 ring-offset-1 ring-offset-white dark:ring-offset-slate-800 transition-all"
                                    :style="{
                                        backgroundColor: item.color,
                                        ringColor: item.color + '40',
                                    }"
                                ></div>
                                <span
                                    class="text-xs text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors"
                                    >{{ item.label }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats - Fixed: Light theme instead of dark -->
            <div
                v-if="showStats"
                class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-5"
            >
                <h3
                    class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-2"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                    Overview
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div
                        class="bg-white dark:bg-slate-700 rounded-xl p-3 border border-slate-200 dark:border-slate-600 shadow-sm"
                    >
                        <div
                            class="text-2xl font-bold text-slate-900 dark:text-white"
                        >
                            {{ stats.total }}
                        </div>
                        <div
                            class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                        >
                            Total
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-slate-700 rounded-xl p-3 border border-slate-200 dark:border-slate-600 shadow-sm"
                    >
                        <div
                            class="text-2xl font-bold text-primary-600 dark:text-primary-400"
                        >
                            {{ stats.visible }}
                        </div>
                        <div
                            class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                        >
                            Visible
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Calendar Area - Fixed: No overflow-hidden -->
        <main class="flex-1 min-w-0">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700"
            >
                <!-- Calendar Header -->
                <div
                    class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-t-2xl sticky top-0 z-10"
                >
                    <button
                        type="button"
                        @click="previousMonth"
                        class="p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all shadow-sm hover:shadow border border-transparent hover:border-slate-200 dark:hover:border-slate-600"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>

                    <h3
                        class="text-lg font-bold text-slate-900 dark:text-white"
                    >
                        {{ monthYearLabel }}
                    </h3>

                    <button
                        type="button"
                        @click="nextMonth"
                        class="p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all shadow-sm hover:shadow border border-transparent hover:border-slate-200 dark:hover:border-slate-600"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Calendar Grid - Fixed: Proper overflow handling -->
                <div class="overflow-x-auto">
                    <div class="min-w-[900px]">
                        <!-- Week Headers -->
                        <div
                            class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-700"
                        >
                            <div
                                v-for="(day, index) in weekDays"
                                :key="day"
                                class="px-3 py-2 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50"
                                :class="{
                                    'text-red-500 dark:text-red-400':
                                        index === 0 || index === 6,
                                }"
                            >
                                {{ day }}
                            </div>
                        </div>

                        <!-- Calendar Days -->
                        <div class="grid grid-cols-7">
                            <div
                                v-for="(day, index) in calendarDays"
                                :key="index"
                                class="min-h-[100px] border-b border-r border-slate-200 dark:border-slate-700 p-2 transition-all hover:bg-slate-50 dark:hover:bg-slate-700/30 relative group"
                                :class="{
                                    'bg-slate-50/30 dark:bg-slate-800/20': !day,
                                    'border-r-0': (index + 1) % 7 === 0,
                                }"
                            >
                                <div v-if="day" class="h-full flex flex-col">
                                    <!-- Events -->
                                    <div class="flex-1 space-y-1">
                                        <div
                                            v-for="booking in getVisibleBookingsForDate(
                                                day,
                                            )"
                                            :key="booking.id"
                                            class="p-1.5 rounded-md border-l-2 cursor-pointer hover:shadow-sm transition-all hover:scale-[1.02] active:scale-[0.98]"
                                            :style="{
                                                backgroundColor:
                                                    getEventColor(booking) +
                                                    '15',
                                                borderLeftColor:
                                                    getEventColor(booking),
                                            }"
                                            @click="handleEventClick(booking)"
                                        >
                                            <div
                                                class="font-medium text-sm text-slate-900 dark:text-slate-100 truncate leading-tight"
                                            >
                                                {{ booking.label }}
                                            </div>
                                            <div
                                                v-if="booking.subtitle"
                                                class="text-slate-500 dark:text-slate-400 truncate text-xs mt-0.5"
                                            >
                                                {{ booking.subtitle }}
                                            </div>
                                            <div
                                                v-if="booking.status"
                                                class="inline-flex items-center gap-1 mt-1 text-[9px] font-medium uppercase tracking-wide"
                                                :style="{
                                                    color:
                                                        statusColors[
                                                            booking.status
                                                        ] || '#6B7280',
                                                }"
                                            >
                                                <div
                                                    class="w-1 h-1 rounded-full"
                                                    :style="{
                                                        backgroundColor:
                                                            statusColors[
                                                                booking.status
                                                            ] || '#6B7280',
                                                    }"
                                                ></div>
                                                {{ booking.status }}
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Day Number -->
                                    <div
                                        class="flex items-center justify-between mt-1.5"
                                    >
                                        <span
                                            class="bg-AB text-white font-semibold w-6 h-6 flex items-center justify-center rounded-full transition-colors"
                                            :class="{
                                                'bg-primary-600 text-white shadow-md shadow-primary-500/30':
                                                    isToday(day),
                                                'text-slate-700 dark:text-slate-800 hover:bg-slate-200 dark:hover:bg-slate-600':
                                                    !isToday(day),
                                            }"
                                        >
                                            {{ day }}
                                        </span>
                                         <!-- More Button - Fixed: Smart alignment -->
                                        <Dropdown
                                            v-if="
                                                getRemainingBookingsForDate(day)
                                                    .length
                                            "
                                            :align="getDropdownAlignment(index)"
                                            width="64"
                                            max-height="18rem"
                                            content-class="z-50"
                                        >
                                            <template #trigger>
                                                <button
                                                    type="button"
                                                    class="w-full text-[10px] text-center py-1 px-3 rounded-md border border-dashed border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all"
                                                >
                                                    +{{
                                                        getRemainingBookingsForDate(
                                                            day,
                                                        ).length
                                                    }}
                                                    more
                                                </button>
                                            </template>

                                            <template #content>
                                                <div class="p-2 space-y-1">
                                                    <div
                                                        class="font-semibold text-slate-500 dark:text-slate-400 px-2 py-1 border-b border-slate-100 dark:border-slate-700 mb-1"
                                                    >
                                                        {{
                                                            getBookingsForDate(
                                                                day,
                                                            ).length
                                                        }}
                                                        events
                                                    </div>
                                                    <div
                                                        v-for="booking in getRemainingBookingsForDate(
                                                            day,
                                                        )"
                                                        :key="`remaining-${booking.id}`"
                                                        class="p-2 rounded-md border-l-2 cursor-pointer hover:shadow-sm transition-all"
                                                        :style="{
                                                            backgroundColor:
                                                                getEventColor(
                                                                    booking,
                                                                ) + '15',
                                                            borderLeftColor:
                                                                getEventColor(
                                                                    booking,
                                                                ),
                                                        }"
                                                        @click="
                                                            handleEventClick(
                                                                booking,
                                                            )
                                                        "
                                                    >
                                                        <div
                                                            class="font-medium text-slate-900 dark:text-slate-100 truncate"
                                                        >
                                                            {{ booking.label }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                booking.subtitle
                                                            "
                                                            class="text-slate-500 dark:text-slate-400 truncate text-[9px]"
                                                        >
                                                            {{
                                                                booking.subtitle
                                                            }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                booking.status
                                                            "
                                                            class="text-[9px] font-medium capitalize mt-0.5"
                                                            :style="{
                                                                color:
                                                                    statusColors[
                                                                        booking
                                                                            .status
                                                                    ] ||
                                                                    '#6B7280',
                                                            }"
                                                        >
                                                            {{ booking.status }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark ::-webkit-scrollbar-thumb {
    background: #475569;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Ensure dropdowns can overflow */
:deep(.dropdown-content) {
    position: fixed !important;
    z-index: 9999 !important;
}
</style>
