<script>
import SuppEquipHeaderActions from "./components/SuppEquipHeaderActions.vue";
import SuppEquipReport from '@/Modules/domain/SuppEquipReport';

export default {
    name: "SuppEquipReportsIndex",
    components: {
        SuppEquipHeaderActions,
    },
    computed: {
        SuppEquipReport() {
            return SuppEquipReport;
        },
    }
}
</script>

<template>
    <Head title="Report Attachment" />

    <AppLayout>
        <template #header>
            <supp-equip-header-actions />
        </template>

        <CRCMDatatable :base-model="SuppEquipReport" :can-view="true" :can-create="true" :can-update="true" :can-delete="true" />
    </AppLayout>
</template>

<style scoped>

</style>
