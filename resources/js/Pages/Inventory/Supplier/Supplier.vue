<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import SearchBox from "@/Components/CRCMDatatable/Components/SearchBox.vue";
import SearchBy from "@/Components/CRCMDatatable/Components/SearchBy.vue";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import { Supplier } from "@/Pages/Supplier/components/model/Supplier.js";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import NavLink from "@/Components/NavLink.vue";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";

export default {
    name: "Supplier",
    components: {CloseIcon, NavLink, AddIcon, DataTable, SearchBy, SearchBox, Head, AuthenticatedLayout},
    computed: {
        Supplier() {
            return Supplier;
        },
        apiUrl() {
            return route('api.inventory.suppliers.index');
        },
        webUrl() {
            return route('suppliers.index');
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
            columns:  Supplier.getColumns()
                .map(column => column && column.visible ? { name: column.key, label: column.title } : null)
                .filter(Boolean)
        }
    },
    methods: {
        selectedItemChange(item) {
            this.selectedItem = item;
        }
    },
}
</script>

<template>
    <Head title="Suppliers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Registered Suppliers</h2>
        </template>
        <div class="py-4">
            <div class="flex flex-row gap-5 max-w-7xl mx-auto">
                <div class="flex flex-col gap-2 w-full px-2">
                    <div class="flex gap-3">
                        <search-box class="w-full" v-model="params.search" />
                        <search-by
                            :value="params.filter"
                            :is-exact="params.is_exact"
                            :options="columns"
                            @searchBy="params.filter = $event"
                            @isExact="params.is_exact = $event"
                        />
                    </div>
                    <div>
                        <nav-link :href="route('suppliers.create')" class="flex bg-green-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                            <add-icon class="h-5 w-5" />
                            <span class="ml-2">Add Supplier</span>
                        </nav-link>
                    </div>
                    <data-table
                        :selectable-rows="true"
                        :append-actions="true"
                        :base-model="Supplier"
                        :base-api-url="apiUrl"
                        :base-web-url="webUrl"
                        :params="params"
                        @selectedDataRowChanged="selectedItemChange($event)"
                    />
                </div>
                <div v-if="selectedItem" class="flex flex-col w-1/4 border bg-white rounded sm:p-4 p-2">
                    <div>
                        <close-icon @click="selectedItem = null" class="h-5 w-5 cursor-pointer float-right" />
                    </div>
                    <div>
                        <b>
                            ID:
                        </b>
                        <span>
                            {{ selectedItem.id }}
                        </span>
                    </div>
                    <div>
                        <b>
                            Name:
                        </b>
                        <span>
                        {{ selectedItem.name }}
                    </span>
                    </div>
                    <div>
                        <b>
                            Phone:
                        </b>
                        <span>
                            {{ selectedItem.phone }}
                        </span>
                    </div>
                    <div>
                        <b>
                            Email:
                        </b>
                        <span>
                            {{ selectedItem.email }}
                        </span>
                    </div>
                    <div>
                        <b>
                            Address:
                        </b>
                        <span>
                            {{ selectedItem.address }}
                        </span>
                    </div>
                    <div>
                        <b>
                            Description:
                        </b>
                        <span>
                            {{ selectedItem.description }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
