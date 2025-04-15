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
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/presentation/PersonnelHeaderActions.vue";

export default {
    name: "Personnel",
    components: {
        PersonnelHeaderActions,
        SearchComp, AppLayout, CloseIcon, SearchBy, AddIcon, SearchBox, NavLink, Head, DataTable},
    computed: {
        DataTable() {
            return DataTable
        },
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
            <personnel-header-actions />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <search-comp
                    :propModel="Personnel"
                    :cardSlot="DataTable"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
