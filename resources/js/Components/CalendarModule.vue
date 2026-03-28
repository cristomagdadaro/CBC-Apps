<script>
import { Link } from 'lucide-vue-next';

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
        };
    },
    computed: {
        normalizedEvents() {
            return (this.events || []).map((event) => ({
                ...event,
                label: event.label || event.title || event.purpose || "(Untitled)",
                subtitle: event.subtitle || event.requested_by || "",
                type: this.normalizeTypeValue(
                    event.type || event.vehicle_type || "GENERAL",
                ),
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
                list = list.filter(
                    (event) => event.status === selectedStatus,
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
                    items: Object.entries(this.statusColors).map(
                        ([key, value]) => ({
                            label: key.charAt(0).toUpperCase() + key.slice(1),
                            color: value,
                            key: key,
                            filterKey: this.normalizeStatusValue(key),
                            filterTarget: "status",
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
        normalizeTypeValue(value) {
            return String(value || "general").trim().toUpperCase();
        },
        normalizeStatusValue(value) {
            return String(value || "").trim().toLowerCase();
        },
        formatLocalDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");

            return `${year}-${month}-${day}`;
        },
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

                return dateFrom <= weekEndDate && dateTo >= weekStartDate;
            });
        },
        getWeekStartDate(weekDays) {
            const firstDay = weekDays.find((d) => d !== null);
            if (!firstDay) return null;
            return this.formatLocalDate(new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth(),
                firstDay,
            ));
        },
        getWeekEndDate(weekDays) {
            const lastDay = [...weekDays].reverse().find((d) => d !== null);
            if (!lastDay) return null;
            return this.formatLocalDate(new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth(),
                lastDay,
            ));
        },
        getEventWeekLayout(event, weekDays) {
            const eventStart = this.toDateOnly(
                event.date_from || event.start_at || event.started_at,
            );
            const eventEnd = this.toDateOnly(
                event.date_to || event.end_at || event.end_use_at || event.date_from,
            );

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
                        return !(layout.endCol < existingLayout.startCol ||
                            layout.startCol > existingLayout.endCol);
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
            const typeMatch = this.typeOptions.find(
                (item) => this.normalizeTypeValue(item.key) === event.type,
            );
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
            this.$emit('show-overflow-events', {
                weekStart: this.getWeekStartDate(weekDays),
                weekEnd: this.getWeekEndDate(weekDays),
                events: overflowEvents
            });
        },
        getBookingsForDate(day) {
            if (!day) return [];

            const dateStr = this.formatLocalDate(new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth(),
                day,
            ));

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
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-primary-100 dark:bg-primary-900/30 rounded-xl">
                        <lu-calendar-days class="text-gray-800 dark:text-gray-100" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ title }}
                        </h2>
                        <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 space-y-4">
                <h3
                    class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <lu-filter class="w-4 h-4" />
                    Filters
                </h3>

                <div v-if="showTypeFilter && typeOptions.length" class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">
                        Resource Type
                    </label>
                    <div class="relative">
                        <select v-model="filterType"
                            class="w-full pl-9 pr-8 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all appearance-none cursor-pointer">
                            <option value="all">All Resources</option>
                            <option v-for="option in typeOptions" :key="option.key" :value="option.key">
                                {{ option.label }}
                            </option>
                        </select>
                        <lu-filter
                            class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    </div>
                </div>

                <div v-if="showStatusFilter" class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">
                        Status
                    </label>
                    <div class="relative">
                        <select v-model="filterStatus"
                            class="w-full pl-9 pr-8 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all appearance-none cursor-pointer">
                            <option value="all">All Statuses</option>
                            <option v-for="option in statusOptions" :key="option.key" :value="option.key">
                                {{ option.label }}
                            </option>
                        </select>
                        <lu-check-circle-2
                            class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    </div>
                </div>

                <button v-if="showToday" type="button" @click="goToToday"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 dark:bg-primary-600 dark:hover:bg-primary-500 text-gray-900 font-medium transition-all shadow-md hover:shadow-lg active:scale-[0.98]">
                    <lu-refresh-cw />
                    Jump to Today
                </button>
            </div>

            <!-- Legend -->
            <div v-if="showLegend"
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 space-y-4">
                <h3
                    class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <lu-layers />
                    Legend
                </h3>

                <div class="space-y-3">
                    <div v-for="group in legendData" :key="group.title">
                        <h4
                            class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            {{ group.title }}
                        </h4>
                        <div class="space-y-1">
                            <div v-for="item in group.items" :key="item.label"
                                class="flex items-center gap-2.5 p-1.5 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer group"
                                @click="handleLegendClick(item)">
                                <div class="w-2.5 h-2.5 rounded-full ring-2 ring-offset-1 ring-offset-white dark:ring-offset-slate-800 transition-all"
                                    :style="{
                                        backgroundColor: item.color,
                                        ringColor: item.color + '40',
                                    }"></div>
                                <span
                                    class="text-xs text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">{{
                                    item.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div v-if="showStats"
                class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <h3
                    class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-2">
                    <lu-layout-grid />
                    Overview
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div
                        class="bg-white dark:bg-slate-700 rounded-xl p-3 border border-slate-200 dark:border-slate-600 shadow-sm">
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">
                            {{ stats.total }}
                        </div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Total
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-slate-700 rounded-xl p-3 border border-slate-200 dark:border-slate-600 shadow-sm">
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">
                            {{ stats.visible }}
                        </div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Visible
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Calendar Area -->
        <main class="flex-1 min-w-0">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <!-- Calendar Header -->
                <div
                    class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-t-2xl sticky top-0 z-10">
                    <button type="button" @click="previousMonth"
                        class="p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all shadow-sm hover:shadow border border-transparent hover:border-slate-200 dark:hover:border-slate-600">
                        <lu-chevron-left-icon />
                    </button>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ monthYearLabel }}
                    </h3>

                    <button type="button" @click="nextMonth"
                        class="p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all shadow-sm hover:shadow border border-transparent hover:border-slate-200 dark:hover:border-slate-600">
                        <lu-chevron-right />
                    </button>
                </div>

                <!-- Calendar Grid -->
                <div class="overflow-x-auto">
                    <div class="min-w-[900px]">
                        <!-- Week Headers -->
                        <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-700">
                            <div v-for="(day, index) in weekDays" :key="day"
                                class="px-3 py-2 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50"
                                :class="{
                                    'text-red-500 dark:text-red-400':
                                        index === 0 || index === 6,
                                }">
                                {{ day }}
                            </div>
                        </div>

                        <!-- Calendar Weeks -->
                        <div class="calendar-weeks">
                            <div v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex"
                                class="calendar-week border-b border-slate-200 dark:border-slate-700">
                                <!-- OPTIMIZED: Use CSS Grid instead of absolute positioning -->
                                <div class="week-grid" :style="{
                                    gridTemplateRows: `auto repeat(${Math.max(1, assignEventLanes(getEventsForWeek(week, weekIndex), week).lanes.length)}, minmax(28px, auto)) auto`
                                }">
                                    <!-- Day Numbers Row -->
                                    <div v-for="(day, dayIndex) in week" :key="`day-${weekIndex}-${dayIndex}`"
                                        class="day-cell border-r border-slate-200 dark:border-slate-700 p-2 transition-all hover:bg-slate-50 dark:hover:bg-slate-700/30"
                                        :class="{
                                            'bg-slate-50/30 dark:bg-slate-800/20': !day,
                                            'border-r-0': dayIndex === 6,
                                        }">
                                        <div v-if="day" class="flex flex-col">
                                            <span
                                                class="text-sm font-semibold w-7 h-7 flex items-center justify-center rounded-full transition-colors"
                                                :class="{
                                                    'bg-indigo-500 text-white shadow-md shadow-primary-500/30':
                                                        isToday(day),
                                                    'text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600':
                                                        !isToday(day),
                                                }">
                                                {{ day }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Event Lanes - Now part of the grid flow -->
                                    <template v-if="getEventsForWeek(week, weekIndex).length > 0">
                                        <div v-for="(lane, laneIndex) in assignEventLanes(getEventsForWeek(week, weekIndex), week).lanes"
                                            :key="`lane-${weekIndex}-${laneIndex}`" class="event-lane contents">
                                            <!-- Empty cells for days without events in this lane -->
                                            <div v-for="col in 7" :key="`lane-${laneIndex}-col-${col}`"
                                                class="event-cell border-r border-slate-200 dark:border-slate-700"
                                                :class="{ 'border-r-0': col === 7 }">
                                                <!-- Find event for this column -->
                                                <div v-for="event in lane.filter(e => {
                                                    const layout = getEventWeekLayout(e, week);
                                                    return layout.startCol === col - 1;
                                                })" :key="`event-${event.id}-${weekIndex}`"
                                                    class="event-bar rounded cursor-pointer pointer-events-auto hover:shadow-md transition-all hover:scale-[1.02] active:scale-[0.98] overflow-hidden mx-0.5 p-1.5"
                                                    :style="{
                                                        backgroundColor: getEventColor(event) + '20',
                                                        borderLeft: `3px solid ${getEventColor(event)}`,
                                                        width: `calc(${getEventWeekLayout(event, week).span * 100}% - 4px)`,
                                                        zIndex: 10
                                                    }"
                                                    :title="event.subtitle ? event.label + ' - ' + event.subtitle : event.label"
                                                    @click="handleEventClick(event)">
                                                    <div class="flex h-full flex-col justify-center">
                                                        <div
                                                            class="font-medium text-xs text-slate-900 dark:text-slate-100 truncate">
                                                            {{ event.label }}
                                                        </div>
                                                        <div v-if="event.subtitle"
                                                            class="text-[11px] text-slate-600 dark:text-slate-400 truncate">
                                                            {{ event.subtitle }}
                                                        </div>
                                                        <div v-if="event.status"
                                                            :style="{ color: statusColors[event.status] || '#6B7280' }"
                                                            class="text-[11px] font-semibold capitalize truncate">
                                                            {{ event.status }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Overflow Indicator Row -->
                                    <div v-if="assignEventLanes(getEventsForWeek(week, weekIndex), week).overflowCount > 0"
                                        class="overflow-indicator contents">
                                        <div v-for="col in 7" :key="`overflow-${weekIndex}-${col}`"
                                            class="border-r border-slate-200 dark:border-slate-700 p-1"
                                            :class="{ 'border-r-0': col === 7 }">
                                            <!-- Show indicator only on first column -->
                                            <Dropdown v-if="col === 1" align="left" width="auto" max-height="16rem">
                                                <template #trigger>
                                                    <button type="button"
                                                        class="text-xs w-full text-left px-1.5 py-1 rounded border border-slate-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                        +{{ assignEventLanes(getEventsForWeek(week, weekIndex),
                                                        week).overflowCount }} more
                                                    </button>
                                                </template>

                                                <template #content>
                                                    <div class="w-72 max-w-[85vw] p-2 space-y-1">
                                                        <div v-for="event in assignEventLanes(getEventsForWeek(week, weekIndex), week).overflowEvents"
                                                            :key="`overflow-${event.id}-${weekIndex}`" :style="{
                                                                backgroundColor: getEventColor(event) + '20',
                                                                borderColor: getEventColor(event),
                                                            }"
                                                            class="text-xs p-1.5 rounded border-l-2 cursor-pointer hover:opacity-80 hover:shadow-md transition-opacity"
                                                            :title="event.subtitle ? event.label + ' - ' + event.subtitle : event.label"
                                                            @click="handleEventClick(event)">
                                                            <div
                                                                class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                                                {{ event.label }}
                                                            </div>
                                                            <div v-if="event.subtitle"
                                                                class="text-slate-600 dark:text-slate-400 truncate">
                                                                {{ event.subtitle }}
                                                            </div>
                                                            <div v-if="event.status"
                                                                :style="{ color: statusColors[event.status] || '#6B7280' }"
                                                                class="text-xs font-semibold capitalize truncate">
                                                                {{ event.status }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </Dropdown>
                                        </div>
                                    </div>

                                    <!-- Empty state for weeks with no events -->
                                    <div v-if="getEventsForWeek(week, weekIndex).length === 0"
                                        class="empty-row contents">
                                        <div v-for="col in 7" :key="`empty-${weekIndex}-${col}`"
                                            class="border-r border-slate-200 dark:border-slate-700 min-h-[60px]"
                                            :class="{ 'border-r-0': col === 7 }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-2 mr-5 w-full">
                <a :href="route('google-calendar.rentals')" class="text-xs text-blue-500" target="_blank"
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