<script>
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import SearchComp from "@/Components/Search/SearchComp.vue";
import RentalVehicle from '@/Modules/domain/RentalVehicle';
import RentalVenue from "@/Modules/domain/RentalVenue";
import RentalsHeaderAction from '@/Pages/Rentals/components/RentalsHeaderAction.vue';

export default {
    name: 'RentalsVehicleIndex',
    components: {
        SearchComp,
        RentalsHeaderAction,
        DataTable,
    },
    computed: {
        DataTable() {
            return DataTable
        },
        RentalVehicle() {
            return RentalVehicle
        },
    },
};
</script>

<template>
    <Head title="Rental Services" />

    <AppLayout>
        <template #header>
            <rentals-header-action />
        </template>

        <div class="default-container pt-5">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <search-comp
                    :propModel="RentalVehicle"
                    :cardSlot="DataTable"
                />
            </div>
        </div>
    </AppLayout>
</template>
