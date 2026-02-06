<script>
import VehicleRentalForm from '@/Pages/Rentals/components/VehicleRentalForm.vue';


export default {
    name: 'VehicleRentalFormGuest',
    components: { VehicleRentalForm },
    data() {
        return {
            delayReady: false,
            title: 'Vehicle Rental Form',
            subtitle: 'Please fill out the form below to rent a vehicle.',
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
        :delay-ready="delayReady"
        >
        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="flex gap-5 flex-col w-full">
                <vehicle-rental-form />
            </div>
        </transition-container>
    </GuestFormPage>
</template>
