<script>
import DataTable from '@/Modules/DataTable/presentation/DataTable.vue';

export default {
    name: 'LaboratoryLogsDataTable',
    components: { DataTable },
    props: {
        apiResponse: {
            type: Object,
            default: null,
        },
        processing: {
            type: Boolean,
            default: false,
        },
        model: {
            type: Function,
            default: null,
        },
    },
};
</script>

<template>
    <DataTable
        :api-response="apiResponse"
        :processing="processing"
        :model="model"
        :append-actions="true"
    />
</template>
