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
            const room = roomCode ? String(roomCode).padStart(2, '0') : '';
            const barcodePrefix = room ? `CBC-${room}-` : '';
            const isSameFilter = this.form.filter === 'barcode' && this.form.search === barcodePrefix;

            if (!room || isSameFilter) {
                this.form.filter = '';
                this.form.filter_by = '';
                this.form.search = '';
                this.form.is_exact = false;
                this.searchEvent();
                return;
            }

            this.form.filter = 'barcode';
            this.form.filter_by = room;
            this.form.search = barcodePrefix;
            this.form.is_exact = false;
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
        <div v-if="isUpdateView" class="py-4">
            <div class="max-w-5xl mx-auto">
                <outgoing-form
                    :data="data"
                    :summary="summary"
                    :attached-reports="attachedReports"
                    :personnels="personnels"
                    mode="update"
                />
            </div>
        </div>
        <div v-else class="default-container py-4">
            <div class="flex flex-col justify-between max-w-[90vw] gap-3 mx-auto">
                <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                    <search-by :value="form.filter" :is-exact="form.is_exact" :options="model.constructor.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
                    <text-input placeholder="Search..." v-model="form.search"  @keydown.enter.prevent="searchEvent()" />
                    <search-btn @click="searchEvent" :disabled="model?.processing" class="w-[10rem] text-center">
                        <span v-if="!model?.processing">Search</span>
                        <span v-else>Searching</span>
                    </search-btn>
                </div>
                <div class="flex gap-1 items-center w-full justify-center">
                    <custom-dropdown :with-all-option="false" placeholder="Stock Level" label="Filter by Stock" @selectedChange="setFilter('quantity', $event)" :options="stockLevel" />
                    <custom-dropdown :with-all-option="false" placeholder="Category" label="Filter by Category" @selectedChange="setFilter('category', $event)" :options="categories" />
                    <custom-dropdown :with-all-option="false" placeholder="Storage Room" label="Filter by Storage Room" @selectedChange="applyStorageRoomFilter($event)" :options="storage_locations" />
                    <custom-dropdown :with-all-option="false" placeholder="Project Code" label="Filter by Project Code" @selectedChange="setFilter('project_code', $event)" :options="projectCodes" class="col-span-3 md:col-span-2" />
                </div>
                <h3>There are {{outgoingFromApi?.data?.length || 0}} items registered</h3>
                <div v-if="outgoingFromApi" class="flex flex-col w-full gap-2 items-center">
                    <div id="dtPaginatorContainer" class="flex hidden gap-1 items-center w-full justify-center">
                        <!-- First Button -->
                        <paginate-btn @click="form.page = 1; searchEvent();" :disabled="form.page === 1">
                            First
                        </paginate-btn>

                        <!-- Previous Button -->
                        <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchEvent();" :disabled="form.page === 1">
                            <template v-slot:icon>
                                <arrow-left class="h-auto w-6" />
                            </template>
                            Prev
                        </paginate-btn>

                        <!-- Current Page Indicator -->
                        <div class="text-xs flex flex-col whitespace-nowrap text-center">
                            <span class="font-medium mx-1" title="current page and total pages">
                                <span>{{ outgoingFromApi?.current_page }}</span> / <span>{{ outgoingFromApi?.last_page }}</span>
                            </span>
                        </div>

                        <!-- Next Button -->
                        <paginate-btn
                            @click="form.page = Math.min(outgoingFromApi?.last_page, form.page + 1); searchEvent();"
                            :disabled="form.page === outgoingFromApi?.last_page"
                        >
                            Next
                            <template v-slot:icon>
                                <arrow-right class="h-auto w-6" />
                            </template>
                        </paginate-btn>

                        <!-- Last Button -->
                        <paginate-btn
                            @click="form.page = outgoingFromApi?.last_page; searchEvent();"
                            :disabled="form.page === outgoingFromApi?.last_page"
                        >
                            Last
                        </paginate-btn>
                    </div>
                    <div class="w-full overflow-hidden">
                        <outgoing-item-card 
                            v-if="outgoingFromApi && Array.isArray(outgoingFromApi.data) && outgoingFromApi.data.length > 0" 
                            :outgoing-from-api="outgoingFromApi" 
                            @select-item="selectItem"
                        />

                        <!-- Show "Searching" when processing -->
                        <div v-else-if="model.api.processing" class="text-center py-3 border border-AB rounded-lg">
                            Searching...
                        </div>

                        <!-- Show "Form does not exist" when search was performed but no results -->
                        <div v-else-if="outgoingFromApi && outgoingFromApi.total === 0 && form.search" class="text-center py-3 border border-AB rounded-lg">
                            Form does not exist. Try using some filters.
                        </div>

                        <!-- Show "No forms available" when nothing was returned and no search was performed -->
                        <div v-else class="text-center py-3 border border-AB rounded-lg">
                            No forms available.
                        </div>
                    </div>
                    <div id="dtPaginatorContainer" class="hidden flex gap-1 items-center w-full justify-center">
                        <!-- First Button -->
                        <paginate-btn @click="form.page = 1; searchEvent();" :disabled="form.page === 1">
                            First
                        </paginate-btn>

                        <!-- Previous Button -->
                        <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchEvent();" :disabled="form.page === 1">
                            <template v-slot:icon>
                                <arrow-left class="h-auto w-6" />
                            </template>
                            Prev
                        </paginate-btn>

                        <!-- Current Page Indicator -->
                        <div class="text-xs flex flex-col whitespace-nowrap text-center">
                            <span class="font-medium mx-1" title="current page and total pages">
                                <span>{{ outgoingFromApi?.current_page }}</span> / <span>{{ outgoingFromApi?.last_page }}</span>
                            </span>
                        </div>

                        <!-- Next Button -->
                        <paginate-btn
                            @click="form.page = Math.min(outgoingFromApi?.last_page, form.page + 1); searchEvent();"
                            :disabled="form.page === outgoingFromApi?.last_page"
                        >
                            Next
                            <template v-slot:icon>
                                <arrow-right class="h-auto w-6" />
                            </template>
                        </paginate-btn>

                        <!-- Last Button -->
                        <paginate-btn
                            @click="form.page = outgoingFromApi?.last_page; searchEvent();"
                            :disabled="form.page === outgoingFromApi?.last_page"
                        >
                            Last
                        </paginate-btn>
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
