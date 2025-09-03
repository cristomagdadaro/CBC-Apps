<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Item from "@/Pages/Inventory/Items/components/model/Item";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/presentation/ItemsHeaderActions.vue";

export default {
    name: "Items",
    components: {
        ItemsHeaderActions,
        AppLayout,
        SearchComp,
        DataTable,
        Head
    },
    computed: {
        DataTable() {
            return DataTable
        },
        Item() {
            return Item;
        },
    },
}
</script>

<template>
    <Head title="Items" />

    <AppLayout>
        <template #header>
            <items-header-actions />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <search-comp
                    :propModel="Item"
                    :cardSlot="DataTable"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
