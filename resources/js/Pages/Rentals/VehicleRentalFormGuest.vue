<script>
import VehicleRentalForm from '@/Pages/Rentals/components/VehicleRentalForm.vue';

export default {
    name: 'VehicleRentalFormGuest',
    components: { VehicleRentalForm },
    props: {
        vehicleOptions: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            delayReady: false,
            title: 'Vehicle Rental Form',
            subtitle: 'Please refer to the official dispatch as guide for your input. ',
        }
    },
    mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    }
};
</script>

<template>
    <Head :title="title" />
    <GuestFormPage
        :title="title"
        :subtitle="subtitle"
        guide-key="rental-vehicle-guest"
        :delay-ready="delayReady"
        >
        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="flex gap-5 flex-col w-full">
                <vehicle-rental-form :vehicle-options="vehicleOptions" />
            </div>
        </transition-container>
    </GuestFormPage>
</template>
