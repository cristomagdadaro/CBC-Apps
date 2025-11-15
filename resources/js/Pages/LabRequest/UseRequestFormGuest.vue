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
    <Head title="Access and Use Request Form" />

    <guest-form-page
        :title="'Access and Use Request Form'"
        :subtitle="'Kindly fill out the form below to file a request'"
        :delay-ready="delayReady"
    >
        <template #top>
            <transition-container :duration="500" type="pop-out">
                <div v-show="delayReady"  class="border select-none md:rounded-md flex flex-col gap-2 bg-gray-100 w-full drop-shadow-lg">
                    <div class="relative flex flex-row bg-AB text-white p-2 px-4 rounded-md gap-2 shadow py-4">
                        <img src="/imgs/logo.png" alt="logo" class="w-auto h-16" />
                        <div class="flex flex-col justify-center">
                            <label class="leading-none font-semibold text-xl">Access and Use Request Form</label>
                            <p class="text-sm leading-none">
                                Kindly fill out the form below to file a request
                            </p>
                        </div>
                    </div>
                </div>
            </transition-container>
        </template>

        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="flex gap-5 flex-col">
                <requester-guest-card :data="requestForm" />
                <label class="text-[0.6rem] leading-none select-none">Form from URL</label>
            </div>
        </transition-container>
    </guest-form-page>
</template>
