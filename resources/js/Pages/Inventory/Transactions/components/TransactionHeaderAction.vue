<script>
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";
import InventoryDashboardLink from "@/Pages/Inventory/Transactions/components/presentation/InventoryDashboardLink.vue";
import AttachReportLink from "@/Pages/Inventory/Transactions/components/presentation/AttachReportLink.vue";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";
import RecountingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/RecountingTransactionLink.vue";

export default {
    name: "TransactionHeaderAction",
    components: {IncommingTransactionLink, OutgoingTransactionLink, InventoryDashboardLink, AttachReportLink, CreatePersonnelLink, RecountingTransactionLink},
}
</script>

<template>
    <ActionHeaderLayout title="Transaction History" subtitle="Track inventory movements and generate reports." :route-link="route('transactions.index')">
        <InventoryDashboardLink />
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
        <RecountingTransactionLink />
        <CreatePersonnelLink />
        <AttachReportLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
