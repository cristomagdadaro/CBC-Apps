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

export default {
    name: "OutgoingFormGuest",
    components: {
        CameraScanner,
        OutgoingForm,
        RequesterGuestCard,
        TransactionHeaderAction,
        OutgoingItemCard,
    },
    mixins: [ApiMixin, DataFormatterMixin],
    props: {
        stockLevel: {
            type: Array,
            required: true
        }
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
            successMessage: 'Your transaction has been recorded.',
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
        }
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('get');
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
            return DataTable
        },
        Transaction() {
            return Transaction
        },
        personnels() {
            return this.$page.props.personnels.map(personnel => {
                return {
                    name: personnel.id,
                    label: (new Personnel(personnel)).fullName,
                }
            });
        },
        // Override mixin's isExpired to avoid conflicts with item-level expiration checking
        isExpired() {
            return null;
        }
    },
    methods: {
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
            this.form.filter = 'barcode';
            this.form.is_exact = true;
            this.selectedItem = null;
            this.showModel = false;
            await this.searchEvent();
            if (this.outgoingFromApi && Array.isArray(this.outgoingFromApi.data)) {
                const exactMatches = this.outgoingFromApi.data.filter(item => item.barcode === barcode);
                if (exactMatches.length === 1) {
                    this.selectItem(exactMatches[0]);
                }
            }
        },
        applyNameSort() {
            if (!this.form) return;
            this.form.sort = 'name';
            this.form.order = 'asc';
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
                this.searchEvent();
            }, 400);
        },
        configureRealtime() {
            this.cleanupRealtime();

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: 'public',
                    channel: 'public.inventory.stock',
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
    <Head title="Outgoing Form" />
    <SuccessModal
        :show="showSuccessModal"
        title="Transaction Recorded"
        :message="successMessage"
        @close="showSuccessModal = false"
    />

    <guest-form-page
        :title="'Supplies Checkout Form'"
        :subtitle="'Kindly fill out the form below to record your transaction'"
        guide-key="supplies-checkout-guest"
        :delay-ready="delayReady"
    >
        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="border p-2 md:rounded-md flex flex-col gap-2 bg-white w-full h-full drop-shadow-lg mx-auto">
                <div class="flex flex-col justify-start gap-2 w-full">
                    <div data-guide="supplies-search" class="w-full flex gap-2 items-center lg:px-0">
                        <text-input placeholder="Search..." v-model="form.search" @update:model-value="form.filter = null; form.is_exact = false;" @keydown.enter="searchEvent" />
                        <search-btn @click="searchEvent" :disabled="model?.processing" class="text-center h-full">
                            <span v-if="!model?.processing" class="hidden md:flex">Search</span>
                            <span v-else class="hidden md:flex">Searching</span>
                        </search-btn>
                    </div>
                    <div v-if="outgoingFromApi" class="flex flex-col w-full gap-2 items-center">
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 items-center w-full justify-center">
                            <custom-dropdown :with-all-option="false" placeholder="Category" label="Filter by Category" @selectedChange="setFilter('category', $event)" :options="categories" />
                            <custom-dropdown v-if="projectCodes"  placeholder="Project Code"  label="Filter by Project Code"  :options="projectCodes" @selectedChange="setFilter('project_code', $event)" />
                            <custom-dropdown :with-all-option="false" placeholder="Storage Room" label="Filter by Storage Room" @selectedChange="applyStorageRoomFilter($event)" :options="storage_locations" />
                            <search-by :value="form.filter" :is-exact="form.is_exact" :options="model.constructor.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
                            <custom-dropdown :with-all-option="false" placeholder="Stock Level" label="Filter by Stock" @selectedChange="setFilter('quantity', $event)" :options="stockLevel" />
                            <camera-scanner class="md:col-span-5 col-span-3" @decoded="searchFromBarcode" />
                        </div>
                        <h3>There are {{outgoingFromApi?.data?.length || 0}} items registered</h3>
                        <div data-guide="supplies-results" class="w-full max-h-[60vh] overflow-y-auto overflow-x-hidden">
                            <div v-show="processing" class="text-center py-3 border border-AB rounded-lg w-full h-full z-50">
                                <div class="flex items-center justify-center gap-3 py-2 px-4 h-full">
                                    <loader-icon />
                                    Loading...
                                </div>
                            </div>
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
                                Item does not exist. Try using some filters.
                            </div>

                            <!-- Show "No forms available" when nothing was returned and no search was performed -->
                            <div v-else class="text-center py-3 border border-AB rounded-lg">
                                No forms available.
                            </div>

                        </div>
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
                    </div>
                </div>
                <modal :show="!!selectedItem && showModel" @close="showModel = false">
                    <outgoing-form :data="selectedItem" :personnels="personnels"  @submitted="closeForm" @error="showModel = true"/>
                </modal>
            </div>
        </transition-container>
    </guest-form-page>
</template>
<style scoped>

</style>
