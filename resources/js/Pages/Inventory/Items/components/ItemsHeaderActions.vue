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
    <div class="flex justify-between items-center">
        <Link :href="route('items.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            List of Items
        </Link>
        <div class="flex justify-between items-center gap-1">
            <CreateItemLink />
            <BarcodePrintingLink />
            <CreatePersonnelLink />
            <CreateSupplierLink />
            <IncommingTransactionLink />
            <OutgoingTransactionLink />
        </div>
    </div>
</template>

<style scoped>

</style>
