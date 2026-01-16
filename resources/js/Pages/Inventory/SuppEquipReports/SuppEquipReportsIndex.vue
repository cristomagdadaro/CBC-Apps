<script>
import {Head} from "@inertiajs/vue3";
import {markRaw} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import SuppEquipReport from "@/Pages/Inventory/SuppEquipReports/components/model/SuppEquipReport";
import SuppEquipReportsCard from "@/Pages/Inventory/SuppEquipReports/components/presentation/SuppEquipReportsCard.vue";
import SuppEquipHeaderActions from "./components/presentation/SuppEquipHeaderActions.vue";

export default {
    name: "SuppEquipReportsIndex",
    components: {
        SuppEquipHeaderActions,
        SuppEquipReportsCard,
        SearchComp,
        AppLayout,
        Head
    },
    data() {
        return {
            reportModel: markRaw(SuppEquipReport),
            reportsCard: markRaw(SuppEquipReportsCard),
        };
    },
}
</script>

<template>
    <Head title="Supplies and Equipment Reports" />

    <AppLayout>
        <template #header>
            <supp-equip-header-actions />
        </template>

        <div class="py-10">
            <div class="max-w-6xl mx-auto space-y-8 px-4">
                <search-comp
                    :propModel="reportModel"
                    :cardSlot="reportsCard"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
