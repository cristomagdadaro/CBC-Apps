
<script>
import {Link} from "@inertiajs/vue3";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";

export default {
    name: "PersonnelHeaderActions",
    components: {Link, CreatePersonnelLink, IncommingTransactionLink, OutgoingTransactionLink}
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('personnels.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Registered Personnel
        </Link>
        <div class="flex justify-between items-center gap-1">
            <CreatePersonnelLink />
            <IncommingTransactionLink />
            <OutgoingTransactionLink />
        </div>
    </div>
</template>

<style scoped>

</style>
