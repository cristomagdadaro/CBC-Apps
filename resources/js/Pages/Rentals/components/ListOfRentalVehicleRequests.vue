<script>
import RentalVehicleApprovalCard from "@/Pages/Rentals/components/RentalVehicleApprovalCard.vue";

export default {
    name: "ListOfRentalVehicleRequests",
    components: {
        RentalVehicleApprovalCard,
    },
    props: {
        rentalsData: {
            type: Array,
            default: () => [],
        },
        vehicleOptions: {
            type: Array,
            default: () => [],
        },
    },
};
</script>

<template>
    <div class="grid xl:grid-cols-3 lg:grid-cols-2 grid-flow-row gap-2 p-2">
        <rental-vehicle-approval-card
            v-for="data in rentalsData"
            :key="data.id"
            :data="data"
            :vehicle-options="vehicleOptions"
            @updated="$emit('updated', $event)"
            @failedUpdate="$emit('failedUpdate', $event)"
        />
    </div>
</template>
