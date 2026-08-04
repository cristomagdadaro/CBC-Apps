<script>
import {Link} from "@inertiajs/vue3";
import CreateItemLink from "@/Pages/Inventory/Transactions/components/presentation/CreateItemLink.vue";
import CreateSupplierLink from "@/Pages/Inventory/Transactions/components/presentation/CreateSupplierLink.vue";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";
import BarcodePrintingLink from "@/Pages/Inventory/Transactions/components/presentation/BarcodePrintingLink.vue";

export default {
    name: "ItemsHeaderActions",
    components: {Link, CreateItemLink, CreateSupplierLink, CreatePersonnelLink, IncommingTransactionLink, OutgoingTransactionLink, BarcodePrintingLink},
}
</script>

<template>
    <ActionHeaderLayout title="Inventory Items" subtitle="Manage your inventory items and related actions." :route-link="route('items.index')">
        <CreateItemLink />
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
