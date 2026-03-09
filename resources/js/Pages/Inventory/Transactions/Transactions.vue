<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Transaction from "@/Modules/domain/Transaction";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";

export default {
    name: "Transaction",
    components: {
        TransactionHeaderAction,
        SearchComp,
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
        transactionTypeOptions() {
            return [
                { name: 'incoming', label: 'Incoming' },
                { name: 'outgoing', label: 'Outgoing' },
            ];
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

        <div class="default-container pt-5">
            <search-comp
                :propModel="Transaction"
                :cardSlot="DataTable"
                :quick-filter-options="transactionTypeOptions"
                quick-filter-column="transac_type"
                quick-filter-label="Type"
            />
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
