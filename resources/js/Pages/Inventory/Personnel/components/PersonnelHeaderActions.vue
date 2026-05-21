
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
        <Link :href="route('personnels.registrations.index')" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">
            Registration Approvals
        </Link>
        <CreatePersonnelLink />
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
