<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Supplier from "@/Pages/Inventory/Supplier/components/model/Supplier";
import SupplierHeaderActions from "@/Pages/Inventory/Supplier/components/presentation/SupplierHeaderActions.vue";

export default {
    name: "Supplier",
    components: {
        SupplierHeaderActions,
        SearchComp, AppLayout, DataTable, Head
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <search-comp
                    :propModel="Supplier"
                    :cardSlot="DataTable"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
