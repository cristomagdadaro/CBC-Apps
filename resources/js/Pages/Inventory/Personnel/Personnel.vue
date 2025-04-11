<script>
import {Head} from "@inertiajs/vue3";
/*import DataTable from "@/Components/DataTable/presentation/DataTable.vue";*/
import NavLink from "@/Components/NavLink.vue";
import SearchBox from "@/Pages/Inventory/Scan/components/searchBox.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import SearchBy from "@/Components/DataTable/presentation/components/SearchBy.vue";
import Personnel from "@/Pages/Inventory/Personnel/components/model/Personnel";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import {defineAsyncComponent} from "vue";

export default {
    name: "Personnel",
    components: {SearchComp, AppLayout, CloseIcon, SearchBy, AddIcon, SearchBox, NavLink, Head},
    computed: {
        Personnel() {
            return Personnel;
        },
    },
    data() {
        return {
            selectedItem: null,
            apiResponse: null,
            cardSlot: defineAsyncComponent({
                loader: () => import("@/Pages/Inventory/Personnel/components/presentation/PersonnelList.vue"),
            })
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

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight uppercase">Registered Personnel</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <search-comp
                    :propModel="Personnel"
                    @searchedData="apiResponse = $event"
                    :cardSlot="cardSlot"
                />
                <div class="flex flex-col gap-2 w-full px-2">
                    <div>
                        <nav-link :href="route('personnels.create')" class="flex bg-green-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                            <add-icon class="h-5 w-5" />
                            <span>Add</span>
                        </nav-link>
                    </div>
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
    </AppLayout>
</template>

<style scoped>

</style>
