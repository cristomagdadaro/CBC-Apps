<script>
import { Chart, PieController, BarController, BarElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend } from "chart.js";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import Modal from "@/Components/Modal.vue";
import TabNavigation from "@/Components/TabNavigation.vue";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import PreregistrationCard from "@/Pages/Forms/components/PreregistrationCard.vue";
import PreregistrationQuizBeeCard from "@/Pages/Forms/components/PreregistrationQuizBeeCard.vue";
import PreregistrationQuizbeeTeamCard from "@/Pages/Forms/components/PreregistrationQuizbeeTeamCard.vue";
import RegistrationCard from "@/Pages/Forms/components/RegistrationCard.vue";
import FeedbackCard from "@/Pages/Forms/components/FeedbackCard.vue";
import { router } from "@inertiajs/vue3";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

Chart.register(BarController, PieController, BarElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend);

export default {
    name: "FormUpdateDashboard",
    components: {
        Modal,
        TabNavigation,
        DataTable,
        PreregistrationCard,
        PreregistrationQuizBeeCard,
        PreregistrationQuizbeeTeamCard,
        RegistrationCard,
        FeedbackCard,
    },
    mixins: [DataFormatterMixin],
    props: {
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
        eventId: [String, Number],
        config: Object,
    },
    data() {
        return {
            showResponseModal: false,
            selectedResponse: null,
            selectedResponseType: null,
            selectedFormType: null,
            activeFormType: null,
            labelMap: {
                pre_registration: "Pre-registration",
                pre_registration_biotech: "Pre-registration + Quiz Bee",
                pre_registration_quizbee: "Pre-registration Quiz Bee",
                preregistration_quizbee: "Pre-registration Quiz Bee",
                registration: "Registration",
                pre_test: "Pre-test",
                post_test: "Post-test",
                feedback: "Feedback",
            },
            // Adjusted color palettes for the slate/indigo theme
            responseColors: ["#4f46e5", "#0ea5e9", "#10b981", "#f59e0b", "#8b5cf6", "#ec4899"],
            totalsColors: ["#3b82f6", "#f97316", "#10b981"],
            responseChartInstance: null,
            totalsChartInstance: null,
            regionPieInstance: null,
            provincePieInstance: null,
            cityPieInstance: null,
            selectedRegion: null,
            selectedProvince: null,
            dynamicChartConfigs: [],
            dynamicChartInstances: {},
            selectedChartFormType: null,
            selectedChartColumn: null,
            selectedChartType: null,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
        };
    },
    computed: {
        responseLabels() {
            return Object.keys(this.stats?.responses_by_type || {}).map((key) => this.getFormTypeDisplayLabel(key));
        },
        responseValues() {
            return Object.values(this.stats?.responses_by_type || {});
        },
        responseDataGroups() {
            const groups = this.responsesByType || {};
            return Object.entries(groups).map(([key, items]) => {
                const itemsArray = Array.isArray(items) ? items : [];
                const uniqueKeys = new Set();
                itemsArray.forEach((item) => {
                    if (item.response_data && typeof item.response_data === "object") {
                        Object.keys(item.response_data).forEach((k) => uniqueKeys.add(k));
                    }
                });

                const dataColumns = Array.from(uniqueKeys).sort();
                const schemaLabels = this.getFieldLabelMapForFormType(key);
                const dataColumnLabels = dataColumns.reduce((acc, column) => {
                    acc[column] = schemaLabels[column] || this.humanizeColumn(column);
                    return acc;
                }, {});

                return {
                    form_type: key,
                    label: this.getFormTypeDisplayLabel(key),
                    items: itemsArray,
                    dataColumns,
                    dataColumnLabels,
                };
            });
        },
        responseTabs() {
            return this.responseDataGroups.map((group) => ({
                key: group.form_type,
                label: group.label,
                count: group.items.length,
            }));
        },
        activeGroup() {
            return this.responseDataGroups.find((group) => group.form_type === this.activeFormType) || null;
        },
        allResponses() {
            const groups = this.responsesByType || {};
            return Object.values(groups).flatMap((items) => (Array.isArray(items) ? items : []));
        },
        geoRegionSummary() {
            const regionMap = new Map();

            this.allResponses.forEach((item) => {
                const region = this.normalizeText(item.response_data?.region_address);
                if (!region) return;

                if (!regionMap.has(region)) {
                    regionMap.set(region, { region, provinces: new Set(), cities: new Set() });
                }

                const province = this.normalizeText(item.response_data?.province_address);
                const city = this.normalizeText(item.response_data?.city_address);
                const entry = regionMap.get(region);

                if (province) entry.provinces.add(province);
                if (city) entry.cities.add(city);
            });

            return Array.from(regionMap.values())
                .map((entry) => ({
                    region: entry.region,
                    provinceCount: entry.provinces.size,
                    cityCount: entry.cities.size,
                }))
                .sort((a, b) => a.region.localeCompare(b.region));
        },
        filteredProvinceCounts() {
            if (!this.selectedRegion) {
                return this.aggregateCounts("province_address", (v) => this.normalizeText(v));
            }
            const counts = {};
            this.allResponses.forEach((item) => {
                const region = this.normalizeText(item.response_data?.region_address);
                if (region !== this.selectedRegion) return;
                const province = this.normalizeText(item.response_data?.province_address);
                if (!province) return;
                counts[province] = (counts[province] || 0) + 1;
            });
            return counts;
        },
        filteredCityCounts() {
            if (!this.selectedProvince && !this.selectedRegion) {
                return this.aggregateCounts("city_address", (v) => this.normalizeText(v));
            }
            const counts = {};
            this.allResponses.forEach((item) => {
                if (this.selectedRegion) {
                    const region = this.normalizeText(item.response_data?.region_address);
                    if (region !== this.selectedRegion) return;
                }
                if (this.selectedProvince) {
                    const province = this.normalizeText(item.response_data?.province_address);
                    if (province !== this.selectedProvince) return;
                }
                const city = this.normalizeText(item.response_data?.city_address);
                if (!city) return;
                counts[city] = (counts[city] || 0) + 1;
            });
            return counts;
        },
        chartFormOptions() {
            return this.responseDataGroups.map((group) => ({
                value: group.form_type,
                label: group.label,
            }));
        },
        selectedFormColumns() {
            if (!this.selectedChartFormType) return [];
            const group = this.responseDataGroups.find((g) => g.form_type === this.selectedChartFormType);
            return group?.dataColumns || [];
        },
        selectedColumnDataType() {
            if (!this.selectedChartFormType || !this.selectedChartColumn) return null;
            const values = this.getColumnValues(this.selectedChartFormType, this.selectedChartColumn);
            return this.inferDataType(values);
        },
        activeGroupColumns() {
            if (!this.activeGroup) return [];
            return [
                {
                    key: "created_at",
                    title: "Submitted On",
                    sortable: true,
                },
                ...this.activeGroup.dataColumns.map((col) => ({
                    key: `response_data.${col}`,
                    title: this.activeGroup.dataColumnLabels?.[col] || this.humanizeColumn(col),
                    sortable: true,
                })),
            ];
        },
    },
    methods: {
        getRequirementForFormType(formType) {
            const requirements = this.config?.requirements;
            if (!Array.isArray(requirements)) return null;

            const formTypeKey = String(formType || "");
            const [baseType, templateId] = formTypeKey.split(":");

            const directMatch = requirements.find((item) => {
                if (!item) return false;
                if (templateId) {
                    return item?.form_type === baseType && item?.form_type_template_id === templateId;
                }
                return item?.form_type === formTypeKey || item?.step_type === formTypeKey;
            });

            if (directMatch) return directMatch;
            if (baseType === "custom") return requirements.find((item) => item?.form_type === "custom") || null;
            return null;
        },
        getFormTypeDisplayLabel(formType) {
            const requirement = this.getRequirementForFormType(formType);
            const templateName = requirement?.template?.name || requirement?.form_type_template?.name || requirement?.form_type_template_name || requirement?.template_name;
            if (templateName) return templateName;
            return this.labelMap[formType] || this.humanizeColumn(formType);
        },
        humanizeColumn(column) {
            return String(column || "")
                .replace(/_/g, " ")
                .replace(/\s+/g, " ")
                .trim();
        },
        getFieldLabelMapForFormType(formType) {
            const requirements = this.config?.requirements;
            if (!Array.isArray(requirements)) return {};

            const requirement = requirements.find((item) => item?.form_type === formType);
            const schema = requirement?.resolved_field_schema || requirement?.field_schema || [];

            if (!Array.isArray(schema)) return {};

            return schema.reduce((acc, field) => {
                const fieldKey = field?.field_key;
                const title = field?.title;
                const label = field?.label;
                const preferred = title || label;

                if (fieldKey && preferred) {
                    acc[fieldKey] = preferred;
                }

                return acc;
            }, {});
        },
        getColumnLabel(formType, column) {
            const group = this.responseDataGroups.find((g) => g.form_type === formType);
            if (group?.dataColumnLabels?.[column]) return group.dataColumnLabels[column];
            return this.humanizeColumn(column);
        },
        normalizeText(value) {
            if (value === null || value === undefined) return null;
            const str = String(value).trim();
            return str === "" ? null : str;
        },
        normalizeBooleanValue(value) {
            if (value === null || value === undefined || value === "") return null;
            if (typeof value === "boolean") return value;
            if (typeof value === "string") {
                const lower = value.toLowerCase().trim();
                if (lower === "true" || lower === "1" || lower === "yes") return true;
                if (lower === "false" || lower === "0" || lower === "no") return false;
                return null;
            }
            if (typeof value === "number") return value !== 0;
            return null;
        },
        isHttpUrl(value) {
            if (!value || typeof value !== "string") return false;
            return /^https?:\/\//i.test(value.trim());
        },
        isStorageFilePath(value) {
            if (!value || typeof value !== "string") return false;
            const v = value.trim();
            if (v.startsWith("quizbee/")) return true;
            return /\/.+\.[a-z0-9]+$/i.test(v);
        },
        getFileDownloadUrl(path) {
            if (!path) return "#";
            if (this.isHttpUrl(path)) return path;
            let normalized = String(path).trim().replace(/^\/+/, "");
            if (normalized.startsWith("storage/")) return `/${normalized}`;
            return `/storage/${normalized}`;
        },
        getFileName(path) {
            if (!path) return "Download file";
            const name = String(path).split("/").pop();
            return name || "Download file";
        },
        getFormCardComponent(formType) {
            const components = {
                preregistration: "PreregistrationCard",
                preregistration_biotech: "PreregistrationQuizBeeCard",
                preregistration_quizbee: "PreregistrationQuizbeeTeamCard",
                registration: "RegistrationCard",
                feedback: "FeedbackCard",
            };
            return components[formType] || null;
        },
        normalizeBoolean(value) {
            if (value === null || value === undefined || value === "") return null;
            if (value === true || value === 1 || value === "1" || value === "Yes" || value === "yes") return "Yes";
            if (value === false || value === 0 || value === "0" || value === "No" || value === "no") return "No";
            return this.normalizeText(value);
        },
        getColumnsForFormType(formType) {
            const group = this.responseDataGroups.find((g) => g.form_type === formType);
            return group?.dataColumns || [];
        },
        getColumnValues(formType, column) {
            const group = this.responseDataGroups.find((g) => g.form_type === formType);
            if (!group) return [];
            return group.items.flatMap((item) => {
                const value = item.response_data?.[column];
                if (Array.isArray(value)) return value;
                return [value];
            });
        },
        isBooleanLike(value) {
            if (value === true || value === false) return true;
            if (value === 1 || value === 0) return true;
            if (typeof value === "string") {
                const lower = value.toLowerCase().trim();
                return ["true", "false", "yes", "no", "1", "0"].includes(lower);
            }
            return false;
        },
        isNumericLike(value) {
            if (value === null || value === undefined || value === "") return false;
            if (typeof value === "number") return Number.isFinite(value);
            if (typeof value === "string") {
                const normalized = value.replace(/,/g, "").trim();
                return normalized !== "" && !Number.isNaN(Number(normalized));
            }
            return false;
        },
        isDateLike(value) {
            if (!value || typeof value !== "string") return false;
            const trimmed = value.trim();
            if (trimmed.length < 6) return false;
            const timestamp = Date.parse(trimmed);
            return !Number.isNaN(timestamp);
        },
        inferDataType(values) {
            const samples = values.filter((v) => v !== null && v !== undefined && v !== "").slice(0, 50);
            if (!samples.length) return "string";

            let boolCount = 0;
            let numberCount = 0;
            let dateCount = 0;

            samples.forEach((value) => {
                if (this.isBooleanLike(value)) boolCount += 1;
                else if (this.isNumericLike(value)) numberCount += 1;
                else if (this.isDateLike(value)) dateCount += 1;
            });

            const total = samples.length;
            if (boolCount / total >= 0.8) return "boolean";
            if (dateCount / total >= 0.7) return "date";
            if (numberCount / total >= 0.7) return "number";
            return "string";
        },
        getChartTypeOptions(dataType) {
            if (dataType === "date") return ["bar"];
            return ["bar", "pie", "doughnut"];
        },
        normalizeChartValue(value, dataType) {
            if (value === null || value === undefined || value === "") return null;
            if (dataType === "boolean") return this.normalizeBoolean(value);
            if (dataType === "number") {
                const parsed = Number(String(value).replace(/,/g, "").trim());
                return Number.isNaN(parsed) ? null : parsed;
            }
            if (dataType === "date") {
                const timestamp = Date.parse(String(value));
                return Number.isNaN(timestamp) ? null : new Date(timestamp);
            }
            return this.normalizeText(value);
        },
        buildCategoricalCounts(values, dataType, maxItems = 12) {
            const counts = {};
            values.forEach((value) => {
                const normalized = this.normalizeChartValue(value, dataType);
                if (normalized === null || normalized === undefined || normalized === "") return;
                const key = String(normalized);
                counts[key] = (counts[key] || 0) + 1;
            });

            const entries = Object.entries(counts).sort((a, b) => b[1] - a[1]);
            if (entries.length <= maxItems) return entries;

            const top = entries.slice(0, maxItems);
            const remainder = entries.slice(maxItems);
            const otherCount = remainder.reduce((sum, [, value]) => sum + value, 0);
            if (otherCount > 0) top.push(["Other", otherCount]);
            return top;
        },
        buildNumericSeries(values) {
            const numericValues = values.map((value) => this.normalizeChartValue(value, "number")).filter((value) => typeof value === "number");
            if (!numericValues.length) return { labels: [], data: [] };

            const uniqueValues = Array.from(new Set(numericValues)).sort((a, b) => a - b);
            if (uniqueValues.length <= 10) {
                const labels = uniqueValues.map((v) => String(v));
                const data = labels.map((label) => numericValues.filter((v) => String(v) === label).length);
                return { labels, data };
            }

            const min = Math.min(...numericValues);
            const max = Math.max(...numericValues);
            const binCount = 8;
            const range = max - min || 1;
            const binSize = Math.ceil(range / binCount);

            const bins = Array.from({ length: binCount }, (_, index) => ({
                min: min + index * binSize,
                max: min + (index + 1) * binSize,
                count: 0,
            }));

            numericValues.forEach((value) => {
                const index = Math.min(Math.floor((value - min) / binSize), binCount - 1);
                bins[index].count += 1;
            });

            const labels = bins.map((bin) => `${bin.min}-${bin.max}`);
            const data = bins.map((bin) => bin.count);
            return { labels, data };
        },
        buildDateSeries(values) {
            const dates = values.map((value) => this.normalizeChartValue(value, "date")).filter((value) => value instanceof Date);
            if (!dates.length) return { labels: [], data: [] };

            const buckets = {};
            dates.forEach((date) => {
                const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
                buckets[key] = (buckets[key] || 0) + 1;
            });

            const labels = Object.keys(buckets).sort();
            const data = labels.map((label) => buckets[label]);
            return { labels, data };
        },
        buildChartData(config) {
            const values = this.getColumnValues(config.formType, config.column);
            const dataType = config.dataType || this.inferDataType(values);

            if (dataType === "number") return this.buildNumericSeries(values);
            if (dataType === "date") return this.buildDateSeries(values);

            const entries = this.buildCategoricalCounts(values, dataType);
            const labels = entries.map(([label]) => label);
            const data = entries.map(([, value]) => value);
            return { labels, data };
        },
        getChartTitle(config) {
            const label = this.getFormTypeDisplayLabel(config.formType);
            return `${label} · ${this.getColumnLabel(config.formType, config.column)}`;
        },
        addDynamicChart() {
            if (!this.selectedChartFormType || !this.selectedChartColumn || !this.selectedChartType) return;
            const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            const dataType = this.selectedColumnDataType || "string";
            this.dynamicChartConfigs.push({
                id,
                formType: this.selectedChartFormType,
                column: this.selectedChartColumn,
                chartType: this.selectedChartType,
                dataType,
            });
            this.buildCharts();
        },
        removeDynamicChart(id) {
            this.dynamicChartConfigs = this.dynamicChartConfigs.filter((chart) => chart.id !== id);
            if (this.dynamicChartInstances[id]) {
                this.dynamicChartInstances[id].destroy();
                delete this.dynamicChartInstances[id];
            }
            this.buildCharts();
        },
        updateDynamicChart(chart) {
            const columns = this.getColumnsForFormType(chart.formType);
            if (!columns.includes(chart.column)) {
                chart.column = columns[0] || null;
            }
            chart.dataType = this.inferDataType(this.getColumnValues(chart.formType, chart.column));
            const availableTypes = this.getChartTypeOptions(chart.dataType);
            if (!availableTypes.includes(chart.chartType)) {
                chart.chartType = availableTypes[0];
            }
            this.buildCharts();
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }
            this.realtimeCleanup = null;
        },
        scheduleRealtimeRefresh() {
            if (!this.eventId) return;
            if (this.realtimeRefreshTimer) clearTimeout(this.realtimeRefreshTimer);

            this.realtimeRefreshTimer = setTimeout(() => {
                router.reload({
                    only: ["eventStats", "eventResponsesByType"],
                    preserveScroll: true,
                    preserveState: true,
                });
            }, 400);
        },
        configureRealtime() {
            this.cleanupRealtime();
            if (!this.eventId) return;

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "private",
                    channel: `forms.event.${this.eventId}`,
                    event: "forms.response.changed",
                    feature: "forms",
                    handler: (payload) => {
                        if (String(payload?.event_id || "") !== String(this.eventId)) return;
                        this.scheduleRealtimeRefresh();
                    },
                },
            ]);
        },
        aggregateCounts(field, normalizer) {
            const counts = {};
            this.allResponses.forEach((item) => {
                const raw = item.response_data?.[field];
                const normalized = normalizer ? normalizer(raw) : raw;
                if (normalized === null || normalized === undefined || normalized === "") return;
                counts[normalized] = (counts[normalized] || 0) + 1;
            });
            return counts;
        },
        createDonutChart(refName, labels, data, colors, onSliceClick = null) {
            const canvas = this.$refs[refName];
            if (!canvas || !labels.length) return null;

            const doughnutLabelPlugin = {
                id: "doughnutLabelPlugin",
                afterDatasetsDraw(chart) {
                    const MAX_LABELS = 5;
                    const ctx = chart.ctx;
                    const dataset = chart.data.datasets[0];
                    const meta = chart.getDatasetMeta(0);
                    const total = dataset.data.reduce((s, v) => s + (Number(v) || 0), 0);
                    if (!meta || !meta.data) return;
                    if (chart.data.labels.length > MAX_LABELS) return;

                    meta.data.forEach((arc, i) => {
                        const value = Number(dataset.data[i]) || 0;
                        if (value <= 0) return;
                        const startAngle = arc.startAngle;
                        const endAngle = arc.endAngle;
                        const angle = (startAngle + endAngle) / 2;
                        const r = (arc.innerRadius + arc.outerRadius) / 2;
                        const x = arc.x + Math.cos(angle) * r;
                        const y = arc.y + Math.sin(angle) * r;

                        if (total > 0 && value / total < 0.03) return;

                        ctx.save();
                        // Adjust fill style based on light/dark mode later, but white/slate is safe here
                        ctx.fillStyle = "#fff";
                        ctx.font = "bold 11px Inter, sans-serif";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        ctx.shadowColor = "rgba(0,0,0,0.5)";
                        ctx.shadowBlur = 4;
                        const labelText = String(chart.data.labels[i] || "");
                        ctx.fillText(labelText, x, y);
                        ctx.restore();
                    });
                },
            };

            return new Chart(canvas, {
                type: "doughnut",
                data: {
                    labels,
                    datasets: [
                        {
                            data,
                            backgroundColor: colors,
                            borderWidth: 2,
                            borderColor: "transparent",
                            hoverOffset: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "65%",
                    onClick: onSliceClick
                        ? (event, elements) => {
                              if (elements.length > 0) {
                                  const index = elements[0].index;
                                  onSliceClick(labels[index]);
                              }
                          }
                        : undefined,
                    plugins: {
                        legend: { position: "bottom", display: false },
                        tooltip: {
                            enabled: true,
                            backgroundColor: "rgba(15, 23, 42, 0.9)",
                            titleFont: { size: 13, family: "Inter" },
                            bodyFont: { size: 12, family: "Inter" },
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: true,
                        },
                    },
                },
                plugins: [doughnutLabelPlugin],
            });
        },
        buildCharts() {
            this.destroyCharts();

            this.$nextTick(() => {
                if (this.$refs.responseChartCanvas) {
                    this.responseChartInstance = new Chart(this.$refs.responseChartCanvas, {
                        type: "pie",
                        data: {
                            labels: this.responseLabels,
                            datasets: [
                                {
                                    label: "Responses by Form Type",
                                    data: this.responseValues,
                                    backgroundColor: this.responseColors,
                                    borderWidth: 2,
                                    borderColor: "transparent",
                                    hoverOffset: 4,
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
                                tooltip: {
                                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                                    padding: 10,
                                    cornerRadius: 8,
                                },
                            },
                        },
                    });
                }

                if (this.$refs.totalsChartCanvas) {
                    this.totalsChartInstance = new Chart(this.$refs.totalsChartCanvas, {
                        type: "bar",
                        data: {
                            labels: ["Registrations", "Participants", "Responses"],
                            datasets: [
                                {
                                    label: "Totals",
                                    data: [this.stats?.registrations_total || 0, this.stats?.participants_total || 0, this.stats?.responses_total || 0],
                                    backgroundColor: this.totalsColors,
                                    borderRadius: 6,
                                    borderSkipped: false,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                                    padding: 10,
                                    cornerRadius: 8,
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false, drawBorder: false },
                                    ticks: {
                                        color: "#94a3b8",
                                        font: { family: "Inter", weight: "600" },
                                    },
                                    border: { display: false },
                                },
                                y: {
                                    grid: { color: "rgba(148, 163, 184, 0.1)", drawBorder: false },
                                    ticks: { display: false },
                                    border: { display: false },
                                },
                            },
                        },
                    });
                }

                const regionEntries = this.geoRegionSummary || [];
                const regionLabels = regionEntries.map((entry) => entry.region);

                const regionCounts = regionEntries.map((entry) => this.allResponses.filter((r) => this.normalizeText(r.response_data?.region_address) === entry.region).length);
                const provinceCountsMap = this.filteredProvinceCounts;
                const provinceLabels = Object.keys(provinceCountsMap);
                const provinceCounts = provinceLabels.map((l) => provinceCountsMap[l]);
                const cityCountsMap = this.filteredCityCounts;
                const cityLabels = Object.keys(cityCountsMap);
                const cityCounts = cityLabels.map((l) => cityCountsMap[l]);

                const palette = this.responseColors.concat(this.totalsColors);
                const regionColors = regionLabels.map((_, i) => palette[i % palette.length]);
                const provinceColors = provinceLabels.map((_, i) => palette[(i + regionLabels.length) % palette.length]);
                const cityColors = cityLabels.map((_, i) => palette[(i + regionLabels.length + provinceLabels.length) % palette.length]);

                this.regionPieInstance = this.createDonutChart("regionPieCanvas", regionLabels, regionCounts, regionColors, (label) => this.onRegionClick(label));
                this.provincePieInstance = this.createDonutChart("provincePieCanvas", provinceLabels, provinceCounts, provinceColors, (label) => this.onProvinceClick(label));
                this.cityPieInstance = this.createDonutChart("cityPieCanvas", cityLabels, cityCounts, cityColors);

                const canvases = Array.isArray(this.$refs.dynamicChartCanvas) ? this.$refs.dynamicChartCanvas : this.$refs.dynamicChartCanvas ? [this.$refs.dynamicChartCanvas] : [];

                this.dynamicChartConfigs.forEach((config, index) => {
                    const canvas = canvases[index];
                    if (!canvas) return;
                    const { labels, data } = this.buildChartData(config);
                    if (!labels.length) return;

                    const palette = this.responseColors.concat(this.totalsColors);
                    const colors = labels.map((_, i) => palette[i % palette.length]);

                    this.dynamicChartInstances[config.id] = new Chart(canvas, {
                        type: config.chartType,
                        data: {
                            labels,
                            datasets: [
                                {
                                    data,
                                    backgroundColor: colors,
                                    borderWidth: config.chartType === "bar" ? 0 : 2,
                                    borderColor: "transparent",
                                    borderRadius: config.chartType === "bar" ? 6 : 0,
                                    hoverOffset: 4,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: config.chartType !== "bar",
                                    position: "bottom",
                                    labels: { color: "#94a3b8", usePointStyle: true, boxWidth: 8 },
                                },
                                tooltip: {
                                    enabled: true,
                                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                                    padding: 10,
                                    cornerRadius: 8,
                                },
                            },
                            scales:
                                config.chartType === "bar"
                                    ? {
                                          x: {
                                              grid: { display: false, drawBorder: false },
                                              ticks: {
                                                  color: "#94a3b8",
                                                  font: { family: "Inter" },
                                              },
                                              border: { display: false },
                                          },
                                          y: {
                                              grid: {
                                                  color: "rgba(148, 163, 184, 0.1)",
                                                  drawBorder: false,
                                              },
                                              ticks: { precision: 0, color: "#94a3b8" },
                                              border: { display: false },
                                          },
                                      }
                                    : undefined,
                        },
                    });
                });
            });
        },
        destroyCharts() {
            if (this.responseChartInstance) {
                this.responseChartInstance.destroy();
                this.responseChartInstance = null;
            }
            if (this.totalsChartInstance) {
                this.totalsChartInstance.destroy();
                this.totalsChartInstance = null;
            }
            if (this.regionPieInstance) {
                this.regionPieInstance.destroy();
                this.regionPieInstance = null;
            }
            if (this.provincePieInstance) {
                this.provincePieInstance.destroy();
                this.provincePieInstance = null;
            }
            if (this.cityPieInstance) {
                this.cityPieInstance.destroy();
                this.cityPieInstance = null;
            }
            Object.values(this.dynamicChartInstances).forEach((chart) => chart.destroy());
            this.dynamicChartInstances = {};
        },
        onRegionClick(regionLabel) {
            if (this.selectedRegion === regionLabel) {
                this.selectedRegion = null;
                this.selectedProvince = null;
            } else {
                this.selectedRegion = regionLabel;
                this.selectedProvince = null;
            }
            this.buildCharts();
        },
        onProvinceClick(provinceLabel) {
            if (this.selectedProvince === provinceLabel) {
                this.selectedProvince = null;
            } else {
                this.selectedProvince = provinceLabel;
            }
            this.buildCharts();
        },
        onResponseUpdated(updatedResponse) {
            this.closeResponseModal();
        },
        openResponseModal(response, formType) {
            this.selectedResponse = response;
            this.selectedResponseType = formType;
            this.selectedFormType = formType;
            this.showResponseModal = true;
        },
        closeResponseModal() {
            this.showResponseModal = false;
            this.selectedResponse = null;
            this.selectedResponseType = null;
            this.selectedFormType = null;
        },
        formatValue(value) {
            if (value === null || value === undefined || value === "") return "-";
            const normalizedBool = this.normalizeBooleanValue(value);
            if (normalizedBool !== null) return normalizedBool;
            if (Array.isArray(value)) return value.join(", ");
            return String(value);
        },
        escapeCSV(value) {
            if (value === null || value === undefined) return "";
            const str = Array.isArray(value) ? value.join(", ") : String(value);
            if (str.includes(",") || str.includes('"') || str.includes("\n")) {
                return `"${str.replace(/"/g, '""')}"`;
            }
            return str;
        },
    },
    watch: {
        stats: {
            handler() {
                this.buildCharts();
            },
            deep: true,
        },
        responsesByType: {
            handler() {
                this.buildCharts();
            },
            deep: true,
        },
        responseDataGroups: {
            handler(groups) {
                if (!this.activeFormType && groups.length) {
                    this.activeFormType = groups[0].form_type;
                } else if (this.activeFormType && !groups.find((group) => group.form_type === this.activeFormType)) {
                    this.activeFormType = groups[0]?.form_type ?? null;
                }

                if (!this.selectedChartFormType && groups.length) {
                    this.selectedChartFormType = groups[0].form_type;
                }
                if (this.selectedChartFormType && !this.selectedFormColumns.length) {
                    this.selectedChartFormType = groups[0]?.form_type ?? null;
                }
                if (this.selectedChartFormType && !this.selectedChartColumn) {
                    this.selectedChartColumn = this.selectedFormColumns[0] || null;
                }
                if (!this.selectedChartType) {
                    const options = this.getChartTypeOptions(this.selectedColumnDataType || "string");
                    this.selectedChartType = options[0] || "bar";
                }
            },
            immediate: true,
        },
        selectedChartFormType() {
            this.selectedChartColumn = this.selectedFormColumns[0] || null;
            const options = this.getChartTypeOptions(this.selectedColumnDataType || "string");
            this.selectedChartType = options[0] || "bar";
        },
        selectedChartColumn() {
            const options = this.getChartTypeOptions(this.selectedColumnDataType || "string");
            if (!options.includes(this.selectedChartType)) {
                this.selectedChartType = options[0] || "bar";
            }
        },
        dynamicChartConfigs: {
            handler() {
                this.buildCharts();
            },
            deep: true,
        },
    },
    mounted() {
        this.buildCharts();
        this.configureRealtime();
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) clearTimeout(this.realtimeRefreshTimer);
        this.cleanupRealtime();
        this.destroyCharts();
    },
};
</script>

<template>
    <div class="space-y-6">
        <!-- Responses Data Table Section -->
        <TabNavigation
            v-model="activeFormType"
            :tabs="responseTabs">
            <template #default>
                <div
                    class="mt-5"
                    v-if="activeGroup">
                    <div class="rounded-xl">
                        <DataTable
                            :mode="'offline'"
                            :rows="activeGroup.items"
                            :columns="activeGroupColumns"
                            :enablePagination="true"
                            :enableSearch="true"
                            :enableFilters="true"
                            :enableExport="true"
                            emptyMessage="No responses available.">
                            <template #cell-created_at="{ value }">
                                <span class="font-medium text-slate-600 dark:text-slate-300">
                                    {{ formatDateTime(value) }}
                                </span>
                            </template>

                            <template
                                v-for="col in activeGroup.dataColumns"
                                :key="col"
                                #[`cell-response_data.${col}`]="{ value }">
                                <template v-if="col === 'proof_of_enrollment' && value">
                                    <a
                                        :href="getFileDownloadUrl(value)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <LuFileText class="h-3.5 w-3.5" />
                                        File
                                    </a>
                                </template>
                                <template v-else-if="isHttpUrl(value)">
                                    <a
                                        :href="value"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <LuLink class="h-3.5 w-3.5" />
                                        Link
                                    </a>
                                </template>
                                <template v-else-if="isStorageFilePath(value)">
                                    <a
                                        :href="getFileDownloadUrl(value)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <LuDownload class="h-3.5 w-3.5" />
                                        File
                                    </a>
                                </template>
                                <template v-else>
                                    {{ formatValue(value) }}
                                </template>
                            </template>

                            <template #actions="{ row }">
                                <button
                                    @click="openResponseModal(row, activeGroup.form_type)"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-400">
                                    <LuFileEdit class="h-3.5 w-3.5" />
                                    Edit
                                </button>
                            </template>
                        </DataTable>
                    </div>
                </div>
                <div
                    v-else
                    class="py-12 text-center font-medium text-slate-400 dark:text-slate-500">
                    <LuInbox class="mx-auto mb-3 h-10 w-10 opacity-50" />
                    No responses available yet.
                </div>
            </template>
            <template #icon="{ tab }">
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-md bg-slate-100 px-1.5 text-[0.65rem] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ tab.count }}
                </span>
            </template>
        </TabNavigation>

        <!-- High Level Stats Row -->
        <div class="grid gap-5 md:grid-cols-3">
            <!-- Total Count Card -->
            <div class="flex min-h-[16rem] flex-col items-center justify-center rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl dark:bg-slate-900/80 dark:ring-white/5">
                <div class="mb-4 rounded-2xl bg-indigo-50 p-3 dark:bg-indigo-500/10">
                    <LuUsers class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                </div>
                <h3 class="mb-1 text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Total Responses</h3>
                <p class="text-6xl font-black tracking-tighter text-slate-900 dark:text-white">
                    {{ stats.responses_total }}
                </p>
                <p class="mt-2 text-xs font-semibold text-slate-400 dark:text-slate-500">Across all form types</p>
            </div>

            <!-- Responses By Type Chart -->
            <div class="rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl dark:bg-slate-900/80 dark:ring-white/5">
                <div class="mb-6 flex items-center gap-2.5">
                    <LuPieChart class="h-5 w-5 text-indigo-500 dark:text-indigo-400" />
                    <h3 class="text-xs font-bold uppercase text-slate-600 dark:text-slate-300">By Form Type</h3>
                </div>
                <div class="relative h-48">
                    <canvas ref="responseChartCanvas"></canvas>
                </div>
            </div>

            <!-- Event Totals Chart -->
            <div class="rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl dark:bg-slate-900/80 dark:ring-white/5">
                <div class="mb-6 flex items-center gap-2.5">
                    <LuBarChart2 class="h-5 w-5 text-indigo-500 dark:text-indigo-400" />
                    <h3 class="text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Event Overview</h3>
                </div>
                <div class="relative h-48">
                    <canvas ref="totalsChartCanvas"></canvas>
                </div>
            </div>
        </div>

        <!-- Custom Dynamic Charts Builder -->
        <div class="rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl dark:bg-slate-900/80 dark:ring-white/5">
            <div class="mb-6 flex flex-col gap-2 border-b border-slate-100 pb-5 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <LuLineChart class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">Custom Charts</h3>
                </div>
                <p class="ml-7.5 text-sm font-medium text-slate-500 dark:text-slate-400">Build visual reports from specific subform data columns.</p>
            </div>

            <div class="grid items-end gap-4 rounded-xl border border-slate-100 bg-slate-50 p-5 md:grid-cols-4 dark:border-slate-800 dark:bg-slate-800/40">
                <div>
                    <label class="mb-1.5 block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Source Form</label>
                    <div class="relative">
                        <select
                            v-model="selectedChartFormType"
                            class="block w-full appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option
                                v-for="option in chartFormOptions"
                                :key="option.value"
                                :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Data Column</label>
                    <div class="relative">
                        <select
                            v-model="selectedChartColumn"
                            class="block w-full appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option
                                v-for="column in selectedFormColumns"
                                :key="column"
                                :value="column">
                                {{ getColumnLabel(selectedChartFormType, column) }}
                            </option>
                        </select>
                        <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Visualization</label>
                    <div class="relative">
                        <select
                            v-model="selectedChartType"
                            class="block w-full appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm font-semibold text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option
                                v-for="chartType in getChartTypeOptions(selectedColumnDataType || 'string')"
                                :key="chartType"
                                :value="chartType">
                                {{ chartType.charAt(0).toUpperCase() + chartType.slice(1) }} Chart
                            </option>
                        </select>
                        <LuChevronDown class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-slate-400" />
                    </div>
                </div>
                <div>
                    <button
                        @click="addDynamicChart"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-700 active:scale-95">
                        <LuPlus class="h-4 w-4" />
                        Add Chart
                    </button>
                </div>
            </div>

            <p
                v-if="selectedColumnDataType"
                class="ml-2 mt-3 flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <LuInfo class="h-3.5 w-3.5" />
                Detected data format:
                <span class="uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                    {{ selectedColumnDataType }}
                </span>
            </p>

            <!-- Rendered Dynamic Charts -->
            <div
                v-if="dynamicChartConfigs.length"
                class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="chart in dynamicChartConfigs"
                    :key="chart.id"
                    class="group relative rounded-xl border border-slate-200/60 bg-slate-50 p-5 transition-all hover:border-slate-300 dark:border-slate-700/60 dark:bg-slate-800/40 dark:hover:border-slate-600">
                    <button
                        @click="removeDynamicChart(chart.id)"
                        class="absolute right-4 top-4 rounded-lg p-1.5 text-slate-400 opacity-0 transition-colors hover:bg-red-50 hover:text-red-500 group-hover:opacity-100 dark:hover:bg-red-500/10"
                        title="Remove Chart">
                        <LuTrash2 class="h-4 w-4" />
                    </button>

                    <div class="mb-5 border-b border-slate-200 pb-4 pr-8 dark:border-slate-700/50">
                        <h4 class="truncate text-sm font-bold text-slate-900 dark:text-white">
                            {{ getChartTitle(chart) }}
                        </h4>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="inline-flex rounded bg-indigo-100 px-1.5 py-0.5 text-[0.6rem] font-bold uppercase text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                {{ chart.chartType }}
                            </span>
                            <span class="inline-flex rounded bg-slate-200 px-1.5 py-0.5 text-[0.6rem] font-bold uppercase text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                {{ chart.dataType }}
                            </span>
                        </div>
                    </div>

                    <div class="relative h-48">
                        <canvas ref="dynamicChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="mt-6 rounded-xl border-2 border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                <LuLayoutDashboard class="mx-auto mb-3 h-8 w-8 text-slate-300 dark:text-slate-600" />
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">No custom charts added yet.</p>
            </div>
        </div>

        <!-- Geographic Coverage (Map/Pies) -->
        <div class="rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl dark:bg-slate-900/80 dark:ring-white/5">
            <div class="mb-5 flex items-center gap-2.5 border-b border-slate-100 pb-4 dark:border-slate-800">
                <LuMap class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                <h3 class="text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">Geographic Coverage</h3>
            </div>

            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2">
                <div
                    v-if="selectedRegion || selectedProvince"
                    class="mb-6 flex items-center justify-between rounded-xl border border-indigo-100 bg-indigo-50 p-3 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                    <div class="flex items-center gap-4 text-sm font-semibold">
                        <span
                            v-if="selectedRegion"
                            class="flex items-center gap-1.5 text-indigo-900 dark:text-indigo-200">
                            <LuMapPin class="h-4 w-4 text-indigo-400" />
                            Region:
                            <span class="font-black">{{ selectedRegion }}</span>
                        </span>
                        <span
                            v-if="selectedProvince"
                            class="flex items-center gap-1.5 text-indigo-900 dark:text-indigo-200">
                            <LuMapPin class="h-4 w-4 text-indigo-400" />
                            Province:
                            <span class="font-black">{{ selectedProvince }}</span>
                        </span>
                    </div>
                    <button
                        @click="
                            () => {
                                selectedRegion = null;
                                selectedProvince = null;
                                buildCharts();
                            }
                        "
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-bold text-red-600 transition-colors hover:bg-red-200 dark:bg-red-500/20 dark:text-red-400 dark:hover:bg-red-500/30">
                        <LuX class="h-3.5 w-3.5" />
                        Clear Filters
                    </button>
                </div>
            </transition>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <h4 class="mb-4 text-center text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">
                        Regions
                        <span class="ml-1 text-[0.6rem] font-medium text-indigo-500">(Click to filter)</span>
                    </h4>
                    <div class="relative h-44">
                        <canvas
                            ref="regionPieCanvas"
                            class="cursor-pointer"></canvas>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <h4 class="mb-4 text-center text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">
                        Provinces
                        <span class="ml-1 text-[0.6rem] font-medium text-indigo-500">(Click to filter)</span>
                    </h4>
                    <div class="relative h-44">
                        <canvas
                            ref="provincePieCanvas"
                            class="cursor-pointer"></canvas>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                    <h4 class="mb-4 text-center text-[0.65rem] font-bold uppercase text-slate-500 dark:text-slate-400">Cities</h4>
                    <div class="relative h-44">
                        <canvas ref="cityPieCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Response Edit Modal (Unchanged Layout Logic, handled inside child components) -->
        <modal
            :show="showResponseModal"
            @close="closeResponseModal"
            max-width="3xl">
            <div class="max-h-[85vh] overflow-y-auto bg-slate-50 p-6 dark:bg-slate-900">
                <component
                    v-if="selectedResponseType && getFormCardComponent(selectedResponseType)"
                    :is="getFormCardComponent(selectedResponseType)"
                    :responseData="selectedResponse"
                    :eventId="eventId"
                    :config="config"
                    @updatedModel="onResponseUpdated"
                    @createdModel="onResponseUpdated" />
            </div>
        </modal>
    </div>
</template>
