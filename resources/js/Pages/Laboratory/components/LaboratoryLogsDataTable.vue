<script>
export default {
    name: 'LaboratoryLogsDataTable',
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
