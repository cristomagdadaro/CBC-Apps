<script>
import {Head} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import SuppEquipReport from "@/Pages/Inventory/SuppEquipReports/components/model/SuppEquipReport";
import SuppEquipReportForm from "@/Pages/Inventory/SuppEquipReports/components/presentation/SuppEquipReportForm.vue";
import SuppEquipReportsTable from "@/Pages/Inventory/SuppEquipReports/components/presentation/SuppEquipReportsTable.vue";
import SuppEquipHeaderActions from "./components/presentation/SuppEquipHeaderActions.vue";

export default {
    name: "SuppEquipReportsIndex",
    components: {
        SuppEquipHeaderActions,
        SuppEquipReportForm,
        SuppEquipReportsTable,
        SearchComp,
        AppLayout,
        Head
    },
    props: {
        reportTemplates: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            lastSearchMeta: null,
        };
    },
    computed: {
        SuppEquipReport() {
            return SuppEquipReport;
        },
        totalReports() {
            if (!this.lastSearchMeta) return 0;
            if (typeof this.lastSearchMeta.total !== 'undefined') {
                return this.lastSearchMeta.total;
            }
            if (Array.isArray(this.lastSearchMeta.data)) {
                return this.lastSearchMeta.data.length;
            }
            return 0;
        },
    },
    methods: {
        handleSearch(response) {
            this.lastSearchMeta = response;
        },
        refreshReports() {
            this.$refs.reportSearch?.searchEvent?.();
        },
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
                <supp-equip-report-form
                    :report-templates="reportTemplates"
                    @saved="refreshReports"
                />

                <section class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-100 p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">Filed Reports</h3>
                            <p class="text-xs text-gray-500">Review every report linked to supply and equipment transactions.</p>
                        </div>
                        <p class="text-sm text-gray-500">Total records: <span class="font-semibold text-gray-700">{{ totalReports }}</span></p>
                    </div>
                    <search-comp
                        ref="reportSearch"
                        :propModel="SuppEquipReport"
                        :cardSlot="SuppEquipReportsTable"
                        @searchedData="handleSearch"
                    />
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
