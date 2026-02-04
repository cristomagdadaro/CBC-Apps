<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Item from "@/Modules/domain/Item";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";

export default {
    name: "Items",
    components: {
        ItemsHeaderActions,
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
            <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8">
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
