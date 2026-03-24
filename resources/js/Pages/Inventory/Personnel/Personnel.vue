<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Personnel from "@/Modules/domain/Personnel";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/PersonnelHeaderActions.vue";
import CRCMDatatable from '@/Components/CRCMDatatable/CRCMDatatable.vue';

export default {
    name: "Personnel",
    components: {
        PersonnelHeaderActions, SearchComp, Head, DataTable, CRCMDatatable
    },
    computed: {
        DataTable() {
            return DataTable
        },
        Personnel() {
            return Personnel;
        },
    },
}
</script>

<template>
    <Head title="Items" />

    <AppLayout>
        <template #header>
            <personnel-header-actions />
        </template>

        <CRCMDatatable :base-model="Personnel" :can-view="true" :can-create="false" :can-update="true" :can-delete="true" />
    </AppLayout>
</template>

<style scoped>

</style>
