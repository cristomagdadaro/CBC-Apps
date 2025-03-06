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
    <div class="flex flex-wrap gap-2">
        <event-card v-for="data in formsData" :data="data" />
    </div>
</template>

<style scoped>

</style>
