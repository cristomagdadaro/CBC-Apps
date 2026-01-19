<script>
import {Link} from "@inertiajs/vue3";
import AddButton from "@/Components/Buttons/AddButton.vue";
import ScanBtn from "@/Components/Buttons/ScanBtn.vue";

export default {
    name: "SuppEquipHeaderActions",
    components: {ScanBtn, Link, AddButton},
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
            Supplies and Equipment Reports
        </Link>
        <div class="flex justify-between items-center gap-2">
            <Link
                v-if="showBackButton"
                :href="route('suppEquipReports.index')"
                class="flex items-center gap-2 text-sm text-gray-600 hover:text-AB"
            >
                <span class="font-semibold">View Filed Reports</span>
            </Link>
            <Link
                v-if="showAttachButton"
                :href="route('suppEquipReports.create')"
                class="flex items-center gap-2 text-sm text-AB"
            >
                <scan-btn />
                <span class="font-semibold">Attach Report</span>
            </Link>
            <Link :href="route('transactions.index')" class="flex items-center gap-2 text-sm text-AB">
                <add-button />
                <span class="font-semibold">Open Transaction History</span>
            </Link>
        </div>
    </div>
</template>

<style scoped>

</style>
