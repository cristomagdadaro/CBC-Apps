<script>
import {Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import SearchBox from "@/Pages/Scan/components/searchBox.vue";
import SearchBy from "@/Components/DataTable/presentation/components/SearchBy.vue";
import Modal from "@/Components/Modal.vue";
import TextField from "@/Components/TextField.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import FilterIcon from "@/Components/Icons/FilterIcon.vue";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default {
    name: "Outgoing",
    components: {FilterIcon, CustomDropdown, TextField, Modal, SearchBy, SearchBox, AddIcon, AuthenticatedLayout, Head},
    data() {
        return {
            api: null,
            data: [],
            errors: {},
            showAction: false,
            hoverOver: null,
            params: {
                search: null,
                filter: null,
                is_exact: null,
                paginate: false
            },
            selectedItem: null,
            form: {
                personnel_id: null,
                quantity: 0,
                item_id: null,
                unit: null,
            },
        }
    },
    mounted() {
        this.api = new CoreApi(route('api.inventory.transactions.remaining-stocks'));
        this.refreshData();
    },
    computed: {
        personnels() {
            return this.$page.props.personnels.map(personnel => {
                return {
                    name: personnel.id,
                    label: this.fullName(personnel),
                }
            });
        },
    },
    methods: {
        clearForm() {
            this.selectedItem = null;
            this.errors = {};
            this.form = {
                personnel_id: null,
                quantity: 0,
                item_id: null,
                unit: null,
            };
        },
        formatNumber(value){
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        showActionToItem(item) {
            this.hoverOver = item;
        },
        selectItem(item) {
            this.selectedItem = item;
            this.form.item_id = item.item_id;
            this.form.unit = item.unit;
        },
        refreshData(){
            this.api.setBaseUrl(route('api.inventory.transactions.remaining-stocks', {
                search: this.params.search,
                filter: this.params.filter,
                is_exact: this.params.is_exact,
                paginate: this.params.paginate
            }));

            this.api.get().then(response => {
                this.data = response.data;
            });
        },
        fullName(personnel) {
            const parts = [];

            if (personnel.fname) parts.push(personnel.fname);
            if (personnel.mname) parts.push(personnel.mname[0] + '.');
            if (personnel.lname) parts.push(personnel.lname);
            if (personnel.suffix) parts.push(personnel.suffix);

            return parts.join(' ');
        },
        async submit(){
            this.api.setBaseUrl(route('api.inventory.transactions.outgoing', {
                id: this.form.item_id,
            }));
            const response = await this.api.put({
                unit: this.selectedItem.unit,
                item_id: this.form.item_id,
                transac_type: 'out',
                personnel_id: this.form.personnel_id,
                quantity: this.form.quantity,
                user_id: this.$page.props.auth.user.id,
                id: this.form.item_id,
                name: this.selectedItem.name,
                brand: this.selectedItem.brand,
            });

            if (response.status === 201) {
                this.refreshData();
                this.selectedItem = null;
                this.form.item_id = null;
                this.form.personnel_id = null;
                this.form.quantity = null;
                this.errors = {};
            } else if (response instanceof ErrorResponse) {
                this.errors = response.errors;
            } else {
                console.log(response);
            }
        }
    }
}
</script>

<template>
    <Head title="Outgoing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Outgoing</h2>
        </template>
        <div class="py-4">
            <div class="flex flex-col justify-between max-w-7xl gap-3 mx-auto">
                <div class="flex gap-3">
                    <search-box class="w-full" v-model="params.search" @keydown.enter="refreshData" />
                </div>
                <div v-if="data.length" class="sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 flex flex-col sm:gap-1 min-w-fit">
                    <div v-for="item in data" @click="selectItem(item)" class="flex flex-col bg-white shadow hover:bg-gray-200 hover:border-gray-500 border rounded active:scale-95 duration-75">
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
                <div v-else class="w-full text-center text-gray-500 select-none">No data found</div>
            </div>
            <modal :show="!!selectedItem" @close="clearForm">
                <div class="flex flex-col sm:p-5 p-2 sm:gap-3 gap-1 bg-white shadow rounded">
                    <div class="flex select-none justify-between items-center gap-5 py-2 px-4 border-b">
                        <div class="flex flex-col">
                                <span class="font-bold text-xs whitespace-nowrap overflow-ellipsis overflow-hidden">
                                    {{ selectedItem.name }} ({{ selectedItem.unit }})
                                </span>
                            <span class="text-xs text-gray-500">{{ selectedItem.brand }}</span>
                        </div>
                        <div class="flex sm:gap-4 gap-1">
                            <div class="flex flex-col">
                                <span class="text-center text-gray-600">
                                    {{ formatNumber(selectedItem.remaining_quantity) }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    Remaining
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-center text-gray-600">
                                    {{ formatNumber(selectedItem.total_outgoing) }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    Consumed
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-center text-gray-600">
                                    {{ (100-((selectedItem.remaining_quantity/selectedItem.total_ingoing) *100)).toFixed(2)  }}%
                                </span>
                                <span class="text-xs text-gray-400">
                                    Utilization
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:gap-3 gap-1 border sm:p-3 p-1 rounded bg-gray-100">
                        <h1 class="text-lg font-semibold text-gray-800">Outgoing Form</h1>
                        <form @submit.prevent="submit" class="flex flex-col gap-3">
                            <custom-dropdown
                                required
                                searchable
                                :with-all-option="false"
                                :value="form.personnel_id"
                                :options="personnels"
                                placeholder="Select Personnel"
                                label="Personnel"
                                :error="errors.personnel_id"
                                @selectedChange="form.personnel_id = $event"
                                class="w-full"
                            >
                                <template #icon>
                                    <filter-icon class="h-4 w-4" />
                                </template>
                            </custom-dropdown>
                            <text-field
                                required
                                type-input="number"
                                autocomplete="off"
                                label="Quantity"
                                name="quantity"
                                id="quantity"
                                class="hidden"
                                v-model="form.quantity"
                                :error="errors.quantity"
                            />
                            <text-field
                                required
                                type-input="number"
                                autocomplete="off"
                                label="Quantity"
                                name="quantity"
                                id="quantity"
                                v-model="form.quantity"
                                :error="errors.quantity"
                            />
                            <div class="flex justify-between">
                                <button @click="clearForm" type="button" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 active:scale-95 duration-75">Cancel</button>
                                <button @click="submit" type="button" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 active:scale-95 duration-75">
                                    <span v-if="!api.processing">Submit</span>
                                    <span v-else>Processing...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </modal>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
