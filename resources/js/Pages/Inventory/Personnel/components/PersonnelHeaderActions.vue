
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
    <ActionHeaderLayout title="Registered Personnel" subtitle="Manage personnel and track their transactions." :route-link="route('personnels.index')">
        <CreatePersonnelLink />
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
