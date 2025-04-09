<script>
import {Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBox from "@/Pages/Scan/components/searchBox.vue";
import SearchBy from "@/Components/DataTable/presentation/components/SearchBy.vue";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import {Transaction} from "@/Pages/Transactions/components/model/Transaction.js";
import NavLink from "@/Components/NavLink.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import DeleteIcon from "@/Components/Icons/DeleteIcon.vue";
import CoreApi from "@/Components/DataTable/infrastracture/CoreApi.js";
import BaseResponse from "@/Components/DataTable/domain/BaseResponse.js";
import ErrorResponse from "@/Components/DataTable/domain/ErrorResponse.js";

export default {
    name: "Transaction",
    components: {DeleteIcon, AddIcon, NavLink, DataTable, SearchBy, SearchBox, AuthenticatedLayout, Head},
    computed: {
        Transaction() {
            return Transaction
        },
        apiUrl() {
            return route('api.inventory.transactions.index');
        },
        webUrl() {
            return route('transactions.index');
        },
    },
    data() {
        return {
            selectedItem: null,
            params: {
                search: null,
                filter: null,
                is_exact: null
            },
            columns: Transaction.getColumns()
                .map(column => column && column.visible ? { name: column.key, label: column.title } : null)
                .filter(Boolean),
            api: null,
            errors: {},
        }
    },
    methods: {
        selectedItemChange(item) {
            this.selectedItem = item;
        },
        async deleteSelected() {
            if (this.selectedItem.length > 0) {
                this.api = new CoreApi(route('api.inventory.transactions.multi-destroy'));

                const response = await this.api.delete(this.selectedItem);
                if (response instanceof BaseResponse && response.status === 200) {
                    this.selectedItem = null;
                } else if (response instanceof ErrorResponse) {
                    this.errors = response.errors;
                }
                else {
                    console.log(response);
                }
            }
        }
    }
}
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold sm:text-xl text-sm text-gray-800 leading-tight uppercase">Transaction History</h2>
        </template>
        <div class="py-4">
            <div class="flex flex-row gap-5 max-w-7xl mx-auto">
                <div class="flex flex-col gap-2 w-full px-2">
                    <div class="flex gap-3">
                        <search-box class="w-full" v-model="params.search"></search-box>
                        <search-by
                            :value="params.filter"
                            :is-exact="params.is_exact"
                            :options="columns"
                            @searchBy="params.filter = $event"
                            @isExact="params.is_exact = $event"
                        />
                    </div>
                    <div class="flex flex-row gap-1">
                        <nav-link :href="route('transactions.incoming')" class="flex items-center bg-blue-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                            <add-icon class="h-5 w-5" />
                            <span>Incoming</span>
                        </nav-link>
                        <nav-link :href="route('transactions.outgoing')" class="flex items-center bg-red-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                            <add-icon class="h-5 w-5" />
                            <span>Outgoing</span>
                        </nav-link>
                        <button
                            @click="deleteSelected"
                            v-if="selectedItem && selectedItem.length > 1"
                            class="flex items-center bg-red-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                            <delete-icon class="h-5 w-5" />
                            <span>
                                Delete Selected
                                <span class="text-xs">
                                    ({{ selectedItem.length }})
                                </span>
                            </span>
                        </button>
                    </div>
                    <data-table
                        :append-actions="true"
                        :base-model="Transaction"
                        :base-api-url="apiUrl"
                        :base-web-url="webUrl"
                        :params="params"
                        :selectable-rows="true"
                        @selectedDataRowChanged="selectedItemChange($event)"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
