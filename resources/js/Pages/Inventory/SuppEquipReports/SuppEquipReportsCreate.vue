<script>
import { Head } from "@inertiajs/vue3";
import SuppEquipHeaderActions from "./components/SuppEquipHeaderActions.vue";
import SuppEquipReportForm from "@/Pages/Inventory/SuppEquipReports/components/SuppEquipReportForm.vue";

export default {
    name: "SuppEquipReportsCreate",
    components: {
        SuppEquipReportForm,
        SuppEquipHeaderActions,
        Head,
    },
    props: {
        reportTemplates: {
            type: Object,
            required: true,
        },
    },
};
</script>

<template>
    <Head title="Attach Supplies and Equipment Report" />

    <AppLayout>
        <template #header>
            <supp-equip-header-actions :show-attach-button="false" :show-back-button="true" />
        </template>

        <div class="py-10">
            <div class="max-w-4xl mx-auto space-y-6 px-4">
                <supp-equip-report-form :report-templates="reportTemplates" />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
