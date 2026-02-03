<script>

import {Head} from "@inertiajs/vue3";
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import RequesterGuestCard from "@/Pages/LabRequest/components/RequesterGuestCard.vue";
import GuestFormPage from "@/Pages/Shared/GuestFormPage.vue";

export default {
    name: 'UseRequestFormGuest',
    components: {
        GuestFormPage,
        RequesterGuestCard,
        TransitionContainer, GuestCard,
        Head
    },
    props: {
        requestForm: { type: Object },
        quote: String,
    },
    data() {
        return {
            delayReady: false,
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
    <Head title="Facilities, Equipment, and Supplies Request Form" />
    <guest-form-page
        :title="'Facilities, Equipment, and Supplies Request Form'"
        :subtitle="'This form is intended for requesting access to and use of the facilities, equipment, and services provided by the DA-Crop Biotechnology Center. Please provide accurate and complete information to ensure a smooth processing of your request.'"
        :delay-ready="delayReady"
    >
        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="flex gap-5 flex-col w-full">
                <requester-guest-card :data="requestForm" />
            </div>
        </transition-container>
    </guest-form-page>
</template>
