<script>
import DataTable from "@/Modules/DataTable/presentation/DataTable.vue";
import RentalVehicle from '@/Modules/domain/RentalVehicle';
import RentalsHeaderAction from '@/Pages/Rentals/components/RentalsHeaderAction.vue';

export default {
    name: 'RentalsIndex',
    components: {
        RentalsHeaderAction,
    },
    computed: {
        DataTable() {
            return DataTable
        },
        RentalVehicle() {
            return RentalVehicle;
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

        <div class="py-5">
            <div class="max-w-[90%] mx-auto sm:px-6 lg:px-8">
                <search-comp
                    :propModel="RentalVehicle"
                    :cardSlot="DataTable"
                />
            </div>
        </div>
    </AppLayout>
</template>
