<script>
import RentalVenueApprovalCard from "@/Pages/Rentals/components/RentalVenueApprovalCard.vue";

export default {
    name: "ListOfRentalVenueRequests",
    components: {
        RentalVenueApprovalCard,
    },
    props: {
        rentalsData: {
            type: Array,
            default: () => [],
        },
    },
};
</script>

<template>
    <div class="grid xl:grid-cols-3 lg:grid-cols-2 grid-flow-row gap-2 p-2">
        <rental-venue-approval-card
            v-for="data in rentalsData"
            :key="data.id"
            :data="data"
            @updated="$emit('updated', $event)"
            @failedUpdate="$emit('failedUpdate', $event)"
        />
    </div>
</template>
