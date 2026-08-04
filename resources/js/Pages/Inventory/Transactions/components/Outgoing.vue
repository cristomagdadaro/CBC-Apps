<script>
import SearchBy from "@/Modules/DataTable/presentation/components/SearchBy.vue";
import Transaction from "@/Modules/domain/Transaction";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import OutgoingForm from "@/Pages/Inventory/Transactions/components/OutgoingForm.vue";
import OutgoingItemCard from "@/Pages/Inventory/Transactions/components/presentation/OutgoingItemCard.vue";
import Personnel from "@/Modules/domain/Personnel.js";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

export default {
    name: "Outgoing",
    components: {
        TransactionHeaderAction,
        OutgoingForm,
        OutgoingItemCard,
        ListOfForms,
        SearchBy
    },
    mixins: [ApiMixin],
    props: {
        stockLevel: {
            type: Array,
            default: () => []
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
            default: 'create',
        },
    },
    data() {
        return {
            api: null,
            errors: {},
            showModel: false,
            selectedItem: null,
            outgoingFromApi: null,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
        }
    },
    beforeMount() {
        if (this.isUpdateView) {
            return;
        }

        this.model = new Transaction();
        this.setFormAction('get');
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
            return this.mode === 'update';
        },
        personnels() {
            return (this.$page.props.personnels ?? []).map(personnel => {
                return {
                    name: personnel.id,
                    label: (new Personnel(personnel)).fullName,
                }
            });
        },
    },
    methods: {
        formatNumber(value){
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        selectItem(item) {
            this.selectedItem = item;
            this.form.item_id = item.item_id;
            this.form.unit = item.unit;
            this.showModel = true;
        },
        async searchEvent() {
            this.form.per_page = '*';
            this.processing = true;
            await this.fetchGetApi('api.inventory.transactions.remaining-stocks', this.form.data()).then((response) => {
                this.outgoingFromApi = response;
            })
            this.processing = false;
        },
        setFilter(filter, filter_by) {
            if (this.form.filter_by === filter_by) {
                this.form.filter = '';
                this.form.filter_by = '';
                this.searchEvent();
                return;
            }

            this.form.filter = filter;
            this.form.filter_by = filter_by;
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
            if (typeof this.realtimeCleanup === 'function') {
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
                    type: 'private',
                    channel: 'inventory.checkout',
                    event: 'inventory.transaction.changed',
                    feature: 'inventory',
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ]);
        },
    }
}
</script>

<template>
    <app-layout title="Outgoing Transaction">
        <template #header>
            <transaction-header-action />
        </template>
        <div v-if="isUpdateView" class="py-4 sm:py-6">
            <div class="max-w-5xl mx-auto px-3 sm:px-6">
                <outgoing-form
                    :data="data"
                    :summary="summary"
                    :attached-reports="attachedReports"
                    :personnels="personnels"
                    mode="update"
                />
            </div>
        </div>
        <div v-else class="default-container py-4 sm:py-6 text-slate-900 dark:text-slate-100">
            <div class="flex flex-col justify-between max-w-7xl mx-auto gap-4 sm:gap-6">
                <!-- Search Bar Container -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-5 rounded-2xl shadow-xs space-y-3">
                    <div class="flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-center">
                        <search-by :value="form.filter" :is-exact="form.is_exact" :options="model.constructor.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" class="sm:w-64 shrink-0" />
                        <div class="flex-1 flex gap-2">
                            <text-input placeholder="Search items, barcodes, descriptions..." v-model="form.search" @keydown.enter.prevent="searchEvent()" class="w-full" />
                            <search-btn @click="searchEvent" :disabled="model?.processing" class="w-28 text-center shrink-0">
                                <span v-if="!model?.processing">Search</span>
                                <span v-else>Searching</span>
                            </search-btn>
                        </div>
                    </div>

                    <!-- Dropdown Filters Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                        <custom-dropdown :with-all-option="false" placeholder="Stock Level" label="Filter by Stock" @selectedChange="setFilter('quantity', $event)" :options="stockLevel" />
                        <custom-dropdown :with-all-option="false" placeholder="Category" label="Filter by Category" @selectedChange="setFilter('category', $event)" :options="categories" />
                        <custom-dropdown :with-all-option="false" placeholder="Storage Room" label="Filter by Storage Room" @selectedChange="applyStorageRoomFilter($event)" :options="storage_locations" />
                        <custom-dropdown :with-all-option="false" placeholder="Project Code" label="Filter by Project Code" @selectedChange="setFilter('project_code', $event)" :options="projectCodes" />
                    </div>
                </div>

                <!-- Total Count Badge -->
                <div class="flex justify-between items-center px-1">
                    <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Registered Stock Items
                    </h3>
                    <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-extrabold text-lime-600 dark:text-lime-400">
                        {{ outgoingFromApi?.data?.length || 0 }} Items Available
                    </span>
                </div>

                <!-- Items Grid & Empty States -->
                <div v-if="outgoingFromApi" class="flex flex-col w-full gap-3 items-center">
                    <div class="w-full">
                        <outgoing-item-card 
                            v-if="outgoingFromApi && Array.isArray(outgoingFromApi.data) && outgoingFromApi.data.length > 0" 
                            :outgoing-from-api="outgoingFromApi" 
                            @select-item="selectItem"
                        />

                        <!-- Show "Searching" when processing -->
                        <div v-else-if="model.api.processing" class="text-center py-10 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 text-xs text-slate-500 font-semibold">
                            Searching items...
                        </div>

                        <!-- Show fallback when search returned no results -->
                        <div v-else-if="outgoingFromApi && outgoingFromApi.total === 0 && form.search" class="text-center py-10 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 text-xs text-slate-500 font-semibold">
                            No matching items found for "{{ form.search }}". Try adjusting your search filters.
                        </div>

                        <!-- Show empty state -->
                        <div v-else class="text-center py-10 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 text-xs text-slate-500 font-semibold">
                            No items available for checkout.
                        </div>
                    </div>
                </div>
            </div>
            <modal :show="!!selectedItem && showModel" @close="showModel = false; resetForm">
                <outgoing-form :data="selectedItem" :personnels="personnels" />
            </modal>
        </div>
    </app-layout>
</template>

<style scoped>

</style>
