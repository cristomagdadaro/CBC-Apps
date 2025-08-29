<script>
import UseRequestCard from "@/Pages/LabRequest/components/UseRequestCard.vue";
import EventCard from "@/Pages/Forms/components/EventCard.vue";

export default {
    name: "ListOfUseRequests",
    components: {EventCard, UseRequestCard},
    props: {
        formsData: Array,
    },
    methods: {
        handleDelete(deletedModel) {
            this.$emit("removeModel", deletedModel);
        },
    },
}
</script>

<template>
    <div class="grid grid-cols-2 gap-2 p-2">
        <use-request-card
            v-for="data in formsData"
            :key="data.id"
            :data="data"
            @deletedModel="handleDelete"
        />
    </div>
</template>

<style scoped>

</style>
