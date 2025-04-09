<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import NavLink from "@/Components/NavLink.vue";
import SearchBox from "@/Pages/Scan/components/searchBox.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import SearchBy from "@/Components/DataTable/presentation/components/SearchBy.vue";
import {Personnel} from "@/Pages/Personnel/components/model/Personnel.js";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";

export default {
    name: "Personnel",
    components: {CloseIcon, SearchBy, AddIcon, SearchBox, NavLink, DataTable, Head, AuthenticatedLayout},
    computed: {
        Personnel() {
            return Personnel;
        },
        apiUrl() {
            return route('api.inventory.personnels.index');
        },
        webUrl() {
            return route('personnels.index');
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
            columns: Personnel.getColumns()
                .map(column => column && column.visible ? { name: column.key, label: column.title } : null)
                .filter(Boolean),
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
    <Head title="Items" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Registered Personnel</h2>
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
                        <nav-link :href="route('personnels.create')" class="flex bg-green-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                            <add-icon class="h-5 w-5" />
                            <span>Add</span>
                        </nav-link>
                    </div>
                    <data-table
                        :selectable-rows="true"
                        :append-actions="true"
                        :base-model="Personnel"
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
                        {{ selectedItem.fullname }}
                    </span>
                    </div>
                    <div>
                        <b>
                            Position:
                        </b>
                            <span>
                            {{ selectedItem.position }}
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
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
