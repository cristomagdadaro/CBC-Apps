<script>
import SearchBy from "@/Modules/DataTable/presentation/components/SearchBy.vue";
import Transaction from "@/Modules/domain/Transaction";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import OutgoingForm from "@/Pages/Inventory/Transactions/components/OutgoingForm.vue";
import OutgoingItemCard from "@/Pages/Inventory/Transactions/components/presentation/OutgoingItemCard.vue";
import Personnel from "@/Modules/domain/Personnel.js";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import CameraScanner from "@/Components/CameraScanner.vue";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import { Filter, ChevronDown, ChevronUp } from "lucide-vue-next";

import TagifyInput from "@/Components/Tagify.vue";

export default {
    name: "Outgoing",
    components: {
        TransactionHeaderAction,
        OutgoingForm,
        OutgoingItemCard,
        ListOfForms,
        SearchBy,
        CameraScanner,
        Filter,
        ChevronDown,
        ChevronUp,
        TagifyInput,
    },
    mixins: [ApiMixin],
    props: {
        stockLevel: {
            type: Array,
            default: () => [],
        },
        data: {
            type: Object,
            default: null,
        },
        summary: {
            type: Object,
            default: null,
        },
        attachedReports: {
            type: Array,
            default: () => [],
        },
        mode: {
            type: String,
            default: "create",
        },
    },
    data() {
        return {
            api: null,
            errors: {},
            showModel: false,
            showFilters: false,
            selectedItem: null,
            outgoingFromApi: null,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
        };
    },
    beforeMount() {
        if (this.isUpdateView) {
            return;
        }

        this.model = new Transaction();
        this.setFormAction("get");
        this.applyNameSort();
        this.form.category_ids = ["1", "2", "3", "5", "6", "11", "12"];
    },
    async mounted() {
        if (this.isUpdateView) {
            return;
        }

        await this.searchEvent();
        this.configureRealtime();
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
    },
    computed: {
        isUpdateView() {
            return this.mode === "update";
        },
        personnels() {
            return (this.$page.props.personnels ?? []).map((personnel) => {
                return {
                    name: personnel.id,
                    label: new Personnel(personnel).fullName,
                };
            });
        },
        activeFilterCount() {
            let count = 0;
            if (this.form?.filter && this.form?.filter_by) count++;
            if (this.form?.storage_location_id) count++;
            if (this.form?.category_ids?.length !== 7) count++;
            return count;
        },
    },
    methods: {
        applyNameSort() {
            if (!this.form) return;
            this.form.sort = "name";
            this.form.order = "asc";
        },
        formatNumber(value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        selectItem(item) {
            this.selectedItem = item;
            this.form.item_id = item.item_id;
            this.form.unit = item.unit;
            this.showModel = true;
        },
        async searchEvent() {
            this.form.per_page = "*";
            this.processing = true;
            await this.fetchGetApi("api.inventory.transactions.remaining-stocks", this.form.data()).then((response) => {
                this.outgoingFromApi = response;
            });
            this.processing = false;
        },
        searchFromBarcode(decodedValue) {
            this.form.search = decodedValue;
            this.form.filter = "barcode";
            this.form.is_exact = true;
            this.searchEvent();
        },
        setFilter(filter, filter_by) {
            if (this.form.filter_by === filter_by) {
                this.form.filter = "";
                this.form.filter_by = "";
                this.searchEvent();
                return;
            }

            this.form.filter = filter;
            this.form.filter_by = filter_by;
            this.searchEvent();
        },
        updateCategories(value) {
            this.form.category_ids = value;
            this.searchEvent();
        },
        applyStorageRoomFilter(roomCode) {
            const normalizedRoom = roomCode ? String(roomCode) : null;
            const isSameFilter = (this.form.storage_location_id ?? null) === normalizedRoom;

            if (!normalizedRoom || isSameFilter) {
                this.form.storage_location_id = null;
                this.form.page = 1;
                this.searchEvent();
                return;
            }

            this.form.storage_location_id = normalizedRoom;
            this.form.page = 1;
            this.searchEvent();
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                if (!this.isUpdateView) {
                    this.searchEvent();
                }
            }, 400);
        },
        configureRealtime() {
            this.cleanupRealtime();

            if (this.isUpdateView) {
                return;
            }

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "private",
                    channel: "inventory.checkout",
                    event: "inventory.transaction.changed",
                    feature: "inventory",
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ]);
        },
    },
};
</script>

<template>
    <app-layout title="Outgoing Transaction">
        <template #header>
            <transaction-header-action />
        </template>
        <div
            v-if="isUpdateView"
            class="py-4 sm:py-6">
            <div class="mx-auto max-w-5xl px-3 sm:px-6">
                <outgoing-form
                    :data="data"
                    :summary="summary"
                    :attached-reports="attachedReports"
                    :personnels="personnels"
                    mode="update" />
            </div>
        </div>
        <div
            v-else
            class="default-container py-4 text-slate-900 sm:py-6 dark:text-slate-100">
            <div class="flex flex-col justify-between gap-4 sm:gap-6">
                <!-- Search Bar & Controls Container -->
                <div class="shadow-xs space-y-3 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                        <div class="flex flex-1 gap-2">
                            <text-input
                                placeholder="Search items, barcodes, descriptions..."
                                v-model="form.search"
                                @update:model-value="
                                    form.filter = null;
                                    form.is_exact = false;
                                "
                                @keydown.enter.prevent="searchEvent()"
                                class="w-full" />
                            <search-btn
                                @click="searchEvent"
                                :disabled="model?.processing"
                                class="w-28 shrink-0 text-center">
                                <span v-if="!model?.processing">Search</span>
                                <span v-else>Searching</span>
                            </search-btn>
                        </div>

                        <!-- Filter Toggle Button -->
                        <button
                            type="button"
                            @click="showFilters = !showFilters"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border px-3.5 py-2.5 text-xs font-semibold transition-all active:scale-95"
                            :class="showFilters || activeFilterCount > 0 ? 'shadow-xs border-lime-300 bg-lime-50 text-lime-700 dark:border-lime-800 dark:bg-lime-950/40 dark:text-lime-300' : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'">
                            <Filter class="h-4 w-4 text-lime-600 dark:text-lime-400" />
                            <span>Filters</span>
                            <span
                                v-if="activeFilterCount > 0"
                                class="rounded-full bg-lime-600 px-1.5 py-0.5 text-[0.65rem] font-bold text-white">
                                {{ activeFilterCount }}
                            </span>
                            <component
                                :is="showFilters ? 'ChevronUp' : 'ChevronDown'"
                                class="h-4 w-4 text-slate-400" />
                        </button>
                    </div>

                    <!-- Collapsible Dropdown Filter Drawer -->
                    <transition-container type="pop-in">
                        <div
                            v-if="showFilters"
                            class="flex flex-col gap-3 border-t border-slate-100 pb-1 pt-3 dark:border-slate-800">
                            <div class="grid w-full grid-cols-2 items-center justify-center gap-2 md:grid-cols-5">
                                <custom-dropdown
                                    :show-valid-indicator="false"
                                    :with-all-option="false"
                                    placeholder="Category"
                                    label="Filter by Category"
                                    @selectedChange="setFilter('category', $event)"
                                    :options="categories" />
                                <custom-dropdown
                                    v-if="projectCodes"
                                    :show-valid-indicator="false"
                                    placeholder="Project Code"
                                    label="Filter by Project Code"
                                    :options="projectCodes"
                                    @selectedChange="setFilter('project_code', $event)" />
                                <custom-dropdown
                                    :with-all-option="false"
                                    :show-valid-indicator="false"
                                    placeholder="Storage Room"
                                    label="Filter by Storage Room"
                                    @selectedChange="applyStorageRoomFilter($event)"
                                    :options="storage_locations" />
                                <search-by
                                    :value="form.filter"
                                    :is-exact="form.is_exact"
                                    :options="model.constructor.getFilterColumns()"
                                    @isExact="form.is_exact = $event"
                                    @searchBy="form.filter = $event" />
                                <custom-dropdown
                                    :show-valid-indicator="false"
                                    :with-all-option="false"
                                    placeholder="Stock Level"
                                    label="Filter by Stock"
                                    @selectedChange="setFilter('quantity', $event)"
                                    :options="stockLevel" />
                            </div>
                            <div class="w-full">
                                <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Categories for Remaining Stocks (IDs)</label>
                                <TagifyInput
                                    v-model="form.category_ids"
                                    name="category_ids"
                                    placeholder="Enter Category IDs"
                                    :whitelist="['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13']"
                                    :enforce-whitelist="false"
                                    @update:modelValue="updateCategories" />
                            </div>
                        </div>
                    </transition-container>

                    <!-- Camera Barcode Scanner -->
                    <camera-scanner
                        class="w-full"
                        @decoded="searchFromBarcode" />
                </div>

                <!-- Total Count Badge & Header -->
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 sm:text-sm dark:text-slate-300">Registered Stock Items</h3>
                    <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-extrabold text-lime-600 dark:border-slate-700 dark:bg-slate-800 dark:text-lime-400">{{ outgoingFromApi?.data?.length || 0 }} Items Available</span>
                </div>

                <!-- Items Grid & Empty States -->
                <div
                    v-if="outgoingFromApi"
                    class="flex w-full flex-col items-center gap-3">
                    <div class="w-full">
                        <outgoing-item-card
                            v-if="outgoingFromApi && Array.isArray(outgoingFromApi.data) && outgoingFromApi.data.length > 0"
                            :outgoing-from-api="outgoingFromApi"
                            @select-item="selectItem" />

                        <!-- Show "Searching" when processing -->
                        <div
                            v-else-if="model.api.processing"
                            class="rounded-2xl border border-slate-200 bg-white py-10 text-center text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-900">
                            Searching items...
                        </div>

                        <!-- Show fallback when search returned no results -->
                        <div
                            v-else-if="outgoingFromApi && outgoingFromApi.total === 0 && form.search"
                            class="rounded-2xl border border-slate-200 bg-white py-10 text-center text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-900">
                            No matching items found for "{{ form.search }}". Try adjusting your search filters.
                        </div>

                        <!-- Show empty state -->
                        <div
                            v-else
                            class="rounded-2xl border border-slate-200 bg-white py-10 text-center text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-900">
                            No items available for checkout.
                        </div>
                    </div>
                </div>
            </div>
            <modal
                :show="!!selectedItem && showModel"
                @close="
                    showModel = false;
                    resetForm;
                ">
                <outgoing-form
                    :data="selectedItem"
                    :personnels="personnels" />
            </modal>
        </div>
    </app-layout>
</template>

<style scoped></style>
