<script>

import {Head} from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin.js";
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import UseReqGuestCard from "@/Pages/LabRequest/components/UseReqGuestCard.vue";

export default {
    name: 'UseRequestFormGuest',
    components: {
        LabReqGuestCard: UseReqGuestCard,
        TransitionContainer, GuestCard,
        Head
    },
    props: {
        requestForm: { type: Object },
        quote: String,
    },
    beforeMount() {
        //tis.model = new LabRequest();
        //this.setFormAction('create');
        //this.form.event_id = this.eventId;
    },
    methods: {
        submitForm() {
            this.$inertia.post('/lab-request', this.formData);
        }
    }
}
</script>

<template>
    <Head title="DA-CBC Forms" />

    <div class="absolute top-0 left-0 w-full h-full z-[999] flex justify-center">
        <div class="relative sm:flex flex-col gap-5 sm:justify-center sm:items-center min-h-screen">
            <div class="md:relative flex flex-col lg:gap-5 w-full">
                <div class="border select-none p-2 md:rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl w-full drop-shadow-lg">
                    <div class="relative flex flex-row bg-AB text-white p-2 px-4 rounded-md gap-2 shadow py-4">
                        <img src="/imgs/logo.png" alt="logo" class="w-auto h-16" />
                        <div class="flex flex-col justify-center">
                            <label class="leading-none font-semibold text-xl">Laboratory Request Form</label>
                            <p class="text-sm leading-none">
                                Kindly fill out the form below to file a request
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-5 md:flex-row flex-col">
                <transition-container :duration="1000" type="slide-bottom">
                    <div>
                        <lab-req-guest-card :data="requestForm" @createdModel="lastCreatedForm = $event" />
                        <label class="text-[0.6rem] leading-none select-none">Form from URL</label>
                    </div>
                </transition-container>
            </div>
        </div>
    </div>
    <div class="min-h-screen flex items-center justify-center text-white text-3xl font-bold relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-radial animate-gradient"></div>
    </div>
</template>

<style>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
