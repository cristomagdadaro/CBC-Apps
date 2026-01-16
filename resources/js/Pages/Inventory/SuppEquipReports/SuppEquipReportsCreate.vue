<script>
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import SuppEquipHeaderActions from "./components/presentation/SuppEquipHeaderActions.vue";
import SuppEquipReportForm from "@/Pages/Inventory/SuppEquipReports/components/presentation/SuppEquipReportForm.vue";

export default {
    name: "SuppEquipReportsCreate",
    components: {
        SuppEquipReportForm,
        SuppEquipHeaderActions,
        AppLayout,
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
                <section class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-100 p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800">Attach a Structured Report</h2>
                    <p class="text-sm text-gray-500 mt-1">Link a supplies or equipment incident to a transaction for faster auditing and compliance reviews.</p>
                </section>

                <supp-equip-report-form :report-templates="reportTemplates" />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
