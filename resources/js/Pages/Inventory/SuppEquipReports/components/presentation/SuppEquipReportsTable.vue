<script>
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";

export default {
    name: 'SuppEquipReportsTable',
    components: { DataTable },
    props: {
        apiResponse: {
            type: Object,
            required: false,
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
}
</script>

<template>
    <data-table
        :api-response="apiResponse"
        :processing="processing"
        :model="model"
        :append-actions="true"
        :enable-export="false"
    />
</template>
