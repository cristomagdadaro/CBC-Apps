<script>
import {Head} from "@inertiajs/vue3";
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import Personnel from "@/Pages/Inventory/Personnel/components/model/Personnel";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/presentation/PersonnelHeaderActions.vue";

export default {
    name: "Personnel",
    components: {
        PersonnelHeaderActions, SearchComp, AppLayout, Head, DataTable
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

        <div class="py-12">
            <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8">
                <search-comp
                    :propModel="Personnel"
                    :cardSlot="DataTable"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
