<script>
import RequesterGuestCard from "@/Pages/LabRequest/components/RequesterGuestCard.vue";

export default {
    name: 'UseRequestFormGuest',
    components: {
        RequesterGuestCard,
    },
    props: {
        requestForm: { type: Object },
        requestTypeOptions: {
            type: Array,
            default: () => []
        },
        quote: String,
    },
    data() {
        return {
            delayReady: false,
            title: 'Facilities, Equipment, and Supplies Request Form',
            subtitle: 'This form is intended for requesting access and/or use of the facilities and equipment provided by the DA-CBC.',
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
        guide-key="fes-request-guest"
        max-width="max-w-3xl"
    >
        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div data-guide="fes-request-card" class="flex gap-5 flex-col w-full">
                <requester-guest-card :data="requestForm" :request-type-options="requestTypeOptions" />
            </div>
        </transition-container>
    </guest-form-page>
</template>
