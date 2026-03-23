<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Supplier from "@/Modules/domain/Supplier";
import SupplierHeaderActions from "@/Pages/Inventory/Supplier/components/SupplierHeaderActions.vue";
import CRCMDatatable from '@/Components/CRCMDatatable/CRCMDatatable.vue';

export default {
    name: "Supplier",
    components: {
        SupplierHeaderActions,
        SearchComp, DataTable, Head, CRCMDatatable
    },
    computed: {
        DataTable() {
            return DataTable
        },
        Supplier() {
            return Supplier;
        }
    },
}
</script>

<template>
    <Head title="Suppliers" />

    <AppLayout>
        <template #header>
            <supplier-header-actions />
        </template>

        <CRCMDatatable :base-model="Supplier" :can-view="true" :can-create="false" :can-update="true" :can-delete="true" />
    </AppLayout>
</template>

<style scoped>

</style>
