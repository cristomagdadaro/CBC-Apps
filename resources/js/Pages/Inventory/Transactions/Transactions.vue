<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Components/DataTable/presentation/DataTable.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/presentation/TransactionHeaderAction.vue";

export default {
    name: "Transaction",
    components: {
        TransactionHeaderAction,
        SearchComp,
        AppLayout,
        DataTable,
        Head
    },
    computed: {
        DataTable() {
            return DataTable
        },
        Transaction() {
            return Transaction
        },
    },
}
</script>

<template>
    <Head title="Transactions" />

    <AppLayout>
        <template #header>
            <transaction-header-action />
        </template>

        <div class="py-5">
            <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
                <search-comp
                    :propModel="Transaction"
                    :cardSlot="DataTable"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
