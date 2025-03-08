<script>
import {useForm} from "@inertiajs/vue3";
import EventCard from "@/Pages/Forms/components/EventCard.vue";

export default {
    name: "ListOfForms",
    components: {EventCard},
    props: {
        formsData: Object,
    },
    computed: {
        columnsList() {
            return Object.keys(this.formsData[0]);
        },
    }
}
</script>

<template>
    <div class="grid lg:grid-cols-2 grid-cols-1 gap-2 p-2">
        <event-card v-for="data in formsData" :data="data" />
    </div>
</template>

<style scoped>

</style>
