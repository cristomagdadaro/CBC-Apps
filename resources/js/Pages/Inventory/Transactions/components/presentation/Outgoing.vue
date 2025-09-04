<script>
import {Head} from "@inertiajs/vue3";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import SearchBy from "@/Components/DataTable/presentation/components/SearchBy.vue";
import Modal from "@/Components/Modal.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SearchBox from "@/Pages/Inventory/Scan/components/searchBox.vue";
import TextInput from "@/Components/TextInput.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import PaginateBtn from "@/Components/PaginateBtn.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import OutgoingForm from "@/Pages/Inventory/Transactions/components/presentation/OutgoingForm.vue";
import Personnel from "@/Pages/Inventory/Personnel/components/model/Personnel.js";
import TransactionHeaderAction
    from "@/Pages/Inventory/Transactions/components/presentation/TransactionHeaderAction.vue";

export default {
    name: "Outgoing",
    components: {
        TransactionHeaderAction,
        OutgoingForm,
        ListOfForms,
        ArrowLeft,
        ArrowRight,
        PaginateBtn,
        SearchBtn, TextInput, SearchBox, AppLayout, FilterIcon, CustomDropdown, Modal, SearchBy, AddIcon, Head},
    mixins: [ApiMixin],
    data() {
        return {
            api: null,
            errors: {},
            showModel: false,
            selectedItem: null,
            outgoingFromApi: null,
        }
    },
    beforeMount() {
        this.model = new Transaction();
        this.setFormAction('get');
    },
    async mounted() {
       await this.searchEvent();
    },
    computed: {
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
            await this.fetchGetApi('api.inventory.transactions.remaining-stocks').then((response) => {
                this.outgoingFromApi = response;
            })
        },
    }
}
</script>

<template>
    <app-layout title="Outgoing Transaction">
        <template #header>
            <transaction-header-action />
        </template>
        <div class="py-4">
            <div class="flex flex-col justify-between max-w-7xl gap-3 mx-auto">
                <div class="w-full flex gap-2 items-end lg:px-0 px-2">
                    <search-by :value="form.filter" :is-exact="form.is_exact" :options="model.constructor.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
                    <text-input placeholder="Search..." v-model="form.search" />
                    <search-btn @click="searchEvent" :disabled="model?.processing" class="w-[10rem] text-center">
                        <span v-if="!model?.processing">Search</span>
                        <span v-else>Searching</span>
                    </search-btn>
                </div>
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
                        <!-- Show forms when available -->
                        <div v-if="outgoingFromApi?.data.length" class="sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 flex flex-col sm:gap-1 min-w-fit">
                            <div v-for="item in outgoingFromApi.data" @click="selectItem(item)" class="flex flex-col bg-white shadow hover:bg-gray-200 hover:border-gray-500 border rounded active:scale-95 duration-75">
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
