<script>
import {Link} from "@inertiajs/vue3";
import IncommingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/IncommingTransactionLink.vue";
import OutgoingTransactionLink from "@/Pages/Inventory/Transactions/components/presentation/OutgoingTransactionLink.vue";
import AttachReportLink from "../../Transactions/components/presentation/AttachReportLink.vue";

export default {
    name: "SuppEquipHeaderActions",
    components: {Link, IncommingTransactionLink, OutgoingTransactionLink, AttachReportLink},
    props: {
        showAttachButton: {
            type: Boolean,
            default: true,
        },
        showBackButton: {
            type: Boolean,
            default: false,
        },
    },
}
</script>

<template>
    <ActionHeaderLayout title="Report Attachments" subtitle="Manage your report attachments and related actions." :route-link="route('suppEquipReports.index')">
        <AttachReportLink v-if="showAttachButton" />
        <IncommingTransactionLink />
        <OutgoingTransactionLink />
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
