<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import SearchBox from "@/Pages/Scan/components/searchBox.vue";
import {Item} from "@/Pages/Items/components/model/Item.js";
import SearchBy from "@/Components/DataTable/presentation/components/SearchBy.vue";
import NavLink from "@/Components/NavLink.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";

export default {
    name: "Items",
    components: {CloseIcon, AddIcon, NavLink, SearchBy, SearchBox, DataTable, Head, AuthenticatedLayout},
    computed: {
        Item() {
            return Item;
        },
        apiUrl() {
            return route('api.inventory.items.index');
        },
        webUrl() {
            return route('items.index');
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
            columns: Item.getColumns()
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Registered Items</h2>
        </template>
        <div class="py-4">
            <div class="flex flex-row gap-5 max-w-7xl mx-auto">
                <div class="flex flex-col gap-2 w-full px-2">
                    <div class="flex sm:gap-3 gap-2">
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
                        <nav-link :href="route('items.create')" class="flex items-center bg-green-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                            <add-icon class="h-5 w-5" />
                            <span>Add Item</span>
                        </nav-link>
                    </div>
                    <data-table
                        :append-actions="true"
                        :base-model="Item"
                        :base-api-url="apiUrl"
                        :base-web-url="webUrl"
                        :params="params"
                        @selectedDataRowChanged="selectedItemChange($event)"
                    />

                </div>
                <div v-if="selectedItem" class="flex flex-col gap-2 border-l max-w-80 border-gray-400 pl-5">
                    <div class="flex flex-row  items-center justify-between text-gray-600 text-sm">
                        <span>Selected Item</span>
                        <close-icon @click="selectedItem = null" class="text-red-500 h-6 w-6 hover:scale-110 active:scale-100" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="relative">
                            <div class="flex gap-2">
                                <div class="text-gray-600 font-bold">Name:</div>
                                <div>{{ selectedItem.name }}</div>
                            </div>
                            <div class="flex gap-2">
                                <div class="text-gray-600 font-bold">Brand:</div>
                                <div>{{ selectedItem.brand }}</div>
                            </div>
                            <div class="flex gap-2">
                                <div class="text-gray-600 font-bold">Category:</div>
                                <div>{{ selectedItem.category_id }}</div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="text-gray-600 font-bold">Description:</div>
                                <div>{{ selectedItem.description }}</div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="text-gray-600 font-bold">Image:</div>
                                <div>
                                    <img v-if="selectedItem.image" :src="selectedItem.image" class="shadow rounded" alt="image">
                                    <span v-else class="text-gray-500 italic">No image</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
