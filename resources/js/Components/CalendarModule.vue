<script>
import { Link } from "lucide-vue-next";

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
        // NEW: Max visible event lanes per week before showing "more" indicator
        maxEventLanes: {
            type: Number,
            default: 4,
        },
    },
    data() {
        return {
            currentDate: this.startDate ? new Date(this.startDate) : new Date(),
            filterType: "all",
            filterStatus: "all",
            weekDays: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            showFilters: false,
        };
    },
    computed: {
        normalizedEvents() {
            return (this.events || []).map((event) => ({
                ...event,
                label: event.label || event.title || event.purpose || "(Untitled)",
                subtitle: event.subtitle || event.requested_by || "",
                type: this.normalizeTypeValue(event.type || event.vehicle_type || "GENERAL"),
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
        calendarWeeks() {
            const weeks = [];
            let currentWeek = [];

            for (let i = 0; i < this.firstDayOfMonth; i++) {
                currentWeek.push(null);
            }

            for (let day = 1; day <= this.daysInMonth; day++) {
                currentWeek.push(day);
                if (currentWeek.length === 7) {
                    weeks.push([...currentWeek]);
                    currentWeek = [];
                }
            }

            if (currentWeek.length > 0) {
                while (currentWeek.length < 7) {
                    currentWeek.push(null);
                }
                weeks.push(currentWeek);
            }

            return weeks;
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
                        key: key,
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
        activeFilterCount() {
            let count = 0;
            if (this.showTypeFilter && this.filterType !== "all") count++;
            if (this.showStatusFilter && this.filterStatus !== "all") count++;
            return count;
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
        isToday(day) {
            const today = new Date();
            return day === today.getDate() && this.currentDate.getMonth() === today.getMonth() && this.currentDate.getFullYear() === today.getFullYear();
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
        getEventsForWeek(weekDays, weekIndex) {
            const weekStartDate = this.getWeekStartDate(weekDays);
            const weekEndDate = this.getWeekEndDate(weekDays);

            return this.filteredEvents.filter((event) => {
                const dateFrom = this.toDateOnly(event.date_from || event.start_at || event.started_at);
                const dateTo = this.toDateOnly(event.date_to || event.end_at || event.end_use_at || event.date_from);
                if (!dateFrom || !dateTo) return false;

                return dateFrom <= weekEndDate && dateTo >= weekStartDate;
            });
        },
        getWeekStartDate(weekDays) {
            const firstDay = weekDays.find((d) => d !== null);
            if (!firstDay) return null;
            return this.formatLocalDate(new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), firstDay));
        },
        getWeekEndDate(weekDays) {
            const lastDay = [...weekDays].reverse().find((d) => d !== null);
            if (!lastDay) return null;
            return this.formatLocalDate(new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), lastDay));
        },
        getEventWeekLayout(event, weekDays) {
            const eventStart = this.toDateOnly(event.date_from || event.start_at || event.started_at);
            const eventEnd = this.toDateOnly(event.date_to || event.end_at || event.end_use_at || event.date_from);

            const weekStart = this.getWeekStartDate(weekDays);
            const weekEnd = this.getWeekEndDate(weekDays);

            const visibleStart = eventStart < weekStart ? weekStart : eventStart;
            const visibleEnd = eventEnd > weekEnd ? weekEnd : eventEnd;

            const startCol = this.getDayColumn(visibleStart, weekDays);
            const endCol = this.getDayColumn(visibleEnd, weekDays);

            return {
                startCol,
                endCol,
                span: endCol - startCol + 1,
            };
        },
        getDayColumn(dateStr, weekDays) {
            const dayOfMonth = Number(String(dateStr).slice(8, 10));
            const col = weekDays.findIndex((d) => d === dayOfMonth);
            return col >= 0 ? col : 0;
        },
        // OPTIMIZED: Assign lanes with max limit and overflow detection
        assignEventLanes(events, weekDays) {
            const lanes = [];
            let overflowEvents = [];

            events.forEach((event) => {
                const layout = this.getEventWeekLayout(event, weekDays);

                let laneIndex = 0;
                let placed = false;

                while (!placed && laneIndex < this.maxEventLanes) {
                    if (!lanes[laneIndex]) {
                        lanes[laneIndex] = [];
                    }

                    const hasConflict = lanes[laneIndex].some((existingEvent) => {
                        const existingLayout = this.getEventWeekLayout(existingEvent, weekDays);
                        return !(layout.endCol < existingLayout.startCol || layout.startCol > existingLayout.endCol);
                    });

                    if (!hasConflict) {
                        lanes[laneIndex].push(event);
                        placed = true;
                    } else {
                        laneIndex++;
                    }
                }

                // If couldn't place in max lanes, add to overflow
                if (!placed) {
                    overflowEvents.push(event);
                }
            });

            return { lanes, overflowCount: overflowEvents.length, overflowEvents };
        },
        getEventColor(event) {
            if (event.status && this.statusColors?.[event.status]) {
                return this.statusColors[event.status];
            }
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
                console.warn("No Route configured:", event);
            }
        },
        // NEW: Handle clicking the overflow indicator
        handleOverflowClick(weekDays, overflowEvents) {
            // Emit event or open modal with overflow events
            this.$emit("show-overflow-events", {
                weekStart: this.getWeekStartDate(weekDays),
                weekEnd: this.getWeekEndDate(weekDays),
                events: overflowEvents,
            });
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
    <div class="flex flex-col gap-4">
        <!-- Top Control Bar -->
        <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white/80 p-4 backdrop-blur-lg sm:p-5 dark:border-slate-800 dark:bg-slate-900/80">
            <!-- Header & Toggle -->
            <div class="flex flex-col items-stretch justify-between gap-4 sm:flex-row sm:items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-primary-100 dark:bg-primary-900/30 rounded-xl p-2.5">
                        <lu-calendar-days class="h-5 w-5 text-gray-800 sm:h-6 sm:w-6 dark:text-gray-100" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold leading-tight text-slate-900 sm:text-lg dark:text-white">
                            {{ title }}
                        </h2>
                        <p
                            v-if="subtitle"
                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="showFilters = !showFilters"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border px-3.5 py-2 text-xs font-semibold transition-all active:scale-95"
                    :class="showFilters || activeFilterCount > 0 ? 'border-emerald-300 bg-emerald-50 text-emerald-700 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'">
                    <lu-filter class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span>Filters</span>
                    <span
                        v-if="activeFilterCount > 0"
                        class="rounded-full bg-emerald-600 px-1.5 py-0.5 text-[0.65rem] font-bold text-white">
                        {{ activeFilterCount }}
                    </span>
                    <lu-chevron-up-icon
                        v-if="showFilters"
                        class="h-3.5 w-3.5 text-slate-400" />
                    <lu-chevron-down
                        v-else
                        class="h-3.5 w-3.5 text-slate-400" />
                </button>
            </div>

            <!-- Collapsible Filters & Legend (Compact) -->
            <transition-container type="pop-in">
                <div
                    v-if="showFilters"
                    class="mt-1 flex flex-col gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                    <!-- Filters -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div
                            v-if="showTypeFilter && typeOptions.length"
                            class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Type:</span>
                            <div class="relative">
                                <select
                                    v-model="filterType"
                                    class="cursor-pointer appearance-none rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-7 pr-7 text-xs text-slate-900 transition-all focus:border-transparent focus:ring-2 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="all">All Resources</option>
                                    <option
                                        v-for="option in typeOptions"
                                        :key="option.key"
                                        :value="option.key">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <lu-filter class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>

                        <div
                            v-if="showStatusFilter"
                            class="flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Status:</span>
                            <div class="relative">
                                <select
                                    v-model="filterStatus"
                                    class="cursor-pointer appearance-none rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-7 pr-7 text-xs text-slate-900 transition-all focus:border-transparent focus:ring-2 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="all">All Statuses</option>
                                    <option
                                        v-for="option in statusOptions"
                                        :key="option.key"
                                        :value="option.key">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <lu-check-circle-2 class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                            </div>
                        </div>

                        <button
                            v-if="showToday"
                            type="button"
                            @click="goToToday"
                            class="ml-auto inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition-all hover:bg-slate-200 sm:ml-0 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                            <lu-refresh-cw class="h-3.5 w-3.5" />
                            Today
                        </button>
                    </div>
                </div>
            </transition-container>
        </div>

        <!-- Main Calendar Area -->
        <main
            class="min-w-0 flex-1"
            data-guide="calendar-main-area">
            <div class="rounded-2xl border border-gray-100 bg-white/80 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/80">
                <!-- Calendar Header -->
                <div class="sticky top-0 z-10 flex items-center justify-between rounded-t-2xl border-b border-gray-100 bg-slate-50/80 px-4 py-3 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-800/80">
                    <button
                        type="button"
                        @click="previousMonth"
                        class="rounded-lg border border-transparent p-2 text-slate-600 shadow-sm transition-all hover:border-slate-200 hover:bg-white hover:text-slate-900 hover:shadow dark:text-slate-400 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white">
                        <lu-chevron-left-icon />
                    </button>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ monthYearLabel }}
                    </h3>

                    <button
                        type="button"
                        @click="nextMonth"
                        class="rounded-lg border border-transparent p-2 text-slate-600 shadow-sm transition-all hover:border-slate-200 hover:bg-white hover:text-slate-900 hover:shadow dark:text-slate-400 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white">
                        <lu-chevron-right />
                    </button>
                </div>

                <!-- Calendar Grid -->
                <div class="overflow-x-auto">
                    <div class="min-w-[900px]">
                        <!-- Week Headers -->
                        <div class="grid grid-cols-7 border-b border-gray-100 dark:border-slate-800">
                            <div
                                v-for="(day, index) in weekDays"
                                :key="day"
                                class="bg-slate-50/50 px-3 py-2 text-center text-xs font-semibold text-slate-600 dark:bg-slate-800/50 dark:text-slate-400"
                                :class="{
                                    'text-red-500 dark:text-red-400': index === 0 || index === 6,
                                }">
                                {{ day }}
                            </div>
                        </div>

                        <!-- Calendar Weeks -->
                        <div class="calendar-weeks">
                            <div
                                v-for="(week, weekIndex) in calendarWeeks"
                                :key="weekIndex"
                                class="calendar-week border-b border-gray-100 dark:border-slate-800">
                                <!-- OPTIMIZED: Use CSS Grid instead of absolute positioning -->
                                <div
                                    class="week-grid"
                                    :style="{
                                        gridTemplateRows: `auto repeat(${Math.max(1, assignEventLanes(getEventsForWeek(week, weekIndex), week).lanes.length)}, minmax(28px, auto)) auto`,
                                    }">
                                    <!-- Day Numbers Row -->
                                    <div
                                        v-for="(day, dayIndex) in week"
                                        :key="`day-${weekIndex}-${dayIndex}`"
                                        class="day-cell border-r border-gray-100 p-2 transition-all hover:bg-slate-50/80 dark:border-slate-800 dark:hover:bg-slate-700/30"
                                        :class="{
                                            'bg-slate-50/30 dark:bg-slate-800/20': !day,
                                            'border-r-0': dayIndex === 6,
                                        }">
                                        <div
                                            v-if="day"
                                            class="flex flex-col">
                                            <span
                                                class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold transition-colors"
                                                :class="{
                                                    'shadow-primary-500/30 bg-indigo-500 text-white shadow-md': isToday(day),
                                                    'text-slate-700 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-600': !isToday(day),
                                                }">
                                                {{ day }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Event Lanes - Now part of the grid flow -->
                                    <template v-if="getEventsForWeek(week, weekIndex).length > 0">
                                        <div
                                            v-for="(lane, laneIndex) in assignEventLanes(getEventsForWeek(week, weekIndex), week).lanes"
                                            :key="`lane-${weekIndex}-${laneIndex}`"
                                            class="event-lane contents">
                                            <!-- Empty cells for days without events in this lane -->
                                            <div
                                                v-for="col in 7"
                                                :key="`lane-${laneIndex}-col-${col}`"
                                                class="event-cell border-r border-gray-100 dark:border-slate-800"
                                                :class="{ 'border-r-0': col === 7 }">
                                                <!-- Find event for this column -->
                                                <div
                                                    v-for="event in lane.filter((e) => {
                                                        const layout = getEventWeekLayout(e, week);
                                                        return layout.startCol === col - 1;
                                                    })"
                                                    :key="`event-${event.id}-${weekIndex}`"
                                                    class="event-bar pointer-events-auto mx-0.5 cursor-pointer overflow-hidden rounded p-1.5 transition-all hover:scale-[1.02] hover:shadow-md active:scale-[0.98]"
                                                    :style="{
                                                        backgroundColor: getEventColor(event) + '20',
                                                        borderLeft: `3px solid ${getEventColor(event)}`,
                                                        width: `calc(${getEventWeekLayout(event, week).span * 100}% - 4px)`,
                                                        zIndex: 10,
                                                    }"
                                                    :title="event.subtitle ? event.label + ' - ' + event.subtitle : event.label"
                                                    @click="handleEventClick(event)">
                                                    <div class="flex h-full flex-col justify-center">
                                                        <div class="truncate text-xs font-medium text-slate-900 dark:text-slate-100">
                                                            {{ event.label }}
                                                        </div>
                                                        <div
                                                            v-if="event.subtitle"
                                                            class="truncate text-[11px] text-slate-600 dark:text-slate-400">
                                                            {{ event.subtitle }}
                                                        </div>
                                                        <div
                                                            v-if="event.status"
                                                            :style="{
                                                                color: statusColors[event.status] || '#6B7280',
                                                            }"
                                                            class="truncate text-[11px] font-semibold capitalize">
                                                            {{ event.status }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Overflow Indicator Row -->
                                    <div
                                        v-if="assignEventLanes(getEventsForWeek(week, weekIndex), week).overflowCount > 0"
                                        class="overflow-indicator contents">
                                        <div
                                            v-for="col in 7"
                                            :key="`overflow-${weekIndex}-${col}`"
                                            class="border-r border-gray-100 p-1 dark:border-slate-800"
                                            :class="{ 'border-r-0': col === 7 }">
                                            <!-- Show indicator only on first column -->
                                            <Dropdown
                                                v-if="col === 1"
                                                align="left"
                                                width="auto"
                                                max-height="16rem">
                                                <template #trigger>
                                                    <button
                                                        type="button"
                                                        class="w-full rounded border border-slate-300 px-1.5 py-1 text-left text-xs text-gray-700 transition-colors hover:bg-slate-100 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-700">
                                                        +{{ assignEventLanes(getEventsForWeek(week, weekIndex), week).overflowCount }}
                                                        more
                                                    </button>
                                                </template>

                                                <template #content>
                                                    <div class="w-72 max-w-[85vw] space-y-1 p-2">
                                                        <div
                                                            v-for="event in assignEventLanes(getEventsForWeek(week, weekIndex), week).overflowEvents"
                                                            :key="`overflow-${event.id}-${weekIndex}`"
                                                            :style="{
                                                                backgroundColor: getEventColor(event) + '20',
                                                                borderColor: getEventColor(event),
                                                            }"
                                                            class="cursor-pointer rounded border-l-2 p-1.5 text-xs transition-opacity hover:opacity-80 hover:shadow-md"
                                                            :title="event.subtitle ? event.label + ' - ' + event.subtitle : event.label"
                                                            @click="handleEventClick(event)">
                                                            <div class="truncate font-medium text-slate-900 dark:text-slate-100">
                                                                {{ event.label }}
                                                            </div>
                                                            <div
                                                                v-if="event.subtitle"
                                                                class="truncate text-slate-600 dark:text-slate-400">
                                                                {{ event.subtitle }}
                                                            </div>
                                                            <div
                                                                v-if="event.status"
                                                                :style="{
                                                                    color: statusColors[event.status] || '#6B7280',
                                                                }"
                                                                class="truncate text-xs font-semibold capitalize">
                                                                {{ event.status }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </Dropdown>
                                        </div>
                                    </div>

                                    <!-- Empty state for weeks with no events -->
                                    <div
                                        v-if="getEventsForWeek(week, weekIndex).length === 0"
                                        class="empty-row contents">
                                        <div
                                            v-for="col in 7"
                                            :key="`empty-${weekIndex}-${col}`"
                                            class="min-h-[60px] border-r border-gray-100 dark:border-slate-800"
                                            :class="{ 'border-r-0': col === 7 }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 flex w-full items-center justify-between px-4">
                <!-- Stats in Footer -->
                <div
                    v-if="showStats"
                    class="flex items-center gap-4 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs dark:border-slate-800 dark:bg-slate-800/50">
                    <span class="text-slate-500 dark:text-slate-400">
                        Total:
                        <span class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ stats.total }}
                        </span>
                    </span>
                    <span class="text-slate-500 dark:text-slate-400">
                        Visible:
                        <span class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ stats.visible }}
                        </span>
                    </span>
                </div>
                <div v-else></div>

                <a
                    :href="route('google-calendar.rentals')"
                    class="text-xs text-blue-500 transition-colors hover:text-blue-600 hover:underline"
                    target="_blank"
                    rel="noopener noreferrer">
                    Add to Google Calendar
                </a>
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

/* OPTIMIZED: Use CSS Grid for the week layout */
.calendar-week {
    position: relative;
}

.week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    /* Rows: day numbers + event lanes + optional overflow row */
    min-height: 100px;
}

.day-cell {
    min-height: 40px;
    grid-row: 1;
}

.event-lane {
    display: contents;
}

.event-cell {
    position: relative;
    min-height: 64px;
    padding: 2px 0;
}

.event-bar {
    position: absolute;
    top: 2px;
    left: 2px;
    min-height: 60px;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.event-bar:hover {
    z-index: 20;
    transform: translateY(-1px);
}

.overflow-indicator {
    display: contents;
}

.empty-row {
    display: contents;
}

/* Ensure proper border handling */
.border-r-0 {
    border-right-width: 0;
}
</style>
