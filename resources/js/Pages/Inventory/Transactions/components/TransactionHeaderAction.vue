<script>
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";
import AttachReportLink from "@/Pages/Inventory/Transactions/components/presentation/AttachReportLink.vue";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";

export default {
    name: "TransactionHeaderAction",
    components: {IncommingTransactionLink, OutgoingTransactionLink, AttachReportLink, CreatePersonnelLink},
}
</script>

<template>
    <ActionHeaderLayout title="Transaction History" subtitle="Track inventory movements and generate reports." :route-link="route('transactions.index')">
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
        <CreatePersonnelLink />
        <AttachReportLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
