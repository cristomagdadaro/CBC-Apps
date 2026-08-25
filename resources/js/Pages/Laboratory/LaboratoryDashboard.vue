<script>
import axios from "axios";
import { Chart, BarController, BarElement, DoughnutController, ArcElement, CategoryScale, LinearScale, Tooltip, Legend } from "chart.js";
import { BarChart3, PieChart, Clock, Flame, Activity, Info, Cpu, CheckCircle2, AlertTriangle, Layers, RefreshCw } from "lucide-vue-next";
import CalendarModule from "@/Components/CalendarModule.vue";
import EquipmentLoggerAsset from "@/Modules/domain/EquipmentLoggerAsset";
import EquipmentLoggerPersonnel from "@/Modules/domain/EquipmentLoggerPersonnel";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import LaboratoryLogHeaderAction from "@/Pages/Laboratory/components/LaboratoryLogHeaderAction.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";

Chart.register(BarController, BarElement, DoughnutController, ArcElement, CategoryScale, LinearScale, Tooltip, Legend);

export default {
    name: "LaboratoryDashboard",
    components: {
        CalendarModule,
        LaboratoryLogHeaderAction,
        BarChart3,
        PieChart,
        Clock,
        Flame,
        Activity,
        Info,
        Cpu,
        CheckCircle2,
        AlertTriangle,
        Layers,
        RefreshCw,
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        const today = new Date();
        const selectedDate = [today.getFullYear(), String(today.getMonth() + 1).padStart(2, "0"), String(today.getDate()).padStart(2, "0")].join("-");
        const weekDate = new Date(Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()));
        const weekDay = weekDate.getUTCDay() || 7;
        weekDate.setUTCDate(weekDate.getUTCDate() + 4 - weekDay);
        const yearStart = new Date(Date.UTC(weekDate.getUTCFullYear(), 0, 1));
        const selectedWeek = `${weekDate.getUTCFullYear()}-W${String(Math.ceil(((weekDate - yearStart) / 86400000 + 1) / 7)).padStart(2, "0")}`;
        const selectedMonth = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}`;

        return {
            scope: "month",
            selectedDate,
            selectedWeek,
            selectedMonth,
            selectedYear: String(today.getFullYear()),
            scopeOptions: [
                { name: "all", label: "All Time (Entire Database)" },
                { name: "day", label: "Last 24 Hours" },
                { name: "daily", label: "Specific Day" },
                { name: "week", label: "Last 7 Days" },
                { name: "weekly", label: "Specific Week" },
                { name: "month", label: "Last 30 Days" },
                { name: "monthly", label: "Specific Month" },
                { name: "year", label: "Last 365 Days" },
                { name: "yearly", label: "Specific Year" },
            ],
            dashboard: null,
            loading: false,
            updatingLoggerMode: false,
            refreshTimer: null,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
            showLoggerModeModal: false,
            selectedEquipmentAsset: null,
            selectedLoggerMode: null,
            isUpdatingLoggerMode: false,

            showLocationModal: false,
            selectedLocationCode: null,
            isUpdatingLocation: false,
            loggerModeFormError: null,
            activeTab: "stats",
            chartMetricMode: "frequency", // 'frequency' or 'duration'
            tabs: [
                { key: "stats", label: "Usage Patterns & Analytics" },
                { key: "calendar", label: "Calendar" },
                { key: "logs", label: "Active Logs" },
                { key: "equipment-list", label: "Equipment List" },
                { key: "personnel-list", label: "Personnel List" },
            ],
            dayLabels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            heatLegend: [
                { label: "0 logs", className: "bg-slate-100 dark:bg-slate-800" },
                { label: "1-2 logs", className: "bg-lime-200 dark:bg-lime-900/60" },
                { label: "3-5 logs", className: "bg-lime-400 dark:bg-lime-700" },
                { label: "6-9 logs", className: "bg-lime-600 dark:bg-lime-600" },
                { label: "10+ logs", className: "bg-lime-800 dark:bg-lime-500" },
            ],
            mostUsedChartInstance: null,
            categoryChartInstance: null,
            durationChartInstance: null,
        };
    },
    computed: {
        dashboardParams() {
            const params = { scope: this.scope };
            if (this.scope === "daily") params.date = this.selectedDate;
            if (this.scope === "weekly") params.week = this.selectedWeek;
            if (this.scope === "monthly") params.month = this.selectedMonth;
            if (this.scope === "yearly") params.year = this.selectedYear;
            return params;
        },
        usesAnchoredPeriod() {
            return ["daily", "weekly", "monthly", "yearly"].includes(this.scope);
        },
        periodInputLabel() {
            return (
                {
                    daily: "Date",
                    weekly: "Week",
                    monthly: "Month",
                    yearly: "Year",
                }[this.scope] || "Period"
            );
        },
        periodInputType() {
            return (
                {
                    daily: "date",
                    weekly: "week",
                    monthly: "month",
                    yearly: "number",
                }[this.scope] || "text"
            );
        },
        periodInputValue() {
            return (
                {
                    daily: this.selectedDate,
                    weekly: this.selectedWeek,
                    monthly: this.selectedMonth,
                    yearly: this.selectedYear,
                }[this.scope] || ""
            );
        },
        scopeCaption() {
            const captions = {
                all: "across all time (entire database)",
                day: "Last 24 hours",
                daily: this.selectedDate ? `${this.formatDisplayDate(this.selectedDate)}` : "Selected day",
                week: "Last 7 days",
                weekly: this.selectedWeek ? `Week ${this.selectedWeek}` : "Selected week",
                month: "Last 30 days",
                monthly: this.selectedMonth ? `${this.formatDisplayMonth(this.selectedMonth)}` : "Selected month",
                year: "Last 365 days",
                yearly: this.selectedYear ? `${this.selectedYear}` : "Selected year",
            };
            return captions[this.scope] || "Current month";
        },
        activeLogs() {
            return this.dashboard?.active ?? [];
        },
        overdueLogs() {
            return this.dashboard?.overdue ?? [];
        },
        completedLogs() {
            return this.dashboard?.completed ?? [];
        },
        recentReports() {
            return this.dashboard?.recent_reports ?? [];
        },
        mostUsed() {
            return this.dashboard?.most_used ?? [];
        },
        activeCount() {
            return this.activeLogs.length;
        },
        overdueCount() {
            return this.overdueLogs.length;
        },
        activeUniqueEquipmentCount() {
            return new Set(this.activeLogs.map((log) => log.equipment_id)).size;
        },
        overdueUniqueEquipmentCount() {
            return new Set(this.overdueLogs.map((log) => log.equipment_id)).size;
        },
        currentInUseCount() {
            return this.activeCount + this.overdueCount;
        },
        totalSessionsCount() {
            return this.completedLogs.length + this.activeCount + this.overdueCount || 0;
        },
        currentWorkingLogs() {
            return [...this.activeLogs, ...this.overdueLogs].sort((a, b) => {
                const aTime = new Date(a?.started_at ?? 0).getTime();
                const bTime = new Date(b?.started_at ?? 0).getTime();

                return bTime - aTime;
            });
        },
        groupedActiveLogs() {
            return this.groupLogsByLocation(this.activeLogs);
        },
        groupedOverdueLogs() {
            return this.groupLogsByLocation(this.overdueLogs);
        },
        peakActivityStats() {
            let maxCount = 0;
            let peakDay = -1;
            let peakHour = -1;

            (this.dashboard?.heatmap ?? []).forEach((entry) => {
                const count = entry.usage_count ?? 0;
                if (count > maxCount) {
                    maxCount = count;
                    peakDay = Math.max(0, Math.min(6, (entry.day_of_week ?? 1) - 1));
                    peakHour = Math.max(0, Math.min(23, entry.hour_of_day ?? 0));
                }
            });

            if (maxCount === 0) {
                return { label: "No Peak Data", count: 0 };
            }

            const dayName = this.dayLabels[peakDay] ?? "Day";
            const ampm = peakHour >= 12 ? "PM" : "AM";
            const formattedHour = (peakHour % 12 || 12) + ":00 " + ampm;

            return {
                label: `${dayName} @ ${formattedHour}`,
                count: maxCount,
            };
        },
        busiestDayOfWeek() {
            const dayTotals = Array(7).fill(0);
            (this.dashboard?.heatmap ?? []).forEach((entry) => {
                const dayIndex = Math.max(0, Math.min(6, (entry.day_of_week ?? 1) - 1));
                dayTotals[dayIndex] += entry.usage_count ?? 0;
            });

            let maxDayIndex = 0;
            let maxTotal = 0;
            dayTotals.forEach((total, idx) => {
                if (total > maxTotal) {
                    maxTotal = total;
                    maxDayIndex = idx;
                }
            });

            return maxTotal > 0 ? `${this.dayLabels[maxDayIndex]} (${maxTotal} logs)` : "N/A";
        },
        equipmentTypeShares() {
            let ictCount = this.dashboard?.total_ict_logs ?? 0;
            let labCount = this.dashboard?.total_lab_logs ?? 0;

            const total = ictCount + labCount;
            if (total === 0) {
                return { ictCount: 0, labCount: 0, ictPercent: 50, labPercent: 50 };
            }

            return {
                ictCount,
                labCount,
                ictPercent: Math.round((ictCount / total) * 100),
                labPercent: Math.round((labCount / total) * 100),
            };
        },
        durationDistribution() {
            const tiers = {
                short: 0, // < 1 hour
                standard: 0, // 1 - 4 hours
                extended: 0, // 4 - 8 hours
                overnight: 0, // > 8 hours
            };

            const allLogs = [...this.completedLogs, ...this.activeLogs, ...this.overdueLogs];
            allLogs.forEach((log) => {
                const start = new Date(log.started_at ?? 0).getTime();
                const end = new Date(log.actual_end_at || log.end_use_at || Date.now()).getTime();
                const hours = (end - start) / (1000 * 3600);

                if (hours <= 1) tiers.short++;
                else if (hours <= 4) tiers.standard++;
                else if (hours <= 8) tiers.extended++;
                else tiers.overnight++;
            });

            return tiers;
        },
        calendarEvents() {
            const mapLog = (log) => ({
                id: log.id,
                type: "equipment",
                status: log.status,
                date_from: log.started_at,
                date_to: log.actual_end_at || log.end_use_at,
                label: log.equipment?.name || "Equipment",
                subtitle: this.formatPersonnelName(log.personnel),
                color: log.status === "overdue" ? "#EF4444" : log.status === "completed" ? "#9CA3AF" : "#10B981",
                checkoutPage: log.equipment_type === "ict" ? "ict.equipments.show" : "laboratory.equipments.show",
                checkoutPageId: log.equipment?.id,
                checkoutPageTarget: "_blank",
            });

            return [...this.activeLogs.map(mapLog), ...this.overdueLogs.map(mapLog), ...this.completedLogs.map(mapLog)];
        },
        calendarLegend() {
            return [
                {
                    title: "Status",
                    items: [
                        { label: "Active", color: "#10B981" },
                        { label: "Overdue", color: "#EF4444" },
                        { label: "Completed", color: "#9CA3AF" },
                    ],
                },
            ];
        },
        heatmap() {
            const rows = Array.from({ length: 7 }, () => Array(24).fill(0));

            (this.dashboard?.heatmap ?? []).forEach((entry) => {
                const dayIndex = Math.max(0, Math.min(6, (entry.day_of_week ?? 1) - 1));
                const hourIndex = Math.max(0, Math.min(23, entry.hour_of_day ?? 0));
                rows[dayIndex][hourIndex] = entry.usage_count ?? 0;
            });

            return rows;
        },
        EquipmentLoggerAsset() {
            return EquipmentLoggerAsset;
        },
        EquipmentLoggerPersonnel() {
            return EquipmentLoggerPersonnel;
        },
        loggerModeLabels() {
            const options = this.$page.props?.equipment_logger_mode_options;

            if (!Array.isArray(options)) {
                return {};
            }

            return options.reduce((labels, option) => {
                const key = option?.name ?? option?.value;

                if (key) {
                    labels[key] = option?.label ?? key;
                }
                return labels;
            }, {});
        },
        storageLocationOptions() {
            return this.$page.props?.storage_locations || [];
        },

        loggerModeOptions() {
            const options = this.$page.props?.equipment_logger_mode_options;

            if (!Array.isArray(options)) {
                return [];
            }

            return options
                .map((option) => ({
                    name: option?.name ?? option?.value ?? null,
                    label: option?.label ?? option?.name ?? option?.value ?? null,
                }))
                .filter((option) => option.name && option.label);
        },
        canSubmitLoggerModeUpdate() {
            return !!this.selectedEquipmentAsset && !!this.selectedLoggerMode && this.selectedLoggerMode !== this.selectedEquipmentAsset?.equipment_logger_mode && !this.updatingLoggerMode;
        },
    },
    methods: {
        equipmentShowRoute(model) {
            return model?.equipment_type === "ict" ? "ict.equipments.show" : "laboratory.equipments.show";
        },
        transactionShowHref(row) {
            if (!row?.latest_incoming_transaction_id) {
                return null;
            }

            return route("transactions.show", { id: row.latest_incoming_transaction_id });
        },
        heatColor(val) {
            if (val === 0) return "bg-slate-100 dark:bg-slate-800/40";
            if (val <= 2) return "bg-lime-200 dark:bg-lime-900/60 text-lime-950 dark:text-lime-200";
            if (val <= 5) return "bg-lime-400 dark:bg-lime-600 text-slate-950 dark:text-white";
            if (val <= 9) return "bg-lime-600 dark:bg-lime-500 text-white font-semibold";
            return "bg-lime-700 dark:bg-lime-400 text-white dark:text-slate-950 font-bold";
        },
        destroyCharts() {
            if (this.mostUsedChartInstance) {
                this.mostUsedChartInstance.destroy();
                this.mostUsedChartInstance = null;
            }
            if (this.categoryChartInstance) {
                this.categoryChartInstance.destroy();
                this.categoryChartInstance = null;
            }
            if (this.durationChartInstance) {
                this.durationChartInstance.destroy();
                this.durationChartInstance = null;
            }
        },
        buildAllCharts() {
            this.destroyCharts();
            this.buildMostUsedChart();
            this.buildCategoryChart();
            this.buildDurationChart();
        },
        buildMostUsedChart() {
            if (!this.$refs.mostUsedChartCanvas || !this.mostUsed.length) {
                return;
            }

            try {
                const canvas = this.$refs.mostUsedChartCanvas;
                if (!canvas || !canvas.offsetWidth || !canvas.offsetHeight) return;
                const ctx = canvas.getContext("2d");
                if (!ctx) return;

                const labels = this.mostUsed.map((item) => {
                    const name = item.equipment_name || item.equipment_id;
                    const barcode = item.equipment_barcode || item.barcode;
                    return barcode ? [name, `(${barcode})`] : name;
                });
                const isFreq = this.chartMetricMode === "frequency";
                const datasetData = isFreq ? this.mostUsed.map((item) => item.usage_count ?? 0) : this.mostUsed.map((item) => Number(((item.total_duration_seconds ?? 0) / 3600).toFixed(1)));

                this.mostUsedChartInstance = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels,
                        datasets: [
                            {
                                label: isFreq ? "Usage Sessions (Count)" : "Operating Duration (Hours)",
                                data: datasetData,
                                borderColor: isFreq ? "#65A30D" : "#0284C7",
                                backgroundColor: isFreq ? "rgba(101, 163, 13, 0.75)" : "rgba(2, 132, 199, 0.75)",
                                borderRadius: 8,
                                borderWidth: 0,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 600 },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                                labels: {
                                    boxWidth: 12,
                                    font: { size: 11, weight: "600" },
                                    color: "#64748B",
                                },
                            },
                            tooltip: {
                                backgroundColor: "#0F172A",
                                padding: 12,
                                titleFont: { size: 12, weight: "bold" },
                                bodyFont: { size: 12 },
                                cornerRadius: 8,
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    font: { size: 10, weight: "500" },
                                    color: "#64748B",
                                    maxRotation: 35,
                                    minRotation: 15,
                                },
                            },
                            y: {
                                grid: { color: "rgba(148, 163, 184, 0.15)" },
                                border: { display: false },
                                beginAtZero: true,
                                ticks: {
                                    font: { size: 10 },
                                    color: "#64748B",
                                },
                            },
                        },
                    },
                });
            } catch (error) {
                console.error("Error building most used chart:", error);
            }
        },
        buildCategoryChart() {
            if (!this.$refs.categoryChartCanvas) return;

            try {
                const canvas = this.$refs.categoryChartCanvas;
                if (!canvas || !canvas.offsetWidth || !canvas.offsetHeight) return;
                const ctx = canvas.getContext("2d");
                if (!ctx) return;

                const shares = this.equipmentTypeShares;

                this.categoryChartInstance = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: ["Laboratory Equipment", "ICT Equipment"],
                        datasets: [
                            {
                                data: [shares.labCount, shares.ictCount],
                                backgroundColor: ["#65A30D", "#0284C7"],
                                hoverBackgroundColor: ["#4D7C0F", "#0369A1"],
                                borderWidth: 3,
                                borderColor: "transparent",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: "72%",
                        plugins: {
                            legend: {
                                position: "bottom",
                                labels: {
                                    boxWidth: 12,
                                    font: { size: 11, weight: "600" },
                                    color: "#64748B",
                                },
                            },
                            tooltip: {
                                backgroundColor: "#0F172A",
                                padding: 10,
                                cornerRadius: 8,
                            },
                        },
                    },
                });
            } catch (error) {
                console.error("Error building category chart:", error);
            }
        },
        buildDurationChart() {
            if (!this.$refs.durationChartCanvas) return;

            try {
                const canvas = this.$refs.durationChartCanvas;
                if (!canvas || !canvas.offsetWidth || !canvas.offsetHeight) return;
                const ctx = canvas.getContext("2d");
                if (!ctx) return;

                const dist = this.durationDistribution;

                this.durationChartInstance = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: ["< 1 Hr (Quick)", "1 - 4 Hrs (Std)", "4 - 8 Hrs (Extended)", "> 8 Hrs (Overnight)"],
                        datasets: [
                            {
                                label: "Logs Count",
                                data: [dist.short, dist.standard, dist.extended, dist.overnight],
                                backgroundColor: ["#38BDF8", "#818CF8", "#F59E0B", "#F43F5E"],
                                borderRadius: 6,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 600 },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: "#0F172A",
                                padding: 10,
                                cornerRadius: 8,
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { font: { size: 10 }, color: "#64748B" },
                            },
                            y: {
                                grid: { color: "rgba(148, 163, 184, 0.15)" },
                                border: { display: false },
                                beginAtZero: true,
                                ticks: { font: { size: 10 }, color: "#64748B" },
                            },
                        },
                    },
                });
            } catch (error) {
                console.error("Error building duration chart:", error);
            }
        },
        async loadDashboard() {
            this.loading = true;

            try {
                const response = await axios.get(route("api.equipment-logger.dashboard"), {
                    params: this.dashboardParams,
                });
                const payload = response?.data?.data ?? response?.data ?? response;
                this.dashboard = payload?.data ?? payload;
            } finally {
                this.loading = false;
            }
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        configureRealtime() {
            this.cleanupRealtime();

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "private",
                    channel: "laboratory.logs",
                    event: "equipment.log.changed",
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ]);
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                this.loadDashboard();
            }, 400);
        },
        setMetricMode(mode) {
            this.chartMetricMode = mode;
            this.$nextTick(() => {
                this.buildMostUsedChart();
            });
        },
        groupLogsByLocation(logs) {
            if (!Array.isArray(logs)) return [];
            const groups = {};
            logs.forEach((log) => {
                const loc = log.location_label || log.location || "Unknown Location";
                if (!groups[loc]) {
                    groups[loc] = { location: loc, items: [] };
                }
                groups[loc].items.push(log);
            });
            return Object.values(groups);
        },
        formatPersonnelName(personnel) {
            if (!personnel) return "—";
            const parts = [personnel.fname, personnel.mname, personnel.lname, personnel.suffix]
                .filter(Boolean)
                .map((v) => String(v).trim())
                .filter(Boolean);
            return parts.length ? parts.join(" ") : "—";
        },
        equipmentTypeBadgeClass(type) {
            return type === "ict" ? "bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300" : "bg-lime-100 text-lime-700 dark:bg-lime-900/40 dark:text-lime-300";
        },
        equipmentTypeLabel(type) {
            return type === "ict" ? "ICT" : "Laboratory";
        },
        currentStatusBadgeClass(status) {
            const map = {
                active: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300",
                overdue: "bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300",
                completed: "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400",
            };
            return map[status] || "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400";
        },
        loggerModeBadgeClass(mode) {
            const map = {
                barcode: "bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300",
                manual: "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300",
                disabled: "bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400",
            };
            return map[mode] || "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400";
        },
        loggerModeLabel(mode) {
            return this.loggerModeLabels?.[mode] || mode || "—";
        },
        openLoggerModeModal(row) {
            this.selectedEquipmentAsset = row;
            this.selectedLoggerMode = row?.equipment_logger_mode ?? null;
            this.loggerModeFormError = null;
            this.showLoggerModeModal = true;
        },
        closeLoggerModeModal() {
            this.showLoggerModeModal = false;
            this.selectedEquipmentAsset = null;
            this.selectedLoggerMode = null;
            this.loggerModeFormError = null;
        },
        openLocationModal(row) {
            this.selectedEquipmentAsset = row;
            this.selectedLocationCode = row?.current_location_code ?? null;
            this.showLocationModal = true;
        },
        closeLocationModal() {
            this.showLocationModal = false;
            this.selectedEquipmentAsset = null;
            this.selectedLocationCode = null;
        },
        async submitLocationUpdate() {
            if (!this.selectedLocationCode) {
                return;
            }
            this.isUpdatingLocation = true;
            try {
                await axios.post(
                    route("api.equipment-logger.equipments.update-location", {
                        equipmentId: this.selectedEquipmentAsset?.id || this.selectedEquipmentAsset?.equipment_id || this.selectedEquipmentAsset?.barcode,
                    }),
                    {
                        location_code: this.selectedLocationCode,
                    },
                );

                if (this.$refs.equipmentListTable?.refresh) {
                    this.$refs.equipmentListTable.refresh();
                }
                this.closeLocationModal();
            } catch (error) {
                console.error("Failed to update equipment location", error);
            } finally {
                this.isUpdatingLocation = false;
            }
        },
        async submitLoggerModeUpdate() {
            if (!this.canSubmitLoggerModeUpdate) return;

            this.updatingLoggerMode = true;
            this.loggerModeFormError = null;

            try {
                await this.fetchPutApi(
                    route("api.equipment-logger.update-logger-mode", {
                        equipment: this.selectedEquipmentAsset?.id,
                    }),
                    null,
                    { equipment_logger_mode: this.selectedLoggerMode },
                );
                this.closeLoggerModeModal();
                await this.loadDashboard();
                if (this.$refs.equipmentListTable?.refresh) {
                    this.$refs.equipmentListTable.refresh();
                }
            } catch (error) {
                this.loggerModeFormError = error?.response?.data?.message || error?.message || "Failed to update logger mode.";
            } finally {
                this.updatingLoggerMode = false;
            }
        },
    },
    watch: {
        dashboardParams: {
            deep: true,
            handler() {
                this.loadDashboard();
            },
        },
        mostUsed() {
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.buildAllCharts();
                });
            });
        },
        activeTab(newVal) {
            if (newVal === "stats") {
                this.$nextTick(() => {
                    requestAnimationFrame(() => {
                        this.buildAllCharts();
                    });
                });
            } else {
                this.destroyCharts();
            }
        },
    },
    async mounted() {
        await this.loadDashboard();
        this.configureRealtime();

        this.$nextTick(() => {
            requestAnimationFrame(() => {
                setTimeout(() => {
                    this.buildAllCharts();
                }, 200);
            });
        });
    },
    beforeUnmount() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }

        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
        this.destroyCharts();
    },
};
</script>

<template>
    <AppLayout title="Equipment Logger Dashboard">
        <template #header>
            <LaboratoryLogHeaderAction />
        </template>

        <div class="space-y-6">
            <div class="shadow-xs flex flex-col items-start justify-between gap-3 border-b border-slate-200 bg-white p-4 sm:p-5 md:flex-row md:items-center dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <Activity class="h-5 w-5 text-lime-600 dark:text-lime-400" />
                        <h2 class="text-lg font-bold tracking-tight sm:text-xl">Equipment Analytics</h2>
                    </div>
                    <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                        Viewing equipment logs
                        <span class="font-semibold text-lime-600 dark:text-lime-400">
                            {{ scopeCaption }}
                        </span>
                    </p>
                </div>

                <div class="flex w-full flex-wrap items-center gap-2.5 md:w-auto">
                    <div class="w-full sm:w-52">
                        <custom-dropdown
                            label="Filter Scope"
                            :value="scope"
                            :options="scopeOptions"
                            :withAllOption="false"
                            :show-clear="false"
                            @selectedChange="scope = $event || 'all'"
                            :show-valid-indicator="false" />
                    </div>
                    <div
                        v-if="usesAnchoredPeriod"
                        class="w-full sm:w-44">
                        <label class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">
                            {{ periodInputLabel }}
                        </label>
                        <input
                            class="shadow-xs w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-900 transition-colors focus:border-lime-500 focus:ring-1 focus:ring-lime-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            :type="periodInputType"
                            :value="periodInputValue"
                            min="2000"
                            max="2100"
                            @input="updatePeriodAnchor($event.target.value)" />
                    </div>
                    <button
                        @click="loadDashboard"
                        class="shadow-xs rounded-xl border border-slate-200 bg-slate-50 p-2 text-slate-600 transition-all hover:bg-slate-100 active:scale-95 sm:p-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/80"
                        title="Refresh Analytics">
                        <RefreshCw
                            class="sm:w-4.5 sm:h-4.5 h-4 w-4"
                            :class="{ 'animate-spin': loading }" />
                    </button>
                </div>
            </div>

            <TabNavigation
                v-model="activeTab"
                :tabs="tabs" />

            <!-- Stats & Visualizations Tab -->
            <div
                v-show="activeTab === 'stats'"
                class="space-y-6 px-5">
                <!-- KPI Analytics Banner -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400">Equipment Users</h3>
                            <Activity class="h-4 w-4 text-lime-600 dark:text-lime-400" />
                        </div>
                        <p class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl dark:text-slate-100">
                            {{ currentInUseCount }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Active + overdue sessions</p>
                    </div>

                    <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <Dropdown
                            align="right"
                            width="auto"
                            max-height="24rem">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="w-full text-left">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400">Active Equipment</h3>
                                        <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    </div>
                                    <p class="mt-2 text-2xl font-bold text-emerald-600 sm:text-3xl dark:text-emerald-400">
                                        {{ activeUniqueEquipmentCount }}
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Click to view location details</p>
                                </button>
                            </template>

                            <template #content>
                                <div class="w-[32rem] max-w-[85vw] p-3 text-xs sm:text-sm">
                                    <div
                                        v-if="groupedActiveLogs.length"
                                        class="space-y-2">
                                        <div
                                            v-for="group in groupedActiveLogs"
                                            :key="group.location"
                                            class="rounded-xl border border-slate-200 p-2.5 dark:border-slate-800">
                                            <div class="px-2 py-1 font-bold text-slate-900 dark:text-slate-100">{{ group.location }} ({{ group.items.length }})</div>
                                            <DropdownOption
                                                v-for="log in group.items"
                                                :key="log.id"
                                                class="!whitespace-normal !px-2 !py-2">
                                                <a
                                                    class="font-semibold text-lime-600 hover:underline"
                                                    target="_blank"
                                                    :href="route(equipmentShowRoute(log), log.equipment?.id)">
                                                    {{ log.equipment?.name || "Equipment" }}
                                                </a>
                                                <div class="text-xs text-slate-500">Barcode: {{ log.equipment_barcode || "-" }}</div>
                                                <div class="text-xs text-slate-500">Started: {{ formatDateTime(log.started_at) }}</div>
                                                <div class="text-xs text-slate-500">Ends: {{ formatDateTime(log.end_use_at) }}</div>
                                                <div class="text-xs text-slate-500">User: {{ formatPersonnelName(log.personnel) }}</div>
                                            </DropdownOption>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        class="p-2 text-slate-500">
                                        No active equipment logs.
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <Dropdown
                            align="right"
                            width="auto"
                            max-height="24rem">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="w-full text-left">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400">Overdue Equipment</h3>
                                        <AlertTriangle class="h-4 w-4 text-rose-500" />
                                    </div>
                                    <p class="mt-2 text-2xl font-bold text-rose-600 sm:text-3xl dark:text-rose-400">
                                        {{ overdueUniqueEquipmentCount }}
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Click to review overdue logs</p>
                                </button>
                            </template>

                            <template #content>
                                <div class="w-[32rem] max-w-[85vw] p-3 text-xs sm:text-sm">
                                    <div
                                        v-if="groupedOverdueLogs.length"
                                        class="space-y-2">
                                        <div
                                            v-for="group in groupedOverdueLogs"
                                            :key="group.location"
                                            class="rounded-xl border border-slate-200 p-2.5 dark:border-slate-800">
                                            <div class="px-2 py-1 font-bold text-slate-900 dark:text-slate-100">{{ group.location }} ({{ group.items.length }})</div>
                                            <DropdownOption
                                                v-for="log in group.items"
                                                :key="log.id"
                                                class="!whitespace-normal !px-2 !py-2">
                                                <a
                                                    class="font-semibold text-rose-600 hover:underline"
                                                    target="_blank"
                                                    :href="route(equipmentShowRoute(log), log.equipment?.id)">
                                                    {{ log.equipment?.name || "Equipment" }}
                                                </a>
                                                <div class="text-xs text-slate-500">Barcode: {{ log.equipment_barcode || "-" }}</div>
                                                <div class="text-xs text-slate-500">
                                                    Expected end:
                                                    {{ formatDateTime(log.end_use_at) }}
                                                </div>
                                                <div class="text-xs text-slate-500">User: {{ formatPersonnelName(log.personnel) }}</div>
                                            </DropdownOption>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        class="p-2 text-slate-500">
                                        No overdue equipment.
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400">Peak Usage Window</h3>
                            <Flame class="h-4 w-4 text-amber-500" />
                        </div>
                        <p class="mt-2 truncate text-lg font-bold text-slate-900 dark:text-slate-100">
                            {{ peakActivityStats.label }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Highest concurrent demand period</p>
                    </div>
                </div>

                <!-- Main Visualization Charts Section -->
                <div class="grid gap-5 lg:grid-cols-12">
                    <!-- Top Equipment Usage Pattern Chart (8 Cols) -->
                    <div class="shadow-xs flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-8 dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 sm:text-base dark:text-slate-100">
                                    <BarChart3 class="h-4 w-4 text-lime-600 dark:text-lime-400" />
                                    Top Equipment Usage Patterns
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Comparative usage intensity across laboratory assets.</p>
                            </div>
                            <div class="flex shrink-0 items-center rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1 text-xs font-semibold transition-all"
                                    :class="chartMetricMode === 'frequency' ? 'shadow-xs bg-white font-bold text-slate-900 dark:bg-slate-900 dark:text-slate-100' : 'text-slate-600 dark:text-slate-400'"
                                    @click="setMetricMode('frequency')">
                                    Frequency (Sessions)
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1 text-xs font-semibold transition-all"
                                    :class="chartMetricMode === 'duration' ? 'shadow-xs bg-white font-bold text-slate-900 dark:bg-slate-900 dark:text-slate-100' : 'text-slate-600 dark:text-slate-400'"
                                    @click="setMetricMode('duration')">
                                    Operating Hours
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="mostUsed.length"
                            class="relative h-80 w-full">
                            <canvas
                                ref="mostUsedChartCanvas"
                                :key="chartMetricMode"
                                style="max-height: 100%; max-width: 100%"></canvas>
                        </div>
                        <div
                            v-else
                            class="flex h-80 items-center justify-center text-xs text-slate-500 sm:text-sm">
                            No usage pattern data available yet.
                        </div>
                    </div>

                    <!-- Category Share & Duration Distribution Side Cards (4 Cols) -->
                    <div class="space-y-5 lg:col-span-4">
                        <!-- Category Distribution -->
                        <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-100">
                                <PieChart class="h-4 w-4 text-sky-500" />
                                Category Distribution
                            </h3>
                            <div class="relative h-44 w-full">
                                <canvas
                                    ref="categoryChartCanvas"
                                    style="max-height: 100%; max-width: 100%"></canvas>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-center text-xs">
                                <div class="rounded-xl border border-slate-200/60 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-800/60">
                                    <span class="block text-[10px] text-slate-500 dark:text-slate-400">Laboratory</span>
                                    <div class="mt-0.5 flex items-baseline justify-center gap-1">
                                        <span class="text-sm font-bold text-lime-600 dark:text-lime-400">{{ equipmentTypeShares.labPercent }}%</span>
                                        <span class="text-[0.65rem] font-medium text-slate-400">({{ equipmentTypeShares.labCount }})</span>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200/60 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-800/60">
                                    <span class="block text-[10px] text-slate-500 dark:text-slate-400">ICT Assets</span>
                                    <div class="mt-0.5 flex items-baseline justify-center gap-1">
                                        <span class="text-sm font-bold text-sky-600 dark:text-sky-400">{{ equipmentTypeShares.ictPercent }}%</span>
                                        <span class="text-[0.65rem] font-medium text-slate-400">({{ equipmentTypeShares.ictCount }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Session Duration Breakdown -->
                        <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-100">
                                <Clock class="h-4 w-4 text-indigo-500" />
                                Session Duration Spectrum
                            </h3>
                            <div class="relative h-40 w-full">
                                <canvas
                                    ref="durationChartCanvas"
                                    style="max-height: 100%; max-width: 100%"></canvas>
                            </div>
                        </div>

                        <!-- Recently Reported Equipments List -->
                        <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-slate-100">
                                <Layers class="h-4 w-4 text-emerald-500" />
                                Recently reported equipments
                            </h3>
                            <div class="mt-4 space-y-3">
                                <template
                                    v-for="(item, index) in recentReports.slice(0, 5)"
                                    :key="item.id">
                                    <a
                                        :href="item.transaction_id ? route('transactions.show', item.transaction_id) : route(equipmentShowRoute(item), item.equipment_barcode)"
                                        target="_blank"
                                        class="group flex cursor-pointer items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-3 transition-colors hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/40 dark:hover:bg-slate-800">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                                {{ index + 1 }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-900 transition-colors group-hover:text-emerald-600 dark:text-slate-100">
                                                    {{ item.equipment_name || "Unknown" }}
                                                </p>
                                                <p class="truncate text-[10px] text-slate-500">
                                                    {{ item.equipment_barcode || "No barcode" }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-semibold uppercase text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                                {{ item.report_type }}
                                            </span>
                                            <span class="mt-1 block text-[9px] text-slate-500">
                                                {{ formatDateTime(item.reported_at) }}
                                            </span>
                                        </div>
                                    </a>
                                </template>
                                <div
                                    v-if="!recentReports.length"
                                    class="py-4 text-center text-xs text-slate-500">
                                    No recently reported equipment.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Heatmap Matrix Card -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 sm:text-base dark:text-slate-100">
                                <Flame class="h-4 w-4 text-amber-500" />
                                24-Hour Usage Heatmap Matrix
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hourly density of equipment checkout and checkin activity across days of the week.</p>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-[10px] font-semibold uppercase text-slate-500 dark:text-slate-400">Intensity</span>
                            <div class="flex flex-wrap gap-2 text-xs text-slate-600 dark:text-slate-300">
                                <div
                                    v-for="legend in heatLegend"
                                    :key="legend.label"
                                    class="flex items-center gap-1.5 text-[11px]">
                                    <span :class="['shadow-2xs inline-block h-3 w-4 rounded-md', legend.className]"></span>
                                    <span>{{ legend.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="min-w-[700px]">
                            <div
                                class="mb-1 grid gap-1 text-xs"
                                style="grid-template-columns: repeat(25, minmax(0, 1fr))">
                                <div class="text-[0.65rem] font-bold uppercase text-slate-400">Day / Hr</div>
                                <div
                                    v-for="hour in 24"
                                    :key="hour"
                                    class="text-center text-[0.65rem] font-semibold text-slate-500">
                                    {{ hour - 1 }}h
                                </div>
                            </div>
                            <div
                                v-for="(row, dayIndex) in heatmap"
                                :key="dayIndex"
                                class="mb-1 grid gap-1"
                                style="grid-template-columns: repeat(25, minmax(0, 1fr))">
                                <div class="flex items-center text-[0.7rem] font-bold text-slate-600 dark:text-slate-300">
                                    {{ dayLabels[dayIndex] }}
                                </div>
                                <div
                                    v-for="(cell, hourIndex) in row"
                                    :key="hourIndex"
                                    :class="['flex h-6 cursor-pointer items-center justify-center rounded-md text-[9px] transition-all duration-200 hover:z-10 hover:scale-110', heatColor(cell)]"
                                    :title="`${dayLabels[dayIndex]} @ ${hourIndex}:00 — ${cell} log(s)`">
                                    <span
                                        v-if="cell > 0"
                                        class="font-bold opacity-90">
                                        {{ cell }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-200 pt-3 text-xs sm:grid-cols-3 dark:border-slate-800">
                        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <Layers class="h-4 w-4 text-lime-600 dark:text-lime-400" />
                            <span>
                                Busiest Day:
                                <strong class="text-slate-900 dark:text-slate-100">
                                    {{ busiestDayOfWeek }}
                                </strong>
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <Activity class="h-4 w-4 text-emerald-500" />
                            <span>
                                Total Sessions Logged:
                                <strong class="text-slate-900 dark:text-slate-100">
                                    {{ totalSessionsCount }}
                                </strong>
                            </span>
                        </div>
                        <div class="col-span-2 flex items-center gap-2 text-slate-600 sm:col-span-1 dark:text-slate-300">
                            <Cpu class="h-4 w-4 text-sky-500" />
                            <span>
                                Top Used:
                                <strong class="text-slate-900 dark:text-slate-100">
                                    {{ mostUsed[0]?.equipment_name || "N/A" }}
                                </strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Tab -->
            <div
                v-show="activeTab === 'calendar'"
                class="space-y-6 px-5">
                <CalendarModule
                    title="Equipment Usage Calendar"
                    subtitle="Active, overdue, and completed equipment usage by day."
                    :events="calendarEvents"
                    :type-options="[{ key: 'equipment', label: 'Equipment' }]"
                    :status-options="[
                        { key: 'active', label: 'Active' },
                        { key: 'overdue', label: 'Overdue' },
                        { key: 'completed', label: 'Completed' },
                    ]"
                    :status-colors="{ active: '#10B981', overdue: '#EF4444', completed: '#9CA3AF' }"
                    :legend-groups="calendarLegend"
                    :show-type-filter="false" />
            </div>

            <!-- Active Logs Tab -->
            <div
                v-show="activeTab === 'logs'"
                class="space-y-4 px-5">
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Currently Active Sessions</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Active and overdue sessions for ICT/laboratory equipment currently in use.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ currentWorkingLogs.length }} active sessions</span>
                    </div>

                    <div
                        v-if="currentWorkingLogs.length"
                        class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-xs sm:text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Equipment</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Type</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Personnel</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Location</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Started</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Expected End</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr
                                    v-for="log in currentWorkingLogs"
                                    :key="log.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3">
                                        <a
                                            :href="route(equipmentShowRoute(log), log?.equipment_barcode)"
                                            target="_blank"
                                            class="font-semibold text-lime-600 hover:underline dark:text-lime-400">
                                            {{ log.equipment?.name || "Equipment" }}
                                        </a>
                                        <div class="text-xs text-slate-500">
                                            {{ log.equipment_barcode || "No barcode" }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                            :class="equipmentTypeBadgeClass(log.equipment_type)">
                                            {{ equipmentTypeLabel(log.equipment_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase"
                                            :class="currentStatusBadgeClass(log.status)">
                                            {{ log.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                        {{ formatPersonnelName(log.personnel) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                        {{ log.location_label || "Unknown Location" }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                        {{ formatDateTime(log.started_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                        {{ formatDateTime(log.end_use_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="p-4 text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                        No ICT or laboratory equipment is currently checked in.
                    </div>
                </div>
            </div>

            <!-- Equipment List Tab -->
            <div
                v-show="activeTab === 'equipment-list'"
                class="px-5">
                <CRCMDatatable
                    ref="equipmentListTable"
                    :base-model="EquipmentLoggerAsset"
                    :can-view="true"
                    :can-create="false"
                    :can-update="true"
                    :can-delete="false"
                    storage-key="equipment-logger-asset">
                    <template #custom-filters="{ customFilters, refresh }">
                        <custom-dropdown
                            :options="[
                                { name: 'laboratory', label: 'Laboratory' },
                                { name: 'ict', label: 'ICT' },
                            ]"
                            label="Filter by Group"
                            placeholder="All Groups"
                            :value="customFilters.equipment_type"
                            :with-all-option="true"
                            all-option-label="All Groups"
                            @selectedChange="
                                (value) => {
                                    customFilters.equipment_type = value;
                                    refresh();
                                }
                            "
                            :show-valid-indicator="false">
                            <template #icon>
                                <LuFilter class="h-4 w-4 text-gray-400" />
                            </template>
                        </custom-dropdown>
                    </template>
                    <template #cell-name="{ row, value }">
                        <div class="w-full whitespace-normal py-1.5 leading-tight">
                            <div class="font-medium">
                                <Link
                                    v-if="transactionShowHref(row)"
                                    :href="transactionShowHref(row)"
                                    class="hover:text-primary-800 text-lime-600 hover:underline dark:text-lime-400">
                                    {{ row.name }}
                                </Link>
                                <span
                                    v-if="row.description"
                                    class="block text-xs text-gray-500">
                                    Model: {{ row.description }}
                                </span>
                            </div>
                            <div
                                class="text-xs"
                                v-if="row.brand">
                                {{ row.brand }}
                            </div>
                            <div
                                class="text-xs"
                                v-if="row.barcode">
                                {{ row.barcode }}
                            </div>
                        </div>
                    </template>
                    <template #cell-equipment_type="{ value }">
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="equipmentTypeBadgeClass(value)">
                            {{ equipmentTypeLabel(value) }}
                        </span>
                    </template>
                    <template #cell-equipment_logger_mode="{ row, value }">
                        <button
                            type="button"
                            :id="`cell-equipment_logger_mode-${row.id}`"
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold transition hover:ring-2 hover:ring-lime-300"
                            :class="loggerModeBadgeClass(value)"
                            title="Double-click to update equipment logger mode"
                            @dblclick.stop.prevent="openLoggerModeModal(row)">
                            {{ loggerModeLabel(value) }}
                        </button>
                    </template>
                    <template #cell-current_location_label="{ row, value }">
                        <button
                            type="button"
                            :id="`cell-location-${row.id}`"
                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold transition hover:ring-2 hover:ring-amber-300"
                            :class="[row.current_location_source === 'temporary' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300']"
                            title="Double-click to edit or finalize location"
                            @dblclick.stop.prevent="openLocationModal(row)">
                            {{ value || "Unknown" }}
                        </button>
                    </template>
                </CRCMDatatable>
            </div>

            <!-- Personnel List Tab -->
            <div
                v-show="activeTab === 'personnel-list'"
                class="px-5">
                <CRCMDatatable
                    :base-model="EquipmentLoggerPersonnel"
                    :can-view="true"
                    :can-create="false"
                    :can-update="false"
                    :can-delete="false"
                    storage-key="equipment-logger-personnel">
                    <template #cell-fullName="{ row, value }">
                        <div class="min-w-[16rem]">
                            <a
                                :href="
                                    route('equipment-logger.personnels.show', {
                                        personnelId: row.id,
                                    })
                                "
                                class="font-semibold text-lime-600 hover:underline dark:text-lime-400">
                                {{ value }}
                            </a>
                            <div class="text-xs text-slate-500">
                                {{ row.employee_id || "No employee ID" }}
                            </div>
                        </div>
                    </template>
                </CRCMDatatable>
            </div>

            <!-- Logger Mode Update Modal -->
            <DialogModal
                :show="showLoggerModeModal"
                max-width="md"
                @close="closeLoggerModeModal">
                <template #title>
                    <div class="flex items-center gap-2 py-2">
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Update Equipment Logger Mode</span>
                    </div>
                </template>

                <template #content>
                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 sm:text-sm dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                            <p class="font-bold text-slate-900 dark:text-slate-100">
                                {{ selectedEquipmentAsset?.name || "Equipment" }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ selectedEquipmentAsset?.brand || "Unknown Brand" }} •
                                {{ selectedEquipmentAsset?.barcode || "No barcode" }}
                            </p>
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                Latest incoming transaction:
                                {{ selectedEquipmentAsset?.latest_incoming_transaction_id || "Unavailable" }}
                            </p>
                        </div>
                        <custom-dropdown
                            required
                            searchable
                            :with-all-option="false"
                            :value="selectedLoggerMode"
                            :options="loggerModeOptions"
                            label="Equipment Logger Mode"
                            placeholder="Select logger mode"
                            :error="loggerModeFormError"
                            guide="This updates the latest incoming transaction linked to the selected equipment row."
                            @selectedChange="selectedLoggerMode = $event"
                            :show-valid-indicator="false" />
                    </div>
                </template>

                <template #footer>
                    <div class="flex w-full justify-between gap-3">
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            :disabled="updatingLoggerMode"
                            @click="closeLoggerModeModal">
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="shadow-xs rounded-xl bg-lime-600 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-lime-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                            :disabled="!canSubmitLoggerModeUpdate"
                            @click="submitLoggerModeUpdate">
                            {{ updatingLoggerMode ? "Updating..." : "Update Mode" }}
                        </button>
                    </div>
                </template>
            </DialogModal>

            <!-- Location Update Modal -->
            <DialogModal
                :show="showLocationModal"
                max-width="md"
                @close="closeLocationModal">
                <template #title>
                    <div class="flex items-center gap-2 py-2">
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Update Equipment Location</span>
                    </div>
                </template>

                <template #content>
                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 sm:text-sm dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                            <p class="font-bold text-slate-900 dark:text-slate-100">
                                {{ selectedEquipmentAsset?.name || "Equipment" }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ selectedEquipmentAsset?.brand || "Unknown Brand" }} •
                                {{ selectedEquipmentAsset?.barcode || "No barcode" }}
                            </p>
                            <p
                                v-if="selectedEquipmentAsset?.current_location_source === 'temporary'"
                                class="mt-2 text-xs font-bold text-amber-600">
                                Current Location (Temporary):
                                {{ selectedEquipmentAsset?.current_location_label }}
                            </p>
                        </div>
                        <custom-dropdown
                            required
                            searchable
                            :with-all-option="false"
                            :value="selectedLocationCode"
                            :options="storageLocationOptions"
                            label="Equipment Location"
                            placeholder="Select location"
                            guide="This permanently updates the equipment barcode location, and finalizes any temporary survey report."
                            @selectedChange="selectedLocationCode = $event"
                            :show-valid-indicator="false" />
                    </div>
                </template>

                <template #footer>
                    <div class="flex w-full justify-between gap-3">
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                            :disabled="isUpdatingLocation"
                            @click="closeLocationModal">
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="shadow-xs rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-amber-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                            :disabled="isUpdatingLocation || !selectedLocationCode"
                            @click="submitLocationUpdate">
                            {{ isUpdatingLocation ? "Saving..." : "Save Location" }}
                        </button>
                    </div>
                </template>
            </DialogModal>
        </div>
    </AppLayout>
</template>
