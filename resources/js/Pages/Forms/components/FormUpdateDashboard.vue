<script>
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
import Modal from '@/Components/Modal.vue';
import TabNavigation from '@/Components/TabNavigation.vue';
import PreregistrationCard from '@/Pages/Forms/components/PreregistrationCard.vue';
import PreregistrationQuizBeeCard from '@/Pages/Forms/components/PreregistrationQuizBeeCard.vue';
import PreregistrationQuizbeeTeamCard from '@/Pages/Forms/components/PreregistrationQuizbeeTeamCard.vue';
import RegistrationCard from '@/Pages/Forms/components/RegistrationCard.vue';
import FeedbackCard from '@/Pages/Forms/components/FeedbackCard.vue';

Chart.register(BarController, PieController, BarElement, ArcElement, CategoryScale, LinearScale, Tooltip, Legend);

export default {
    name: 'FormUpdateDashboard',
    components: {
        Modal,
        TabNavigation,
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
                pre_registration: 'Pre-registration',
                pre_registration_biotech: 'Pre-registration + Quiz Bee',
                pre_registration_quizbee: 'Pre-registration Quiz Bee',
                preregistration_quizbee: 'Pre-registration Quiz Bee',
                registration: 'Registration',
                pre_test: 'Pre-test',
                post_test: 'Post-test',
                feedback: 'Feedback',
            },
            responseColors: ['#22c55e', '#0ea5e9', '#eab308', '#ef4444', '#a855f7', '#10b981'],
            totalsColors: ['#3b82f6', '#f97316', '#22c55e'],
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
        };
    },
    computed: {
        responseLabels() {
            return Object.keys(this.stats?.responses_by_type || {}).map((key) => this.labelMap[key] || key);
        },
        responseValues() {
            return Object.values(this.stats?.responses_by_type || {});
        },
        responseTableRows() {
            const entries = Object.entries(this.stats?.responses_by_type || {});
            return entries.map(([key, value]) => ({
                form_type: key,
                label: this.labelMap[key] || key,
                count: value ?? 0,
            }));
        },
        responseDataGroups() {
            const groups = this.responsesByType || {};
            return Object.entries(groups).map(([key, items]) => {
                const itemsArray = Array.isArray(items) ? items : [];
                const uniqueKeys = new Set();
                itemsArray.forEach(item => {
                    if (item.response_data && typeof item.response_data === 'object') {
                        Object.keys(item.response_data).forEach(k => uniqueKeys.add(k));
                    }
                });
                return {
                    form_type: key,
                    label: this.labelMap[key] || key,
                    items: itemsArray,
                    dataColumns: Array.from(uniqueKeys).sort(),
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
                if (!region) {
                    return;
                }

                if (!regionMap.has(region)) {
                    regionMap.set(region, {
                        region,
                        provinces: new Set(),
                        cities: new Set(),
                    });
                }

                const province = this.normalizeText(item.response_data?.province_address);
                const city = this.normalizeText(item.response_data?.city_address);
                const entry = regionMap.get(region);

                if (province) {
                    entry.provinces.add(province);
                }

                if (city) {
                    entry.cities.add(city);
                }
            });

            return Array.from(regionMap.values())
                .map((entry) => ({
                    region: entry.region,
                    provinceCount: entry.provinces.size,
                    cityCount: entry.cities.size,
                }))
                .sort((a, b) => a.region.localeCompare(b.region));
        },
        geoRegionLabels() {
            return this.geoRegionSummary.map((e) => e.region);
        },
        geoDetailedNames() {
            // collect unique province and city names
            const provinces = new Set();
            const cities = new Set();
            this.allResponses.forEach((item) => {
                const province = this.normalizeText(item.response_data?.province_address);
                const city = this.normalizeText(item.response_data?.city_address);
                if (province) provinces.add(province);
                if (city) cities.add(city);
            });
            return {
                provinces: Array.from(provinces).sort(),
                cities: Array.from(cities).sort(),
            };
        },
        filteredProvinceCounts() {
            if (!this.selectedRegion) {
                return this.aggregateCounts('province_address', (v) => this.normalizeText(v));
            }
            const counts = {};
            this.allResponses.forEach((item) => {
                const region = this.normalizeText(item.response_data?.region_address);
                if (region !== this.selectedRegion) return;
                const province = this.normalizeText(item.response_data?.province_address);
                if (province === null || province === undefined || province === '') return;
                counts[province] = (counts[province] || 0) + 1;
            });
            return counts;
        },
        filteredCityCounts() {
            if (!this.selectedProvince && !this.selectedRegion) {
                return this.aggregateCounts('city_address', (v) => this.normalizeText(v));
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
                if (city === null || city === undefined || city === '') return;
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
    },
    methods: {
        normalizeText(value) {
            if (value === null || value === undefined) {
                return null;
            }
            const str = String(value).trim();
            return str === '' ? null : str;
        },
        normalizeBooleanValue(value) {
            if (value === null || value === undefined || value === '') {
                return null;
            }
            if (typeof value === 'boolean') {
                return value;
            }
            if (typeof value === 'string') {
                const lower = value.toLowerCase().trim();
                if (lower === 'true' || lower === '1' || lower === 'yes') {
                    return true;
                }
                if (lower === 'false' || lower === '0' || lower === 'no') {
                    return false;
                }
                return null;
            }
            if (typeof value === 'number') {
                return value !== 0;
            }
            return null;
        },
        isHttpUrl(value) {
            if (!value || typeof value !== 'string') {
                return false;
            }
            return /^https?:\/\//i.test(value.trim());
        },
        isStorageFilePath(value) {
            if (!value || typeof value !== 'string') {
                return false;
            }
            const v = value.trim();
            if (v.startsWith('quizbee/')) {
                return true;
            }
            // Heuristic: looks like a path with an extension
            return /\/.+\.[a-z0-9]+$/i.test(v);
        },
        getFileDownloadUrl(path) {
            if (!path) {
                return '#';
            }
            if (this.isHttpUrl(path)) {
                return path;
            }
            let normalized = String(path).trim().replace(/^\/+/, '');
            if (normalized.startsWith('storage/')) {
                return `/${normalized}`;
            }
            return `/storage/${normalized}`;
        },
        getFileName(path) {
            if (!path) {
                return 'Download file';
            }
            const name = String(path).split('/').pop();
            return name || 'Download file';
        },
        getFormCardComponent(formType) {
            const components = {
                'preregistration': 'PreregistrationCard',
                'preregistration_biotech': 'PreregistrationQuizBeeCard',
                'preregistration_quizbee': 'PreregistrationQuizbeeTeamCard',
                'registration': 'RegistrationCard',
                'feedback': 'FeedbackCard',
            };

            return components[formType] || null;
        },
        normalizeBoolean(value) {
            if (value === null || value === undefined || value === '') {
                return null;
            }
            if (value === true || value === 1 || value === '1' || value === 'Yes' || value === 'yes') {
                return 'Yes';
            }
            if (value === false || value === 0 || value === '0' || value === 'No' || value === 'no') {
                return 'No';
            }
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
            if (typeof value === 'string') {
                const lower = value.toLowerCase().trim();
                return ['true', 'false', 'yes', 'no', '1', '0'].includes(lower);
            }
            return false;
        },
        isNumericLike(value) {
            if (value === null || value === undefined || value === '') return false;
            if (typeof value === 'number') return Number.isFinite(value);
            if (typeof value === 'string') {
                const normalized = value.replace(/,/g, '').trim();
                return normalized !== '' && !Number.isNaN(Number(normalized));
            }
            return false;
        },
        isDateLike(value) {
            if (!value || typeof value !== 'string') return false;
            const trimmed = value.trim();
            if (trimmed.length < 6) return false;
            const timestamp = Date.parse(trimmed);
            return !Number.isNaN(timestamp);
        },
        inferDataType(values) {
            const samples = values
                .filter((v) => v !== null && v !== undefined && v !== '')
                .slice(0, 50);
            if (!samples.length) return 'string';

            let boolCount = 0;
            let numberCount = 0;
            let dateCount = 0;

            samples.forEach((value) => {
                if (this.isBooleanLike(value)) {
                    boolCount += 1;
                } else if (this.isNumericLike(value)) {
                    numberCount += 1;
                } else if (this.isDateLike(value)) {
                    dateCount += 1;
                }
            });

            const total = samples.length;
            if (boolCount / total >= 0.8) return 'boolean';
            if (dateCount / total >= 0.7) return 'date';
            if (numberCount / total >= 0.7) return 'number';
            return 'string';
        },
        getChartTypeOptions(dataType) {
            if (dataType === 'date') {
                return ['bar'];
            }
            return ['bar', 'pie', 'doughnut'];
        },
        normalizeChartValue(value, dataType) {
            if (value === null || value === undefined || value === '') return null;
            if (dataType === 'boolean') {
                return this.normalizeBoolean(value);
            }
            if (dataType === 'number') {
                const parsed = Number(String(value).replace(/,/g, '').trim());
                return Number.isNaN(parsed) ? null : parsed;
            }
            if (dataType === 'date') {
                const timestamp = Date.parse(String(value));
                return Number.isNaN(timestamp) ? null : new Date(timestamp);
            }
            return this.normalizeText(value);
        },
        buildCategoricalCounts(values, dataType, maxItems = 12) {
            const counts = {};
            values.forEach((value) => {
                const normalized = this.normalizeChartValue(value, dataType);
                if (normalized === null || normalized === undefined || normalized === '') return;
                const key = String(normalized);
                counts[key] = (counts[key] || 0) + 1;
            });

            const entries = Object.entries(counts).sort((a, b) => b[1] - a[1]);
            if (entries.length <= maxItems) {
                return entries;
            }

            const top = entries.slice(0, maxItems);
            const remainder = entries.slice(maxItems);
            const otherCount = remainder.reduce((sum, [, value]) => sum + value, 0);
            if (otherCount > 0) {
                top.push(['Other', otherCount]);
            }
            return top;
        },
        buildNumericSeries(values) {
            const numericValues = values
                .map((value) => this.normalizeChartValue(value, 'number'))
                .filter((value) => typeof value === 'number');
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
            const dates = values
                .map((value) => this.normalizeChartValue(value, 'date'))
                .filter((value) => value instanceof Date);
            if (!dates.length) return { labels: [], data: [] };

            const buckets = {};
            dates.forEach((date) => {
                const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                buckets[key] = (buckets[key] || 0) + 1;
            });

            const labels = Object.keys(buckets).sort();
            const data = labels.map((label) => buckets[label]);
            return { labels, data };
        },
        buildChartData(config) {
            const values = this.getColumnValues(config.formType, config.column);
            const dataType = config.dataType || this.inferDataType(values);

            if (dataType === 'number') {
                return this.buildNumericSeries(values);
            }

            if (dataType === 'date') {
                return this.buildDateSeries(values);
            }

            const entries = this.buildCategoricalCounts(values, dataType);
            const labels = entries.map(([label]) => label);
            const data = entries.map(([, value]) => value);
            return { labels, data };
        },
        getChartTitle(config) {
            const label = this.labelMap[config.formType] || config.formType;
            return `${label} · ${config.column.replace(/_/g, ' ')}`;
        },
        addDynamicChart() {
            if (!this.selectedChartFormType || !this.selectedChartColumn || !this.selectedChartType) return;
            const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            const dataType = this.selectedColumnDataType || 'string';
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
        aggregateCounts(field, normalizer) {
            const counts = {};
            this.allResponses.forEach((item) => {
                const raw = item.response_data?.[field];
                const normalized = normalizer ? normalizer(raw) : raw;
                if (normalized === null || normalized === undefined || normalized === '') {
                    return;
                }
                counts[normalized] = (counts[normalized] || 0) + 1;
            });
            return counts;
        },
        createPieChart(refName, labels, data, colors) {
            const canvas = this.$refs[refName];
            if (!canvas || !labels.length) {
                return null;
            }
            return new Chart(canvas, {
                type: 'pie',
                data: {
                    labels,
                    datasets: [
                        {
                            data,
                            backgroundColor: colors,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                        },
                    },
                },
            });
        },
        createBarChart(refName, labels, data, color) {
            const canvas = this.$refs[refName];
            if (!canvas || !labels.length) {
                return null;
            }
            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            data,
                            backgroundColor: color,
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
                            border: { display: false },
                        },
                        y: {
                            grid: { display: false, drawBorder: false },
                            ticks: { precision: 0 },
                            border: { display: false },
                        },
                    },
                },
            });
        },
        createStackedBarChart(refName, labels, datasets) {
            const canvas = this.$refs[refName];
            if (!canvas || !labels.length) {
                return null;
            }
            // Plugin to draw dataset label (province/city name) on each stacked segment
            const stackedLabelPlugin = {
                id: 'stackedLabelPlugin',
                afterDatasetsDraw(chart) {
                    const ctx = chart.ctx;
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        const meta = chart.getDatasetMeta(datasetIndex);
                        meta.data.forEach((bar, i) => {
                            const value = dataset.data[i];
                            if (!value || value === 0) return;

                            const x = bar.x;
                            const y = bar.y;
                            const base = bar.base ?? (bar.y + bar.height || 0);
                            const height = Math.abs(base - y);
                            const centerY = y + (base - y) / 2;

                            ctx.save();
                            ctx.font = '11px Arial';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            const labelText = String(dataset.label || '');
                            ctx.font = '11px Arial';
                            const textWidth = ctx.measureText(labelText).width;
                            const barWidth = bar.width || Math.max((bar.right - bar.left) || 0, 0);

                            if (height > 20 && textWidth <= barWidth - 6) {
                                // fits inside segment - render full name in white
                                ctx.fillStyle = '#ffffff';
                                ctx.fillText(labelText, x, centerY);
                            } else {
                                // render full name above the segment, wrapping if necessary
                                ctx.fillStyle = '#111827';
                                const maxCharsPerLine = 30;
                                const words = labelText.split(' ');
                                const lines = [];
                                let line = '';
                                words.forEach((w) => {
                                    if ((line + ' ' + w).trim().length <= maxCharsPerLine) {
                                        line = (line + ' ' + w).trim();
                                    } else {
                                        if (line) lines.push(line);
                                        line = w;
                                    }
                                });
                                if (line) lines.push(line);

                                const lineHeight = 14;
                                const startY = y - 8 - (lines.length - 1) * lineHeight;
                                lines.forEach((ln, idx) => {
                                    ctx.fillText(ln, x, startY + idx * lineHeight);
                                });
                            }

                            ctx.restore();
                        });
                    });
                },
            };

            return new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label(ctx) {
                                    const v = ctx.raw ?? ctx.parsed?.y ?? ctx.parsed?.r ?? 0;
                                    return `${ctx.dataset.label}: ${v}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false, drawBorder: false },
                            border: { display: false },
                        },
                        y: {
                            stacked: true,
                            grid: { display: false, drawBorder: false },
                            ticks: { precision: 0 },
                            border: { display: false },
                        },
                    },
                },
                plugins: [stackedLabelPlugin],
            });
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
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            // Check if value is a boolean-like string and normalize it
            const normalizedBool = this.normalizeBooleanValue(value);
            if (normalizedBool !== null) {
                return normalizedBool;
            }
            if (Array.isArray(value)) {
                return value.join(', ');
            }
            return String(value);
        },
        escapeCSV(value) {
            if (value === null || value === undefined) {
                return '';
            }
            const str = Array.isArray(value) ? value.join(', ') : String(value);
            if (str.includes(',') || str.includes('"') || str.includes('\n')) {
                return `"${str.replace(/"/g, '""')}"`;
            }
            return str;
        },
        exportToCSV(group) {
            const headers = ['Submitted On', 'Respondent', ...group.dataColumns.map(col => col.replace(/_/g, ' '))];
            const rows = group.items.map(item => [
                item.created_at,
                item.response_data?.name || item.response_data?.full_name || item.response_data?.email || 'N/A',
                ...group.dataColumns.map(col => item.response_data?.[col] ?? ''),
            ]);

            const csvContent = [
                headers.map(this.escapeCSV).join(','),
                ...rows.map(row => row.map(this.escapeCSV).join(',')),
            ].join('\n');

            // Add UTF-8 BOM to ensure proper encoding of special characters
            const BOM = '\uFEFF';
            const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `${group.form_type}_responses.csv`);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
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
            // regionGeoChartInstance removed
            if (this.regionPieInstance) { this.regionPieInstance.destroy(); this.regionPieInstance = null; }
            if (this.provincePieInstance) { this.provincePieInstance.destroy(); this.provincePieInstance = null; }
            if (this.cityPieInstance) { this.cityPieInstance.destroy(); this.cityPieInstance = null; }
            Object.values(this.dynamicChartInstances).forEach((chart) => chart.destroy());
            this.dynamicChartInstances = {};
        },
        createDonutChart(refName, labels, data, colors, onSliceClick = null) {
            const canvas = this.$refs[refName];
            if (!canvas || !labels.length) return null;
            const doughnutLabelPlugin = {
                id: 'doughnutLabelPlugin',
                afterDatasetsDraw(chart) {
                    const MAX_LABELS = 5; // if more segments than this, hide labels to avoid clutter
                    const ctx = chart.ctx;
                    const dataset = chart.data.datasets[0];
                    const meta = chart.getDatasetMeta(0);
                    const total = dataset.data.reduce((s, v) => s + (Number(v) || 0), 0);
                    if (!meta || !meta.data) return;
                    if (chart.data.labels.length > MAX_LABELS) return; // skip drawing when many labels

                    meta.data.forEach((arc, i) => {
                        const value = Number(dataset.data[i]) || 0;
                        if (value <= 0) return;
                        const startAngle = arc.startAngle;
                        const endAngle = arc.endAngle;
                        const angle = (startAngle + endAngle) / 2;
                        const r = (arc.innerRadius + arc.outerRadius) / 2;
                        const x = arc.x + Math.cos(angle) * r;
                        const y = arc.y + Math.sin(angle) * r;

                        // hide very small slices (less than 3% of total)
                        if (total > 0 && value / total < 0.03) return;

                        ctx.save();
                        ctx.fillStyle = '#000';
                        ctx.font = '12px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const labelText = String(chart.data.labels[i] || '');
                        ctx.fillText(labelText, x, y);
                        ctx.restore();
                    });
                },
            };

            return new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{ data, backgroundColor: colors }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: onSliceClick ? (event, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            onSliceClick(labels[index]);
                        }
                    } : undefined,
                    plugins: {
                        legend: { position: 'bottom', display: false },
                        tooltip: { enabled: true },
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
                        type: 'pie',
                        data: {
                            labels: this.responseLabels,
                            datasets: [
                                {
                                    label: 'Responses by Form Type',
                                    data: this.responseValues,
                                    backgroundColor: this.responseColors,
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
                                            return this.responseColors[index] || '#6b7280';
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                if (this.$refs.totalsChartCanvas) {
                    this.totalsChartInstance = new Chart(this.$refs.totalsChartCanvas, {
                        type: 'bar',
                        data: {
                            labels: ['Registrations', 'Participants', 'Responses'],
                            datasets: [
                                {
                                    label: 'Totals',
                                    data: [
                                        this.stats?.registrations_total || 0,
                                        this.stats?.participants_total || 0,
                                        this.stats?.responses_total || 0,
                                    ],
                                    backgroundColor: this.totalsColors,
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
                                        color: (ctx) => this.totalsColors[ctx.index] || '#6b7280',
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

                const regionEntries = this.geoRegionSummary || [];
                const regionLabels = regionEntries.map((entry) => entry.region);

                // create donuts: region, province, city
                const regionCounts = regionEntries.map((entry) => this.allResponses.filter(r => this.normalizeText(r.response_data?.region_address) === entry.region).length);
                const provinceCountsMap = this.filteredProvinceCounts;
                const provinceLabels = Object.keys(provinceCountsMap);
                const provinceCounts = provinceLabels.map(l => provinceCountsMap[l]);
                const cityCountsMap = this.filteredCityCounts;
                const cityLabels = Object.keys(cityCountsMap);
                const cityCounts = cityLabels.map(l => cityCountsMap[l]);

                const palette = this.responseColors.concat(this.totalsColors);
                const regionColors = regionLabels.map((_, i) => palette[i % palette.length]);
                const provinceColors = provinceLabels.map((_, i) => palette[(i + regionLabels.length) % palette.length]);
                const cityColors = cityLabels.map((_, i) => palette[(i + regionLabels.length + provinceLabels.length) % palette.length]);

                this.regionPieInstance = this.createDonutChart(
                    'regionPieCanvas',
                    regionLabels,
                    regionCounts,
                    regionColors,
                    (label) => this.onRegionClick(label)
                );
                this.provincePieInstance = this.createDonutChart(
                    'provincePieCanvas',
                    provinceLabels,
                    provinceCounts,
                    provinceColors,
                    (label) => this.onProvinceClick(label)
                );
                this.cityPieInstance = this.createDonutChart('cityPieCanvas', cityLabels, cityCounts, cityColors);

                const canvases = Array.isArray(this.$refs.dynamicChartCanvas)
                    ? this.$refs.dynamicChartCanvas
                    : this.$refs.dynamicChartCanvas
                        ? [this.$refs.dynamicChartCanvas]
                        : [];

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
                                    borderWidth: 0,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: config.chartType !== 'bar',
                                    position: 'bottom',
                                },
                                tooltip: { enabled: true },
                            },
                            scales: config.chartType === 'bar' ? {
                                x: {
                                    grid: { display: false, drawBorder: false },
                                    border: { display: false },
                                },
                                y: {
                                    grid: { display: false, drawBorder: false },
                                    ticks: { precision: 0 },
                                    border: { display: false },
                                },
                            } : undefined,
                        },
                    });
                });
            });
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
                } else if (this.activeFormType && !groups.find(group => group.form_type === this.activeFormType)) {
                    this.activeFormType = groups[0]?.form_type ?? null;
                }

                if (!this.selectedChartFormType && groups.length) {
                    this.selectedChartFormType = groups[0].form_type;
                }
                if (this.selectedChartFormType && (!this.selectedFormColumns.length)) {
                    this.selectedChartFormType = groups[0]?.form_type ?? null;
                }
                if (this.selectedChartFormType && !this.selectedChartColumn) {
                    this.selectedChartColumn = this.selectedFormColumns[0] || null;
                }
                if (!this.selectedChartType) {
                    const options = this.getChartTypeOptions(this.selectedColumnDataType || 'string');
                    this.selectedChartType = options[0] || 'bar';
                }
            },
            immediate: true,
        },
        selectedChartFormType() {
            this.selectedChartColumn = this.selectedFormColumns[0] || null;
            const options = this.getChartTypeOptions(this.selectedColumnDataType || 'string');
            this.selectedChartType = options[0] || 'bar';
        },
        selectedChartColumn() {
            const options = this.getChartTypeOptions(this.selectedColumnDataType || 'string');
            if (!options.includes(this.selectedChartType)) {
                this.selectedChartType = options[0] || 'bar';
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
    },
    beforeUnmount() {
        this.destroyCharts();
    },
};
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <TabNavigation v-model="activeFormType" :tabs="responseTabs">
                    <template #default>
                        <div class="mt-4" v-if="activeGroup">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <span class="uppercase">{{ activeGroup.label }}</span>
                                </h3>
                                <button
                                    @click="exportToCSV(activeGroup)"
                                    class="inline-flex items-center gap-2 px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Export CSV
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 border-b">
                                        <tr>
                                            <th class="px-4 py-2">Submitted On</th>
                                            <th
                                                v-for="col in activeGroup.dataColumns"
                                                :key="col"
                                                class="px-4 py-2"
                                            >
                                                {{ col.replace(/_/g, ' ') }}
                                            </th>
                                            <th class="px-4 py-2 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in activeGroup.items" :key="item.id" class="border-b">
                                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ formatDateTime(item.created_at) }}</td>
                                            <td
                                                v-for="col in activeGroup.dataColumns"
                                                :key="col"
                                                class="px-4 py-2 text-gray-700 dark:text-gray-200"
                                            >
                                                <template v-if="col === 'proof_of_enrollment' && item.response_data?.[col]">
                                                    <a
                                                        :href="getFileDownloadUrl(item.response_data?.[col])"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="text-blue-600 hover:text-blue-800 hover:underline"
                                                    >
                                                        {{ getFileName(item.response_data?.[col]) }}
                                                    </a>
                                                </template>
                                                <template v-else-if="isHttpUrl(item.response_data?.[col])">
                                                    <a
                                                        :href="item.response_data?.[col]"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="text-blue-600 hover:text-blue-800 hover:underline"
                                                    >
                                                        {{ formatValue(item.response_data?.[col]) }}
                                                    </a>
                                                </template>
                                                <template v-else-if="isStorageFilePath(item.response_data?.[col])">
                                                    <a
                                                        :href="getFileDownloadUrl(item.response_data?.[col])"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="text-blue-600 hover:text-blue-800 hover:underline"
                                                    >
                                                        {{ getFileName(item.response_data?.[col]) }}
                                                    </a>
                                                </template>
                                                <template v-else>
                                                    {{ formatValue(item.response_data?.[col]) }}
                                                </template>
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <button
                                                    @click="openResponseModal(item, activeGroup.form_type)"
                                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-green-500 hover:bg-green-600 text-white rounded transition-colors"
                                                    title="Edit this response"
                                                >
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="!activeGroup.items.length">
                                            <td :colspan="3 + activeGroup.dataColumns.length" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                                No responses available.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No responses available.
                        </div>
                    </template>
                    <template #icon="{ tab }">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ tab.count }}</span>
                    </template>
                </TabNavigation>
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Responses</h3>
                <p class="mt-2 text-3xl md:text-5xl font-semibold text-gray-900 dark:text-white text-center">{{ stats.responses_total }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Across all form types</p>
            </div>
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
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Custom Charts</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Select a form, a column, and a chart type to visualize custom subform data.</p>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-4">
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Form Type</label>
                        <select v-model="selectedChartFormType" class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option v-for="option in chartFormOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Column</label>
                        <select v-model="selectedChartColumn" class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option v-for="column in selectedFormColumns" :key="column" :value="column">{{ column.replace(/_/g, ' ') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">Chart Type</label>
                        <select v-model="selectedChartType" class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option v-for="chartType in getChartTypeOptions(selectedColumnDataType || 'string')" :key="chartType" :value="chartType">
                                {{ chartType.charAt(0).toUpperCase() + chartType.slice(1) }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="addDynamicChart"
                            class="w-full inline-flex items-center justify-center gap-2 rounded bg-blue-500 px-3 py-2 text-sm text-white hover:bg-blue-600"
                        >
                            Add Chart
                        </button>
                    </div>
                </div>
                <p v-if="selectedColumnDataType" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Detected data type: <span class="font-semibold">{{ selectedColumnDataType }}</span>
                </p>
            </div>

            <div v-if="dynamicChartConfigs.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="(chart, index) in dynamicChartConfigs" :key="chart.id" class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ getChartTitle(chart) }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Type: {{ chart.chartType }} · Data: {{ chart.dataType }}</p>
                        </div>
                        <button @click="removeDynamicChart(chart.id)" class="text-xs text-red-500 hover:underline">Remove</button>
                    </div>
                    <div class="mt-3 grid gap-2 md:grid-cols-3">
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400">Form</label>
                            <select v-model="chart.formType" @change="updateDynamicChart(chart)" class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option v-for="option in chartFormOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400">Column</label>
                            <select v-model="chart.column" @change="updateDynamicChart(chart)" class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option v-for="column in getColumnsForFormType(chart.formType)" :key="column" :value="column">{{ column.replace(/_/g, ' ') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400">Chart</label>
                            <select v-model="chart.chartType" @change="updateDynamicChart(chart)" class="mt-1 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option v-for="chartType in getChartTypeOptions(chart.dataType || 'string')" :key="chartType" :value="chartType">
                                    {{ chartType.charAt(0).toUpperCase() + chartType.slice(1) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 h-56">
                        <canvas ref="dynamicChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                No custom charts yet. Add a chart to visualize any column from your subforms.
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Region, Province, and City Coverage</h3>
            <div v-if="selectedRegion || selectedProvince" class="mt-2 mb-3 p-2 bg-blue-50 dark:bg-blue-900 rounded">
                <p class="text-xs text-blue-900 dark:text-blue-100">
                    <span v-if="selectedRegion"><strong>Region:</strong> {{ selectedRegion }}</span>
                    <span v-if="selectedProvince" class="ml-2"><strong>Province:</strong> {{ selectedProvince }}</span>
                </p>
                <button @click="() => { selectedRegion = null; selectedProvince = null; buildCharts(); }" class="mt-1 text-xs text-blue-600 dark:text-blue-300 hover:underline">
                    ← Reset Filter
                </button>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="h-48">
                    <h4 class="text-xs text-gray-500 mb-2">Regions (Click to filter)</h4>
                    <canvas ref="regionPieCanvas" class="h-40 w-full cursor-pointer"></canvas>
                </div>
                <div class="h-48">
                    <h4 class="text-xs text-gray-500 mb-2">Provinces (Click to filter)</h4>
                    <canvas ref="provincePieCanvas" class="h-40 w-full cursor-pointer"></canvas>
                </div>
                <div class="h-48">
                    <h4 class="text-xs text-gray-500 mb-2">Cities</h4>
                    <canvas ref="cityPieCanvas" class="h-40 w-full"></canvas>
                </div>
            </div>
            <!-- stacked bar removed; multi-series pies above display region/province/city -->
        </div>
        
        <!-- Response Edit Modal -->
        <modal :show="showResponseModal" @close="closeResponseModal">
            <div class="p-6 max-h-[90vh] overflow-y-auto">                
                <component 
                    v-if="selectedResponseType && getFormCardComponent(selectedResponseType)"
                    :is="getFormCardComponent(selectedResponseType)"
                    :responseData="selectedResponse"
                    :eventId="eventId"
                    :config="config"
                    @updatedModel="onResponseUpdated"
                    @createdModel="onResponseUpdated"
                />
            </div>
        </modal>
    </div>
</template>
