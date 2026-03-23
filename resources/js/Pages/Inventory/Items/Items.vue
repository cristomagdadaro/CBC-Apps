<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Item from "@/Modules/domain/Item";
import ItemsHeaderActions from "@/Pages/Inventory/Items/components/ItemsHeaderActions.vue";
import CRCMDatatable from '@/Components/CRCMDatatable/CRCMDatatable.vue';

export default {
    name: "Items",
    components: {
        ItemsHeaderActions,
        SearchComp,
        DataTable,
        Head,
        CRCMDatatable,
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

        <CRCMDatatable :base-model="Item" :can-view="true" :can-create="false" :can-update="true" :can-delete="true" />
    </AppLayout>
</template>

<style scoped>

</style>
