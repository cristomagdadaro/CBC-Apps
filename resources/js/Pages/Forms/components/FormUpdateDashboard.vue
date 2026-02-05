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
            sexChartInstance: null,
            ageChartInstance: null,
            ipChartInstance: null,
            pwdChartInstance: null,
            organizationChartInstance: null,
            regionPieInstance: null,
            provincePieInstance: null,
            cityPieInstance: null,
            selectedRegion: null,
            selectedProvince: null,
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
        sexCounts() {
            return this.aggregateCounts('sex', (value) => this.normalizeText(value));
        },
        ageCounts() {
            const counts = {};
            this.allResponses.forEach((item) => {
                const raw = item.response_data?.age;
                const age = Number.parseInt(raw, 10);
                if (Number.isNaN(age)) {
                    return;
                }
                counts[age] = (counts[age] || 0) + 1;
            });
            return counts;
        },
        ipCounts() {
            return this.aggregateCounts('is_ip', (value) => this.normalizeBoolean(value));
        },
        pwdCounts() {
            return this.aggregateCounts('is_pwd', (value) => this.normalizeBoolean(value));
        },
        organizationCounts() {
            return this.aggregateCounts('organization', (value) => this.normalizeText(value));
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
    },
    methods: {
        normalizeText(value) {
            if (value === null || value === undefined) {
                return null;
            }
            const str = String(value).trim();
            return str === '' ? null : str;
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
            if (this.sexChartInstance) {
                this.sexChartInstance.destroy();
                this.sexChartInstance = null;
            }
            if (this.ageChartInstance) {
                this.ageChartInstance.destroy();
                this.ageChartInstance = null;
            }
            if (this.ipChartInstance) {
                this.ipChartInstance.destroy();
                this.ipChartInstance = null;
            }
            if (this.pwdChartInstance) {
                this.pwdChartInstance.destroy();
                this.pwdChartInstance = null;
            }
            if (this.organizationChartInstance) {
                this.organizationChartInstance.destroy();
                this.organizationChartInstance = null;
            }
            // regionGeoChartInstance removed
            if (this.regionPieInstance) { this.regionPieInstance.destroy(); this.regionPieInstance = null; }
            if (this.provincePieInstance) { this.provincePieInstance.destroy(); this.provincePieInstance = null; }
            if (this.cityPieInstance) { this.cityPieInstance.destroy(); this.cityPieInstance = null; }
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

                const piePalette = this.responseColors;

                const sexEntries = Object.entries(this.sexCounts || {});
                const sexLabels = sexEntries.map(([label]) => label);
                const sexValues = sexEntries.map(([, value]) => value);
                this.sexChartInstance = this.createDonutChart(
                    'sexChartCanvas',
                    sexLabels,
                    sexValues,
                    sexLabels.map((_, index) => piePalette[index % piePalette.length])
                );

                const ageEntries = Object.entries(this.ageCounts || {})
                    .map(([label, value]) => [Number.parseInt(label, 10), value])
                    .filter(([label]) => !Number.isNaN(label))
                    .sort((a, b) => a[0] - b[0]);
                const ageLabels = ageEntries.map(([label]) => String(label));
                const ageValues = ageEntries.map(([, value]) => value);
                this.ageChartInstance = this.createBarChart('ageChartCanvas', ageLabels, ageValues, '#22c55e');

                const ipEntries = Object.entries(this.ipCounts || {});
                const ipLabels = ipEntries.map(([label]) => label);
                const ipValues = ipEntries.map(([, value]) => value);
                this.ipChartInstance = this.createDonutChart(
                    'ipChartCanvas',
                    ipLabels,
                    ipValues,
                    ipLabels.map((_, index) => piePalette[index % piePalette.length])
                );

                const pwdEntries = Object.entries(this.pwdCounts || {});
                const pwdLabels = pwdEntries.map(([label]) => label);
                const pwdValues = pwdEntries.map(([, value]) => value);
                this.pwdChartInstance = this.createDonutChart(
                    'pwdChartCanvas',
                    pwdLabels,
                    pwdValues,
                    pwdLabels.map((_, index) => piePalette[index % piePalette.length])
                );

                const orgEntries = Object.entries(this.organizationCounts || {});
                const orgLabels = orgEntries.map(([label]) => label);
                const orgValues = orgEntries.map(([, value]) => value);
                this.organizationChartInstance = this.createDonutChart(
                    'organizationChartCanvas',
                    orgLabels,
                    orgValues,
                    orgLabels.map((_, index) => piePalette[index % piePalette.length])
                );

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
        responseDataGroups: {
            handler(groups) {
                if (!this.activeFormType && groups.length) {
                    this.activeFormType = groups[0].form_type;
                } else if (this.activeFormType && !groups.find(group => group.form_type === this.activeFormType)) {
                    this.activeFormType = groups[0]?.form_type ?? null;
                }
            },
            immediate: true,
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
                        <div class="mt-4 overflow-x-auto" v-if="activeGroup">
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
                                            {{ formatValue(item.response_data?.[col]) }}
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
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sex</h3>
                <div class="mt-4 h-56">
                    <canvas ref="sexChartCanvas"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Age</h3>
                <div class="mt-4 h-56">
                    <canvas ref="ageChartCanvas"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Indigenous Peoples (IP)</h3>
                <div class="mt-4 h-56">
                    <canvas ref="ipChartCanvas"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Person with Disability (PWD)</h3>
                <div class="mt-4 h-56">
                    <canvas ref="pwdChartCanvas"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</h3>
                <div class="mt-4 h-56">
                    <canvas ref="organizationChartCanvas"></canvas>
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
