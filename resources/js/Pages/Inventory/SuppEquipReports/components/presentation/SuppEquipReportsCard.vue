<script>
import SuppEquipReportsTable from "@/Pages/Inventory/SuppEquipReports/components/presentation/SuppEquipReportsTable.vue";

export default {
    name: "SuppEquipReportsCard",
    components: { SuppEquipReportsTable },
    props: {
        apiResponse: {
            type: Object,
            required: false,
            default: null,
        },
        processing: {
            type: Boolean,
            default: false,
        },
        model: {
            type: Function,
            required: true,
        },
    },
    computed: {
        totalReports() {
            if (!this.apiResponse) return 0;
            if (typeof this.apiResponse.total !== "undefined") return this.apiResponse.total;
            if (Array.isArray(this.apiResponse.data)) return this.apiResponse.data.length;
            return 0;
        },
        hasResults() {
            if (!this.apiResponse) return false;
            if (Array.isArray(this.apiResponse.data)) return this.apiResponse.data.length > 0;
            if (typeof this.apiResponse.total !== "undefined") return this.apiResponse.total > 0;
            return false;
        },
    },
};
</script>

<template>
    <section class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-800">Filed Reports</h3>
                <p class="text-xs text-gray-500">Review every report linked to supply and equipment transactions.</p>
            </div>
            <p class="text-sm text-gray-500">Total records: <span class="font-semibold text-gray-700">{{ totalReports }}</span></p>
        </div>
        <div v-if="!apiResponse" class="border border-dashed border-gray-200 rounded-md p-4 text-sm text-gray-500 bg-gray-50">
            Run a search to load supplies and equipment reports.
        </div>
        <div v-else>
            <supp-equip-reports-table :api-response="apiResponse" :processing="processing" :model="model" />
            <p v-if="!hasResults" class="text-sm text-center text-gray-500 mt-4">No reports found for the current filters.</p>
        </div>
    </section>
</template>

<style scoped>

</style>
