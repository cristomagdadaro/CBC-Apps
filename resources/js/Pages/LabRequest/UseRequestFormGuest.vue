<script>
import RequesterGuestCard from "@/Pages/LabRequest/components/RequesterGuestCard.vue";

export default {
    name: 'UseRequestFormGuest',
    components: {
        RequesterGuestCard,
    },
    props: {
        requestForm: { type: Object },
        quote: String,
    },
    data() {
        return {
            delayReady: false,
            title: 'Facilities, Equipment, and Supplies Request Form',
            subtitle: 'This form is intended for requesting access to and use of the facilities, equipment, and services provided by the DA-Crop Biotechnology Center. Please provide accurate and complete information to ensure a smooth processing of your request.',
        }
    },
    mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    }
}
</script>

<template>
    <Head :title="title" />
    <guest-form-page
        :title="title"
        :subtitle="subtitle"
        :delay-ready="delayReady"
    >
        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="flex gap-5 flex-col w-full">
                <requester-guest-card :data="requestForm" />
            </div>
        </transition-container>
    </guest-form-page>
</template>
