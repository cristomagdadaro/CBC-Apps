<script>
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import Transaction from "@/Modules/domain/Transaction";
import RequesterGuestCard from "@/Pages/LabRequest/components/RequesterGuestCard.vue";
import OutgoingForm from "@/Pages/Inventory/Transactions/components/OutgoingForm.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Modules/domain/Personnel";
import CameraScanner from "@/Components/CameraScanner.vue";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import OutgoingItemCard from "@/Pages/Inventory/Transactions/components/presentation/OutgoingItemCard.vue";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import { Filter, ChevronDown, ChevronUp } from "lucide-vue-next";

export default {
    name: "OutgoingFormGuest",
    components: {
        CameraScanner,
        OutgoingForm,
        RequesterGuestCard,
        TransactionHeaderAction,
        OutgoingItemCard,
        Filter,
        ChevronDown,
        ChevronUp,
    },
    mixins: [ApiMixin, DataFormatterMixin],
    props: {
        stockLevel: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            api: null,
            errors: {},
            showModel: false,
            selectedItem: null,
            outgoingFromApi: null,
            delayReady: false,
            processing: false,
            showSuccessModal: false,
            showFilters: false,
            successMessage: "Your transaction has been recorded.",
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
        };
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction("get");

        // Re-initialize the Inertia form with the extra properties so they are included in form.data()
        this.form = this.createFormWithRemember(
            {
                ...this.form.data(),
                category_filter: null,
                project_code_filter: null,
                stock_level_filter: null,
                storage_location_id: null,
            },
            "get",
        );

        this.applyNameSort();
    },
    async mounted() {
        await this.searchEvent();
        this.configureRealtime();

        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
    },
    computed: {
        DataTable() {
            return DataTable;
        },
        Transaction() {
            return Transaction;
        },
        personnels() {
            return this.$page.props.personnels.map((personnel) => {
                return {
                    name: personnel.id,
                    label: new Personnel(personnel).fullName,
                };
            });
        },
        activeFilterCount() {
            let count = 0;
            if (this.form?.filter) count++; // generic search column
            if (this.form?.category_filter) count++;
            if (this.form?.project_code_filter) count++;
            if (this.form?.stock_level_filter) count++;
            if (this.form?.storage_location_id) count++;
            return count;
        },
        // Override mixin's isExpired to avoid conflicts with item-level expiration checking
        isExpired() {
            return null;
        },
    },
    methods: {
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
        updateSpecificFilter(field, value) {
            if (this.form[field] === value) {
                this.form[field] = null;
                this.form.page = 1;
                this.searchEvent();
                return;
            }
            this.form[field] = value || null;
            this.form.page = 1;
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
        async closeForm() {
            await this.searchEvent();
            this.showSuccessModal = true;
            this.showModel = false;
            this.selectedItem = null;
            this.resetForm();
            this.applyNameSort();
        },
        async searchFromBarcode(barcode) {
            this.form.search = barcode;
            this.form.filter = "barcode";
            this.form.is_exact = true;
            this.selectedItem = null;
            this.showModel = false;
            await this.searchEvent();
            if (this.outgoingFromApi && Array.isArray(this.outgoingFromApi.data)) {
                const exactMatches = this.outgoingFromApi.data.filter((item) => item.barcode === barcode);
                if (exactMatches.length === 1) {
                    this.selectItem(exactMatches[0]);
                }
            }
        },
        applyNameSort() {
            if (!this.form) return;
            this.form.sort = "name";
            this.form.order = "asc";
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
                this.searchEvent();
            }, 400);
        },
        configureRealtime() {
            this.cleanupRealtime();

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "public",
                    channel: "public.inventory.stock",
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
    <Head title="Outgoing Form" />
    <SuccessModal
        :show="showSuccessModal"
        title="Transaction Recorded"
        :message="successMessage"
        @close="showSuccessModal = false" />

    <guest-form-page
        :title="'Supplies Checkout Form'"
        :subtitle="'Kindly fill out the form below to record your transaction'"
        guide-key="supplies-checkout-guest"
        :delay-ready="delayReady">
        <transition-container
            v-show="delayReady"
            :duration="1000"
            type="slide-bottom">
            <div class="shadow-xs mx-auto flex w-full flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 text-slate-900 sm:p-5 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100">
                <div class="flex w-full flex-col justify-start gap-3">
                    <!-- Search Bar & Collapsible Filter Toggle -->
                    <div
                        data-guide="supplies-search"
                        class="flex w-full flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                        <div class="flex flex-1 gap-2">
                            <text-input
                                placeholder="Search items, barcodes, descriptions..."
                                v-model="form.search"
                                @update:model-value="
                                    form.filter = null;
                                    form.is_exact = false;
                                "
                                @keydown.enter="searchEvent"
                                class="w-full" />
                            <search-btn
                                @click="searchEvent"
                                :disabled="model?.processing"
                                class="h-full px-5 text-center">
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
                            data-guide="supplies-filters"
                            class="grid w-full grid-cols-2 items-center justify-center gap-2 border-t border-slate-100 pb-1 pt-3 md:grid-cols-5 dark:border-slate-800">
                            <custom-dropdown
                                :with-all-option="false"
                                placeholder="Category"
                                label="Filter by Category"
                                :value="form.category_filter"
                                @selectedChange="updateSpecificFilter('category_filter', $event)"
                                :options="categories"
                                :show-valid-indicator="false" />
                            <custom-dropdown
                                v-if="projectCodes"
                                placeholder="Project Code"
                                label="Filter by Project Code"
                                :value="form.project_code_filter"
                                :options="projectCodes"
                                @selectedChange="updateSpecificFilter('project_code_filter', $event)"
                                :show-valid-indicator="false" />
                            <custom-dropdown
                                :with-all-option="false"
                                placeholder="Storage Room"
                                label="Filter by Storage Room"
                                :value="form.storage_location_id"
                                @selectedChange="applyStorageRoomFilter($event)"
                                :options="storage_locations"
                                :show-valid-indicator="false" />
                            <custom-dropdown
                                :with-all-option="false"
                                placeholder="Stock Level"
                                label="Filter by Stock"
                                :value="form.stock_level_filter"
                                @selectedChange="updateSpecificFilter('stock_level_filter', $event)"
                                :options="stockLevel"
                                :show-valid-indicator="false" />
                            <search-by
                                class="col-span-2 md:col-span-1"
                                :value="form.filter"
                                placeholder="Select a column"
                                :is-exact="form.is_exact"
                                :options="model.constructor.getFilterColumns()"
                                @isExact="form.is_exact = $event"
                                @searchBy="form.filter = $event" />
                        </div>
                    </transition-container>

                    <!-- Camera Barcode Scanner -->
                    <camera-scanner
                        data-guide="supplies-barcode-scanner"
                        class="w-full"
                        @decoded="searchFromBarcode" />

                    <!-- Results List Header -->
                    <div
                        v-if="outgoingFromApi"
                        class="flex w-full flex-col items-center gap-3">
                        <div class="flex w-full items-center justify-between px-1 pt-1">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Inventory Results</h3>
                            <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-bold text-lime-600 dark:border-slate-700 dark:bg-slate-800 dark:text-lime-400">{{ outgoingFromApi?.data?.length || 0 }} Items Available</span>
                        </div>
                        <div
                            data-guide="supplies-results"
                            class="max-h-[60vh] w-full overflow-y-auto overflow-x-hidden border-y p-3 drop-shadow sm:p-5">
                            <div
                                v-show="processing"
                                class="rounded-2xl border border-slate-200 bg-slate-50 py-6 text-center text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                                Loading stock items...
                            </div>
                            <outgoing-item-card
                                v-if="outgoingFromApi && Array.isArray(outgoingFromApi.data) && outgoingFromApi.data.length > 0"
                                :outgoing-from-api="outgoingFromApi"
                                class="shadow-none"
                                @select-item="selectItem" />
                            <!-- Show "Searching" when processing -->
                            <div
                                v-else-if="model.api.processing"
                                class="rounded-2xl border border-slate-200 bg-slate-50 py-6 text-center text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                                Searching items...
                            </div>

                            <!-- Show fallback when search returned no results -->
                            <div
                                v-else-if="outgoingFromApi && outgoingFromApi.total === 0 && form.search"
                                class="rounded-2xl border border-slate-200 bg-slate-50 py-6 text-center text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                                Item does not exist. Try using some filters.
                            </div>

                            <!-- Show empty state -->
                            <div
                                v-else
                                class="rounded-2xl border border-slate-200 bg-slate-50 py-6 text-center text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800/40">
                                No items available for checkout.
                            </div>
                        </div>
                    </div>
                </div>
                <modal
                    :show="!!selectedItem && showModel"
                    @close="
                        showModel = false;
                        selectedItem = null;
                    ">
                    <outgoing-form
                        :data="selectedItem"
                        :personnels="personnels"
                        :is-guest="true"
                        @submitted="closeForm"
                        @close="
                            showModel = false;
                            selectedItem = null;
                        "
                        @error="showModel = true" />
                </modal>
            </div>
        </transition-container>
    </guest-form-page>
</template>
<style scoped></style>
