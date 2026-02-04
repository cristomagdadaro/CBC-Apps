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
    <div class="flex justify-between items-center">
        <Link :href="route('suppEquipReports.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Report Attachments
        </Link>
        <div class="flex justify-between items-center gap-1">
            <AttachReportLink />
            <IncommingTransactionLink />
            <OutgoingTransactionLink />
        </div>
    </div>
</template>

<style scoped>

</style>
