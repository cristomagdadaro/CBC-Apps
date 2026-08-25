<script>
import EventCard from "@/Pages/Forms/components/EventCard.vue";

export default {
    name: "ListOfForms",
    components: { EventCard },
    props: {
        formsData: Array,
    },
    methods: {
        handleDelete(deletedModel) {
            this.$emit("removeModel", deletedModel);
        },
    },
};
</script>

<template>
    <div class="grid grid-cols-1 gap-2 p-2 sm:grid-cols-2 md:grid-cols-3 md:p-4 lg:grid-cols-4">
        <event-card
            v-for="data in formsData"
            :key="data.id"
            :data="data"
            @deletedModel="handleDelete" />
    </div>
</template>
