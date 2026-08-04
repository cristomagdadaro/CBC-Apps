<script>
import {Link} from "@inertiajs/vue3";
import CreatePersonnelLink from "@/Pages/Inventory/Transactions/components/presentation/CreatePersonnelLink.vue";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";
import PersonnelRegistrationLink from "@/Pages/Inventory/Transactions/components/presentation/PersonnelRegistrationLink.vue";
import PrintApprovedIdsLink from "@/Pages/Inventory/Transactions/components/presentation/PrintApprovedIdsLink.vue";

export default {
    name: "PersonnelHeaderActions",
    components: {Link, CreatePersonnelLink, IncommingTransactionLink, OutgoingTransactionLink, PersonnelRegistrationLink, PrintApprovedIdsLink}
}
</script>

<template>
    <ActionHeaderLayout title="Registered Personnel" subtitle="Manage personnel and track their transactions." :route-link="route('personnels.index')">
        <PersonnelRegistrationLink />
        <CreatePersonnelLink />
        <PrintApprovedIdsLink />
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
