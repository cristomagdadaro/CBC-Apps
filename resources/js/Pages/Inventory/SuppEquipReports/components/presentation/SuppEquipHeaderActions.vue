<script>
import {Link} from "@inertiajs/vue3";
import AddButton from "@/Components/Buttons/AddButton.vue";
import ScanBtn from "@/Components/Buttons/ScanBtn.vue";

export default {
    name: "SuppEquipHeaderActions",
    components: {ScanBtn, Link, AddButton}
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('suppEquipReports.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Supplies and Equipment Reports
        </Link>
        <div class="flex justify-between items-center gap-2">
            <Link :href="route('transactions.index')" class="flex items-center gap-2 text-sm text-AB">
                <add-button />
                <span class="font-semibold">Open Transaction History</span>
            </Link>
        </div>
    </div>
</template>

<style scoped>

</style>
