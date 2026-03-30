<script>
import { Head } from "@inertiajs/vue3";
import SuppEquipHeaderActions from "./components/SuppEquipHeaderActions.vue";
import SuppEquipReportForm from "@/Pages/Inventory/SuppEquipReports/components/SuppEquipReportForm.vue";

export default {
    name: "SuppEquipReportsFormPage",
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
        mode: {
            type: String,
            default: 'create',
        },
        data: {
            type: Object,
            default: null,
        },
    },
};
</script>

<template>
    <Head :title="mode === 'update' ? 'Update Supplies and Equipment Report' : 'Attach Supplies and Equipment Report'" />

    <AppLayout>
        <template #header>
            <supp-equip-header-actions :show-attach-button="false" :show-back-button="true" />
        </template>
        <div class="py-10">
            <div class="max-w-4xl mx-auto space-y-6 px-4">
                <supp-equip-report-form :report-templates="reportTemplates" :mode="mode" :data="data" />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
