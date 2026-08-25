<!-- SearchComp.vue -->
<script setup lang="ts">
import { ref, computed, onMounted, watch, defineAsyncComponent, shallowRef } from "vue";
import type { Component, VNodeProps, AllowedComponentProps } from "vue";

interface Props {
    propModel: new () => any;
    cardSlot?: Component;
    listSlot?: Component;
    cardSlotProps?: Record<string, any>; // Props to pass to cardSlot
    listSlotProps?: Record<string, any>; // Props to pass to listSlot
    action?: "get" | "post" | "put" | "delete";
    quickFilterOptions?: Array<{ name: string; label: string }>;
    quickFilterLabel?: string;
    quickFilterColumn?: string;
    enableDebounce?: boolean;
    debounceDelay?: number;
    defaultPerPage?: number;
    enableExport?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    action: "get",
    quickFilterOptions: () => [],
    quickFilterLabel: "Quick Filter",
    quickFilterColumn: "",
    enableDebounce: true,
    debounceDelay: 350,
    defaultPerPage: 25,
    enableExport: false,
    cardSlotProps: () => ({}),
    listSlotProps: () => ({}),
});

const emit = defineEmits<{
    (e: "searchedData", data: any): void;
    (e: "deletedModel", data: any): void;
}>();

// Initialize model instance
const model = ref<InstanceType<typeof props.propModel> | null>(null);
const apiResponse = ref<any>(null);
const processing = ref(false);

// Search state
const searchForm = ref({
    search: "",
    filter: null as string | null,
    filter_by: null as string | null,
    is_exact: false,
    page: 1,
    per_page: props.defaultPerPage,
    sort: null as string | null,
    order: "asc" as "asc" | "desc",
});

const quickFilterValue = ref<string | null>(null);
const showDeleteModal = ref(false);
const itemToDelete = ref<any>(null);
const searchDebounceTimer = ref<ReturnType<typeof setTimeout> | null>(null);
const requestId = ref(0);

// Computed
const columns = computed(() => {
    if (!props.propModel.getColumns) return [];
    return props.propModel
        .getColumns()
        .filter((col: any) => col.visible !== false)
        .map((col: any) => ({
            name: col.key,
            label: col.title,
        }));
});

const perPageOptions = [
    { name: 10, label: "10" },
    { name: 25, label: "25" },
    { name: 50, label: "50" },
    { name: 100, label: "100" },
];

// Merge default props with user-provided cardSlotProps
const mergedCardSlotProps = computed(() => ({
    "api-response": apiResponse.value,
    processing: processing.value,
    model: props.propModel,
    "enable-search": false, // Disable DataTable search by default (SearchComp handles it)
    "enable-filters": false, // Disable DataTable filters by default
    "enable-export": props.enableExport,
    "enable-pagination": false, // SearchComp handles pagination
    ...props.cardSlotProps, // User can override any of these
}));

const mergedListSlotProps = computed(() => ({
    "api-response": apiResponse.value,
    processing: processing.value,
    model: props.propModel,
    ...props.listSlotProps,
}));

// Methods
const debouncedSearch = (resetPage = false) => {
    if (!props.enableDebounce) {
        executeSearch(resetPage);
        return;
    }

    if (searchDebounceTimer.value) {
        clearTimeout(searchDebounceTimer.value);
    }

    searchDebounceTimer.value = setTimeout(() => {
        executeSearch(resetPage);
    }, props.debounceDelay);
};

const executeSearch = async (resetPage = false) => {
    if (resetPage) {
        searchForm.value.page = 1;
    }

    const currentRequestId = ++requestId.value;

    try {
        processing.value = true;

        if (!model.value) {
            model.value = new props.propModel();
        }

        const params = {
            ...searchForm.value,
            ...(model.value.api?.getSearchFields?.() || {}),
        };

        const response = await model.value.api.getIndex(params);

        if (currentRequestId !== requestId.value) return;

        apiResponse.value = response;
        emit("searchedData", response);
    } catch (error) {
        console.error("Search error:", error);
    } finally {
        processing.value = false;
    }
};

const handleSort = (column: string, direction: "asc" | "desc") => {
    searchForm.value.sort = column;
    searchForm.value.order = direction;
    executeSearch();
};

const handleSearchInput = (value: string) => {
    searchForm.value.search = value;
    debouncedSearch(true);
};

const handleFilterChange = (filters: Record<string, any>) => {
    Object.assign(searchForm.value, filters);
    executeSearch(true);
};

const handlePerPageChange = (value: number) => {
    searchForm.value.per_page = value;
    executeSearch(true);
};

const handlePageChange = (value: number) => {
    searchForm.value.page = value;
    executeSearch();
};

const handleDeleteRequest = (row: any) => {
    itemToDelete.value = row;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!itemToDelete.value || !model.value) return;

    try {
        processing.value = true;
        const response = await model.value.api.deleteApiIndex(itemToDelete.value);

        showDeleteModal.value = false;
        emit("deletedModel", response);
        await executeSearch();
    } catch (error) {
        console.error("Delete error:", error);
    } finally {
        processing.value = false;
        itemToDelete.value = null;
    }
};

// Watchers
watch(quickFilterValue, (newValue) => {
    if (!props.quickFilterColumn) return;

    if (!newValue) {
        if (searchForm.value.filter === props.quickFilterColumn) {
            searchForm.value.filter = null;
            searchForm.value.search = "";
            searchForm.value.is_exact = false;
        }
        return;
    }

    searchForm.value.filter = props.quickFilterColumn;
    searchForm.value.search = newValue;
    searchForm.value.is_exact = true;
    executeSearch(true);
});

// Lifecycle
onMounted(() => {
    executeSearch();
});
</script>

<template>
    <div class="search-container space-y-4">
        <!-- Search Toolbar -->
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col items-end gap-4 lg:flex-row">
                <!-- Per Page -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">Per Page</label>
                    <select
                        v-model="searchForm.per_page"
                        @change="handlePerPageChange(searchForm.per_page)"
                        class="focus:ring-primary-500 block w-24 rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:ring-2 dark:border-slate-600 dark:bg-slate-800">
                        <option
                            v-for="opt in perPageOptions"
                            :key="opt.name"
                            :value="opt.name">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <!-- Search By Column -->
                <div class="flex max-w-xs flex-1 flex-col gap-1">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">Search By</label>
                    <div class="flex gap-2">
                        <select
                            v-model="searchForm.filter"
                            class="focus:ring-primary-500 block w-full rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:ring-2 dark:border-slate-600 dark:bg-slate-800">
                            <option :value="null">All</option>
                            <option
                                v-for="col in columns"
                                :key="col.name"
                                :value="col.name">
                                {{ col.label }}
                            </option>
                        </select>
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-600 dark:bg-slate-800">
                            <input
                                v-model="searchForm.is_exact"
                                type="checkbox"
                                class="text-primary-600 focus:ring-primary-500 rounded" />
                            <span class="text-sm text-slate-600 dark:text-slate-400">Exact</span>
                        </label>
                    </div>
                </div>

                <!-- Quick Filter -->
                <div
                    v-if="quickFilterOptions.length && quickFilterColumn"
                    class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">
                        {{ quickFilterLabel }}
                    </label>
                    <select
                        v-model="quickFilterValue"
                        class="focus:ring-primary-500 block w-40 rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:ring-2 dark:border-slate-600 dark:bg-slate-800">
                        <option :value="null">All</option>
                        <option
                            v-for="opt in quickFilterOptions"
                            :key="opt.name"
                            :value="opt.name">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="flex flex-1 flex-col gap-1">
                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400">&nbsp;</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                v-model="searchForm.search"
                                @input="handleSearchInput(($event.target as HTMLInputElement).value)"
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
                        <button
                            @click="executeSearch(true)"
                            :disabled="processing"
                            class="bg-primary-600 hover:bg-primary-700 rounded-lg px-6 py-2 font-medium text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50">
                            <span v-if="!processing">Search</span>
                            <span
                                v-else
                                class="flex items-center gap-2">
                                <svg
                                    class="h-4 w-4 animate-spin"
                                    fill="none"
                                    viewBox="0 0 24 24">
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Searching...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination Info -->
        <div
            v-if="apiResponse"
            class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-400">
            <span>
                Showing
                <span class="font-medium">{{ apiResponse.from || 1 }}</span>
                to
                <span class="font-medium">{{ apiResponse.to || 0 }}</span>
                of
                <span class="font-medium">{{ apiResponse.total || 0 }}</span>
                results
            </span>

            <!-- Page Navigation -->
            <div class="flex items-center gap-2">
                <button
                    @click="handlePageChange(1)"
                    :disabled="apiResponse.current_page === 1 || processing"
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
                    @click="handlePageChange(apiResponse.current_page - 1)"
                    :disabled="apiResponse.current_page === 1 || processing"
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
                    {{ apiResponse.current_page }}
                </span>
                <span class="text-slate-500">of {{ apiResponse.last_page }}</span>

                <button
                    @click="handlePageChange(apiResponse.current_page + 1)"
                    :disabled="apiResponse.current_page === apiResponse.last_page || processing"
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
                    @click="handlePageChange(apiResponse.last_page)"
                    :disabled="apiResponse.current_page === apiResponse.last_page || processing"
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

        <!-- Results Display -->
        <div class="results-container">
            <!-- DataTable Mode -->
            <component
                v-if="cardSlot && !listSlot"
                :is="cardSlot"
                v-bind="mergedCardSlotProps"
                @sort="handleSort"
                @delete-record="handleDeleteRequest" />

            <!-- Custom List/Card Mode -->
            <component
                v-else-if="listSlot"
                :is="listSlot"
                v-bind="mergedListSlotProps"
                @delete-record="handleDeleteRequest">
                <template
                    v-for="(_, name) in $slots"
                    #[name]="slotData">
                    <slot
                        :name="name"
                        v-bind="slotData" />
                </template>
            </component>

            <!-- Fallback -->
            <div
                v-else
                class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-yellow-800 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200">
                Please provide either a cardSlot (DataTable) or listSlot (custom layout) component.
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                @click.self="showDeleteModal = false">
                <div class="mx-4 w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    <h3 class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">Confirm Delete</h3>
                    <p class="mb-4 text-slate-600 dark:text-slate-400">Are you sure you want to delete this record? This action cannot be undone.</p>
                    <p
                        v-if="itemToDelete?.fullName"
                        class="mb-4 text-sm font-medium text-slate-900 dark:text-white">
                        {{ itemToDelete.fullName }} (ID: {{ itemToDelete.id }})
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showDeleteModal = false"
                            class="rounded-lg px-4 py-2 text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            Cancel
                        </button>
                        <button
                            @click="confirmDelete"
                            :disabled="processing"
                            class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700 disabled:opacity-50">
                            <span
                                v-if="processing"
                                class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>
