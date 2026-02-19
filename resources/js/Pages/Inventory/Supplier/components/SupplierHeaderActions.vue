<script>
import {Link} from "@inertiajs/vue3";
import CreateSupplierLink from "@/Pages/Inventory/Transactions/components/presentation/CreateSupplierLink.vue";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";

export default {
    name: "SupplierHeaderActions",
    components: {Link, CreateSupplierLink, CreatePersonnelLink, IncommingTransactionLink, OutgoingTransactionLink}
}
</script>

<template>
    <ActionHeaderLayout title="Registered Suppliers" subtitle="Manage suppliers and track their transactions." :route-link="route('suppliers.index')">
        <CreateSupplierLink />
        <CreatePersonnelLink />
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
