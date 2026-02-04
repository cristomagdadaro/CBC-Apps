<script>
import {Link} from "@inertiajs/vue3";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";
import AttachReportLink from "@/Pages/Inventory/Transactions/components/presentation/AttachReportLink.vue";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";

export default {
    name: "TransactionHeaderAction",
    components: {IncommingTransactionLink, OutgoingTransactionLink, Link, AttachReportLink, CreatePersonnelLink},
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('transactions.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Transaction History
        </Link>
        <div class="flex justify-between items-center gap-1">
            <IncommingTransactionLink />
            <OutgoingTransactionLink />
            <CreatePersonnelLink />
            <AttachReportLink />
        </div>
    </div>
</template>

<style scoped>

</style>
