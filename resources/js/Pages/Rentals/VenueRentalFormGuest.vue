<script>
import VenueRentalForm from '@/Pages/Rentals/components/VenueRentalForm.vue';

export default {
    name: 'VenueRentalFormGuest',
    components: { VenueRentalForm },
    props: {
        venueOptions: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            delayReady: false,
            title: 'Venue Rental Form',
            subtitle: 'Please fill out the form below to rent a venue.',
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
                <venue-rental-form :venue-options="venueOptions" />
            </div>
        </transition-container>
    </GuestFormPage>
</template>
