<script>
import DtTable from "./components/DtTable.vue";
import DtThead from "./components/DtThead.vue";
import DtTbody from "./components/DtTbody.vue";
import DtRowHead from "./components/DtRowHead.vue";
import DtRowBody from "./components/DtRowBody.vue";
import DtHead from "./components/DtHead.vue";
import DtData from "./components/DtData.vue";
import DtLinkButton from "./components/DtLinkButton.vue";
import DataTableApi from "../infrastructure/DataTableApi";
import { resolveDatatableRealtimeSubscriptions, subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

export default {
    name: "DataTable",
    components: {
        DtTable,
        DtThead,
        DtTbody,
        DtRowHead,
        DtRowBody,
        DtHead,
        DtData,
        DtLinkButton,
    },
    props: {
        apiResponse: {
            type: [Object, Array],
            default: null,
        },
        rows: {
            type: Array,
            default: () => [],
        },
        processing: {
            type: Boolean,
            default: false,
        },
        model: {
            type: [Function, Object],
            default: null,
        },
        indexApi: {
            type: String,
            default: "",
        },
        mode: {
            type: String,
            default: "auto",
        },
        onlineParams: {
            type: Object,
            default: () => ({}),
        },
        searchFields: {
            type: Object,
            default: () => ({}),
        },
        enableExport: {
            type: Boolean,
            default: false,
        },
        enableColumnToggle: {
            type: Boolean,
            default: true,
        },
        enableSorting: {
            type: Boolean,
            default: true,
        },
        enableSearch: {
            type: Boolean,
            default: true,
        },
        enableFilters: {
            type: Boolean,
            default: true,
        },
        enablePagination: {
            type: Boolean,
            default: true,
        },
        enableDebounce: {
            type: Boolean,
            default: true,
        },
        debounceDelay: {
            type: Number,
            default: 350,
        },
        defaultPerPage: {
            type: Number,
            default: 25,
        },
        emptyMessage: {
            type: String,
            default: "No data available",
        },
        stickyHeader: {
            type: Boolean,
            default: true,
        },
        striped: {
            type: Boolean,
            default: true,
        },
        hoverable: {
            type: Boolean,
            default: true,
        },
        compact: {
            type: Boolean,
            default: false,
        },
        columns: {
            type: Array,
            default: () => [],
        },
    },
    emits: ["sort", "delete-record", "row-click", "row-dblclick", "export"],
    data() {
        return {
            showColumnPanel: false,
            sortColumn: null,
            sortDirection: "asc",
            localColumns: [],
            internalResponse: null,
            internalProcessing: false,
            searchDebounceTimer: null,
            dataApi: null,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
            tableState: {
                search: "",
                filter: null,
                is_exact: false,
                page: 1,
                per_page: this.defaultPerPage,
                sort: null,
                order: "asc",
            },
        };
    },
    computed: {
        isOnlineMode() {
            if (this.mode === "online") {
                return true;
            }

            if (this.mode === "offline") {
                return false;
            }

            return Boolean(this.indexApi);
        },
        resolvedResponse() {
            if (this.isOnlineMode) {
                return this.normalizeApiResponse(this.internalResponse);
            }

            return this.buildOfflineResponse();
        },
        resolvedRows() {
            return Array.isArray(this.resolvedResponse.data) ? this.resolvedResponse.data : [];
        },
        currentProcessing() {
            return this.processing || this.internalProcessing;
        },
        allColumns() {
            return this.localColumns;
        },
        visibleColumns() {
            return this.allColumns.filter((column) => column.visible !== false);
        },
        filterOptions() {
            return this.allColumns.map((column) => ({
                key: this.isOnlineMode ? column.db_key || column.key : column.key,
                localKey: column.key,
                title: column.title,
            }));
        },
        filteredLocalRows() {
            if (this.isOnlineMode) {
                return this.resolvedRows;
            }

            let rows = [...this.getOfflineRows()];
            const activeFilter = this.resolveFilterColumn(this.tableState.filter);
            const searchTerm = this.normalizeString(this.tableState.search);

            if (searchTerm) {
                rows = rows.filter((row) => {
                    const columns = activeFilter ? [activeFilter] : this.visibleColumns;

                    return columns.some((column) => {
                        const value = this.normalizeString(this.getNestedValue(row, column.key));

                        if (this.tableState.is_exact) {
                            return value === searchTerm;
                        }

                        return value.includes(searchTerm);
                    });
                });
            }

            if (this.enableSorting && this.sortColumn) {
                rows.sort((left, right) => {
                    const leftValue = this.getComparableValue(left, this.sortColumn);
                    const rightValue = this.getComparableValue(right, this.sortColumn);

                    if (leftValue < rightValue) {
                        return this.sortDirection === "asc" ? -1 : 1;
                    }

                    if (leftValue > rightValue) {
                        return this.sortDirection === "asc" ? 1 : -1;
                    }

                    return 0;
                });
            }

            return rows;
        },
        pagination() {
            if (this.isOnlineMode) {
                const response = this.resolvedResponse;
                const perPage = Number(response.per_page || this.tableState.per_page || this.defaultPerPage);
                const total = Number(response.total || 0);
                const currentPage = Number(response.current_page || 1);
                const lastPage = Number(response.last_page || 1);

                return {
                    from: Number(response.from || (total ? (currentPage - 1) * perPage + 1 : 0)),
                    to: Number(response.to || Math.min(total, currentPage * perPage)),
                    total,
                    current_page: currentPage,
                    last_page: lastPage,
                    per_page: perPage,
                };
            }

            const total = this.filteredLocalRows.length;
            const perPage = Number(this.tableState.per_page || this.defaultPerPage || 1);
            const lastPage = Math.max(1, Math.ceil(total / perPage) || 1);
            const currentPage = Math.min(this.tableState.page, lastPage);
            const from = total ? (currentPage - 1) * perPage + 1 : 0;
            const to = total ? Math.min(total, currentPage * perPage) : 0;

            return {
                from,
                to,
                total,
                current_page: currentPage,
                last_page: lastPage,
                per_page: perPage,
            };
        },
        displayedRows() {
            if (this.isOnlineMode || !this.enablePagination) {
                return this.filteredLocalRows;
            }

            const start = (this.pagination.current_page - 1) * this.pagination.per_page;
            return this.filteredLocalRows.slice(start, start + this.pagination.per_page);
        },
        fromRow() {
            return this.pagination.from || 1;
        },
        hasActions() {
            return Boolean(this.$slots.actions || this.displayedRows.some((row) => row?.showPage));
        },
        emptyColspan() {
            return this.visibleColumns.length + 1 + (this.hasActions ? 1 : 0);
        },
        showToolbar() {
            return this.enableSearch || this.enableFilters || this.enablePagination || this.enableExport || this.enableColumnToggle;
        },
    },
    watch: {
        columns: {
            handler() {
                this.syncColumns();
            },
            deep: true,
            immediate: true,
        },
        model: {
            handler() {
                this.syncColumns();
                this.resetDataApi();
                this.configureRealtime();
                if (this.isOnlineMode) {
                    this.fetchOnlineData(true);
                }
            },
            immediate: true,
        },
        indexApi() {
            this.resetDataApi();
            this.configureRealtime();
            if (this.isOnlineMode) {
                this.fetchOnlineData(true);
            }
        },
        mode() {
            this.resetDataApi();
            this.configureRealtime();
            if (this.isOnlineMode) {
                this.fetchOnlineData(true);
            }
        },
        onlineParams: {
            handler() {
                if (this.isOnlineMode) {
                    this.fetchOnlineData(true);
                }
            },
            deep: true,
        },
        defaultPerPage(value) {
            this.tableState.per_page = value;
        },
    },
    mounted() {
        if (this.isOnlineMode) {
            this.fetchOnlineData(true);
        }
        this.configureRealtime();
    },
    beforeUnmount() {
        if (this.searchDebounceTimer) {
            clearTimeout(this.searchDebounceTimer);
        }

        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
    },
    methods: {
        normalizeApiResponse(response) {
            if (Array.isArray(response)) {
                return {
                    data: response,
                    total: response.length,
                    current_page: 1,
                    last_page: 1,
                    per_page: response.length || this.defaultPerPage,
                    from: response.length ? 1 : 0,
                    to: response.length,
                };
            }

            if (response && Array.isArray(response.data)) {
                return response;
            }

            return {
                data: [],
                total: 0,
                current_page: 1,
                last_page: 1,
                per_page: this.tableState.per_page,
                from: 0,
                to: 0,
            };
        },
        getOfflineRows() {
            if (Array.isArray(this.rows) && this.rows.length) {
                return this.rows;
            }

            if (Array.isArray(this.apiResponse)) {
                return this.apiResponse;
            }

            if (Array.isArray(this.apiResponse?.data)) {
                return this.apiResponse.data;
            }

            return [];
        },
        buildOfflineResponse() {
            return this.normalizeApiResponse({
                data: this.displayedRows,
                total: this.filteredLocalRows.length,
                current_page: this.pagination.current_page,
                last_page: this.pagination.last_page,
                per_page: this.pagination.per_page,
                from: this.pagination.from,
                to: this.pagination.to,
            });
        },
        cloneColumn(column) {
            const normalizedColumn = {
                ...column,
                key: column.key || column.name,
                title: column.title || column.label,
                visible: column.visible !== false,
            };

            return normalizedColumn;
        },
        syncColumns() {
            const sourceColumns = this.columns.length ? this.columns : typeof this.model?.getColumns === "function" ? this.model.getColumns() : [];

            const previousVisibility = this.localColumns.reduce((carry, column) => {
                carry[column.key] = column.visible !== false;
                return carry;
            }, {});

            this.localColumns = (sourceColumns || []).map((column) => {
                const nextColumn = this.cloneColumn(column);

                if (Object.prototype.hasOwnProperty.call(previousVisibility, nextColumn.key)) {
                    nextColumn.visible = previousVisibility[nextColumn.key];
                }

                return nextColumn;
            });
        },
        resetDataApi() {
            this.dataApi = null;
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        configureRealtime() {
            this.cleanupRealtime();

            if (!this.isOnlineMode) {
                return;
            }

            const subscriptions = resolveDatatableRealtimeSubscriptions(this.resolveIndexApi()).map((subscription) => ({
                ...subscription,
                handler: (payload) => {
                    if (typeof subscription.shouldRefresh === "function" && !subscription.shouldRefresh(payload)) {
                        return;
                    }

                    this.scheduleRealtimeRefresh();
                },
            }));

            if (!subscriptions.length) {
                return;
            }

            this.realtimeCleanup = subscribeToRealtimeChannels(subscriptions);
        },
        scheduleRealtimeRefresh() {
            if (!this.isOnlineMode) {
                return;
            }

            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                this.fetchOnlineData();
            }, 400);
        },
        resolveIndexApi() {
            if (this.indexApi) {
                return this.indexApi;
            }

            if (this.mode !== "online") {
                return "";
            }

            const instance = this.getModelInstance();
            return instance?.api?.apiIndex || instance?.api?._apiIndex || "";
        },
        ensureDataApi() {
            const resolvedIndexApi = this.resolveIndexApi();

            if (!this.dataApi) {
                this.dataApi = new DataTableApi(resolvedIndexApi, this.model, this.onlineParams);
                return;
            }

            this.dataApi.setBaseUrl(resolvedIndexApi);
            this.dataApi.setModel(this.model);
            this.dataApi.setParams(this.onlineParams);
        },
        getModelInstance() {
            if (!this.model) {
                return null;
            }

            return typeof this.model === "function" ? new this.model() : this.model;
        },
        buildRequestParams() {
            const modelInstance = this.getModelInstance();

            return {
                ...this.onlineParams,
                ...(modelInstance?.api?.getSearchFields?.() || {}),
                ...this.searchFields,
                search: this.tableState.search,
                filter: this.tableState.filter,
                is_exact: this.tableState.is_exact,
                page: this.tableState.page,
                per_page: this.tableState.per_page,
                sort: this.tableState.sort,
                order: this.tableState.order,
            };
        },
        async fetchOnlineData(resetPage = false) {
            if (!this.isOnlineMode) {
                return;
            }

            this.ensureDataApi();

            if (!this.dataApi || !this.resolveIndexApi()) {
                this.internalResponse = null;
                return;
            }

            if (resetPage) {
                this.tableState.page = 1;
            }

            try {
                this.internalProcessing = true;
                const response = await this.dataApi.getIndex(this.buildRequestParams(), this.model);
                this.internalResponse = this.normalizeApiResponse(response);
            } catch (error) {
                console.error("DataTable search error:", error);
                this.internalResponse = this.normalizeApiResponse(null);
            } finally {
                this.internalProcessing = false;
            }
        },
        normalizeString(value) {
            if (value == null) {
                return "";
            }

            if (typeof value === "object") {
                return JSON.stringify(value).toLowerCase();
            }

            return String(value).trim().toLowerCase();
        },
        getNestedValue(obj, path) {
            if (!obj || !path) return null;
            return path.split(".").reduce((acc, part) => acc?.[part], obj);
        },
        getComparableValue(row, path) {
            const value = this.getNestedValue(row, path);

            if (typeof value === "number") {
                return value;
            }

            if (value instanceof Date) {
                return value.getTime();
            }

            return this.normalizeString(value);
        },
        resolveFilterColumn(filterKey) {
            if (!filterKey) {
                return null;
            }

            const option = this.filterOptions.find((column) => column.key === filterKey || column.localKey === filterKey);
            if (!option) {
                return null;
            }

            return this.allColumns.find((column) => column.key === option.localKey) || null;
        },
        getCellValue(row, column) {
            const value = this.getNestedValue(row, column.key);
            if (typeof column.formatter === "function") {
                return column.formatter(value, row);
            }
            return value ?? "-";
        },
        getCellClass(row, column) {
            if (typeof column.cellClass === "function") {
                return column.cellClass(row);
            }
            return column.cellClass || "";
        },
        getRowId(row) {
            return row?.identifier?.()?.id ?? row?.id ?? JSON.stringify(row);
        },
        toggleSort(column) {
            if (!column.sortable || !this.enableSorting) {
                return;
            }

            const targetColumn = this.isOnlineMode ? column.db_key || column.key : column.key;

            if (this.sortColumn === targetColumn) {
                this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
            } else {
                this.sortColumn = targetColumn;
                this.sortDirection = "asc";
            }

            this.tableState.sort = targetColumn;
            this.tableState.order = this.sortDirection;
            this.$emit("sort", targetColumn, this.sortDirection);

            if (this.isOnlineMode) {
                this.fetchOnlineData();
            }
        },
        toggleColumnVisibility(column) {
            column.visible = column.visible === false;
        },
        handleSearchInput() {
            this.tableState.page = 1;

            if (!this.isOnlineMode) {
                return;
            }

            if (!this.enableDebounce) {
                this.fetchOnlineData(true);
                return;
            }

            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
            }

            this.searchDebounceTimer = setTimeout(() => {
                this.fetchOnlineData(true);
            }, this.debounceDelay);
        },
        handleFilterChange() {
            this.tableState.page = 1;

            if (this.isOnlineMode) {
                this.fetchOnlineData(true);
            }
        },
        handlePerPageChange() {
            this.tableState.page = 1;

            if (this.isOnlineMode) {
                this.fetchOnlineData(true);
            }
        },
        handlePageChange(page) {
            const nextPage = Math.min(Math.max(page, 1), this.pagination.last_page || 1);
            this.tableState.page = nextPage;

            if (this.isOnlineMode) {
                this.fetchOnlineData();
            }
        },
        exportData() {
            const columns = this.visibleColumns;
            const rows = this.isOnlineMode ? this.resolvedRows : this.filteredLocalRows;
            const headers = columns.map((column) => column.title);
            const csvRows = rows.map((row) =>
                columns.map((column) => {
                    const value = this.getNestedValue(row, column.key);
                    return value != null ? String(value) : "";
                }),
            );

            const csv = [headers, ...csvRows].map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(",")).join("\n");

            const blob = new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8;" });
            const url = URL.createObjectURL(blob);
            const anchor = document.createElement("a");
            anchor.href = url;
            anchor.download = `export-${new Date().toISOString().split("T")[0]}.csv`;
            anchor.click();
            URL.revokeObjectURL(url);

            this.$emit("export");
        },
        openDelete(row) {
            this.$emit("delete-record", row);
        },
        handleRowClick(row) {
            this.$emit("row-click", row);
        },
        handleRowDblclick(row) {
            this.$emit("row-dblclick", row);
        },
        created() {
            this.syncColumns();
            this.resetDataApi();
            if (this.isOnlineMode) {
                this.fetchOnlineData(true);
            }
        },
    },
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div
            v-if="showToolbar"
            class="space-y-3 border-b border-slate-200 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-end">
                <div
                    v-if="enablePagination"
                    class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">Per Page</label>
                    <select
                        v-model.number="tableState.per_page"
                        @change="handlePerPageChange"
                        class="focus:ring-primary-500 block w-24 rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:ring-2 dark:border-slate-600 dark:bg-slate-800">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>

                <div
                    v-if="enableFilters"
                    class="flex max-w-xs flex-1 flex-col gap-1">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">Search By</label>
                    <div class="flex gap-2">
                        <select
                            v-model="tableState.filter"
                            @change="handleFilterChange"
                            class="focus:ring-primary-500 block w-full rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:ring-2 dark:border-slate-600 dark:bg-slate-800">
                            <option :value="null">All</option>
                            <option
                                v-for="column in filterOptions"
                                :key="column.key"
                                :value="column.key">
                                {{ column.title }}
                            </option>
                        </select>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-800">
                            <input
                                v-model="tableState.is_exact"
                                @change="handleFilterChange"
                                type="checkbox"
                                class="text-primary-600 focus:ring-primary-500 rounded" />
                            <span class="text-sm text-slate-600 dark:text-slate-400">Exact</span>
                        </label>
                    </div>
                </div>

                <div
                    v-if="enableSearch"
                    class="flex flex-1 flex-col gap-1">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">&nbsp;</label>
                    <div class="relative flex-1">
                        <input
                            v-model="tableState.search"
                            @input="handleSearchInput"
                            type="text"
                            placeholder="Search..."
                            class="focus:ring-primary-500 block w-full rounded-lg border border-slate-300 bg-white py-2 pl-10 pr-4 focus:ring-2 dark:border-slate-600 dark:bg-slate-800" />
                        <svg
                            class="absolute left-3 top-2.5 h-5 w-5 text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="flex items-center gap-2 xl:ml-auto">
                    <button
                        v-if="enableColumnToggle"
                        @click="showColumnPanel = !showColumnPanel"
                        class="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition-all hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Columns
                    </button>

                    <button
                        v-if="enableExport"
                        @click="exportData"
                        class="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition-all hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 -translate-y-1"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-1">
            <div
                v-if="showColumnPanel && enableColumnToggle"
                class="border-b border-slate-200 bg-slate-100 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/80">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="mr-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Visible:</span>
                    <label
                        v-for="column in allColumns"
                        :key="column.key"
                        class="inline-flex cursor-pointer select-none items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1.5 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:hover:bg-slate-600">
                        <input
                            type="checkbox"
                            :checked="column.visible !== false"
                            @change="toggleColumnVisibility(column)"
                            class="text-primary-600 focus:ring-primary-500 h-4 w-4 rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                        <span class="text-sm text-slate-700 dark:text-slate-300">
                            {{ column.title }}
                        </span>
                    </label>
                </div>
            </div>
        </transition>

        <div class="relative overflow-x-auto">
            <transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div
                    v-if="currentProcessing"
                    class="absolute inset-0 z-20 flex items-center justify-center bg-white/80 backdrop-blur-sm dark:bg-slate-900/80">
                    <div class="flex flex-col items-center gap-3">
                        <div class="border-primary-600 h-10 w-10 animate-spin rounded-full border-b-2"></div>
                    </div>
                </div>
            </transition>

            <DtTable :class="['w-full border-collapse text-left', compact ? 'text-sm' : 'text-sm']">
                <DtThead>
                    <DtRowHead :class="['bg-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-700 dark:bg-slate-800/90 dark:text-slate-300', stickyHeader ? 'sticky top-0 z-10 shadow-sm' : '']">
                        <DtHead class="w-12 border-b border-slate-200 px-4 py-3 text-center dark:border-slate-700">#</DtHead>

                        <DtHead
                            v-for="column in visibleColumns"
                            :key="column.key"
                            :class="['whitespace-nowrap border-b border-slate-200 px-4 py-3 dark:border-slate-700', column.sortable && enableSorting ? 'cursor-pointer select-none transition-colors hover:bg-slate-200 dark:hover:bg-slate-700' : '', sortColumn === (isOnlineMode ? column.db_key || column.key : column.key) ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : '']"
                            :style="column.width ? { width: column.width } : {}"
                            @click="toggleSort(column)">
                            <div class="flex items-center gap-1">
                                {{ column.title }}
                                <span
                                    v-if="column.sortable && enableSorting"
                                    class="sort-indicator">
                                    <svg
                                        v-if="sortColumn !== (isOnlineMode ? column.db_key || column.key : column.key)"
                                        class="h-4 w-4 text-slate-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                    <svg
                                        v-else-if="sortDirection === 'asc'"
                                        class="text-primary-600 h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 15l7-7 7 7" />
                                    </svg>
                                    <svg
                                        v-else
                                        class="text-primary-600 h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </div>
                        </DtHead>

                        <DtHead
                            v-if="hasActions"
                            class="w-24 border-b border-slate-200 px-4 py-3 text-center dark:border-slate-700">
                            Actions
                        </DtHead>
                    </DtRowHead>
                </DtThead>

                <DtTbody>
                    <template v-if="displayedRows.length">
                        <DtRowBody
                            v-for="(row, index) in displayedRows"
                            :key="getRowId(row)"
                            :class="['transition-colors duration-150', striped && index % 2 === 1 ? 'bg-slate-50/50 dark:bg-slate-800/30' : 'bg-white dark:bg-slate-900', hoverable ? 'hover:bg-slate-100 dark:hover:bg-slate-800/60' : '']"
                            @click="handleRowClick(row)"
                            @dblclick="handleRowDblclick(row)">
                            <DtData class="border-b border-slate-200 px-4 py-3 text-center font-medium tabular-nums text-slate-500 dark:border-slate-700/50 dark:text-slate-400">
                                {{ fromRow + index }}
                            </DtData>

                            <DtData
                                v-for="column in visibleColumns"
                                :key="column.key"
                                :class="['border-b border-slate-200 px-4 py-3 dark:border-slate-700/50', getCellClass(row, column), column.align === 'center' ? 'text-center' : column.align === 'right' ? 'text-right' : 'text-left']">
                                <slot
                                    :name="`cell-${column.key}`"
                                    :row="row"
                                    :value="getCellValue(row, column)"
                                    :column="column">
                                    <span :class="['text-slate-900 dark:text-slate-100', getNestedValue(row, column.align) || '']">
                                        {{ getCellValue(row, column) }}
                                    </span>
                                </slot>
                            </DtData>

                            <DtData
                                v-if="hasActions"
                                class="border-b border-slate-200 px-4 py-3 dark:border-slate-700/50">
                                <div class="flex items-center justify-center gap-2">
                                    <slot
                                        name="actions"
                                        :row="row">
                                        <DtLinkButton
                                            v-if="row?.showPage"
                                            :href="route(row.showPage, getRowId(row))"
                                            :target="row?.showPageTarget || '_self'"
                                            class="rounded-lg p-1.5 text-amber-600 transition-colors hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                            title="Edit">
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </DtLinkButton>
                                        <button
                                            v-if="row?.api?._apiDelete || row?._apiDelete"
                                            @click.stop="openDelete(row)"
                                            class="rounded-lg p-1.5 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                            title="Delete">
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </slot>
                                </div>
                            </DtData>
                        </DtRowBody>
                    </template>

                    <DtRowBody v-else>
                        <DtData
                            :colspan="emptyColspan"
                            class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-500 dark:text-slate-400">
                                <svg
                                    class="h-12 w-12 text-slate-300 dark:text-slate-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm font-medium">{{ emptyMessage }}</p>
                            </div>
                        </DtData>
                    </DtRowBody>
                </DtTbody>
            </DtTable>
        </div>

        <div
            v-if="enablePagination"
            class="flex items-center justify-between border-t border-slate-200 bg-slate-50/40 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/30 dark:text-slate-400">
            <span>
                Showing
                <span class="font-medium">{{ pagination.from || 0 }}</span>
                to
                <span class="font-medium">{{ pagination.to || 0 }}</span>
                of
                <span class="font-medium">{{ pagination.total || 0 }}</span>
                results
            </span>

            <div class="flex items-center gap-2">
                <button
                    @click="handlePageChange(1)"
                    :disabled="pagination.current_page === 1 || currentProcessing"
                    class="rounded-lg p-2 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    @click="handlePageChange(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1 || currentProcessing"
                    class="rounded-lg p-2 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <span class="min-w-[3rem] rounded-lg border border-slate-300 bg-white px-4 py-2 text-center font-medium dark:border-slate-600 dark:bg-slate-800">
                    {{ pagination.current_page }}
                </span>
                <span class="text-slate-500">of {{ pagination.last_page }}</span>

                <button
                    @click="handlePageChange(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page || currentProcessing"
                    class="rounded-lg p-2 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <button
                    @click="handlePageChange(pagination.last_page)"
                    :disabled="pagination.current_page === pagination.last_page || currentProcessing"
                    class="rounded-lg p-2 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.sort-indicator {
    display: inline-flex;
    align-items: center;
    transition: opacity 0.2s;
}
</style>
