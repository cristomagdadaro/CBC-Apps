<script>
import {Head} from "@inertiajs/vue3";
import SearchComp from "@/Components/Search/SearchComp.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import TransactionHeaderAction
    from "@/Pages/Inventory/Transactions/components/presentation/TransactionHeaderAction.vue";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import RequesterGuestCard from "@/Pages/LabRequest/components/RequesterGuestCard.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import SearchBy from "@/Components/DataTable/presentation/components/SearchBy.vue";
import PaginateBtn from "@/Components/PaginateBtn.vue";
import OutgoingForm from "@/Pages/Inventory/Transactions/components/presentation/OutgoingForm.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Pages/Inventory/Personnel/components/model/Personnel";
import GuestFormPage from "@/Pages/Shared/GuestFormPage.vue";
import CameraScanner from "@/Components/CameraScanner.vue";

export default {
    name: "OutgoingFormGuest",
    components: {
        CameraScanner,
        GuestFormPage,
        OutgoingForm,
        PaginateBtn,
        SearchBy,
        Modal,
        TextInput,
        ArrowLeft,
        ArrowRight,
        SearchBtn,
        RequesterGuestCard, TransitionContainer, TransactionHeaderAction, AppLayout, SearchComp, Head},
    mixins: [ApiMixin],
    data() {
        return {
            api: null,
            errors: {},
            showModel: false,
            selectedItem: null,
            outgoingFromApi: null,
            delayReady: false,
        }
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('get');
    },
    async mounted() {
        await this.searchEvent();

        setTimeout(() => {
            this.delayReady = true;
        }, 200);
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
            await this.fetchGetApi('api.inventory.transactions.remaining-stocks', this.form.data()).then((response) => {
                this.outgoingFromApi = response;
            })
        },
    }
}
</script>

<template>
    <Head title="Outgoing Form" />

    <guest-form-page
        :title="'Outgoing Inventory Form'"
        :subtitle="'Kindly fill out the form below to record your transaction'"
        :delay-ready="delayReady"
    >
        <template #top>
            <transition-container :duration="500" type="pop-out">
                <div v-show="delayReady"  class="border select-none md:rounded-md flex flex-col gap-2 bg-gray-100 w-full drop-shadow-lg">
                    <div class="relative flex flex-row bg-AB text-white p-2 px-4 rounded-md gap-2 shadow py-4">
                        <img src="/imgs/logo.png" alt="logo" class="w-auto h-16" />
                        <div class="flex flex-col justify-center">
                            <label class="leading-none font-semibold text-xl">Outgoing Inventory Form</label>
                            <p class="text-sm leading-none">
                                Kindly fill out the form below to record your transaction
                            </p>
                        </div>
                    </div>
                </div>
            </transition-container>
        </template>

        <div class="flex gap-5 flex-col bg-gray-50 p-3 rounded-md">
            <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
                <div class="py-4 flex flex-col md:flex-row gap-3 justify-center">
                    <camera-scanner @decoded="form.search = $event; searchEvent()" />
                    <div class="flex flex-col justify-start gap-3 mx-auto w-full">
                        <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                            <search-by :value="form.filter" :is-exact="form.is_exact" :options="model.constructor.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
                            <text-input placeholder="Search..." v-model="form.search" />
                            <search-btn @click="searchEvent" :disabled="model?.processing" class="w-[10rem] text-center">
                                <span v-if="!model?.processing">Search</span>
                                <span v-else>Searching</span>
                            </search-btn>
                        </div>
                        <div v-if="outgoingFromApi" class="flex flex-col w-full gap-2 items-center">
                            <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
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
                                <!-- Show forms when available -->
                                <div v-if="outgoingFromApi && Array.isArray(outgoingFromApi.data) && outgoingFromApi.data.length > 0" class="sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 flex flex-col sm:gap-1 min-w-fit">
                                    <div v-for="item in outgoingFromApi.data" :key="item.item_id || item.id" @click="selectItem(item)" class="flex flex-col bg-white shadow hover:bg-gray-200 hover:border-gray-500 border rounded active:scale-95 duration-75">
                                        <div class="flex select-none justify-between items-center gap-5 py-2 px-4">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-xs whitespace-nowrap overflow-ellipsis overflow-hidden">
                                                    {{ item.name }} ({{ item.unit }})
                                                </span>
                                                <span class="text-xs text-gray-500">{{ item.brand }}</span>
                                            </div>
                                            <span class="text-right">{{ formatNumber(item.remaining_quantity) }}</span>
                                        </div>
                                    </div>
                                </div>
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
            </transition-container>
        </div>
    </guest-form-page>
</template>
<style scoped>

</style>
