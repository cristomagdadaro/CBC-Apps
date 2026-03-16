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
            weekHeaderHeight: 32,
            weekEventHeight: 22,
            weekEventGap: 4,
            weekRowBottomPadding: 10,
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
        monthYearLabel() {
            const options = { month: "long", year: "numeric" };
            return this.currentDate.toLocaleDateString("en-US", options);
        },
        calendarWeeks() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstOfMonth = this.createDateAtNoon(year, month, 1);
            const lastOfMonth = this.createDateAtNoon(year, month + 1, 0);
            const gridStart = this.addDays(firstOfMonth, -firstOfMonth.getDay());
            const gridEnd = this.addDays(lastOfMonth, 6 - lastOfMonth.getDay());
            const weeks = [];

            for (
                let cursor = this.cloneDate(gridStart);
                cursor <= gridEnd;
                cursor = this.addDays(cursor, 7)
            ) {
                const days = [];

                for (let offset = 0; offset < 7; offset += 1) {
                    const date = this.addDays(cursor, offset);
                    days.push({
                        key: this.formatDateKey(date),
                        date,
                        dayNumber: date.getDate(),
                        inCurrentMonth: date.getMonth() === month,
                        isToday: this.isSameDate(date, new Date()),
                    });
                }

                weeks.push(days);
            }

            return weeks;
        },
        weekEventLanes() {
            return this.calendarWeeks.map((week) => this.buildWeekEventLanes(week));
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
        createDateAtNoon(year, month, day) {
            return new Date(year, month, day, 12, 0, 0, 0);
        },
        cloneDate(value) {
            return this.createDateAtNoon(
                value.getFullYear(),
                value.getMonth(),
                value.getDate(),
            );
        },
        addDays(value, days) {
            const date = this.cloneDate(value);
            date.setDate(date.getDate() + days);
            return this.createDateAtNoon(
                date.getFullYear(),
                date.getMonth(),
                date.getDate(),
            );
        },
        parseDateValue(value) {
            if (!value) return null;

            if (value instanceof Date) {
                return this.createDateAtNoon(
                    value.getFullYear(),
                    value.getMonth(),
                    value.getDate(),
                );
            }

            const text = String(value).trim();
            const match = text.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (match) {
                return this.createDateAtNoon(
                    Number(match[1]),
                    Number(match[2]) - 1,
                    Number(match[3]),
                );
            }

            const parsed = new Date(text);
            if (Number.isNaN(parsed.getTime())) {
                return null;
            }

            return this.createDateAtNoon(
                parsed.getFullYear(),
                parsed.getMonth(),
                parsed.getDate(),
            );
        },
        formatDateKey(value) {
            const date = this.parseDateValue(value);
            if (!date) return null;

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");

            return `${year}-${month}-${day}`;
        },
        isSameDate(left, right) {
            const leftDate = this.parseDateValue(left);
            const rightDate = this.parseDateValue(right);

            return !!leftDate && !!rightDate && this.formatDateKey(leftDate) === this.formatDateKey(rightDate);
        },
        getEventDateRange(event) {
            const start = this.parseDateValue(
                event.date_from || event.start_at || event.started_at,
            );
            const end = this.parseDateValue(
                event.date_to || event.end_at || event.end_use_at || event.date_from,
            );

            if (!start || !end) {
                return null;
            }

            if (end < start) {
                return { start: end, end: start };
            }

            return { start, end };
        },
        diffInDays(start, end) {
            const startDate = this.parseDateValue(start);
            const endDate = this.parseDateValue(end);
            if (!startDate || !endDate) return 0;

            return Math.round((endDate.getTime() - startDate.getTime()) / 86400000);
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
        buildWeekEventLanes(week) {
            if (!Array.isArray(week) || !week.length) {
                return [];
            }

            const weekStart = week[0].date;
            const weekEnd = week[6].date;
            const segments = this.filteredEvents
                .map((event) => {
                    const range = this.getEventDateRange(event);
                    if (!range) {
                        return null;
                    }

                    if (range.end < weekStart || range.start > weekEnd) {
                        return null;
                    }

                    const visibleStart = range.start < weekStart ? weekStart : range.start;
                    const visibleEnd = range.end > weekEnd ? weekEnd : range.end;
                    const startCol = this.diffInDays(weekStart, visibleStart) + 1;
                    const endCol = this.diffInDays(weekStart, visibleEnd) + 1;

                    return {
                        event,
                        startCol,
                        endCol,
                        span: endCol - startCol + 1,
                        continuesBefore: range.start < weekStart,
                        continuesAfter: range.end > weekEnd,
                        sortStart: range.start,
                        sortEnd: range.end,
                    };
                })
                .filter(Boolean)
                .sort((left, right) => {
                    if (left.startCol !== right.startCol) {
                        return left.startCol - right.startCol;
                    }

                    if (left.span !== right.span) {
                        return right.span - left.span;
                    }

                    return String(left.event.label || "").localeCompare(String(right.event.label || ""));
                });

            const lanes = [];

            segments.forEach((segment) => {
                let laneIndex = lanes.findIndex((lane) => {
                    const last = lane[lane.length - 1];
                    return !last || segment.startCol > last.endCol;
                });

                if (laneIndex === -1) {
                    laneIndex = lanes.length;
                    lanes.push([]);
                }

                lanes[laneIndex].push({
                    ...segment,
                    laneIndex,
                });
            });

            return lanes;
        },
        getWeekMinHeight(weekIndex) {
            const laneCount = this.weekEventLanes[weekIndex]?.length || 0;
            const eventRowsHeight = laneCount
                ? (laneCount * this.weekEventHeight) + ((laneCount - 1) * this.weekEventGap)
                : this.weekEventHeight;

            return `${this.weekHeaderHeight + eventRowsHeight + this.weekRowBottomPadding}px`;
        },
        getWeekEventStyle(segment) {
            const leftPercent = ((segment.startCol - 1) / 7) * 100;
            const widthPercent = (segment.span / 7) * 100;
            const top = this.weekHeaderHeight + (segment.laneIndex * (this.weekEventHeight + this.weekEventGap));

            return {
                left: `calc(${leftPercent}% + 4px)`,
                width: `calc(${widthPercent}% - 8px)`,
                top: `${top}px`,
                height: `${this.weekEventHeight}px`,
            };
        },
        getWeekEventClasses(segment) {
            return {
                "rounded-l-none": segment.continuesBefore,
                "rounded-r-none": segment.continuesAfter,
            };
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

                <!-- Calendar Grid - Week-based spanning event rows -->
                <div class="overflow-x-auto">
                    <div class="min-w-[900px]">
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

                        <div
                            v-for="(week, weekIndex) in calendarWeeks"
                            :key="`week-${weekIndex}`"
                            class="relative border-b border-slate-200 dark:border-slate-700"
                            :style="{ minHeight: getWeekMinHeight(weekIndex) }"
                        >
                            <div class="grid grid-cols-7">
                                <div
                                    v-for="(day, dayIndex) in week"
                                    :key="day.key"
                                    class="relative border-r border-slate-200 dark:border-slate-700 px-2 py-1.5 transition-all hover:bg-slate-50 dark:hover:bg-slate-700/30"
                                    :class="{
                                        'border-r-0': dayIndex === 6,
                                        'bg-slate-50/30 dark:bg-slate-800/20': !day.inCurrentMonth,
                                    }"
                                    :style="{ minHeight: getWeekMinHeight(weekIndex) }"
                                >
                                    <div class="relative z-[1] flex items-start justify-between">
                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold transition-colors"
                                            :class="{
                                                'bg-primary-600 text-white shadow-md shadow-primary-500/30': day.isToday,
                                                'text-slate-700 dark:text-slate-200': day.inCurrentMonth && !day.isToday,
                                                'text-slate-400 dark:text-slate-500': !day.inCurrentMonth && !day.isToday,
                                            }"
                                        >
                                            {{ day.dayNumber }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="pointer-events-none absolute inset-x-0"
                                :style="{
                                    top: `${weekHeaderHeight}px`,
                                    bottom: `${weekRowBottomPadding}px`,
                                }"
                            >
                                <button
                                    v-for="segment in weekEventLanes[weekIndex]?.flat() || []"
                                    :key="`${weekIndex}-${segment.event.id}-${segment.startCol}-${segment.endCol}`"
                                    type="button"
                                    class="pointer-events-auto absolute flex items-center gap-1 overflow-hidden whitespace-nowrap rounded-md border-l-2 px-2 text-left text-[11px] font-medium text-slate-800 shadow-sm transition-all hover:brightness-95 dark:text-slate-100"
                                    :class="getWeekEventClasses(segment)"
                                    :style="{
                                        ...getWeekEventStyle(segment),
                                        backgroundColor: `${getEventColor(segment.event)}20`,
                                        borderLeftColor: getEventColor(segment.event),
                                    }"
                                    @click="handleEventClick(segment.event)"
                                >
                                    <span
                                        v-if="segment.continuesBefore"
                                        class="text-[10px] text-slate-500 dark:text-slate-300"
                                    >
                                        ◀
                                    </span>
                                    <span class="truncate">
                                        {{ segment.event.label }}
                                    </span>
                                    <span
                                        v-if="segment.continuesAfter"
                                        class="text-[10px] text-slate-500 dark:text-slate-300"
                                    >
                                        ▶
                                    </span>
                                </button>
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

</style>
