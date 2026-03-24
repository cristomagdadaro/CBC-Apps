<script>
import SuppEquipReport from '@/Modules/domain/SuppEquipReport';

export default {
    name: "SuppEquipReportsCard",
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
        SuppEquipReport() {
            return SuppEquipReport;
        },
    },
};
</script>

<template>
    
</template>

<style scoped>

</style>
